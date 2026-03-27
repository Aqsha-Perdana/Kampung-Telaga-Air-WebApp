<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyFinanceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_date',
        'revenue',
        'cost_of_sales',
        'gross_profit',
        'operating_expenses',
        'net_profit',
        'gross_margin_percent',
        'net_margin_percent',
        'refund_fee_income',
        'net_cash_movement',
        'top_expense_category',
        'expense_breakdown_json',
        'snapshot_json',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'expense_breakdown_json' => 'array',
        'snapshot_json' => 'array',
    ];
}
