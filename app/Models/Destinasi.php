<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Destinasi extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_destinasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_destinasi',
        'nama',
        'lokasi',
        'deskripsi'
    ];

    // Relasi one-to-many dengan foto
    public function fotos()
    {
        return $this->hasMany(FotoDestinasi::class, 'id_destinasi', 'id_destinasi')
                    ->orderBy('urutan');
    }

    public function footage360()
    {
        return $this->hasMany(Footage360::class, 'id_destinasi', 'id_destinasi')
                    ->orderBy('urutan');
    }


    // Auto generate ID saat create
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id_destinasi)) {
                $model->id_destinasi = 'DST' . str_pad(
                    (Destinasi::count() + 1), 
                    5, 
                    '0', 
                    STR_PAD_LEFT
                );
            }
        });
    }
}

class FotoDestinasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_destinasi',
        'foto',
        'urutan'
    ];

    // Relasi many-to-one dengan destinasi
    public function destinasi()
    {
        return $this->belongsTo(Destinasi::class, 'id_destinasi', 'id_destinasi');
    }


}