<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashOpeningBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'balance_date',
        'amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'balance_date' => 'date',
        'amount' => 'decimal:2',
    ];
}
