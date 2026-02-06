<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\PaketWisata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get cart identifier (user_id atau session_id)
     * Prioritas: user_id jika login, session_id jika guest
     */
    protected function getCartIdentifier()
    {
        if (Auth::check()) {
            return ['type' => 'user_id', 'value' => Auth::id()];
        }
        
        if (!session()->has('cart_session_id')) {
            session()->put('cart_session_id', 'cart_' . session()->getId() . '_' . time());
        }
        
        return ['type' => 'session_id', 'value' => session('cart_session_id')];
    }

    /**
     * Get cart items berdasarkan identifier
     */
    protected function getCartQuery()
    {
        $identifier = $this->getCartIdentifier();
        
        if ($identifier['type'] === 'user_id') {
            return Cart::where('user_id', $identifier['value']);
        }
        
        return Cart::where('session_id', $identifier['value'])->whereNull('user_id');
    }

    /**
     * Merge guest cart ke user cart saat login
     * Dipanggil dari LoginController setelah user berhasil login
     */
    public static function mergeGuestCartToUser($userId)
    {
        if (!session()->has('cart_session_id')) {
            return;
        }

        $sessionId = session('cart_session_id');
        
        // Ambil semua cart items dari guest session
        $guestCartItems = Cart::where('session_id', $sessionId)
                              ->whereNull('user_id')
                              ->get();

        foreach ($guestCartItems as $guestItem) {
            // Cek apakah user sudah punya item yang sama
            $existingItem = Cart::where('user_id', $userId)
                                ->where('id_paket', $guestItem->id_paket)
                                ->where('tanggal_keberangkatan', $guestItem->tanggal_keberangkatan)
                                ->first();

            if ($existingItem) {
                // Merge jumlah peserta
                $existingItem->jumlah_peserta += $guestItem->jumlah_peserta;
                $existingItem->subtotal = $existingItem->harga_satuan;
                $existingItem->save();
                
                // Hapus guest cart item
                $guestItem->delete();
            } else {
                // Transfer guest cart ke user cart
                $guestItem->update([
                    'user_id' => $userId,
                    'session_id' => null
                ]);
            }
        }

        // Clear session cart id
        session()->forget('cart_session_id');
    }

    /**
     * Tampilkan keranjang
     */
    public function index()
    {
        $cartItems = $this->getCartQuery()->with('paket')->get();
        $total = $cartItems->sum('subtotal');

        return view('landing.cart', compact('cartItems', 'total'));
    }

    /**
     * Tambah ke keranjang
     */
    public function add(Request $request)
{
    // Check if user is authenticated
    if (!Auth::check()) {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in first.'
            ], 401);
        }
        return redirect()->route('wisatawan.login')
            ->with('error', 'Please log in first to add the package to your cart.');
    }

    $validated = $request->validate([
        'id_paket' => 'required|exists:paket_wisatas,id_paket',
        'jumlah_peserta' => 'required|integer|min:1',
        'tanggal_keberangkatan' => 'required|date|after:+2 days',
        'catatan' => 'nullable|string|max:500'
    ]);

    $paket = PaketWisata::findOrFail($validated['id_paket']);
    
    if ($paket->status !== 'aktif') {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Paket wisata ini tidak tersedia.'
            ], 400);
        }
        return redirect()->back()->with('error', 'Paket wisata ini tidak tersedia.');
    }

    $identifier = $this->getCartIdentifier();

    $existingCart = $this->getCartQuery()
                        ->where('id_paket', $validated['id_paket'])
                        ->where('tanggal_keberangkatan', $validated['tanggal_keberangkatan'])
                        ->first();

    if ($existingCart) {
        $existingCart->jumlah_peserta += $validated['jumlah_peserta'];
        $existingCart->subtotal = $existingCart->harga_satuan ;
        
        if (!empty($validated['catatan'])) {
            $existingCart->catatan = $validated['catatan'];
        }
        
        $existingCart->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'The number of participants has been successfully updated in the cart!',
                'paket_nama' => $paket->nama_paket
            ]);
        }
        return redirect()->back()->with('success', 'The number of participants has been successfully updated in the cart!');
    }

    $cartData = [
        'id_paket' => $validated['id_paket'],
        'jumlah_peserta' => $validated['jumlah_peserta'],
        'tanggal_keberangkatan' => $validated['tanggal_keberangkatan'],
        'catatan' => $validated['catatan'],
        'harga_satuan' => $paket->harga_final,
        'subtotal' => $paket->harga_final,
    ];

    if ($identifier['type'] === 'user_id') {
        $cartData['user_id'] = $identifier['value'];
        $cartData['session_id'] = null;
    } else {
        $cartData['session_id'] = $identifier['value'];
        $cartData['user_id'] = null;
    }

    Cart::create($cartData);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'The package has been added to your cart!',
            'paket_nama' => $paket->nama_paket
        ]);
    }

    return redirect()->back()->with('success', 'The package has been added to your cart!');
}

    /**
     * Update cart item
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'jumlah_peserta' => 'required|integer|min:1',
            'tanggal_keberangkatan' => 'required|date|after:+2 days',
            'catatan' => 'nullable|string|max:500'
        ], [
            'tanggal_keberangkatan.after' => 'The departure date is at least 3 days from now.',
        ]);

        $cartItem = $this->getCartQuery()->findOrFail($id);

        $cartItem->update([
            'jumlah_peserta' => $validated['jumlah_peserta'],
            'tanggal_keberangkatan' => $validated['tanggal_keberangkatan'],
            'catatan' => $validated['catatan'],
            'subtotal' => $cartItem->harga_satuan,
        ]);

        return redirect()->back()->with('success', 'Your cart has been updated!');
    }

    /**
     * Hapus dari keranjang
     */
    public function remove($id)
    {
        $cartItem = $this->getCartQuery()->findOrFail($id);
        $cartItem->delete();

        return redirect()->back()->with('success', 'Item successfully removed from cart!');
    }

    /**
     * Hapus semua keranjang
     */
    public function clear()
    {
        $this->getCartQuery()->delete();

        return redirect()->back()->with('success', 'The cart has been successfully emptied!');
    }

    /**
     * Get cart count (untuk badge di navbar)
     */
    public function count()
    {
        $cartCount = Auth::check()
    ? Cart::where('user_id', Auth::id())->sum('qty')
    : 0;
    }

    /**
     * Check if item exists in cart
     */
    public function checkItem(Request $request)
    {
        $exists = $this->getCartQuery()
                      ->where('id_paket', $request->id_paket)
                      ->where('tanggal_keberangkatan', $request->tanggal_keberangkatan)
                      ->exists();

        return response()->json(['exists' => $exists]);
    }
}