<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('invoice_number', 50)->nullable()->unique();
            $table->string('status', 20)->default('draft')->index(); // draft, issued, partially_paid, paid, void
            $table->string('pricing_mode', 20)->default('exclusive'); // exclusive, inclusive
            $table->string('currency_code', 10)->default('USD');
            $table->string('currency_symbol', 10)->default('$');
            $table->unsignedTinyInteger('currency_decimals')->default(2);
            $table->date('issue_date')->index();
            $table->date('due_date')->index();
            $table->string('reference')->nullable();
            
            // Financial Totals
            $table->decimal('gross_subtotal', 15, 2)->default(0.00);
            $table->decimal('line_discount_total', 15, 2)->default(0.00);
            $table->decimal('net_subtotal', 15, 2)->default(0.00);
            $table->string('invoice_discount_type', 20)->default('none'); // none, percent, fixed
            $table->decimal('invoice_discount_value', 15, 2)->default(0.00);
            $table->decimal('invoice_discount_total', 15, 2)->default(0.00);
            $table->decimal('discount_total', 15, 2)->default(0.00);
            $table->decimal('taxable_subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_total', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            
            $table->text('notes')->nullable();
            $table->text('payment_terms_text')->nullable();
            
            // Immutable Snapshots on Issue
            $table->json('business_snapshot')->nullable();
            $table->json('customer_snapshot')->nullable();
            $table->json('bank_snapshot')->nullable();
            
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
