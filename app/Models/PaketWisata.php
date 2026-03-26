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
        'minimum_participants',
        'maximum_participants',
        'deskripsi',
        'harga_total',
        'harga_jual',
        'diskon_nominal',
        'diskon_persen',
        'tipe_diskon',
        'harga_final',
        'status'
    ];

    protected $casts = [
        'harga_total' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'harga_final' => 'decimal:2',
        'durasi_hari' => 'integer',
        'minimum_participants' => 'integer',
        'maximum_participants' => 'integer',
    ];

    public function destinasis()
    {
        return $this->belongsToMany(Destinasi::class, 'paket_wisata_destinasi', 'id_paket', 'id_destinasi')
                    ->withPivot('hari_ke')
                    ->withTimestamps();
    }

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

    public function paketCulinaries()
    {
        return $this->belongsToMany(PaketCulinary::class, 'paket_wisata_culinary', 'id_paket', 'id_paket_culinary')
                    ->withPivot('hari_ke')
                    ->withTimestamps();
    }

    public function boats()
    {
        return $this->belongsToMany(Boat::class, 'paket_wisata_boat', 'id_paket', 'id_boat')
                    ->withPivot('hari_ke')
                    ->withTimestamps();
    }

    public function kiosks()
    {
        return $this->belongsToMany(Kiosk::class, 'paket_wisata_kiosk', 'id_paket', 'id_kiosk')
                    ->withPivot('hari_ke')
                    ->withTimestamps();
    }

    public function itineraries()
    {
        return $this->hasMany(PaketWisataItinerary::class, 'id_paket', 'id_paket')
                    ->orderBy('hari_ke');
    }

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

    public function getProfitMargin()
    {
        $modal = $this->harga_total ?? 0;
        $final = $this->harga_final ?? 0;

        if ($modal == 0) return 0;

        return (($final - $modal) / $modal) * 100;
    }

    public function getParticipantRangeLabelAttribute(): string
    {
        $min = max((int) ($this->minimum_participants ?? 1), 1);
        $max = $this->maximum_participants;

        if ($max && $max >= $min) {
            return $min === $max
                ? $min . ' participant' . ($min > 1 ? 's' : '')
                : $min . '-' . $max . ' participants';
        }

        return 'Minimum ' . $min . ' participant' . ($min > 1 ? 's' : '');
    }

    public function getCapacityBadgeLabelAttribute(): string
    {
        $min = max((int) ($this->minimum_participants ?? 1), 1);
        $max = $this->maximum_participants;

        if ($max && $max >= $min) {
            return $min === $max
                ? 'For ' . $min . ' participant' . ($min > 1 ? 's' : '')
                : 'For ' . $min . '-' . $max . ' participants';
        }

        return 'From ' . $min . ' participant' . ($min > 1 ? 's' : '');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_paket)) {
                $model->id_paket = self::generatePaketId();
            }
        });
    }

    private static function generatePaketId()
    {
        $lastPaket = self::orderBy('id_paket', 'desc')->first();

        if (!$lastPaket) {
            return 'PKT00001';
        }

        $lastNumber = (int) substr($lastPaket->id_paket, 3);
        $newNumber = $lastNumber + 1;

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
