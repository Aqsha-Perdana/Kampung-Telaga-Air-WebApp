<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boat extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_boat',
        'nama',
        'kapasitas',
        'harga_sewa',
        'foto',
        'is_active'
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
        'is_active' => 'boolean',
        'kapasitas' => 'integer',
    ];

    public function getFormattedHargaAttribute()
{
    return format_ringgit($this->harga_sewa);
}

    // Auto generate ID Boat
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($boat) {
            if (empty($boat->id_boat)) {
                $lastBoat = self::orderBy('id', 'desc')->first();
                $lastNumber = $lastBoat ? intval(substr($lastBoat->id_boat, 2)) : 0;
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                $boat->id_boat = 'BT' . $newNumber;
            }
        });
    }
}