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
        'subtotal'
    ];

    protected $casts = [
        'tanggal_keberangkatan' => 'date',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2'
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
