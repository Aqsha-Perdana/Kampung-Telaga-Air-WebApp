<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Footage360 extends Model
{
    use HasFactory;

    protected $table = 'footage360';
    protected $primaryKey = 'id_footage360';

    protected $fillable = [
        'id_destinasi',
        'judul',
        'deskripsi',
        'file_foto',
        'file_lrv',
        'cloudinary_public_id',
        'cloudinary_public_id_lrv',
        'urutan',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function destinasi()
    {
        return $this->belongsTo(Destinasi::class, 'id_destinasi', 'id_destinasi');
    }
}