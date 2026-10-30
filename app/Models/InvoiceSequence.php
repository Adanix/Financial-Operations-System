<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    protected $fillable = [
        'year',
        'next_number',
    ];

    protected $casts = [
        'year' => 'integer',
        'next_number' => 'integer',
    ];
}
