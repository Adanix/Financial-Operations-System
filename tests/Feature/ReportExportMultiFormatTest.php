<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\InvoiceLifecycleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportMultiFormatTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::create([
            'business_name' => 'Apex Financial Services',
            'email' => 'finance@apex.test',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'tax_label' => 'VAT',
            'bank_name' => 'Metro Bank',
            'account_holder' => 'Apex Financial',
            'account_number' => 'US1234567890',
            'invoice_prefix' => 'INV',
            'sequence_padding' => 4,
            'default_payment_terms' => 14,
            'default_pricing_mode' => 'exclusive',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->viewer = User::create([
            'name' => 'Reviewer User',
            'email' => 'viewer@example.com',
            'password' => bcrypt('password'),
            'role' => 'viewer',
        ]);

        $tax = TaxRate::create([
            'name' => 'Standard VAT',
            'code' => 'VAT-20',
            'rate' => 20.00,
            'is_default' => true,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Global Systems Corp',
            'email' => 'billing@globalsystems.local',
            'billing_address' => '100 Tech Way',
        ]);

        $category = ExpenseCategory::create([
            'name' => 'Cloud Hosting',
            'code' => 'CLOUD',
            'is_active' => true,
        ]);

        $today = Carbon::now('UTC')->toDateString();

        $inv = Invoice::create([
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

        $inv->items()->create([
            'description' => 'System Architecture Consulting',
            'quantity' => 1,
            'unit_price' => '500.00',
            'discount_type' => 'none',
            'discount_value' => '0.00',
            'tax_rate_id' => $tax->id,
            'sort_order' => 1,
        ]);

        InvoiceLifecycleService::issue($inv);

        Expense::create([
            'expense_category_id' => $category->id,
            'expense_date' => $today,
            'vendor' => 'AWS Cloud Services',
            'description' => 'EC2 Cluster',
            'entered_amount' => '100.00',
            'pricing_mode' => 'exclusive',
            'tax_rate_id' => $tax->id,
            'tax_name' => $tax->name,
            'tax_rate' => '20.00',
            'tax_recoverable' => true,
            'net_amount' => '100.00',
            'tax_amount' => '20.00',
            'gross_amount' => '120.00',
            'payment_status' => 'paid',
            'payment_date' => $today,
            'payment_method' => 'card',
        ]);
    }

    public function test_unauthenticated_export_is_redirected_to_login(): void
    {
        $response = $this->get(route('reports.export', ['tab' => 'pnl', 'format' => 'csv']));
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_viewer_and_owner_can_export_csv(): void
    {
        $tabs = ['pnl', 'tax', 'revenue', 'expenses', 'aging'];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->viewer)->get(route('reports.export', [
                'tab' => $tab,
                'format' => 'csv',
            ]));

            $response->assertStatus(200);
            $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        }
    }

    public function test_xlsx_export_generates_valid_excel_stream(): void
    {
        $tabs = ['pnl', 'tax', 'revenue', 'expenses', 'aging'];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->owner)->get(route('reports.export', [
                'tab' => $tab,
                'format' => 'xlsx',
            ]));

            $response->assertStatus(200);
            $this->assertStringContainsString(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                $response->headers->get('Content-Type')
            );

            // XLSX files are ZIP archives starting with magic byte PK\x03\x04
            $file = $response->getFile();
            $this->assertNotNull($file);
            $content = file_get_contents($file->getPathname());
            $this->assertStringStartsWith("PK\x03\x04", $content);
        }
    }

    public function test_pdf_export_generates_valid_pdf_document(): void
    {
        $tabs = ['pnl', 'tax', 'revenue', 'expenses', 'aging'];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->owner)->get(route('reports.export', [
                'tab' => $tab,
                'format' => 'pdf',
            ]));

            $response->assertStatus(200);
            $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));

            // PDF documents start with %PDF-
            $content = $response->getContent();
            $this->assertStringStartsWith("%PDF-", $content);
        }
    }
}
