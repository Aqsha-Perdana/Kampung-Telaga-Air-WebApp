<?php

use App\Http\Controllers\BoatController;
use App\Http\Controllers\PaketWisataController;
use App\Http\Controllers\PaketWisataLandingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DestinasiController;
use App\Http\Controllers\HomestayController;
use App\Http\Controllers\CulinaryController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KioskLandingController;
use App\Http\Controllers\BebanOperasionalController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\CartController ;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CulinaryLandingController;
use App\Http\Controllers\HomestayLandingController;
use App\Http\Controllers\Admin\Footage360Controller;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\SalesController;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Auth\WisatawanAuthController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Visitor\ProfileController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AICenterController;
use App\Http\Controllers\Admin\AdminNotificationController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Wisatawan Authentication Routes
Route::prefix('visitor')->group(function () {
    Route::get('/login-visitor', [WisatawanAuthController::class, 'showLoginForm'])->name('wisatawan.login');
    Route::post('/login-visitor', [WisatawanAuthController::class, 'login'])
        ->middleware('throttle:visitor-login')
        ->name('wisatawan.login.post');
    Route::post('/logout', [WisatawanAuthController::class, 'logout'])->name('wisatawan.logout');
    
    Route::get('/register', [WisatawanAuthController::class, 'showRegisterForm'])->name('wisatawan.register');
    Route::post('/register', [WisatawanAuthController::class, 'register'])
        ->middleware('throttle:visitor-register')
        ->name('wisatawan.register.post');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('wisatawan.profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('wisatawan.profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('wisatawan.profile.password.update');
    });
});

// Global reset password route required by Laravel default reset notification.
// This project only enables reset-password flow for admin accounts.
Route::get('/reset-password/{token}', function (Request $request, $token) {
    $email = (string) $request->query('email', '');

    if ($email !== '' && Admin::where('email', $email)->exists()) {
        return redirect()->route('admin.password.reset', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    return redirect()->route('wisatawan.login')
        ->withErrors(['email' => 'Password reset is not available for visitor accounts.']);
})->name('password.reset');

//Landing Page
Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/destination', [LandingPageController::class, 'destinasi'])->name('landing.destinasi');
Route::get('/tour-package', [LandingPageController::class, 'paketWisata'])->name('landing.paket-wisata');
Route::get('/tour-package/{id}', [LandingPageController::class, 'detailPaket'])->name('landing.detail-paket');
Route::get('/destination/{id}', [LandingPageController::class, 'detailDestinasi'])
     ->name('landing.detail-destinasi');
// Chatbot Route
Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.send');

// Navigasi Menu    
Route::get('/package-tour', [PaketWisataLandingController::class, 'index'])->name('landing.package-tour');
Route::get('/kiosk', [KioskLandingController::class, 'index'])->name('landing.kiosk');
Route::get('/kiosk/{id_kiosk}', [KioskLandingController::class, 'show'])->name('landing.kiosk.show');
Route::get('/culinary', [CulinaryLandingController::class, 'index'])->name('landing.culinary');
Route::get('/culinary/{id_culinary}', [CulinaryLandingController::class, 'show'])->name('landing.culinary.show');
Route::get('/homestay', [HomestayLandingController::class, 'index'])->name('landing.homestay');
Route::get('/homestay/{id_homestay}', [HomestayLandingController::class, 'show'])->name('landing.homestay.show');
Route::get('/view360/{footage360}', [App\Http\Controllers\View360Controller::class, 'show'])
     ->name('view360.show');



/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login'); // hapus admin. nya
        Route::post('/login', [AdminAuthController::class, 'login'])
            ->middleware('throttle:admin-login')
            ->name('login.submit'); // hapus admin. nya
        Route::get('/forgot-password', [AdminAuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Protected Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    
    // Dashboard - Accessible by ALL roles (admin & pengelola)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents']);
    Route::get('/calendar/date-detail', [CalendarController::class, 'getDateDetail']);
    Route::get('/calendar/statistics', [CalendarController::class, 'getStatistics']);
    Route::get('/calendar/resource-availability', [CalendarController::class, 'getResourceAvailability']);
    Route::get('/calendar/conflict-alerts', [CalendarController::class, 'getConflictAlerts']);
    Route::get('/ai-center', [AICenterController::class, 'index'])->name('admin.ai-center.index');
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/notifications/feed', [AdminNotificationController::class, 'feed'])->name('admin.notifications.feed');
    Route::post('/notifications/mark-read', [AdminNotificationController::class, 'markAllRead'])->name('admin.notifications.mark-read');
    // Master Data - Only accessible by 'admin' role
    Route::middleware(['check.admin.role:master-data'])->group(function () {
        Route::resource('destinasis', DestinasiController::class);
        Route::resource('homestays', HomestayController::class);
        Route::resource('boats', BoatController::class);
        Route::resource('culinaries', CulinaryController::class);
        Route::resource('kiosks', KioskController::class);
        Route::resource('beban-operasional', BebanOperasionalController::class);
        Route::resource('footage360', Footage360Controller::class);
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
        Route::put('/users/{user}/password', [AdminUserController::class, 'updatePassword'])->name('admin.users.password.update');
    });
    
    // Transaction - Only accessible by 'admin' role
    Route::middleware(['check.admin.role:transaction'])->group(function () {
        // Generate Content AI
    Route::post('paket-wisata/generate-content', [PaketWisataController::class, 'generateContent'])->name('paket-wisata.generate-content');

    Route::resource('paket-wisata', PaketWisataController::class);
        Route::post('/paket-wisata/calculate-price', [PaketWisataController::class, 'calculatePrice'])
            ->name('paket-wisata.calculate-price');
        
        Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
        Route::get('/sales/{orderId}', [SalesController::class, 'show'])->name('sales.detail');
        Route::get('/sales/manifest/{id_order}', [SalesController::class, 'downloadManifest'])
        ->name('admin.sales.manifest');
    });
    
    // Financial Reports - Accessible by BOTH 'admin' and 'pengelola' roles
    Route::middleware(['check.admin.role:financial'])->group(function () {
        Route::get('financial-reports', [FinancialReportController::class, 'index'])
            ->name('financial-reports.index');

        // Canonical Owner Detail Route
        Route::get('financial-reports/owner/{type}/{id}', [FinancialReportController::class, 'ownerDetail'])
            ->name('financial-reports.owner');

        // Legacy aliases kept for backward compatibility
        Route::get('financial-reports/{type}/{id}', [FinancialReportController::class, 'ownerReport'])
            ->name('financial-reports.owner.legacy');
        Route::get('owner/{type}/{id}', [FinancialReportController::class, 'ownerDetail'])
            ->name('financial-reports.owner.short');

        // Export Profit & Loss PDF
        Route::get('/financial-reports/export-profit-loss-pdf', [FinancialReportController::class, 'exportProfitLossPdf'])
            ->name('financial-reports.export-profit-loss-pdf');
        
        // Export Cash Flow PDF
        Route::get('/financial-reports/export-cash-flow-pdf', [FinancialReportController::class, 'exportCashFlowPdf'])
            ->name('financial-reports.export-cash-flow-pdf');
    
        // Export Excel
        Route::get('/financial-reports/export-excel', [FinancialReportController::class, 'exportExcel'])
            ->name('financial-reports.export-excel');
        Route::get('owner/{type}/{id}/pdf', [FinancialReportController::class, 'exportOwnerPDF'])->name('financial-reports.owner.pdf');
        Route::get('owner/{type}/{id}/excel', [FinancialReportController::class, 'exportOwnerExcel'])->name('financial-reports.owner.excel');
    });
});

Route::get('/calendar-test', function () {
    return view('admin.calendar.test-standalone');
});

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});


