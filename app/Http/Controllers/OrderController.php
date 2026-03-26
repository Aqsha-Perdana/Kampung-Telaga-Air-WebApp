<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{

    /**
     * Payment page
     */
    public function payment($id_order)
    {
        $query = Order::with('items')
            ->where('id_order', $id_order)
            ->where('status', 'pending');

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        }

        $order = $query->firstOrFail();

        return view('orders.payment', compact('order'));
    }

    /**
     * Process payment (Bank Transfer)
     */
    public function processPayment(Request $request, $id_order)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_proof' => 'required|image|max:2048'
        ]);

        $query = Order::where('id_order', $id_order)
            ->where('status', 'pending');

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        }

        $order = $query->firstOrFail();

        $data = [
            'payment_method' => $request->payment_method,
            'status' => 'paid',
            'paid_at' => now()
        ];

        // Handle payment proof upload
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = 'payment_' . $order->id_order . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/payment_proofs', $filename);
            
            // Save payment proof path (you might need to add this column to orders table)
            $data['payment_proof'] = $filename;
        }

        $order->update($data);

        // Log payment
        \App\Models\PaymentLog::create([
            'id_order' => $order->id_order,
            'payment_method' => $request->payment_method,
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'status' => 'pending'
        ]);

        return redirect()->route('orders.show', $order->id_order)
            ->with('success', 'Pembayaran berhasil diproses. Menunggu konfirmasi admin.');
    }

    /**
     * Download invoice
     */
    public function download(Order $order)
    {
        abort_unless($this->canAccessInvoice($order), 403);

        $order->loadMissing('items.paket');

        abort_if(
            in_array($order->status, ['pending', 'failed', 'cancelled'], true),
            422,
            'Invoice is available after payment is completed.'
        );

        $pdf = Pdf::loadView('pdf.invoice', compact('order'));

        return $pdf->download('Invoice-' . $order->id_order . '.pdf');
    }

    private function canAccessInvoice(Order $order): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }

        return Auth::check() && (int) $order->user_id === (int) Auth::id();
    }
}
