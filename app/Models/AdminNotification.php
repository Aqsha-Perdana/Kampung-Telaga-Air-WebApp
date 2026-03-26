<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_uuid',
        'type',
        'title',
        'message',
        'order_id',
        'customer_name',
        'customer_email',
        'package_names',
        'total_amount',
        'currency',
        'total_people',
        'origin',
        'source_ip',
        'status',
        'meta',
        'event_created_at',
    ];

    protected $casts = [
        'package_names' => 'array',
        'meta' => 'array',
        'total_amount' => 'float',
        'total_people' => 'integer',
        'event_created_at' => 'datetime',
    ];

    public function reads(): HasMany
    {
        return $this->hasMany(AdminNotificationRead::class);
    }

    public function toPayload(): array
    {
        return array_merge([
            'notification_id' => $this->id,
            'id' => (string) $this->event_uuid,
            'type' => (string) $this->type,
            'title' => (string) $this->title,
            'message' => (string) ($this->message ?? ''),
            'order_id' => $this->order_id ? (string) $this->order_id : null,
            'customer_name' => (string) ($this->customer_name ?? ''),
            'customer_email' => (string) ($this->customer_email ?? ''),
            'package_names' => array_values($this->package_names ?? []),
            'total_amount' => (float) ($this->total_amount ?? 0),
            'currency' => (string) ($this->currency ?? 'MYR'),
            'total_people' => (int) ($this->total_people ?? 0),
            'origin' => (string) ($this->origin ?? ''),
            'source_ip' => (string) ($this->source_ip ?? ''),
            'status' => (string) ($this->status ?? ''),
            'created_at' => optional($this->event_created_at ?? $this->created_at)?->toIso8601String(),
            'action_url' => route('admin.notifications.index'),
            'order_detail_url' => $this->order_id ? route('sales.detail', $this->order_id) : null,
        ], $this->meta ?? []);
    }
}

