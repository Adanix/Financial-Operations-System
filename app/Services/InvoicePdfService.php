<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService
{
    /**
     * Render and return Dompdf instance for the given invoice.
     */
    public static function makePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $settings = BusinessSetting::current();
        $isDraft = ($invoice->status === 'draft');

        if ($isDraft) {
            // Draft uses live data
            $customer = $invoice->customer;
            $businessData = [
                'business_name' => $settings->business_name,
                'legal_name' => $settings->legal_name,
                'email' => $settings->email,
                'phone' => $settings->phone,
                'website' => $settings->website,
                'address' => $settings->address,
                'tax_number' => $settings->tax_number,
                'tax_label' => $settings->tax_label,
            ];
            $customerData = [
                'name' => $customer ? $customer->name : 'Draft Customer',
                'company_name' => $customer?->company_name,
                'email' => $customer?->email,
                'phone' => $customer?->phone,
                'billing_address' => $customer?->billing_address,
                'tax_number' => $customer?->tax_number,
            ];
            $bankData = [
                'bank_name' => $settings->bank_name,
                'account_holder' => $settings->account_holder,
                'account_number' => $settings->account_number,
                'swift_bic' => $settings->swift_bic,
                'branch' => $settings->branch,
                'payment_instructions' => $settings->payment_instructions,
            ];
        } else {
            // Issued/paid/void invoices use immutable snapshots
            $businessData = $invoice->business_snapshot ?: [
                'business_name' => $settings->business_name,
                'email' => $settings->email,
            ];
            $customerData = $invoice->customer_snapshot ?: [
                'name' => $invoice->customer?->name,
                'billing_address' => $invoice->customer?->billing_address,
            ];
            $bankData = $invoice->bank_snapshot ?: [
                'bank_name' => $settings->bank_name,
                'account_holder' => $settings->account_holder,
                'account_number' => $settings->account_number,
            ];
        }

        $items = $invoice->items()->get();

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'isDraft' => $isDraft,
            'business' => $businessData,
            'customer' => $customerData,
            'bank' => $bankData,
            'items' => $items,
            'settings' => $settings,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', false);

        return $pdf;
    }

    /**
     * Download invoice PDF.
     */
    public static function download(Invoice $invoice): Response
    {
        $filename = ($invoice->invoice_number ?: 'DRAFT-' . $invoice->id) . '.pdf';
        return self::makePdf($invoice)->download($filename);
    }

    /**
     * Inline preview invoice PDF in browser.
     */
    public static function stream(Invoice $invoice): Response
    {
        $filename = ($invoice->invoice_number ?: 'DRAFT-' . $invoice->id) . '.pdf';
        return self::makePdf($invoice)->stream($filename);
    }
}
