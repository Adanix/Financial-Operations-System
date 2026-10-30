<?php

namespace App\Models;

use App\Services\DecimalMath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'billing_address',
        'tax_number',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getLifetimeBilledAttribute(): string
    {
        $sum = '0.00';
        $invoices = $this->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'paid'])
            ->get(['total', 'currency_decimals']);

        foreach ($invoices as $inv) {
            $sum = DecimalMath::add($sum, $inv->total, $inv->currency_decimals ?? 2);
        }

        return $sum;
    }

    public function getOutstandingBalanceAttribute(): string
    {
        $sum = '0.00';
        $invoices = $this->invoices()
            ->whereIn('status', ['issued', 'partially_paid'])
            ->get(['total', 'amount_paid', 'currency_decimals']);

        foreach ($invoices as $inv) {
            $bal = DecimalMath::max('0.00', DecimalMath::sub($inv->total, $inv->amount_paid, $inv->currency_decimals ?? 2));
            $sum = DecimalMath::add($sum, $bal, $inv->currency_decimals ?? 2);
        }

        return $sum;
    }
}
