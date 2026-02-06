<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_order',
        'payment_intent_id',
        'payment_method',
        'amount',
        'currency',
        'status',
        'response_data'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_data' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }
}
