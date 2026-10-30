<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceEmailLog;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\ExpenseCalculator;
use App\Services\InvoiceCalculator;
use App\Services\InvoiceLifecycleService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Business Settings
        $settings = BusinessSetting::current();
        $settings->update([
            'business_name' => 'SoloLedger Solutions Ltd.',
            'legal_name' => 'SoloLedger Solutions International Ltd.',
            'email' => 'finance@sololedger.example',
            'phone' => '+1 (555) 234-5678',
            'website' => 'https://sololedger.example',
            'address' => "100 Innovation Way, Suite 400\nSan Francisco, CA 94105\nUnited States",
            'tax_number' => 'US-EIN-98-7654321',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'timezone' => 'America/New_York',
            'date_format' => 'Y-m-d',
            'tax_label' => 'Sales Tax',
            'invoice_prefix' => 'INV',
            'sequence_padding' => 4,
            'default_payment_terms' => 14,
            'default_pricing_mode' => 'exclusive',
            'bank_name' => 'Pacific Horizon National Bank (DEMO)',
            'account_holder' => 'SoloLedger Solutions Ltd.',
            'account_number' => 'US98-HORZ-0123-4567-8901',
            'swift_bic' => 'HORZUS33XXX',
            'branch' => 'Financial District Branch, San Francisco',
            'payment_instructions' => 'Please reference your invoice number (e.g. INV-2026-0001) in your wire transfer narration.',
        ]);

        // 2. Demo Users
        $owner = User::updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Alex Vance (Owner)',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]
        );

        $viewer = User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Morgan Riley (Reviewer)',
                'password' => Hash::make('password'),
                'role' => 'viewer',
            ]
        );

        // 3. Tax Rates
        $taxStandard = TaxRate::firstOrCreate(
            ['name' => 'Standard Rate (10%)'],
            [
                'code' => 'STD-10',
                'rate' => '10.0000',
                'description' => 'Standard sales tax on consulting & digital products',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $taxReduced = TaxRate::firstOrCreate(
            ['name' => 'Reduced Rate (5%)'],
            [
                'code' => 'RED-5',
                'rate' => '5.0000',
                'description' => 'Concessional rate for educational services',
                'is_default' => false,
                'is_active' => true,
            ]
        );

        $taxZero = TaxRate::firstOrCreate(
            ['name' => 'Zero Rate (0%)'],
            [
                'code' => 'ZERO-0',
                'rate' => '0.0000',
                'description' => 'Zero-rated export services and non-taxable items',
                'is_default' => false,
                'is_active' => true,
            ]
        );

        // 4. Customers
        $customersData = [
            [
                'name' => 'Acme Corporation',
                'company_name' => 'Acme Global Ventures LLC',
                'email' => 'accounts@acme-corp.example',
                'phone' => '+1 (555) 301-8899',
                'billing_address' => "450 Broadway St\nNew York, NY 10013\nUnited States",
                'tax_number' => 'US-EIN-12-3456789',
                'notes' => 'Net 14 payment terms. Enterprise client.',
                'is_active' => true,
            ],
            [
                'name' => 'Starlight Media Studios',
                'company_name' => 'Starlight Media Group',
                'email' => 'billing@starlight-media.example',
                'phone' => '+1 (555) 412-9900',
                'billing_address' => "1280 Sunset Blvd\nLos Angeles, CA 90026\nUnited States",
                'tax_number' => 'US-EIN-98-1122334',
                'notes' => 'Creative agency partnership.',
                'is_active' => true,
            ],
            [
                'name' => 'Nexus Cloud Technologies',
                'company_name' => 'Nexus Tech Holdings Inc.',
                'email' => 'finance@nexustech.example',
                'phone' => '+1 (555) 670-4411',
                'billing_address' => "700 Bellevue Way NE, Suite 210\nBellevue, WA 98004\nUnited States",
                'tax_number' => 'US-EIN-45-6789012',
                'notes' => 'Annual SaaS architecture retainer.',
                'is_active' => true,
            ],
            [
                'name' => 'Bluebird Logistics',
                'company_name' => 'Bluebird Freight & Logistics',
                'email' => 'invoices@bluebirdfreight.example',
                'phone' => '+1 (555) 890-1234',
                'billing_address' => "3100 North Airport Way\nDallas, TX 75261\nUnited States",
                'tax_number' => 'US-EIN-88-9900112',
                'notes' => 'Fleet optimization software client.',
                'is_active' => true,
            ],
            [
                'name' => 'Horizon Design Labs',
                'company_name' => 'Horizon UX Design Studio',
                'email' => 'hi@horizondesign.example',
                'phone' => '+1 (555) 543-2109',
                'billing_address' => "85 2nd Street, Floor 3\nSan Francisco, CA 94105\nUnited States",
                'tax_number' => 'US-EIN-77-6655443',
                'notes' => 'Subcontractor integration.',
                'is_active' => true,
            ],
            [
                'name' => 'GreenLeaf BioAnalytics',
                'company_name' => 'GreenLeaf Life Sciences Corp',
                'email' => 'ap@greenleaf-bio.example',
                'phone' => '+1 (555) 789-0123',
                'billing_address' => "100 Binney St\nCambridge, MA 02142\nUnited States",
                'tax_number' => 'US-EIN-33-4455667',
                'notes' => 'Biotech data analysis client.',
                'is_active' => true,
            ],
        ];

        $customers = [];
        foreach ($customersData as $c) {
            $customers[] = Customer::firstOrCreate(['email' => $c['email']], $c);
        }

        // 5. Expense Categories
        $categoriesData = [
            ['name' => 'Software & Cloud Infrastructure', 'code' => 'TECH-01'],
            ['name' => 'Office Rent & Utilities', 'code' => 'RENT-02'],
            ['name' => 'Marketing & Advertising', 'code' => 'MKT-03'],
            ['name' => 'Professional Services & Legal', 'code' => 'PROF-04'],
            ['name' => 'Travel, Lodging & Meals', 'code' => 'TRV-05'],
            ['name' => 'Office Supplies & Hardware', 'code' => 'OPS-06'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = ExpenseCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // 6. Expenses across previous months
        $now = Carbon::now();
        $sampleExpenses = [
            [
                'expense_category_id' => $categories[0]->id,
                'expense_date' => $now->copy()->subDays(5)->toDateString(),
                'vendor' => 'Amazon Web Services',
                'description' => 'Production EC2 and RDS cloud hosting',
                'entered_amount' => '350.00',
                'pricing_mode' => 'exclusive',
                'tax_rate_id' => $taxStandard->id,
                'tax_name' => $taxStandard->name,
                'tax_rate' => $taxStandard->rate,
                'tax_recoverable' => true,
                'payment_status' => 'paid',
                'payment_date' => $now->copy()->subDays(5)->toDateString(),
                'payment_method' => 'card',
                'reference' => 'AWS-INV-99218',
            ],
            [
                'expense_category_id' => $categories[0]->id,
                'expense_date' => $now->copy()->subDays(12)->toDateString(),
                'vendor' => 'GitHub Inc.',
                'description' => 'Team Copilot & Enterprise subscription',
                'entered_amount' => '84.00',
                'pricing_mode' => 'exclusive',
                'tax_rate_id' => $taxStandard->id,
                'tax_name' => $taxStandard->name,
                'tax_rate' => $taxStandard->rate,
                'tax_recoverable' => true,
                'payment_status' => 'paid',
                'payment_date' => $now->copy()->subDays(12)->toDateString(),
                'payment_method' => 'card',
                'reference' => 'GH-SUB-202610',
            ],
            [
                'expense_category_id' => $categories[1]->id,
                'expense_date' => $now->copy()->subDays(20)->toDateString(),
                'vendor' => 'WeWork Coworking',
                'description' => 'Monthly dedicated desk & conference room credits',
                'entered_amount' => '650.00',
                'pricing_mode' => 'exclusive',
                'tax_rate_id' => $taxZero->id,
                'tax_name' => $taxZero->name,
                'tax_rate' => $taxZero->rate,
                'tax_recoverable' => false,
                'payment_status' => 'paid',
                'payment_date' => $now->copy()->subDays(20)->toDateString(),
                'payment_method' => 'bank_transfer',
                'reference' => 'WW-RENT-OCT26',
            ],
            [
                'expense_category_id' => $categories[2]->id,
                'expense_date' => $now->copy()->subDays(18)->toDateString(),
                'vendor' => 'Google Ads',
                'description' => 'Q4 Search campaign for B2B financial services',
                'entered_amount' => '420.00',
                'pricing_mode' => 'exclusive',
                'tax_rate_id' => $taxStandard->id,
                'tax_name' => $taxStandard->name,
                'tax_rate' => $taxStandard->rate,
                'tax_recoverable' => true,
                'payment_status' => 'paid',
                'payment_date' => $now->copy()->subDays(18)->toDateString(),
                'payment_method' => 'card',
                'reference' => 'GADS-981244',
            ],
            [
                'expense_category_id' => $categories[3]->id,
                'expense_date' => $now->copy()->subDays(2)->toDateString(),
                'vendor' => 'Smith & Partners Legal LLP',
                'description' => 'Contract review and IP assignment filing',
                'entered_amount' => '1200.00',
                'pricing_mode' => 'exclusive',
                'tax_rate_id' => $taxStandard->id,
                'tax_name' => $taxStandard->name,
                'tax_rate' => $taxStandard->rate,
                'tax_recoverable' => false, // Non-recoverable legal tax
                'payment_status' => 'unpaid',
                'payment_date' => null,
                'payment_method' => null,
                'reference' => 'LEG-2026-88',
            ],
            [
                'expense_category_id' => $categories[4]->id,
                'expense_date' => $now->copy()->subDays(28)->toDateString(),
                'vendor' => 'Delta Airlines',
                'description' => 'Client onsite meeting flight ticket',
                'entered_amount' => '480.00',
                'pricing_mode' => 'inclusive',
                'tax_rate_id' => $taxReduced->id,
                'tax_name' => $taxReduced->name,
                'tax_rate' => $taxReduced->rate,
                'tax_recoverable' => true,
                'payment_status' => 'paid',
                'payment_date' => $now->copy()->subDays(28)->toDateString(),
                'payment_method' => 'card',
                'reference' => 'DL-99120',
            ],
        ];

        foreach ($sampleExpenses as $se) {
            $calc = ExpenseCalculator::calculate(
                $se['entered_amount'],
                $se['tax_rate'],
                $se['pricing_mode'],
                $se['tax_recoverable'],
                2
            );

            Expense::create(array_merge($se, [
                'expense_category_id' => $se['expense_category_id'],
                'net_amount' => $calc['net_amount'],
                'tax_amount' => $calc['tax_amount'],
                'gross_amount' => $calc['gross_amount'],
            ]));
        }

        // 7. Seed Invoices across lifecycle states

        // Invoice 1: Draft
        $draft = Invoice::create([
            'customer_id' => $customers[0]->id,
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'issue_date' => $now->copy()->toDateString(),
            'due_date' => $now->copy()->addDays(14)->toDateString(),
            'reference' => 'PO-DRAFT-01',
            'notes' => 'Draft invoice under preparation for Acme Corporation.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $draft->id,
            'sort_order' => 1,
            'description' => 'Enterprise Architecture Consultation',
            'quantity' => '10',
            'unit' => 'hours',
            'unit_price' => '180.00',
            'line_discount_amount' => '0',
            'tax_rate_id' => $taxStandard->id,
            'tax_name' => $taxStandard->name,
            'tax_rate' => $taxStandard->rate,
            'gross_amount' => '1800.00',
            'net_amount' => '1800.00',
            'taxable_amount' => '1800.00',
            'tax_amount' => '180.00',
            'line_total' => '1980.00',
        ]);

        $draft->update([
            'gross_subtotal' => '1800.00',
            'net_subtotal' => '1800.00',
            'taxable_subtotal' => '1800.00',
            'tax_total' => '180.00',
            'total' => '1980.00',
        ]);

        // Invoice 2: Issued (Active, not yet paid, not overdue)
        $inv2 = Invoice::create([
            'customer_id' => $customers[1]->id,
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'issue_date' => $now->copy()->subDays(3)->toDateString(),
            'due_date' => $now->copy()->addDays(11)->toDateString(),
            'reference' => 'PO-STARLIGHT-99',
            'notes' => 'Video encoding pipeline deployment & optimization.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv2->id,
            'sort_order' => 1,
            'description' => 'Automated Media Transcoding Pipeline',
            'quantity' => '1',
            'unit' => 'project',
            'unit_price' => '2500.00',
            'tax_rate_id' => $taxStandard->id,
            'tax_name' => $taxStandard->name,
            'tax_rate' => $taxStandard->rate,
        ]);
        InvoiceLifecycleService::issue($inv2, $owner);

        // Invoice 3: Partially Paid
        $inv3 = Invoice::create([
            'customer_id' => $customers[2]->id,
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'issue_date' => $now->copy()->subDays(10)->toDateString(),
            'due_date' => $now->copy()->addDays(4)->toDateString(),
            'reference' => 'PO-NEXUS-771',
            'notes' => 'Cloud migration sprint 1 & initial milestone.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv3->id,
            'sort_order' => 1,
            'description' => 'Cloud Migration Phase 1 (Sprint Deliverables)',
            'quantity' => '20',
            'unit' => 'hours',
            'unit_price' => '150.00',
            'tax_rate_id' => $taxStandard->id,
            'tax_name' => $taxStandard->name,
            'tax_rate' => $taxStandard->rate,
        ]);
        $issued3 = InvoiceLifecycleService::issue($inv3, $owner);
        // Total = 3000 + 300 = 3300.00. Record partial payment of 1500.00
        PaymentService::record($issued3, [
            'amount' => '1500.00',
            'payment_date' => $now->copy()->subDays(2)->toDateString(),
            'method' => 'bank_transfer',
            'reference' => 'WIRE-NEX-001',
            'notes' => 'Milestone 1 deposit wire received',
        ], $owner);

        // Invoice 4: Paid in Full
        $inv4 = Invoice::create([
            'customer_id' => $customers[3]->id,
            'status' => 'draft',
            'pricing_mode' => 'inclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'issue_date' => $now->copy()->subDays(25)->toDateString(),
            'due_date' => $now->copy()->subDays(11)->toDateString(),
            'reference' => 'PO-BLUEBIRD-40',
            'notes' => 'Fleet routing module maintenance & security patch.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv4->id,
            'sort_order' => 1,
            'description' => 'Fleet Routing API Integration (Tax Inclusive)',
            'quantity' => '1',
            'unit' => 'package',
            'unit_price' => '1100.00',
            'tax_rate_id' => $taxStandard->id,
            'tax_name' => $taxStandard->name,
            'tax_rate' => $taxStandard->rate,
        ]);
        $issued4 = InvoiceLifecycleService::issue($inv4, $owner);
        // Total = 1100.00 (inclusive tax = 100.00, sales = 1000.00). Pay in full.
        PaymentService::record($issued4, [
            'amount' => '1100.00',
            'payment_date' => $now->copy()->subDays(12)->toDateString(),
            'method' => 'card',
            'reference' => 'STRIPE-CH-99881',
            'notes' => 'Paid via corporate credit card',
        ], $owner);

        // Invoice 5: Overdue (Issue date 30 days ago, due date 16 days ago, unpaid)
        $inv5 = Invoice::create([
            'customer_id' => $customers[4]->id,
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'issue_date' => $now->copy()->subDays(30)->toDateString(),
            'due_date' => $now->copy()->subDays(16)->toDateString(),
            'reference' => 'PO-HORIZON-12',
            'notes' => 'Design sprint workshop and user research synthesis.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv5->id,
            'sort_order' => 1,
            'description' => 'UX Research Workshop & Design Prototype',
            'quantity' => '1',
            'unit' => 'deliverable',
            'unit_price' => '3200.00',
            'tax_rate_id' => $taxStandard->id,
            'tax_name' => $taxStandard->name,
            'tax_rate' => $taxStandard->rate,
        ]);
        InvoiceLifecycleService::issue($inv5, $owner);

        // Invoice 6: Voided
        $inv6 = Invoice::create([
            'customer_id' => $customers[5]->id,
            'status' => 'draft',
            'pricing_mode' => 'exclusive',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'issue_date' => $now->copy()->subDays(14)->toDateString(),
            'due_date' => $now->copy()->toDateString(),
            'reference' => 'PO-GREENLEAF-CANCELLED',
            'notes' => 'Cancelled engagement before delivery commenced.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv6->id,
            'sort_order' => 1,
            'description' => 'Genomics Pipeline Feasibility Study',
            'quantity' => '1',
            'unit' => 'scope',
            'unit_price' => '4000.00',
            'tax_rate_id' => $taxZero->id,
            'tax_name' => $taxZero->name,
            'tax_rate' => $taxZero->rate,
        ]);
        $issued6 = InvoiceLifecycleService::issue($inv6, $owner);
        InvoiceLifecycleService::void($issued6, 'Client re-scoped internal priorities, contract superseded.', $owner);

        // 8. Add sample email log for Invoice 2
        InvoiceEmailLog::create([
            'invoice_id' => $inv2->id,
            'recipient_email' => 'billing@starlight-media.example',
            'subject' => "Invoice {$inv2->invoice_number} from SoloLedger Solutions Ltd.",
            'body_message' => "Dear Starlight Media Studios,\n\nPlease find attached invoice {$inv2->invoice_number} for your recent project.\n\nThank you for your business!",
            'status' => 'sent_demo',
            'attachment_path' => "invoices/{$inv2->invoice_number}_sample.pdf",
            'sent_at' => now()->subDays(3),
        ]);
    }
}
