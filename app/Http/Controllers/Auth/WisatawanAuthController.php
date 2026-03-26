<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\CartController;

class WisatawanAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Show Login Form
    public function showLoginForm()
    {
        return view('auth.login-wisatawan');
    }

    // Login Process
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Attempt to login
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // ===== MERGE GUEST CART TO USER CART =====
            CartController::mergeGuestCartToUser(Auth::id());
            // =========================================

            // Get user name
            $userName = Auth::user()->name;

            // Redirect to intended page or home
            return redirect()->intended('/')
                ->with('success', "Welcome, {$userName}!");
        }

        // Login failed
        return redirect()->back()
            ->withErrors([
                'email' => 'Invalid email or password.',
            ])
            ->withInput($request->only('email'));
    }

    // Show Register Form
    public function showRegisterForm()
    {
        return view('auth.register-wisatawan');
    }

    // Register Process
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'nationality' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'nationality.required' => 'Kewarganegaraan wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'nationality' => $request->nationality,
                'address' => $request->address,
                'password' => Hash::make($request->password),
            ]);

        // Auth::login($user);

        return redirect()->route('wisatawan.login')->with('success', 'Registration successful! Your account has been created. Please login to explore Kampung Telaga Air.');
    }

    // Logout Process
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have logged out. See you later!');
    }
}
