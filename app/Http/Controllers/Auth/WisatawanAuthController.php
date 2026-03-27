<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VisitorVerificationCodeService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WisatawanAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except([
            'logout',
            'showVerificationNotice',
            'verifyAccount',
            'resendVerificationCode',
        ]);
    }

    public function showLoginForm()
    {
        return view('auth.login-wisatawan');
    }

    public function login(Request $request, VisitorVerificationCodeService $verificationService)
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

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            CartController::mergeGuestCartToUser(Auth::id());

            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                $result = $verificationService->sendCode($user, true);

                return redirect()
                    ->route('wisatawan.verification.notice')
                    ->with($result['ok'] ? 'success' : 'warning', $result['ok']
                        ? 'We sent a verification code to your email. Please verify your account to continue.'
                        : ($result['message'] ?? 'Please verify your account to continue.'));
            }

            return redirect()->intended('/')
                ->with('success', 'Welcome, ' . $user->name . '!');
        }

        return redirect()->back()
            ->withErrors([
                'email' => 'Invalid email or password.',
            ])
            ->withInput($request->only('email'));
    }

    public function showRegisterForm()
    {
        return view('auth.register-wisatawan');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password-wisatawan');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
        ]);

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link has been sent to your email.')
            : back()->withErrors(['email' => 'We could not find an account with that email address.']);
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password-wisatawan', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('wisatawan.login')->with('success', 'Your password has been reset successfully. Please log in.')
            : back()->withErrors(['email' => [__($status)]]);
    }

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

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'status' => 'Pending Verification',
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('wisatawan.login')->with('success', 'Registration successful. Please log in first, then verify your email with the code sent to your inbox.');
    }

    public function showVerificationNotice(Request $request, VisitorVerificationCodeService $verificationService)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('wisatawan.login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verify-wisatawan', [
            'maskedEmail' => $verificationService->maskEmail((string) $user->email),
            'cooldownRemaining' => $verificationService->getCooldownRemaining($user),
            'expiresAt' => optional($user->email_verification_code_expires_at)?->toIso8601String(),
        ]);
    }

    public function verifyAccount(Request $request, VisitorVerificationCodeService $verificationService)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Verification code is required.',
            'code.digits' => 'Verification code must be 6 digits.',
        ]);

        $user = $request->user();

        if (!$user) {
            return redirect()->route('wisatawan.login');
        }

        $result = $verificationService->verify($user, (string) $request->input('code'));

        if (($result['ok'] ?? false) !== true) {
            return back()->withErrors(['code' => $result['message'] ?? 'Verification failed.']);
        }

        return redirect()->intended('/')->with('success', 'Your account has been verified successfully.');
    }

    public function resendVerificationCode(Request $request, VisitorVerificationCodeService $verificationService)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('wisatawan.login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $result = $verificationService->sendCode($user);

        return back()->with(($result['ok'] ?? false) ? 'success' : 'warning', $result['ok']
            ? 'A new verification code has been sent to your email.'
            : ($result['message'] ?? 'We could not send a new verification code right now.'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have logged out. See you later!');
    }
}
