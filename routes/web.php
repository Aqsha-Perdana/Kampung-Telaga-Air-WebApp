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
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Auth\WisatawanAuthController;
use App\Http\Controllers\Admin\CalendarController;
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
    Route::post('/login-visitor', [WisatawanAuthController::class, 'login'])->name('wisatawan.login.post');
    Route::post('/logout', [WisatawanAuthController::class, 'logout'])->name('wisatawan.logout');
    
    Route::get('/register', [WisatawanAuthController::class, 'showRegisterForm'])->name('wisatawan.register');
    Route::post('/register', [WisatawanAuthController::class, 'register'])->name('wisatawan.register.post');
    
    Route::get('/forgot-password', [WisatawanAuthController::class, 'showForgotPasswordForm'])->name('wisatawan.password.request');
    Route::post('/forgot-password', [WisatawanAuthController::class, 'sendResetLinkEmail'])->name('wisatawan.password.email');
    
    Route::get('/reset-password/{token}', [WisatawanAuthController::class, 'showResetForm'])->name('wisatawan.password.reset');
    Route::post('/reset-password', [WisatawanAuthController::class, 'resetPassword'])->name('wisatawan.password.update');
});

//Landing Page
Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/destination', [LandingPageController::class, 'destinasi'])->name('landing.destinasi');
Route::get('/tour-package', [LandingPageController::class, 'paketWisata'])->name('landing.paket-wisata');
Route::get('/tour-package/{id}', [LandingPageController::class, 'detailPaket'])->name('landing.detail-paket');
Route::get('/destination/{id}', [LandingPageController::class, 'detailDestinasi'])
     ->name('landing.detail-destinasi');
// Chatbot Route
Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');

// Navigasi Menu    
Route::get('/package-tour', [PaketWisataLandingController::class, 'index'])->name('landing.paket-wisata');
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
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit'); // hapus admin. nya
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
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
    // Master Data - Only accessible by 'admin' role
    Route::middleware(['check.admin.role:master-data'])->group(function () {
        Route::resource('destinasis', DestinasiController::class);
        Route::resource('homestays', HomestayController::class);
        Route::resource('boats', BoatController::class);
        Route::resource('culinaries', CulinaryController::class);
        Route::resource('kiosks', KioskController::class);
        Route::resource('beban-operasional', BebanOperasionalController::class);
        Route::resource('footage360', Footage360Controller::class);
    });
    
    // Transaction - Only accessible by 'admin' role
    Route::middleware(['check.admin.role:transaction'])->group(function () {
        Route::resource('paket-wisata', PaketWisataController::class);
        Route::post('/paket-wisata/calculate-price', [PaketWisataController::class, 'calculatePrice'])
            ->name('paket-wisata.calculate-price');
        
        Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
        Route::get('/sales/{orderId}', [SalesController::class, 'show'])->name('sales.detail');
        Route::get('/admin/sales/manifest/{id_order}', [SalesController::class, 'downloadManifest'])
        ->name('admin.sales.manifest');
    });
    
    // Financial Reports - Accessible by BOTH 'admin' and 'pengelola' roles
    Route::middleware(['check.admin.role:financial'])->group(function () {
        Route::get('financial-reports', [FinancialReportController::class, 'index'])
            ->name('financial-reports.index');
        Route::get('financial-reports/{type}/{id}', [FinancialReportController::class, 'ownerReport'])
            ->name('financial-reports.owner');
            // Export Profit & Loss PDF
        Route::get('/financial-reports/export-profit-loss-pdf', [FinancialReportController::class, 'exportProfitLossPdf'])
            ->name('financial-reports.export-profit-loss-pdf');
        
        // Export Cash Flow PDF
        Route::get('/financial-reports/export-cash-flow-pdf', [FinancialReportController::class, 'exportCashFlowPdf'])
            ->name('financial-reports.export-cash-flow-pdf');
    
        // Export Excel
        Route::get('/financial-reports/export-excel', [FinancialReportController::class, 'exportExcel'])
            ->name('financial-reports.export-excel');
        
        // Owner Detail Report (if needed)
        Route::get('/financial-reports/owner/{type}/{id}', [FinancialReportController::class, 'ownerDetail'])
            ->name('financial-reports.owner');

        Route::get('owner/{type}/{id}', [FinancialReportController::class, 'ownerDetail'])->name('financial-reports.owner');
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
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    
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
});

