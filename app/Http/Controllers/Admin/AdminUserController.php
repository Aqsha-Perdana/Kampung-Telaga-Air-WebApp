<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $nationality = (string) $request->query('nationality', 'all');
        $sort = (string) $request->query('sort', 'latest');
        $perPage = (int) $request->query('per_page', 15);

        if (!in_array($perPage, [10, 15, 25, 50], true)) {
            $perPage = 15;
        }

        $usersQuery = User::query()
            ->withCount('orders')
            ->withCount([
                'orders as paid_orders_count' => fn ($query) => $query->where('status', 'paid'),
            ])
            ->withSum([
                'orders as total_spent' => fn ($query) => $query->where('status', 'paid'),
            ], 'base_amount')
            ->withMax('orders as last_order_at', 'created_at');

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($nationality !== 'all') {
            $usersQuery->where('nationality', $nationality);
        }

        match ($sort) {
            'oldest' => $usersQuery->orderBy('created_at'),
            'most_orders' => $usersQuery->orderByDesc('orders_count')->orderByDesc('created_at'),
            'highest_spending' => $usersQuery->orderByDesc('total_spent')->orderByDesc('created_at'),
            default => $usersQuery->orderByDesc('created_at'),
        };

        $users = $usersQuery->paginate($perPage)->withQueryString();

        $stats = [
            'total_users' => User::count(),
            'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'active_buyers' => User::whereHas('orders', fn ($query) => $query->where('status', 'paid'))->count(),
            'never_ordered' => User::doesntHave('orders')->count(),
        ];

        $nationalities = User::query()
            ->whereNotNull('nationality')
            ->where('nationality', '!=', '')
            ->select('nationality')
            ->distinct()
            ->orderBy('nationality')
            ->pluck('nationality');

        return view('admin.users.index', compact(
            'users',
            'stats',
            'search',
            'nationality',
            'sort',
            'perPage',
            'nationalities',
        ));
    }

    public function show(Request $request, User $user)
    {
        $status = (string) $request->query('status', 'all');

        $ordersBaseQuery = $user->orders();

        $totalOrders = (clone $ordersBaseQuery)->count();
        $paidOrders = (clone $ordersBaseQuery)->where('status', 'paid')->count();
        $pendingOrders = (clone $ordersBaseQuery)->where('status', 'pending')->count();
        $cancelledOrders = (clone $ordersBaseQuery)->whereIn('status', ['failed', 'cancelled'])->count();
        $totalSpent = (float) (clone $ordersBaseQuery)->where('status', 'paid')->sum('base_amount');
        $lastOrderAt = (clone $ordersBaseQuery)->latest('created_at')->value('created_at');

        $profileFields = ['name', 'email', 'phone', 'nationality', 'address'];
        $filledProfileFields = collect($profileFields)->filter(fn ($field) => filled($user->{$field} ?? null))->count();
        $profileCompletion = (int) round(($filledProfileFields / count($profileFields)) * 100);

        $statusBreakdown = (clone $ordersBaseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $ordersQuery = $user->orders()
            ->with(['items:id,id_order,nama_paket,jumlah_peserta'])
            ->latest('created_at');

        if ($status !== 'all') {
            $ordersQuery->where('status', $status);
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        $availableStatuses = $user->orders()
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $summary = [
            'total_orders' => $totalOrders,
            'paid_orders' => $paidOrders,
            'pending_orders' => $pendingOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_spent' => $totalSpent,
            'last_order_at' => $lastOrderAt,
            'avg_order_value' => $paidOrders > 0 ? $totalSpent / $paidOrders : 0,
            'profile_completion' => $profileCompletion,
        ];

        return view('admin.users.show', compact(
            'user',
            'orders',
            'summary',
            'statusBreakdown',
            'availableStatuses',
            'status',
        ));
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validateWithBag('passwordReset', [
            'admin_current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'admin_current_password.required' => 'Admin current password is required.',
            'new_password.required' => 'New user password is required.',
            'new_password.min' => 'New user password must be at least 8 characters.',
            'new_password.confirmed' => 'New user password confirmation does not match.',
        ]);

        $admin = $request->user('admin');

        if (!Hash::check($validated['admin_current_password'], $admin->password)) {
            return back()->withErrors([
                'admin_current_password' => 'Your admin password is incorrect.',
            ], 'passwordReset');
        }

        if (Hash::check($validated['new_password'], $user->password)) {
            return back()->withErrors([
                'new_password' => 'New password must be different from the current user password.',
            ], 'passwordReset');
        }

        $user->password = Hash::make($validated['new_password']);
        $user->remember_token = Str::random(60);
        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User password has been reset successfully.');
    }
}
