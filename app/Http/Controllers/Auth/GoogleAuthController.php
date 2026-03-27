<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CartController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        if (!$this->hasGoogleConfig()) {
            return redirect()
                ->route('wisatawan.login')
                ->withErrors(['google' => 'Google sign-in is not configured yet. Please check GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI.']);
        }

        return Socialite::driver('google')
            ->redirectUrl($this->resolveRedirectUrl())
            ->redirect();
    }

    public function callback()
    {
        if (!$this->hasGoogleConfig()) {
            return redirect()
                ->route('wisatawan.login')
                ->withErrors(['google' => 'Google sign-in is not configured yet. Please check GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI.']);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->resolveRedirectUrl())
                ->stateless()
                ->user();

            if (empty($googleUser->email)) {
                return redirect()
                    ->route('wisatawan.login')
                    ->withErrors(['google' => 'No email address was returned by your Google account.']);
            }

            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name ?: Str::before($googleUser->email, '@'),
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'provider' => 'google',
                    'avatar' => $googleUser->avatar,
                    'password' => bcrypt(Str::random(24)),
                    'role_name' => 'User',
                    'status' => 'Active',
                    'join_date' => now()->toDateTimeString(),
                    'last_login' => now()->toDateTimeString(),
                    'email_verified_at' => now(),
                ]);
            } else {
                $updates = [];

                if (!$user->google_id) {
                    $updates['google_id'] = $googleUser->id;
                }

                if (empty($user->provider)) {
                    $updates['provider'] = 'google';
                }

                if (!empty($googleUser->name) && $user->name !== $googleUser->name) {
                    $updates['name'] = $googleUser->name;
                }

                if (!empty($googleUser->avatar)) {
                    $updates['avatar'] = $googleUser->avatar;
                }

                if (empty($user->email_verified_at)) {
                    $updates['email_verified_at'] = now();
                }

                $updates['last_login'] = now()->toDateTimeString();

                if (!empty($updates)) {
                    $user->update($updates);
                }
            }

            Auth::login($user);
            CartController::mergeGuestCartToUser((int) $user->id);

            return redirect()->route('home');

        } catch (\Exception $e) {
            Log::error('Google OAuth Error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('wisatawan.login')
                ->withErrors(['google' => 'Google authentication failed. Please try again.']);
        }
    }

    private function hasGoogleConfig(): bool
    {
        $config = (array) config('services.google', []);

        return filled($config['client_id'] ?? null)
            && filled($config['client_secret'] ?? null)
            && filled($config['redirect'] ?? null);
    }

    private function resolveRedirectUrl(): string
    {
        $request = request();

        if ($request instanceof \Illuminate\Http\Request) {
            return $request->getSchemeAndHttpHost() . '/auth/google/callback';
        }

        return (string) config('services.google.redirect');
    }
}
