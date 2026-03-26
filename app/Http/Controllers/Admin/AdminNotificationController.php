<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $adminId = (int) Auth::guard('admin')->id();
        $type = trim((string) $request->query('type', ''));

        $query = AdminNotification::query()
            ->with(['reads' => fn ($q) => $q->where('admin_id', $adminId)])
            ->orderByDesc('event_created_at')
            ->orderByDesc('id');

        if ($type !== '') {
            $query->where('type', $type);
        }

        $notifications = $query->paginate(15)->withQueryString();

        $unreadCount = AdminNotification::query()
            ->whereDoesntHave('reads', fn ($q) => $q->where('admin_id', $adminId))
            ->count();

        $typeOptions = [
            'new_order' => 'New Order',
            'payment_paid' => 'Payment Paid',
            'refund_requested' => 'Refund Requested',
            'refund_processed' => 'Refund Processed',
            'cart_added' => 'Added to Cart',
        ];

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'selectedType' => $type,
            'typeOptions' => $typeOptions,
            'canOpenOrders' => Auth::guard('admin')->user()?->canAccessTransaction() ?? false,
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
}

