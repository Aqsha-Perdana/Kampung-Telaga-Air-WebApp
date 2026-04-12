<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Override public disk root for cPanel hosting
        // LiteSpeed cannot follow symlinks outside document root,
        // so we point the public disk directly to the web-accessible storage folder.
        $cpanelStorage = base_path('../../telaga.poyekterapan1.com/storage');
        if (is_dir($cpanelStorage)) {
            config([
                'filesystems.disks.public.root' => $cpanelStorage,
                'filesystems.disks.public.url'  => env('APP_STORAGE_URL', '/storage'),
                'filesystems.default'            => 'public',
            ]);
        }

        // Reset-password link is only enabled for admin accounts.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            if ($notifiable instanceof Admin) {
                return route('admin.password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ]);
            }

            return route('wisatawan.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
