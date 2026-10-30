<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TaxRate;
use App\Services\ActivityLogService;
use App\Services\InvoiceCalculator;
use App\Services\InvoiceEmailService;
use App\Services\InvoiceLifecycleService;
use App\Services\InvoicePdfService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $settings = BusinessSetting::current();
        $query = Invoice::with('customer');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'overdue') {
                $today = Carbon::now($settings->timezone)->toDateString();
                $query->whereIn('status', ['issued', 'partially_paid'])
                      ->where('due_date', '<', $today);
            } else {
                $query->where('status', $status);
            }
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->where('issue_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->where('issue_date', '<=', $toDate);
        }

        $sortField = $request->input('sort', 'issue_date');
        $sortDir = $request->input('direction', 'desc');
        $allowedSorts = ['issue_date', 'due_date', 'total', 'status', 'invoice_number'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'issue_date';
        }
        $sortDir = ($sortDir === 'asc') ? 'asc' : 'desc';

        $invoices = $query->orderBy($sortField, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get();

        return view('invoices.index', [
            'invoices' => $invoices,
            'customers' => $customers,
            'search' => $search,
            'currentStatus' => $status,
            'currentCustomer' => $customerId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'sortField' => $sortField,
            'sortDir' => $sortDir,
            'currencySymbol' => $settings->currency_symbol,
        ]);
    }

    public function create(): View
    {
        $settings = BusinessSetting::current();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $taxRates = TaxRate::where('is_active', true)->orderBy('rate')->get();

        return view('invoices.create', [
            'settings' => $settings,
            'customers' => $customers,
            'taxRates' => $taxRates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $settings = BusinessSetting::current();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'pricing_mode' => ['required', 'in:exclusive,inclusive'],
            'reference' => ['nullable', 'string', 'max:100'],
            'invoice_discount_type' => ['required', 'in:none,percent,fixed'],
            'invoice_discount_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_terms_text' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
        ]);

        $decimals = $settings->currency_decimals ?? 2;

        // Prepare line payloads
        $linePayloads = [];
        foreach ($validated['items'] as $item) {
            $taxRateModel = !empty($item['tax_rate_id']) ? TaxRate::find($item['tax_rate_id']) : null;
            $linePayloads[] = [
                'description' => $item['description'],
                'quantity' => (string) $item['quantity'],
                'unit' => $item['unit'] ?? null,
                'unit_price' => (string) $item['unit_price'],
                'line_discount_amount' => (string) ($item['line_discount_amount'] ?? 0),
                'tax_rate_id' => $taxRateModel?->id,
                'tax_name' => $taxRateModel ? $taxRateModel->name : 'None',
                'tax_rate' => $taxRateModel ? (string)$taxRateModel->rate : '0.0000',
            ];
        }

        // Run authoritative server calculation
        $calc = InvoiceCalculator::calculate(
            $linePayloads,
            $validated['pricing_mode'],
            $validated['invoice_discount_type'],
            $validated['invoice_discount_value'] ?? 0,
            $decimals
        );

        $invoice = DB::transaction(function () use ($validated, $calc, $settings) {
            $inv = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'status' => 'draft',
                'pricing_mode' => $validated['pricing_mode'],
                'currency_code' => $settings->currency_code,
                'currency_symbol' => $settings->currency_symbol,
                'currency_decimals' => $settings->currency_decimals,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'reference' => $validated['reference'] ?? null,
                'gross_subtotal' => $calc['gross_subtotal'],
                'line_discount_total' => $calc['line_discount_total'],
                'net_subtotal' => $calc['net_subtotal'],
                'invoice_discount_type' => $validated['invoice_discount_type'],
                'invoice_discount_value' => $calc['invoice_discount_value'],
                'invoice_discount_total' => $calc['invoice_discount_total'],
                'discount_total' => $calc['discount_total'],
                'taxable_subtotal' => $calc['taxable_subtotal'],
                'tax_total' => $calc['tax_total'],
                'total' => $calc['total'],
                'amount_paid' => '0.00',
                'notes' => $validated['notes'] ?? $settings->default_notes,
                'payment_terms_text' => $validated['payment_terms_text'] ?? "Payment due within {$settings->default_payment_terms} days.",
            ]);

            foreach ($calc['lines'] as $idx => $cline) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'sort_order' => $idx + 1,
                    'description' => $cline['description'],
                    'quantity' => $cline['quantity'],
                    'unit' => $cline['unit'],
                    'unit_price' => $cline['unit_price'],
                    'line_discount_amount' => $cline['line_discount_amount'],
                    'tax_rate_id' => $cline['tax_rate_id'],
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

            ActivityLogService::log(
                'invoice.created',
                "Draft invoice #{$inv->id} created.",
                $inv
            );

            return $inv;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Draft invoice created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'items', 'payments.recordedBy', 'emailLogs']);
        $settings = BusinessSetting::current();

        return view('invoices.show', [
            'invoice' => $invoice,
            'settings' => $settings,
        ]);
    }

    public function edit(Invoice $invoice): View|RedirectResponse
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $settings = BusinessSetting::current();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $taxRates = TaxRate::where('is_active', true)->orderBy('rate')->get();

        return view('invoices.edit', [
            'invoice' => $invoice,
            'settings' => $settings,
            'customers' => $customers,
            'taxRates' => $taxRates,
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be modified.');
        }

        $settings = BusinessSetting::current();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'pricing_mode' => ['required', 'in:exclusive,inclusive'],
            'reference' => ['nullable', 'string', 'max:100'],
            'invoice_discount_type' => ['required', 'in:none,percent,fixed'],
            'invoice_discount_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_terms_text' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
        ]);

        $decimals = $settings->currency_decimals ?? 2;

        $linePayloads = [];
        foreach ($validated['items'] as $item) {
            $taxRateModel = !empty($item['tax_rate_id']) ? TaxRate::find($item['tax_rate_id']) : null;
            $linePayloads[] = [
                'description' => $item['description'],
                'quantity' => (string) $item['quantity'],
                'unit' => $item['unit'] ?? null,
                'unit_price' => (string) $item['unit_price'],
                'line_discount_amount' => (string) ($item['line_discount_amount'] ?? 0),
                'tax_rate_id' => $taxRateModel?->id,
                'tax_name' => $taxRateModel ? $taxRateModel->name : 'None',
                'tax_rate' => $taxRateModel ? (string)$taxRateModel->rate : '0.0000',
            ];
        }

        $calc = InvoiceCalculator::calculate(
            $linePayloads,
            $validated['pricing_mode'],
            $validated['invoice_discount_type'],
            $validated['invoice_discount_value'] ?? 0,
            $decimals
        );

        DB::transaction(function () use ($invoice, $validated, $calc) {
            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'pricing_mode' => $validated['pricing_mode'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'reference' => $validated['reference'] ?? null,
                'gross_subtotal' => $calc['gross_subtotal'],
                'line_discount_total' => $calc['line_discount_total'],
                'net_subtotal' => $calc['net_subtotal'],
                'invoice_discount_type' => $validated['invoice_discount_type'],
                'invoice_discount_value' => $calc['invoice_discount_value'],
                'invoice_discount_total' => $calc['invoice_discount_total'],
                'discount_total' => $calc['discount_total'],
                'taxable_subtotal' => $calc['taxable_subtotal'],
                'tax_total' => $calc['tax_total'],
                'total' => $calc['total'],
                'notes' => $validated['notes'] ?? null,
                'payment_terms_text' => $validated['payment_terms_text'] ?? null,
            ]);

            $invoice->items()->delete();

            foreach ($calc['lines'] as $idx => $cline) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'sort_order' => $idx + 1,
                    'description' => $cline['description'],
                    'quantity' => $cline['quantity'],
                    'unit' => $cline['unit'],
                    'unit_price' => $cline['unit_price'],
                    'line_discount_amount' => $cline['line_discount_amount'],
                    'tax_rate_id' => $cline['tax_rate_id'],
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

            ActivityLogService::log(
                'invoice.updated',
                "Draft invoice #{$invoice->id} updated.",
                $invoice
            );
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Draft invoice updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be deleted.');
        }

        $id = $invoice->id;
        $invoice->items()->delete();
        $invoice->delete();

        ActivityLogService::log(
            'invoice.deleted',
            "Draft invoice #{$id} was deleted."
        );

        return redirect()->route('invoices.index')->with('success', "Draft invoice #{$id} was deleted.");
    }

    public function issue(Invoice $invoice): RedirectResponse
    {
        try {
            $issued = InvoiceLifecycleService::issue($invoice, auth()->user());
            return redirect()->route('invoices.show', $issued)
                ->with('success', "Invoice {$issued->invoice_number} has been officially issued.");
        } catch (InvalidArgumentException $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', $e->getMessage());
        }
    }

    public function void(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        try {
            $voided = InvoiceLifecycleService::void($invoice, $validated['void_reason'], auth()->user());
            return redirect()->route('invoices.show', $voided)
                ->with('success', "Invoice {$voided->invoice_number} has been voided.");
        } catch (InvalidArgumentException $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', $e->getMessage());
        }
    }

    public function pdf(Invoice $invoice): Response
    {
        return InvoicePdfService::download($invoice);
    }

    public function previewPdf(Invoice $invoice): Response
    {
        return InvoicePdfService::stream($invoice);
    }

    public function email(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_email' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body_message' => ['required', 'string'],
        ]);

        try {
            $log = InvoiceEmailService::send(
                $invoice,
                $validated['recipient_email'],
                $validated['subject'],
                $validated['body_message'],
                auth()->user()
            );

            if ($log->status === 'failed') {
                return redirect()->route('invoices.show', $invoice)
                    ->with('error', "Failed to send email: {$log->error_message}");
            }

            $msg = ($log->status === 'sent_demo')
                ? "Demo Mode: Email simulated successfully to {$validated['recipient_email']}. Attachment logged."
                : "Invoice email successfully dispatched to {$validated['recipient_email']}.";

            return redirect()->route('invoices.show', $invoice)->with('success', $msg);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('invoices.show', $invoice)->with('error', $e->getMessage());
        }
    }
}
