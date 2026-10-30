<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::create([
            'business_name' => 'Acme Test Corp',
            'email' => 'billing@acme.test',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'tax_label' => 'VAT',
            'bank_name' => 'Test Bank',
            'account_holder' => 'Acme Test',
            'account_number' => '123456789',
            'invoice_prefix' => 'INV',
            'sequence_padding' => 4,
            'default_payment_terms' => 14,
            'default_pricing_mode' => 'exclusive',
        ]);

        $this->owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->viewer = User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@example.com',
            'password' => bcrypt('password'),
            'role' => 'viewer',
        ]);
    }

    public function test_owner_can_access_and_create_resources(): void
    {
        $response = $this->actingAs($this->owner)->post(route('customers.store'), [
            'name' => 'New Client Ltd',
            'email' => 'client@test.local',
            'billing_address' => '123 Market Street, Suite 400',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('customers', ['name' => 'New Client Ltd']);
    }

    public function test_viewer_is_forbidden_from_mutating_resources(): void
    {
        // 1. Customer Creation
        $response = $this->actingAs($this->viewer)->post(route('customers.store'), [
            'name' => 'Illegal Client',
            'email' => 'illegal@test.local',
            'billing_address' => 'Forbidden Way',
        ]);
        $response->assertStatus(403);
        $this->assertDatabaseMissing('customers', ['name' => 'Illegal Client']);

        // 2. Invoice Creation
        $response = $this->actingAs($this->viewer)->post(route('invoices.store'), [
            'customer_id' => 1,
            'issue_date' => '2026-10-30',
            'due_date' => '2026-11-13',
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100]
            ]
        ]);
        $response->assertStatus(403);

        // 3. Expense Creation
        $response = $this->actingAs($this->viewer)->post(route('expenses.store'), [
            'vendor' => 'Unauthorized Vendor',
            'description' => 'Cost',
            'entered_amount' => 50,
            'pricing_mode' => 'exclusive',
            'payment_status' => 'paid',
        ]);
        $response->assertStatus(403);

        // 4. Tax Rate Creation
        $response = $this->actingAs($this->viewer)->post(route('taxes.store'), [
            'name' => 'Unauthorized VAT',
            'rate' => 20,
        ]);
        $response->assertStatus(403);

        // 5. Settings Mutation
        $response = $this->actingAs($this->viewer)->put(route('settings.update', 'profile'), [
            'business_name' => 'Hacked Corp',
            'email' => 'hacked@test.local',
        ]);
        $response->assertStatus(403);
    }

    public function test_demo_login_fails_in_production(): void
    {
        config(['app.env' => 'production']);
        config(['app.demo_mode' => false]);

        $response = $this->post(route('demo.login'), [
            'role' => 'owner',
        ]);

        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function test_demo_login_succeeds_in_demo_environment(): void
    {
        config(['app.env' => 'local']);
        config(['app.demo_mode' => true]);

        $response = $this->post(route('demo.login'), [
            'role' => 'viewer',
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticatedAs($this->viewer);
    }
}
