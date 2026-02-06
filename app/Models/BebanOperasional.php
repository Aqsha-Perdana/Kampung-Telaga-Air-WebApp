<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BebanOperasional extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'tanggal',
        'kategori',
        'deskripsi',
        'jumlah',
        'metode_pembayaran',
        'nomor_referensi',
        'keterangan',
        'bukti_pembayaran'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2'
    ];

    // Generate kode transaksi otomatis
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->kode_transaksi)) {
                $model->kode_transaksi = self::generateKodeTransaksi();
            }
        });
    }

    public static function generateKodeTransaksi()
    {
        $prefix = 'BO-' . date('Ymd') . '-';
        $lastTransaction = self::where('kode_transaksi', 'like', $prefix . '%')
            ->orderBy('kode_transaksi', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->kode_transaksi, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Scope untuk filter berdasarkan periode
    public function scopePeriode($query, $start, $end)
    {
        return $query->whereBetween('tanggal', [$start, $end]);
    }

    // Scope untuk filter berdasarkan kategori
    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    // Get total beban operasional
    public static function getTotalBeban($start = null, $end = null)
    {
        $query = self::query();
        
        if ($start && $end) {
            $query->periode($start, $end);
        }
        
        return $query->sum('jumlah');
    }

    // Get beban by kategori
    public static function getBebanByKategori($start = null, $end = null)
    {
        $query = self::selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori');
        
        if ($start && $end) {
            $query->periode($start, $end);
        }
        
        return $query->get();
    }
}