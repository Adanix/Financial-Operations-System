<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\InvoiceLifecycleService;
use App\Services\InvoicePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_rendering_for_draft_and_issued(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        BusinessSetting::current()->update([
            'business_name' => 'Original Corp',
            'email' => 'original@example.com',
            'bank_name' => 'Original Bank',
            'account_holder' => 'Original Holder',
            'account_number' => '111122223333',
        ]);

        $customer = Customer::create([
            'name' => 'Original Customer',
            'billing_address' => '789 Pine St',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_decimals' => 2,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Design Service',
            'quantity' => '2',
            'unit_price' => '150.00',
            'tax_name' => 'Standard',
            'tax_rate' => '10.0000',
        ]);

        // 1. Render Draft PDF
        $draftPdf = InvoicePdfService::makePdf($invoice)->output();
        $this->assertNotEmpty($draftPdf);

        // 2. Issue Invoice
        $issued = InvoiceLifecycleService::issue($invoice, $user);
        $issuedPdf = InvoicePdfService::makePdf($issued)->output();
        $this->assertNotEmpty($issuedPdf);

        // 3. Mutate master settings and customer
        BusinessSetting::current()->update([
            'business_name' => 'Changed Company Name',
            'bank_name' => 'Changed Bank',
        ]);
        $customer->update(['name' => 'Renamed Customer']);

        // 4. Issued PDF must retain original snapshot values
        $reloaded = $issued->fresh();
        $this->assertEquals('Original Corp', $reloaded->business_snapshot['business_name']);
        $this->assertEquals('Original Bank', $reloaded->bank_snapshot['bank_name']);
        $this->assertEquals('Original Customer', $reloaded->customer_snapshot['name']);
    }
}
