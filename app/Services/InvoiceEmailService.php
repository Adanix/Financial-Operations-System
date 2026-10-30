<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceEmailLog;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class InvoiceEmailService
{
    /**
     * Send or simulate sending invoice via email with PDF attachment.
     */
    public static function send(
        Invoice $invoice,
        string $recipientEmail,
        string $subject,
        string $bodyMessage,
        ?User $user = null
    ): InvoiceEmailLog {
        if (in_array($invoice->status, ['draft', 'void'])) {
            throw new InvalidArgumentException("Draft and void invoices cannot be sent via email.");
        }

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("A valid recipient email address is required.");
        }

        // Generate and save exact immutable PDF artifact
        $pdfOutput = InvoicePdfService::makePdf($invoice)->output();
        $safeNumber = preg_replace('/[^A-Za-z0-9_-]/', '_', $invoice->invoice_number);
        $timestamp = now()->format('Ymd_His');
        $artifactFilename = "invoices/{$safeNumber}_{$timestamp}.pdf";

        Storage::disk('local')->put("private/{$artifactFilename}", $pdfOutput);

        $isDemo = config('app.demo_mode', false) || config('mail.default') === 'log' || config('mail.default') === 'array';

        if ($isDemo) {
            // Save as sent_demo without external network call
            $log = InvoiceEmailLog::create([
                'invoice_id' => $invoice->id,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body_message' => $bodyMessage,
                'status' => 'sent_demo',
                'attachment_path' => $artifactFilename,
                'sent_at' => now(),
            ]);

            ActivityLogService::log(
                'invoice.emailed',
                "Invoice {$invoice->invoice_number} email was simulated (Demo Mode) to {$recipientEmail}.",
                $invoice,
                ['recipient' => $recipientEmail, 'log_id' => $log->id]
            );

            return $log;
        }

        // Live mail delivery
        try {
            Mail::raw($bodyMessage, function ($message) use ($recipientEmail, $subject, $pdfOutput, $invoice) {
                $message->to($recipientEmail)
                    ->subject($subject)
                    ->attachData($pdfOutput, "{$invoice->invoice_number}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

            $log = InvoiceEmailLog::create([
                'invoice_id' => $invoice->id,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body_message' => $bodyMessage,
                'status' => 'sent',
                'attachment_path' => $artifactFilename,
                'sent_at' => now(),
            ]);

            ActivityLogService::log(
                'invoice.emailed',
                "Invoice {$invoice->invoice_number} was emailed to {$recipientEmail}.",
                $invoice,
                ['recipient' => $recipientEmail, 'log_id' => $log->id]
            );

            return $log;
        } catch (Exception $e) {
            Log::error("Failed to email invoice {$invoice->id}: " . $e->getMessage());

            $log = InvoiceEmailLog::create([
                'invoice_id' => $invoice->id,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body_message' => $bodyMessage,
                'status' => 'failed',
                'attachment_path' => $artifactFilename,
                'error_message' => $e->getMessage(),
            ]);

            return $log;
        }
    }
}
