<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $adminId = (int) Auth::guard('admin')->id();
        $type = trim((string) $request->query('type', ''));
        $readStatus = trim((string) $request->query('read_status', ''));
        $dateFrom = $this->normalizeDateString((string) $request->query('date_from', ''));
        $dateTo = $this->normalizeDateString((string) $request->query('date_to', ''));

        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $query = AdminNotification::query()
            ->with(['reads' => fn ($q) => $q->where('admin_id', $adminId)])
            ->orderByDesc('event_created_at')
            ->orderByDesc('id');

        $this->applyFilters($query, $adminId, [
            'type' => $type,
            'read_status' => $readStatus,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $notifications = (clone $query)->paginate(15)->withQueryString();

        $unreadCount = AdminNotification::query()
            ->whereDoesntHave('reads', fn ($q) => $q->where('admin_id', $adminId))
            ->count();

        $filteredTotal = (clone $query)->count();
        $filteredUnreadCount = (clone $query)
            ->whereDoesntHave('reads', fn ($q) => $q->where('admin_id', $adminId))
            ->count();

        $typeOptions = [
            'new_order' => 'New Booking',
            'payment_paid' => 'Payment Confirmed',
            'refund_requested' => 'Refund Requested',
            'refund_processed' => 'Refund Processed',
            'cart_added' => 'Cart Activity',
        ];

        $readStatusOptions = [
            '' => 'All statuses',
            'unread' => 'Unread only',
            'read' => 'Read only',
        ];

        $hasActiveFilters = $type !== '' || $readStatus !== '' || $dateFrom !== '' || $dateTo !== '';

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'selectedType' => $type,
            'selectedReadStatus' => $readStatus,
            'selectedDateFrom' => $dateFrom,
            'selectedDateTo' => $dateTo,
            'typeOptions' => $typeOptions,
            'readStatusOptions' => $readStatusOptions,
            'canOpenOrders' => Auth::guard('admin')->user()?->canAccessTransaction() ?? false,
            'filteredTotal' => $filteredTotal,
            'filteredUnreadCount' => $filteredUnreadCount,
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $adminId = (int) Auth::guard('admin')->id();
        $limit = max(1, min((int) $request->query('limit', 10), 30));

        $notifications = AdminNotification::query()
            ->with(['reads' => fn ($q) => $q->where('admin_id', $adminId)])
            ->orderByDesc('event_created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $unreadCount = AdminNotification::query()
            ->whereDoesntHave('reads', fn ($q) => $q->where('admin_id', $adminId))
            ->count();

        return response()->json([
            'items' => $notifications->map(fn (AdminNotification $notification) => $this->transform($notification))->values(),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        $adminId = (int) Auth::guard('admin')->id();
        $unreadIds = AdminNotification::query()
            ->whereDoesntHave('reads', fn ($q) => $q->where('admin_id', $adminId))
            ->pluck('id');

        if ($unreadIds->isNotEmpty()) {
            $now = now();
            DB::table('admin_notification_reads')->insertOrIgnore(
                $unreadIds->map(fn ($notificationId) => [
                    'admin_notification_id' => $notificationId,
                    'admin_id' => $adminId,
                    'read_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        }

        return response()->json([
            'marked' => $unreadIds->count(),
            'unread_count' => 0,
        ]);
    }

    private function transform(AdminNotification $notification): array
    {
        $payload = $notification->toPayload();
        $read = $notification->reads->first();

        $payload['is_read'] = $read !== null;
        $payload['read_at'] = $read?->read_at?->toIso8601String();

        return $payload;
    }

    private function applyFilters(Builder $query, int $adminId, array $filters): void
    {
        $type = trim((string) ($filters['type'] ?? ''));
        $readStatus = trim((string) ($filters['read_status'] ?? ''));
        $dateFrom = $this->normalizeDateString((string) ($filters['date_from'] ?? ''));
        $dateTo = $this->normalizeDateString((string) ($filters['date_to'] ?? ''));

        if ($type !== '') {
            $query->where('type', $type);
        }

        if ($readStatus === 'unread') {
            $query->whereDoesntHave('reads', fn ($q) => $q->where('admin_id', $adminId));
        } elseif ($readStatus === 'read') {
            $query->whereHas('reads', fn ($q) => $q->where('admin_id', $adminId));
        }

        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
            $query->where(DB::raw('COALESCE(event_created_at, created_at)'), '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) === 1) {
            $query->where(DB::raw('COALESCE(event_created_at, created_at)'), '<=', $dateTo . ' 23:59:59');
        }
    }

    private function normalizeDateString(string $value): string
    {
        $value = trim($value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
    }
}
