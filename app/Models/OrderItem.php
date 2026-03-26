<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_order',
        'id_paket',
        'nama_paket',
        'durasi_hari',
        'jumlah_peserta',
        'tanggal_keberangkatan',
        'catatan',
        'harga_satuan',
        'subtotal',
        'boat_cost_total',
        'homestay_cost_total',
        'culinary_cost_total',
        'kiosk_cost_total',
        'vendor_cost_total',
        'company_profit_total',
        'boat_cost_items',
        'homestay_cost_items',
        'culinary_cost_items',
        'kiosk_cost_items',
    ];

    protected $casts = [
        'tanggal_keberangkatan' => 'date',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'boat_cost_total' => 'decimal:2',
        'homestay_cost_total' => 'decimal:2',
        'culinary_cost_total' => 'decimal:2',
        'kiosk_cost_total' => 'decimal:2',
        'vendor_cost_total' => 'decimal:2',
        'company_profit_total' => 'decimal:2',
        'boat_cost_items' => 'array',
        'homestay_cost_items' => 'array',
        'culinary_cost_items' => 'array',
        'kiosk_cost_items' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    public function paket()
    {
        return $this->belongsTo(PaketWisata::class, 'id_paket', 'id_paket');
    }
}
