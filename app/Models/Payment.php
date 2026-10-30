<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'recorded_by',
        'amount',
        'payment_date',
        'method',
        'reference',
        'notes',
        'status',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'amount' => 'string',
        'payment_date' => 'date',
        'reversed_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }
}
