<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date')->index();
            $table->string('method', 50)->default('bank_transfer'); // bank_transfer, cash, card, check, other
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('completed')->index(); // completed, reversed
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
