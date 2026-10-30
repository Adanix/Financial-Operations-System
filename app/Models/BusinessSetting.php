<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'singleton_key',
        'business_name',
        'legal_name',
        'email',
        'phone',
        'website',
        'address',
        'logo_path',
        'tax_number',
        'currency_code',
        'currency_symbol',
        'currency_decimals',
        'timezone',
        'date_format',
        'tax_label',
        'invoice_prefix',
        'sequence_padding',
        'default_payment_terms',
        'default_pricing_mode',
        'default_tax_rate_id',
        'default_notes',
        'bank_name',
        'account_holder',
        'account_number',
        'swift_bic',
        'branch',
        'payment_instructions',
    ];

    protected $casts = [
        'currency_decimals' => 'integer',
        'sequence_padding' => 'integer',
        'default_payment_terms' => 'integer',
    ];

    public static function current(): self
    {
        return self::firstOrCreate(
            ['singleton_key' => 'default'],
            [
                'business_name' => 'SoloLedger Corp',
                'email' => 'billing@example.com',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'currency_decimals' => 2,
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'tax_label' => 'Tax',
                'invoice_prefix' => 'INV',
                'sequence_padding' => 4,
                'default_payment_terms' => 14,
                'default_pricing_mode' => 'exclusive',
                'bank_name' => 'Demo Commercial Bank',
                'account_holder' => 'SoloLedger Corp',
                'account_number' => '1234-5678-9012',
                'swift_bic' => 'DEMOUS33XXX',
                'payment_instructions' => 'Please include invoice number in the transfer description.',
            ]
        );
    }
}
