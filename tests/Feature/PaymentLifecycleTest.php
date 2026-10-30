<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\InvoiceLifecycleService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::current()->update([
            'business_name' => 'Acme Test Corp',
            'email' => 'acme@example.com',
            'bank_name' => 'Test Bank',
            'account_holder' => 'Acme Test Corp',
            'account_number' => '999988887777',
            'currency_decimals' => 2,
        ]);
    }

    public function test_example_c_payment_lifecycle(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $customer = Customer::create([
            'name' => 'Alpha Customer',
            'billing_address' => '123 Market St, San Francisco, CA',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'sort_order' => 1,
            'description' => 'Consulting Services',
            'quantity' => '10',
            'unit_price' => '100.00',
            'tax_name' => 'None',
            'tax_rate' => '0.0000',
        ]);

        // Issue invoice
        $issued = InvoiceLifecycleService::issue($invoice, $user);
        $this->assertEquals('issued', $issued->status);
        $this->assertEquals('1000.00', $issued->total);
        $this->assertEquals('0.00', $issued->amount_paid);
        $this->assertEquals('1000.00', $issued->outstanding_balance);

        // Step 1: Record payment of 300.00
        $p1 = PaymentService::record($issued, ['amount' => '300.00', 'method' => 'bank_transfer'], $user);
        $issued->refresh();
        $this->assertEquals('300.00', $issued->amount_paid);
        $this->assertEquals('700.00', $issued->outstanding_balance);
        $this->assertEquals('partially_paid', $issued->status);

        // Step 2: Record payment of 700.00
        $p2 = PaymentService::record($issued, ['amount' => '700.00', 'method' => 'bank_transfer'], $user);
        $issued->refresh();
        $this->assertEquals('1000.00', $issued->amount_paid);
        $this->assertEquals('0.00', $issued->outstanding_balance);
        $this->assertEquals('paid', $issued->status);

        // Step 3: Overpayment rejected
        $this->expectException(InvalidArgumentException::class);
        PaymentService::record($issued, ['amount' => '50.00'], $user);
    }

    public function test_payment_reversal_lifecycle(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $customer = Customer::create([
            'name' => 'Beta Customer',
            'billing_address' => '456 Tech Ave',
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
            'description' => 'Development',
            'quantity' => '1',
            'unit_price' => '1000.00',
            'tax_name' => 'None',
            'tax_rate' => '0',
        ]);

        $issued = InvoiceLifecycleService::issue($invoice, $user);
        $p1 = PaymentService::record($issued, ['amount' => '300.00'], $user);
        $p2 = PaymentService::record($issued, ['amount' => '700.00'], $user);

        // Reverse p2 (700.00)
        PaymentService::reverse($p2, 'Client check bounced', $user);
        $issued->refresh();
        $this->assertEquals('300.00', $issued->amount_paid);
        $this->assertEquals('700.00', $issued->outstanding_balance);
        $this->assertEquals('partially_paid', $issued->status);

        // Repeated reversal should be rejected
        try {
            PaymentService::reverse($p2, 'Another attempt', $user);
            $this->fail('Repeated reversal should throw exception');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('completed', $e->getMessage());
        }

        // Reverse p1 (300.00)
        PaymentService::reverse($p1, 'Refunded due to error', $user);
        $issued->refresh();
        $this->assertEquals('0.00', $issued->amount_paid);
        $this->assertEquals('1000.00', $issued->outstanding_balance);
        $this->assertEquals('issued', $issued->status);

        // Now voiding is allowed since completed payments sum == 0
        InvoiceLifecycleService::void($issued, 'Project cancelled by mutual agreement', $user);
        $issued->refresh();
        $this->assertEquals('void', $issued->status);
    }
}
