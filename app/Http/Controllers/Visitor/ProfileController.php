<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $allowedTabs = ['profile', 'security', 'activity'];
        $activeTab = in_array($request->query('tab'), $allowedTabs, true)
            ? $request->query('tab')
            : 'profile';

        if (session('errors')) {
            if (session('errors')->hasBag('updatePassword')) {
                $activeTab = 'security';
            } elseif (session('errors')->hasBag('profileUpdate')) {
                $activeTab = 'profile';
            }
        }

        $ordersQuery = Order::with('items')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        $recentOrders = (clone $ordersQuery)->take(6)->get();
        $totalOrders = Order::where('user_id', $user->id)->count();
        $completedOrders = Order::where('user_id', $user->id)->where('status', 'paid')->count();
        $pendingOrders = Order::where('user_id', $user->id)->whereIn('status', ['pending', 'refund_requested'])->count();
        $totalSpent = (float) Order::where('user_id', $user->id)->where('status', 'paid')->sum('base_amount');
        $latestOrderAt = Order::where('user_id', $user->id)->latest('created_at')->value('created_at');

        return view('landing.profile.index', compact(
            'user',
            'activeTab',
            'recentOrders',
            'totalOrders',
            'completedOrders',
            'pendingOrders',
            'totalSpent',
            'latestOrderAt'
        ));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validateWithBag('profileUpdate', [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email is already used by another account.',
            'phone.regex' => 'Invalid phone number format.',
        ]);

        $user->fill($validated);
        $user->save();

        return redirect()->route('wisatawan.profile', ['tab' => 'profile'])
            ->with('success_profile', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ], 'updatePassword');
        }

        if (Hash::check($validated['new_password'], $user->password)) {
            return back()->withErrors([
                'new_password' => 'New password must be different from the current password.',
            ], 'updatePassword');
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return redirect()->route('wisatawan.profile', ['tab' => 'security'])
            ->with('success_password', 'Password updated successfully.');
    }
}
