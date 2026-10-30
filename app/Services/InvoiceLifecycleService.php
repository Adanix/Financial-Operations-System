<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceLifecycleService
{
    /**
     * Issue a draft invoice, allocating sequential number and capturing snapshots.
     */
    public static function issue(Invoice $invoice, ?User $user = null): Invoice
    {
        if ($invoice->status !== 'draft') {
            throw new InvalidArgumentException("Only draft invoices can be issued. Current status: {$invoice->status}");
        }

        $customer = $invoice->customer;
        if (!$customer || !$customer->is_active) {
            throw new InvalidArgumentException("An active customer must be assigned to issue the invoice.");
        }

        if (empty(trim($customer->name)) || empty(trim($customer->billing_address))) {
            throw new InvalidArgumentException("Customer name and billing address are required to issue the invoice.");
        }

        $items = $invoice->items()->get();
        if ($items->isEmpty()) {
            throw new InvalidArgumentException("Invoice must contain at least one line item.");
        }

        $settings = BusinessSetting::current();
        if (empty($settings->business_name) || empty($settings->email)) {
            throw new InvalidArgumentException("Business profile (name and email) must be configured in settings.");
        }

        if (empty($settings->bank_name) || empty($settings->account_holder) || empty($settings->account_number)) {
            throw new InvalidArgumentException("Bank transfer details (bank name, account holder, account number) must be configured in settings.");
        }

        // Recalculate totals
        $linePayloads = [];
        foreach ($items as $item) {
            if (DecimalMath::comp($item->quantity, '0') <= 0) {
                throw new InvalidArgumentException("Quantity for all lines must be greater than zero.");
            }
            if (DecimalMath::comp($item->unit_price, '0') < 0) {
                throw new InvalidArgumentException("Unit price cannot be negative.");
            }

            $taxRateVal = $item->tax_rate;
            $taxNameVal = $item->tax_name;
            if ($item->tax_rate_id) {
                $trModel = \App\Models\TaxRate::find($item->tax_rate_id);
                if ($trModel) {
                    $taxRateVal = (string)$trModel->rate;
                    $taxNameVal = $trModel->name;
                    $item->update(['tax_rate' => $taxRateVal, 'tax_name' => $taxNameVal]);
                }
            }

            $linePayloads[] = [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'line_discount_amount' => $item->line_discount_amount,
                'tax_rate_id' => $item->tax_rate_id,
                'tax_name' => $taxNameVal,
                'tax_rate' => $taxRateVal ?: '0.0000',
            ];
        }

        $calc = InvoiceCalculator::calculate(
            $linePayloads,
            $invoice->pricing_mode ?: 'exclusive',
            $invoice->invoice_discount_type ?: 'none',
            $invoice->invoice_discount_value ?: 0,
            $invoice->currency_decimals ?? 2
        );

        if (DecimalMath::comp($calc['total'], '0') <= 0) {
            throw new InvalidArgumentException("Invoice total must be greater than zero.");
        }

        return DB::transaction(function () use ($invoice, $customer, $settings, $calc, $user) {
            // Concurrency-safe number generation
            $invoiceNumber = InvoiceNumberService::generateNextNumber($invoice->issue_date);

            // Persist recalculated items
            foreach ($calc['lines'] as $cline) {
                $invoice->items()->where('id', $cline['id'])->update([
                    'tax_name' => $cline['tax_name'],
                    'tax_rate' => $cline['tax_rate'],
                    'gross_amount' => $cline['gross_amount'],
                    'net_amount' => $cline['net_amount'],
                    'invoice_discount_allocation' => $cline['invoice_discount_allocation'],
                    'taxable_amount' => $cline['taxable_amount'],
                    'tax_amount' => $cline['tax_amount'],
                    'line_total' => $cline['line_total'],
                ]);
            }

            // Prepare snapshots
            $businessSnapshot = [
                'business_name' => $settings->business_name,
                'legal_name' => $settings->legal_name,
                'email' => $settings->email,
                'phone' => $settings->phone,
                'website' => $settings->website,
                'address' => $settings->address,
                'tax_number' => $settings->tax_number,
                'tax_label' => $settings->tax_label,
            ];

            $customerSnapshot = [
                'name' => $customer->name,
                'company_name' => $customer->company_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'billing_address' => $customer->billing_address,
                'tax_number' => $customer->tax_number,
            ];

            $bankSnapshot = [
                'bank_name' => $settings->bank_name,
                'account_holder' => $settings->account_holder,
                'account_number' => $settings->account_number,
                'swift_bic' => $settings->swift_bic,
                'branch' => $settings->branch,
                'payment_instructions' => $settings->payment_instructions,
            ];

            $invoice->update([
                'invoice_number' => $invoiceNumber,
                'status' => 'issued',
                'gross_subtotal' => $calc['gross_subtotal'],
                'line_discount_total' => $calc['line_discount_total'],
                'net_subtotal' => $calc['net_subtotal'],
                'invoice_discount_total' => $calc['invoice_discount_total'],
                'discount_total' => $calc['discount_total'],
                'taxable_subtotal' => $calc['taxable_subtotal'],
                'tax_total' => $calc['tax_total'],
                'total' => $calc['total'],
                'amount_paid' => '0.00',
                'business_snapshot' => $businessSnapshot,
                'customer_snapshot' => $customerSnapshot,
                'bank_snapshot' => $bankSnapshot,
                'issued_at' => now(),
            ]);

            ActivityLogService::log(
                'invoice.issued',
                "Invoice {$invoiceNumber} was issued to customer {$customer->name}.",
                $invoice,
                ['invoice_number' => $invoiceNumber, 'total' => $calc['total']]
            );

            return $invoice->fresh();
        });
    }

    /**
     * Void an issued invoice when completed payments total zero.
     */
    public static function void(Invoice $invoice, string $reason, ?User $user = null): Invoice
    {
        if ($invoice->status === 'draft') {
            throw new InvalidArgumentException("Draft invoices cannot be voided; delete them instead.");
        }

        if ($invoice->status === 'void') {
            throw new InvalidArgumentException("Invoice is already void.");
        }

        return DB::transaction(function () use ($invoice, $reason, $user) {
            $locked = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            $completedSum = $locked->completedPayments()->sum('amount');
            if (DecimalMath::comp((string)$completedSum, '0') > 0) {
                throw new InvalidArgumentException("Cannot void an invoice with active payments. Reverse payments first.");
            }

            $locked->update([
                'status' => 'void',
                'voided_at' => now(),
                'void_reason' => $reason,
            ]);

            ActivityLogService::log(
                'invoice.voided',
                "Invoice {$locked->invoice_number} was voided: {$reason}",
                $locked,
                ['void_reason' => $reason]
            );

            return $locked->fresh();
        });
    }
}
