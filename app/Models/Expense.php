<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id',
        'expense_date',
        'vendor',
        'description',
        'entered_amount',
        'pricing_mode',
        'tax_rate_id',
        'tax_name',
        'tax_rate',
        'tax_recoverable',
        'net_amount',
        'tax_amount',
        'gross_amount',
        'payment_status',
        'payment_date',
        'payment_method',
        'reference',
        'receipt_path',
        'receipt_original_name',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'payment_date' => 'date',
        'tax_recoverable' => 'boolean',
        'entered_amount' => 'string',
        'net_amount' => 'string',
        'tax_amount' => 'string',
        'gross_amount' => 'string',
        'tax_rate' => 'string',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function getExpenseForPnlAttribute(): string
    {
        return $this->tax_recoverable ? (string)$this->net_amount : (string)$this->gross_amount;
    }

    public function getRecoverableInputTaxAttribute(): string
    {
        return $this->tax_recoverable ? (string)$this->tax_amount : '0.00';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
