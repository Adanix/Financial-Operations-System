<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\InvoiceSequence;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * Concurrency-safe invoice number generation.
     * Must be called within an active DB transaction.
     */
    public static function generateNextNumber(Carbon|string $issueDate): string
    {
        $settings = BusinessSetting::current();
        $prefix = $settings->invoice_prefix ?: 'INV';
        $padding = (int) ($settings->sequence_padding ?: 4);

        $dateObj = $issueDate instanceof Carbon ? $issueDate : Carbon::parse($issueDate);
        $year = (int) $dateObj->year;

        // Ensure row exists, then lock for update
        $seq = InvoiceSequence::where('year', $year)->lockForUpdate()->first();
        if (!$seq) {
            $seq = InvoiceSequence::create([
                'year' => $year,
                'next_number' => 1,
            ]);
            // Re-fetch with lock
            $seq = InvoiceSequence::where('year', $year)->lockForUpdate()->first();
        }

        $allocatedNumber = $seq->next_number;
        $seq->next_number = $allocatedNumber + 1;
        $seq->save();

        $formattedSeq = str_pad((string) $allocatedNumber, $padding, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$formattedSeq}";
    }
}
