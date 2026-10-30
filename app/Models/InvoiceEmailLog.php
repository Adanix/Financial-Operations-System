<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceEmailLog extends Model
{
    protected $fillable = [
        'invoice_id',
        'recipient_email',
        'subject',
        'body_message',
        'status',
        'attachment_path',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
