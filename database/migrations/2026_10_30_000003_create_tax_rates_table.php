<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->decimal('rate', 8, 4)->default(0.0000);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
