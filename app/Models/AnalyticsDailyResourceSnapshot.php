<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyResourceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_date',
        'resource_type',
        'total_resources',
        'booked_resources',
        'active_capacity',
        'utilization_percent',
        'snapshot_json',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'snapshot_json' => 'array',
    ];
}
