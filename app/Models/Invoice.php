<?php

namespace App\Models;

use App\Services\DecimalMath;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'invoice_number',
        'status',
        'pricing_mode',
        'currency_code',
        'currency_symbol',
        'currency_decimals',
        'issue_date',
        'due_date',
        'reference',
        'gross_subtotal',
        'line_discount_total',
        'net_subtotal',
        'invoice_discount_type',
        'invoice_discount_value',
        'invoice_discount_total',
        'discount_total',
        'taxable_subtotal',
        'tax_total',
        'total',
        'amount_paid',
        'notes',
        'payment_terms_text',
        'business_snapshot',
        'customer_snapshot',
        'bank_snapshot',
        'issued_at',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'currency_decimals' => 'integer',
        'business_snapshot' => 'array',
        'customer_snapshot' => 'array',
        'bank_snapshot' => 'array',
        'issue_date' => 'date',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc');
    }

    public function completedPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('status', 'completed')->orderBy('payment_date', 'desc');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(InvoiceEmailLog::class)->orderBy('created_at', 'desc');
    }

    public function getOutstandingBalanceAttribute(): string
    {
        $decimals = $this->currency_decimals ?? 2;
        $bal = DecimalMath::sub((string)$this->total, (string)$this->amount_paid, $decimals);
        return DecimalMath::max('0.00', $bal, $decimals);
    }

    public function isOverdue(?string $timezone = null): bool
    {
        if (!in_array($this->status, ['issued', 'partially_paid'])) {
            return false;
        }

        if (DecimalMath::comp($this->outstanding_balance, '0') <= 0) {
            return false;
        }

        $tz = $timezone ?: BusinessSetting::current()->timezone;
        $today = Carbon::now($tz)->toDateString();
        $dueDateStr = is_string($this->due_date) ? $this->due_date : $this->due_date->format('Y-m-d');

        return $dueDateStr < $today;
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->isOverdue()) {
            return 'overdue';
        }
        return $this->status;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partially_paid', 'paid']);
    }

    public function scopeQualifying(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partially_paid', 'paid']);
    }
}
