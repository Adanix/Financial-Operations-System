<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'sort_order',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'line_discount_amount',
        'tax_rate_id',
        'tax_name',
        'tax_rate',
        'gross_amount',
        'net_amount',
        'invoice_discount_allocation',
        'taxable_amount',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'quantity' => 'string',
        'unit_price' => 'string',
        'line_discount_amount' => 'string',
        'tax_rate' => 'string',
        'gross_amount' => 'string',
        'net_amount' => 'string',
        'invoice_discount_allocation' => 'string',
        'taxable_amount' => 'string',
        'tax_amount' => 'string',
        'line_total' => 'string',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }
}
