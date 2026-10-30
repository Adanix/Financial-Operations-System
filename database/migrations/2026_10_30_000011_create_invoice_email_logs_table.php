<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->string('recipient_email');
            $table->string('subject');
            $table->text('body_message');
            $table->string('status', 20)->default('sent'); // sent, sent_demo, failed
            $table->string('attachment_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_email_logs');
    }
};
