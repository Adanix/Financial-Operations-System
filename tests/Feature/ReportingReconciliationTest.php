<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\DecimalMath;
use App\Services\InvoiceLifecycleService;
use App\Services\PaymentService;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::create([
            'business_name' => 'Ledger Testing Firm',
            'email' => 'finance@ledger.test',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'tax_label' => 'VAT',
            'bank_name' => 'First Ledger Bank',
            'account_holder' => 'Ledger Testing Firm',
            'account_number' => 'US9911223344',
            'invoice_prefix' => 'INV',
            'sequence_padding' => 4,
            'default_payment_terms' => 14,
            'default_pricing_mode' => 'exclusive',
        ]);
    }

    public function test_reporting_and_financial_reconciliation(): void
    {
        $tax20 = TaxRate::create([
            'name' => 'Standard VAT',
            'code' => 'VAT-20',
            'rate' => 20.00,
            'is_default' => true,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Alpha Client',
            'email' => 'alpha@client.local',
            'billing_address' => '789 Business Blvd, Tech City',
        ]);

        $category = ExpenseCategory::create([
            'name' => 'Software & Subscriptions',
            'code' => 'SAAS',
            'is_active' => true,
        ]);

        $today = Carbon::now('UTC')->toDateString();

        // 1. Create and Issue Invoice 1: $100 net + 20% tax = $120 total
        $inv1 = Invoice::create([
            'customer_id' => $customer->id,
            'issue_date' => $today,
            'due_date' => Carbon::now('UTC')->addDays(14)->toDateString(),
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'invoice_discount_type' => 'none',
            'invoice_discount_value' => '0.00',
        ]);

        $inv1->items()->create([
            'description' => 'Development Service',
            'quantity' => 1,
            'unit_price' => '100.00',
            'discount_type' => 'none',
            'discount_value' => '0.00',
            'tax_rate_id' => $tax20->id,
            'sort_order' => 1,
        ]);

        InvoiceLifecycleService::issue($inv1);

        // 2. Create and Issue Invoice 2: $200 net + 0% tax = $200 total
        $inv2 = Invoice::create([
            'customer_id' => $customer->id,
            'issue_date' => $today,
            'due_date' => Carbon::now('UTC')->subDays(5)->toDateString(), // 5 days overdue
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'invoice_discount_type' => 'none',
            'invoice_discount_value' => '0.00',
        ]);

        $inv2->items()->create([
            'description' => 'Consulting Hours',
            'quantity' => 2,
            'unit_price' => '100.00',
            'discount_type' => 'none',
            'discount_value' => '0.00',
            'tax_rate_id' => null,
            'sort_order' => 1,
        ]);

        InvoiceLifecycleService::issue($inv2);

        // 3. Record $150 partial payment on Invoice 2
        PaymentService::record($inv2, [
            'payment_date' => $today,
            'amount' => '150.00',
            'method' => 'bank_transfer',
        ]);

        // 4. Record Expense 1: $50 net, $10 tax (20%), Recoverable = true, Paid
        Expense::create([
            'expense_category_id' => $category->id,
            'expense_date' => $today,
            'vendor' => 'Cloud Host Pro',
            'description' => 'Dedicated VPS',
            'entered_amount' => '50.00',
            'pricing_mode' => 'exclusive',
            'tax_rate_id' => $tax20->id,
            'tax_name' => $tax20->name,
            'tax_rate' => '20.00',
            'tax_recoverable' => true,
            'net_amount' => '50.00',
            'tax_amount' => '10.00',
            'gross_amount' => '60.00',
            'payment_status' => 'paid',
            'payment_date' => $today,
            'payment_method' => 'card',
        ]);

        // 5. Record Expense 2: $40 gross, Non-recoverable (0% tax), Paid
        Expense::create([
            'expense_category_id' => $category->id,
            'expense_date' => $today,
            'vendor' => 'Coffee & Co',
            'description' => 'Office Refreshments',
            'entered_amount' => '40.00',
            'pricing_mode' => 'exclusive',
            'tax_rate_id' => null,
            'tax_name' => null,
            'tax_rate' => '0.00',
            'tax_recoverable' => false,
            'net_amount' => '40.00',
            'tax_amount' => '0.00',
            'gross_amount' => '40.00',
            'payment_status' => 'paid',
            'payment_date' => $today,
            'payment_method' => 'cash',
        ]);

        $range = [
            'from_date' => Carbon::now('UTC')->startOfMonth()->toDateString(),
            'to_date' => Carbon::now('UTC')->endOfMonth()->toDateString(),
        ];

        // --- VERIFY MANAGEMENT P&L ---
        $pnl = ReportingService::getManagementPnl($range);
        $this->assertEquals('300.00', $pnl['net_operating_revenue']);
        $this->assertEquals('90.00', $pnl['operating_expenses']); // 50 recoverable net + 40 non-recoverable gross
        $this->assertEquals('210.00', $pnl['net_operating_profit']);
        $this->assertEquals('150.00', $pnl['cash_memo']['cash_collected']);
        $this->assertEquals('100.00', $pnl['cash_memo']['cash_expenses']); // 60 + 40
        $this->assertEquals('50.00', $pnl['cash_memo']['net_cash_flow']);

        // --- VERIFY TAX SUMMARY ---
        $taxSummary = ReportingService::getTaxSummary($range);
        $this->assertEquals('20.00', $taxSummary['total_output_tax']);
        $this->assertEquals('10.00', $taxSummary['total_recoverable_input_tax']);
        $this->assertEquals('10.00', $taxSummary['estimated_net_tax_position']);

        // --- VERIFY AGED RECEIVABLES ---
        $aging = ReportingService::getAgedReceivables($today, 'UTC');
        $this->assertEquals('170.00', $aging['total_receivables']); // 120 from Inv1 + 50 remaining from Inv2
        $this->assertEquals('120.00', $aging['buckets']['current']); // Inv1 is current
        $this->assertEquals('50.00', $aging['buckets']['1_30']); // Inv2 is 5 days overdue

        $bucketSum = DecimalMath::add(
            DecimalMath::add($aging['buckets']['current'], $aging['buckets']['1_30'], 2),
            DecimalMath::add(
                DecimalMath::add($aging['buckets']['31_60'], $aging['buckets']['61_90'], 2),
                $aging['buckets']['90_plus'],
                2
            ),
            2
        );
        $this->assertEquals($aging['total_receivables'], $bucketSum);

        // --- VERIFY DASHBOARD KPIS ---
        $metrics = ReportingService::getDashboardMetrics();
        $this->assertEquals('300.00', $metrics['kpis']['net_revenue']);
        $this->assertEquals('170.00', $metrics['kpis']['outstanding_receivables']);
        $this->assertEquals('50.00', $metrics['kpis']['overdue_receivables']);
        $this->assertEquals('90.00', $metrics['kpis']['operating_expenses']);
        $this->assertEquals('210.00', $metrics['kpis']['operating_profit']);
        $this->assertEquals('150.00', $metrics['kpis']['cash_collected']);
    }
}
