<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketCulinary extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_culinary',
        'nama_paket',
        'kapasitas',
        'harga',
        'deskripsi_paket'
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    // Relasi many-to-one dengan culinary
    public function culinary()
    {
        return $this->belongsTo(Culinary::class, 'id_culinary', 'id_culinary');
    }
}
