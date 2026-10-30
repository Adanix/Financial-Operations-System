<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'rate',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'string',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function invoiceItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
