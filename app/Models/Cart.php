<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'id_paket',
        'jumlah_peserta',
        'tanggal_keberangkatan',
        'catatan',
        'harga_satuan',
        'subtotal'
    ];

    protected $casts = [
        'tanggal_keberangkatan' => 'date',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    /**
     * Relasi ke PaketWisata
     */
    public function paket()
    {
        return $this->belongsTo(PaketWisata::class, 'id_paket', 'id_paket');
    }

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope untuk cart berdasarkan user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk cart berdasarkan session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId)->whereNull('user_id');
    }

    /**
     * Check if cart belongs to authenticated user
     */
    public function belongsToAuthUser()
    {
        return $this->user_id && $this->user_id === auth()->id();
    }

    /**
     * Check if cart is guest cart
     */
    public function isGuestCart()
    {
        return !$this->user_id && $this->session_id;
    }
}