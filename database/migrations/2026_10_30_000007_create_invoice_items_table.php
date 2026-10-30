<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('description');
            $table->decimal('quantity', 12, 3)->default(1.000);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('line_discount_amount', 15, 2)->default(0.00);
            
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();
            $table->string('tax_name', 100)->default('None');
            $table->decimal('tax_rate', 8, 4)->default(0.0000);
            
            // Financial Breakdown per line
            $table->decimal('gross_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2)->default(0.00);
            $table->decimal('invoice_discount_allocation', 15, 2)->default(0.00);
            $table->decimal('taxable_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('line_total', 15, 2)->default(0.00);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
