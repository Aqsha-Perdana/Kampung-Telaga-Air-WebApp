<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KioskPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['kiosk_id', 'foto'];

    public function kiosk()
    {
        return $this->belongsTo(Kiosk::class);
    }
}