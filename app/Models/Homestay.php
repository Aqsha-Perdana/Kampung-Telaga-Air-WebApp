<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Homestay extends Model
{
    use HasFactory;

    protected $table = 'homestays';
    protected $primaryKey = 'id_homestay';  // Gunakan id_homestay sebagai PK
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_homestay',
        'nama',
        'kapasitas',
        'harga_per_malam',
        'foto',
        'is_active'
    ];

    protected $casts = [
        'harga_per_malam' => 'decimal:2',
        'is_active' => 'boolean',
        'kapasitas' => 'integer',
    ];

    public function getFormattedHargaAttribute()
{
    return format_ringgit($this->harga_per_malam);
}


     public function paketWisatas()
    {
        return $this->belongsToMany(PaketWisata::class, 'paket_wisata_homestay', 'id_homestay', 'id_paket', 'id_homestay', 'id_paket')
                    ->withPivot('jumlah_malam')
                    ->withTimestamps();
    }

    // Auto generate ID Homestay
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($homestay) {
            if (empty($homestay->id_homestay)) {
                $lastHomestay = self::orderBy('id', 'desc')->first();
                $lastNumber = $lastHomestay ? intval(substr($lastHomestay->id_homestay, 2)) : 0;
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                $homestay->id_homestay = 'HS' . $newNumber;
            }
        });
    }
}