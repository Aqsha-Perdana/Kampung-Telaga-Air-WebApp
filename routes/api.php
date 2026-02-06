<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/api/order-status/{order_id}', function($order_id) {
    try {
        $order = \App\Models\Order::where('id_order', $order_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id_order,
            'status' => $order->status,
            'redeem_code' => $order->redeem_code,
            'paid_at' => $order->paid_at ? $order->paid_at->toISOString() : null
        ]);

    } catch (\Exception $e) {
        \Log::error('Order status check error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Server error'
        ], 500);
    }
})->middleware('auth')->name('api.order.status');