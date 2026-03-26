<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminProfileController extends Controller
{
    public function show()
    {
        return view('admin.profile.index');
    }

    public function update(Request $request)
    {
        $admin = $request->user('admin');

        $validated = $request->validateWithBag('profileUpdate', [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email is already used by another admin account.',
            'phone.regex' => 'Invalid phone number format.',
        ]);

        $admin->fill($validated);
        $admin->save();

        return redirect()->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        $admin = $request->user('admin');

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ], 'passwordUpdate');
        }

        if (Hash::check($validated['new_password'], $admin->password)) {
            return back()->withErrors([
                'new_password' => 'New password must be different from current password.',
            ], 'passwordUpdate');
        }

        $admin->password = Hash::make($validated['new_password']);
        $admin->save();

        return redirect()->route('admin.profile')
            ->with('success', 'Password updated successfully.');
    }
}

