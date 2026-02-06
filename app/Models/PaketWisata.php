<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketWisata extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_paket';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_paket',
        'nama_paket',
        'durasi_hari',
        'deskripsi',
        'harga_total',      // Cost price (auto-calculated)
        'harga_jual',       // Selling price (manual input)
        'diskon_nominal',   // Discount in RM
        'diskon_persen',    // Discount in %
        'tipe_diskon',      // Type: nominal/persen/none
        'harga_final',      // Final price after discount
        'status'
    ];

    protected $casts = [
        'harga_total' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'harga_final' => 'decimal:2',
        'durasi_hari' => 'integer'
    ];

    // Relasi many-to-many dengan destinasi
    public function destinasis()
    {
        return $this->belongsToMany(Destinasi::class, 'paket_wisata_destinasi', 'id_paket', 'id_destinasi')
                    ->withPivot('hari_ke')
                    ->withTimestamps();
    }

    // Relasi many-to-many dengan homestay
    public function homestays()
    {
        return $this->belongsToMany(
            Homestay::class, 
            'paket_wisata_homestay',
            'id_paket',
            'id_homestay',
            'id_paket',
            'id_homestay'
        )
        ->withPivot('jumlah_malam')
        ->withTimestamps();
    }

    // Relasi many-to-many dengan paket culinary
    public function paketCulinaries()
    {
        return $this->belongsToMany(PaketCulinary::class, 'paket_wisata_culinary', 'id_paket', 'id_paket_culinary')
                    ->withPivot('hari_ke')
                    ->withTimestamps();
    }

    // Relasi many-to-many dengan boat
    public function boats()
    {
        return $this->belongsToMany(Boat::class, 'paket_wisata_boat', 'id_paket', 'id_boat')
                    ->withPivot('hari_ke')
                    ->withTimestamps();
    }

    // Relasi many-to-many dengan kiosk
    public function kiosks()
    {
        return $this->belongsToMany(Kiosk::class, 'paket_wisata_kiosk', 'id_paket', 'id_kiosk')
                    ->withPivot('hari_ke')
                    ->withTimestamps();
    }

    // Relasi one-to-many dengan itinerary
    public function itineraries()
    {
        return $this->hasMany(PaketWisataItinerary::class, 'id_paket', 'id_paket')
                    ->orderBy('hari_ke');
    }

    // Calculate final price based on discount
    public function calculateHargaFinal()
    {
        $hargaJual = $this->harga_jual ?? 0;
        
        if ($this->tipe_diskon === 'nominal') {
            return max(0, $hargaJual - $this->diskon_nominal);
        } elseif ($this->tipe_diskon === 'persen') {
            return max(0, $hargaJual - ($hargaJual * $this->diskon_persen / 100));
        }
        
        return $hargaJual;
    }

    // Get profit margin
    public function getProfitMargin()
    {
        $modal = $this->harga_total ?? 0;
        $final = $this->harga_final ?? 0;
        
        if ($modal == 0) return 0;
        
        return (($final - $modal) / $modal) * 100;
    }

    // Auto generate ID saat create
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id_paket)) {
                $model->id_paket = self::generatePaketId();
            }
        });
    }

    /**
     * Generate ID Paket Wisata baru berdasarkan ID terakhir
     */
    private static function generatePaketId()
    {
        // Ambil paket terakhir berdasarkan ID
        $lastPaket = self::orderBy('id_paket', 'desc')->first();
        
        if (!$lastPaket) {
            // Jika belum ada data, mulai dari PKT00001
            return 'PKT00001';
        }
        
        // Extract nomor dari ID terakhir (contoh: PKT00002 -> 2)
        $lastNumber = (int) substr($lastPaket->id_paket, 3);
        
        // Increment nomor
        $newNumber = $lastNumber + 1;
        
        // Format dengan padding 5 digit
        return 'PKT' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}

class PaketWisataItinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_paket',
        'hari_ke',
        'judul_hari',
        'deskripsi_kegiatan'
    ];

    public function paketWisata()
    {
        return $this->belongsTo(PaketWisata::class, 'id_paket', 'id_paket');
    }
}