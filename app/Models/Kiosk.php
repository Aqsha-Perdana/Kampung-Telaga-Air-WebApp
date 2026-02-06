<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kiosk extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_kiosk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_kiosk',
        'nama',
        'kapasitas',
        'harga_per_paket',
        'deskripsi'
    ];

    protected $casts = [
        'harga_per_paket' => 'decimal:2',
    ];

    // Relasi one-to-many dengan foto
    public function fotos()
    {
        return $this->hasMany(FotoKiosk::class, 'id_kiosk', 'id_kiosk')
                    ->orderBy('urutan');
    }

    // Auto generate ID saat create
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id_kiosk)) {
                $model->id_kiosk = self::generateKioskId();
            }
        });
    }
    private static function generateKioskId()
    {
        // Ambil kiosk terakhir berdasarkan ID
        $lastKiosk = self::orderBy('id_kiosk', 'desc')->first();
        
        if (!$lastKiosk) {
            // Jika belum ada data, mulai dari KSK00001
            return 'KSK00001';
        }
        
        // Extract nomor dari ID terakhir (contoh: KSK00002 -> 2)
        $lastNumber = (int) substr($lastKiosk->id_kiosk, 3);
        
        // Increment nomor
        $newNumber = $lastNumber + 1;
        
        // Format dengan padding 5 digit
        return 'KSK' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}

class FotoKiosk extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kiosk',
        'foto',
        'urutan'
    ];

    // Relasi many-to-one dengan kiosk
    public function kiosk()
    {
        return $this->belongsTo(Kiosk::class, 'id_kiosk', 'id_kiosk');
    }
}