<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_order';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'session_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'cart_signature',
        
        // PAYMENT AMOUNT - ALWAYS MYR
        'base_amount',      // Harga asli dalam MYR (SUMBER KEBENARAN)
        'total_amount',     // = base_amount (untuk compatibility)
        
        // DISPLAY ONLY (optional)
        'display_currency',       // USD, IDR, etc (nullable)
        'display_amount',         // Untuk ditampilkan ke user (nullable)
        'display_exchange_rate',  // Rate saat checkout (nullable)
        
        'payment_method',
        'payment_intent_id',
        'payment_proof',
        'status',
        'paid_at',
        'redeem_code',
        'is_redeemed',
        'resource_type',
        'resource_id',
        'refund_reason',
        'refund_rejected_reason',
        'refund_amount',
        'refund_fee',
        'refund_status',
        'stripe_refund_id',
        'refunded_at',
        'refund_failure_reason',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'is_redeemed' => 'boolean',
        'base_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'display_amount' => 'decimal:2',
        'display_exchange_rate' => 'decimal:6',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'id_order', 'id_order');
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class, 'id_order', 'id_order');
    }

    // Auto generate Order ID
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Generate Order ID
            if (empty($model->id_order)) {
                $date = now()->format('Ymd');
                $count = Order::whereDate('created_at', today())->count() + 1;
                $model->id_order = 'ORD-' . $date . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
            }

            // Auto set total_amount = base_amount
            if (empty($model->total_amount)) {
                $model->total_amount = $model->base_amount;
            }

            // Auto set session_id untuk guest
            if (empty($model->user_id) && empty($model->session_id)) {
                $model->session_id = session()->getId();
            }
        });
    }

    // Scopes
    public function isGuestOrder()
    {
        return empty($this->user_id);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    // Accessors - PAYMENT CURRENCY (ALWAYS MYR)
    public function getPaymentCurrencyAttribute()
    {
        return 'MYR';
    }

    public function getPaymentAmountAttribute()
    {
        return $this->base_amount; // ALWAYS use base_amount for payment
    }

    public function getFormattedPaymentAttribute()
    {
        return 'RM ' . number_format($this->base_amount, 2);
    }

    // Accessors - DISPLAY CURRENCY (optional, for UI only)
    public function getDisplayCurrencySymbolAttribute()
    {
        if (!$this->display_currency) {
            return 'RM'; // fallback to MYR
        }

        $symbols = [
            'MYR' => 'RM',
            'USD' => '$',
            'IDR' => 'Rp',
            'SGD' => 'S$',
            'EUR' => '€',
            'GBP' => '£',
            'AUD' => 'A$',
            'JPY' => '¥',
            'CNY' => '¥'
        ];

        return $symbols[$this->display_currency] ?? $this->display_currency;
    }

    public function getFormattedDisplayAttribute()
    {
        if (!$this->display_amount || !$this->display_currency) {
            return $this->formatted_payment; // fallback to MYR
        }

        $decimals = in_array($this->display_currency, ['IDR', 'JPY']) ? 0 : 2;
        return $this->display_currency_symbol . ' ' . number_format($this->display_amount, $decimals);
    }
}
