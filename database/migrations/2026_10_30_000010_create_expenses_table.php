<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->date('expense_date')->index();
            $table->string('vendor')->index();
            $table->text('description');
            $table->decimal('entered_amount', 15, 2);
            $table->string('pricing_mode', 20)->default('exclusive'); // exclusive, inclusive
            
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();
            $table->string('tax_name', 100)->nullable();
            $table->decimal('tax_rate', 8, 4)->default(0.0000);
            $table->boolean('tax_recoverable')->default(false)->index();
            
            // Financial amounts
            $table->decimal('net_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('gross_amount', 15, 2)->default(0.00);
            
            // Payment state
            $table->string('payment_status', 20)->default('paid')->index(); // paid, unpaid
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('reference')->nullable();
            
            // Private receipt file
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
