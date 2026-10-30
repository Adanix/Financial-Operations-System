<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    /**
     * Record a customer payment against an issued or partially paid invoice.
     */
    public static function record(Invoice $invoice, array $data, ?User $user = null): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $user) {
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if (!in_array($lockedInvoice->status, ['issued', 'partially_paid'])) {
                throw new InvalidArgumentException("Payments can only be recorded for active issued or partially paid invoices.");
            }

            $decimals = $lockedInvoice->currency_decimals ?? 2;
            $amount = DecimalMath::round($data['amount'] ?? 0, $decimals);

            if (DecimalMath::comp($amount, '0') <= 0) {
                throw new InvalidArgumentException("Payment amount must be greater than zero.");
            }

            // Recalculate true outstanding balance from completed payments
            $completedSum = (string) $lockedInvoice->completedPayments()->sum('amount');
            $trueBalance = DecimalMath::max('0.00', DecimalMath::sub((string)$lockedInvoice->total, $completedSum, $decimals), $decimals);

            if (DecimalMath::comp($amount, $trueBalance) > 0) {
                throw new InvalidArgumentException("Payment amount ({$amount}) exceeds current outstanding balance ({$trueBalance}).");
            }

            $payment = Payment::create([
                'invoice_id' => $lockedInvoice->id,
                'recorded_by' => $user?->id,
                'amount' => $amount,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'method' => $data['method'] ?? 'bank_transfer',
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
            ]);

            // Atomically recalculate invoice amount_paid and status
            $newPaid = DecimalMath::add($completedSum, $amount, $decimals);
            $newStatus = (DecimalMath::comp($newPaid, (string)$lockedInvoice->total) >= 0) ? 'paid' : 'partially_paid';

            $lockedInvoice->update([
                'amount_paid' => $newPaid,
                'status' => $newStatus,
            ]);

            ActivityLogService::log(
                'payment.recorded',
                "Payment of {$amount} was recorded for invoice {$lockedInvoice->invoice_number}.",
                $payment,
                ['invoice_id' => $lockedInvoice->id, 'amount' => $amount, 'new_status' => $newStatus]
            );

            return $payment;
        });
    }

    /**
     * Reverse a completed payment.
     */
    public static function reverse(Payment $payment, string $reason, ?User $user = null): Payment
    {
        if (empty(trim($reason))) {
            throw new InvalidArgumentException("A non-empty reason is required to reverse a payment.");
        }

        return DB::transaction(function () use ($payment, $reason, $user) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status !== 'completed') {
                throw new InvalidArgumentException("Only completed payments can be reversed.");
            }

            $lockedInvoice = Invoice::where('id', $lockedPayment->invoice_id)->lockForUpdate()->firstOrFail();
            $decimals = $lockedInvoice->currency_decimals ?? 2;

            $lockedPayment->update([
                'status' => 'reversed',
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ]);

            // Re-sum active completed payments
            $newPaid = (string) $lockedInvoice->completedPayments()->sum('amount');
            $newPaid = DecimalMath::round($newPaid, $decimals);

            if (DecimalMath::comp($newPaid, '0') <= 0) {
                $newStatus = 'issued';
            } elseif (DecimalMath::comp($newPaid, (string)$lockedInvoice->total) >= 0) {
                $newStatus = 'paid';
            } else {
                $newStatus = 'partially_paid';
            }

            $lockedInvoice->update([
                'amount_paid' => $newPaid,
                'status' => $newStatus,
            ]);

            ActivityLogService::log(
                'payment.reversed',
                "Payment of {$lockedPayment->amount} for invoice {$lockedInvoice->invoice_number} was reversed: {$reason}",
                $lockedPayment,
                ['invoice_id' => $lockedInvoice->id, 'reason' => $reason, 'new_status' => $newStatus]
            );

            return $lockedPayment->fresh();
        });
    }
}