// ==========================================
// API ROUTES (for AJAX polling)
// ==========================================
Route::middleware(['auth'])->prefix('api')->group(function () {
    
    // Check order status (untuk polling di checkout success page)
    Route::get('/order-status/{orderId}', [CheckoutController::class, 'checkStatus']);
});

// ==========================================
// WEBHOOK ROUTES (NO AUTH, NO CSRF)
// ==========================================
Route::post('/webhook/stripe', [CheckoutController::class, 'webhook'])
    ->name('webhook.stripe')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::get('/invoice/{order}', [OrderController::class, 'download'])
    ->name('invoice.download');


Route::get('/upload-test', function() {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Upload Test</title>
        <style>
            body { font-family: Arial; padding: 50px; }
            .result { background: #f0f0f0; padding: 20px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <h1>Test Upload File 360°</h1>
        <form action="/upload-test-process" method="POST" enctype="multipart/form-data">
            '.csrf_field().'
            <input type="file" name="file" required>
            <br><br>
            <button type="submit">Test Upload</button>
        </form>
    </body>
    </html>
    ';
});

Route::post('/upload-test-process', function(\Illuminate\Http\Request $request) {
    try {
        echo "<h2>PHP Upload Config:</h2>";
        echo "<pre>";
        echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
        echo "post_max_size: " . ini_get('post_max_size') . "\n";
        echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
        echo "memory_limit: " . ini_get('memory_limit') . "\n";
        echo "</pre>";
        
        echo "<h2>Request Info:</h2>";
        echo "<pre>";
        echo "Has file: " . ($request->hasFile('file') ? 'YES' : 'NO') . "\n";
        echo "All files: " . print_r($request->allFiles(), true) . "\n";
        echo "</pre>";
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            echo "<h2>File Info:</h2>";
            echo "<pre>";
            echo "Is Valid: " . ($file->isValid() ? 'YES' : 'NO') . "\n";
            echo "Filename: " . $file->getClientOriginalName() . "\n";
            echo "Size: " . number_format($file->getSize() / 1024 / 1024, 2) . " MB\n";
            echo "MIME Type: " . $file->getMimeType() . "\n";
            echo "Extension: " . $file->getClientOriginalExtension() . "\n";
            echo "Real Path: " . $file->getRealPath() . "\n";
            echo "Error: " . $file->getError() . "\n";
            echo "</pre>";
            
            echo "<h2>Cloudinary Config:</h2>";
            echo "<pre>";
            echo "Cloud Name: " . config('cloudinary.cloud_name') . "\n";
            echo "API Key: " . config('cloudinary.api_key') . "\n";
            echo "API Secret: " . (config('cloudinary.api_secret') ? substr(config('cloudinary.api_secret'), 0, 5) . '***' : 'NOT SET') . "\n";
            echo "</pre>";
            
            // Test upload ke Cloudinary
            echo "<h2>Testing Upload to Cloudinary...</h2>";
            echo "<pre>";
            
            try {
                $uploaded = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload(
                    $file->getRealPath(),
                    [
                        'folder' => 'test-upload',
                        'resource_type' => 'auto'
                    ]
                );
                
                echo "✅ SUCCESS!\n\n";
                echo "URL: " . $uploaded->getSecurePath() . "\n";
                echo "Public ID: " . $uploaded->getPublicId() . "\n";
                echo "\n<img src='" . $uploaded->getSecurePath() . "' style='max-width: 500px; margin-top: 20px;'>";
                
            } catch (\Exception $e) {
                echo "❌ FAILED!\n\n";
                echo "Error: " . $e->getMessage() . "\n";
                echo "Error Class: " . get_class($e) . "\n";
            }
            
            echo "</pre>";
            
        } else {
            echo "<h2 style='color: red;'>❌ NO FILE RECEIVED!</h2>";
            echo "<pre>";
            echo "POST data: " . print_r($_POST, true) . "\n";
            echo "FILES data: " . print_r($_FILES, true) . "\n";
            echo "</pre>";
        }
        
    } catch (\Exception $e) {
        echo "<h2 style='color: red;'>ERROR:</h2>";
        echo "<pre>" . $e->getMessage() . "</pre>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
});

    