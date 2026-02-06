<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Culinary extends Model
{
    use HasFactory;

    protected $table = 'culinaries';
    protected $primaryKey = 'id_culinary';
    public $incrementing = false; // Karena bukan auto increment
    protected $keyType = 'string'; // Karena VARCHAR

    protected $fillable = [
        'id_culinary',
        'nama',
        'lokasi',
        'deskripsi'
    ];

    // Relasi one-to-many dengan paket
    public function pakets()
    {
        return $this->hasMany(PaketCulinary::class, 'id_culinary', 'id_culinary');
    }

    // Relasi one-to-many dengan foto
    public function fotos()
    {
        return $this->hasMany(FotoCulinary::class, 'id_culinary', 'id_culinary')
                    ->orderBy('urutan');
    }

    // Auto generate ID saat create
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id_culinary)) {
                // Generate ID format: CUL001, CUL002, dst
                $lastId = self::orderBy('id_culinary', 'desc')->first();
                
                if ($lastId) {
                    $number = (int) substr($lastId->id_culinary, 3) + 1;
                } else {
                    $number = 1;
                }
                
                $model->id_culinary = 'CUL' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}


class FotoCulinary extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_culinary',
        'foto',
        'urutan'
    ];

    // Relasi many-to-one dengan culinary
    public function culinary()
    {
        return $this->belongsTo(Culinary::class, 'id_culinary', 'id_culinary');
    }
}