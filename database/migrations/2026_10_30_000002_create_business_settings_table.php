<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('singleton_key', 20)->default('default')->unique();
            $table->string('business_name')->default('SoloLedger Corp');
            $table->string('legal_name')->nullable();
            $table->string('email')->default('billing@example.com');
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('tax_number')->nullable();
            
            // Currency & Locale
            $table->string('currency_code', 10)->default('USD');
            $table->string('currency_symbol', 10)->default('$');
            $table->unsignedTinyInteger('currency_decimals')->default(2);
            $table->string('timezone', 50)->default('UTC');
            $table->string('date_format', 20)->default('Y-m-d');
            $table->string('tax_label', 20)->default('Tax');
            
            // Invoicing Defaults
            $table->string('invoice_prefix', 20)->default('INV');
            $table->unsignedTinyInteger('sequence_padding')->default(4);
            $table->unsignedSmallInteger('default_payment_terms')->default(14);
            $table->string('default_pricing_mode', 20)->default('exclusive');
            $table->unsignedBigInteger('default_tax_rate_id')->nullable();
            $table->text('default_notes')->nullable();
            
            // Bank Details
            $table->string('bank_name')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('account_number')->nullable();
            $table->string('swift_bic')->nullable();
            $table->string('branch')->nullable();
            $table->text('payment_instructions')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
