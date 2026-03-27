<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyPackageSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_date',
        'package_id',
        'package_name',
        'package_status',
        'total_orders',
        'total_participants',
        'total_revenue',
        'total_profit',
        'margin_percent',
        'snapshot_json',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'snapshot_json' => 'array',
    ];
}