// ==========================================
// CHECKOUT ROUTES (Requires Authentication)
// ==========================================
Route::middleware(['auth'])->prefix('checkout')->name('checkout.')->group(function () {
    
    // Checkout page
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    
    // Payment processing
    Route::post('/process', [CheckoutController::class, 'process'])
        ->middleware('throttle:checkout-process')
        ->name('process');
    
    // Success & Failed pages
    Route::get('/success', [CheckoutController::class, 'success'])->name('success');
    Route::get('/failed', [CheckoutController::class, 'failed'])->name('failed');
});

// ==========================================
// ORDER MANAGEMENT ROUTES (Requires Authentication)
// ==========================================
Route::middleware(['auth'])->prefix('orders')->name('orders.')->group(function () {
    
    // Order history
    Route::get('/', [CheckoutController::class, 'history'])->name('history');
    
    // Order detail
    Route::get('/{id_order}', [CheckoutController::class, 'show'])->name('show');
    
    // Cancel order
    Route::post('/{id_order}/cancel', [CheckoutController::class, 'cancel'])->name('cancel');
    
    // Download invoice
    Route::get('/{id_order}/invoice', [CheckoutController::class, 'invoice'])->name('invoice');

    // Request Refund
    Route::post('/{id_order}/refund', [CheckoutController::class, 'requestRefund'])->name('refund.request');
});

// Admin Refund Routes
Route::middleware(['auth:admin'])->prefix('admin/sales')->name('admin.sales.')->group(function () {
    Route::post('/{id_order}/refund/approve', [SalesController::class, 'approveRefund'])->name('refund.approve');
    Route::post('/{id_order}/refund/reject', [SalesController::class, 'rejectRefund'])->name('refund.reject');
});

// ==========================================
// API ROUTES (for AJAX polling)
// ==========================================
Route::middleware(['auth'])->prefix('api')->group(function () {
    
    // Check order status (untuk polling di checkout success page)
    Route::get('/order-status/{orderId}', [CheckoutController::class, 'checkStatus'])
        ->middleware('throttle:order-status');
});

// ==========================================
// WEBHOOK ROUTES (NO AUTH, NO CSRF)
// ==========================================
Route::post('/webhook/stripe', [CheckoutController::class, 'webhook'])
    ->name('webhook.stripe')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::get('/invoice/{order}', [OrderController::class, 'download'])
    ->name('invoice.download');




    

