<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only frontend nav components need cart count.
        View::composer([
            'landing.partials.navbar',
            'landing.partials.mobile-bottom-nav',
        ], function ($view) {
            static $resolvedCartCount = null;

            if ($resolvedCartCount === null) {
                $resolvedCartCount = 0;

                if (Auth::check()) {
                    $resolvedCartCount = Cart::where('user_id', Auth::id())->count();
                } else {
                    $sessionCartId = session('cart_session_id');
                    if ($sessionCartId) {
                        $resolvedCartCount = Cart::where('session_id', $sessionCartId)->count();
                    }
                }
            }

            $view->with('cartCount', $resolvedCartCount);
        });
    }
}
