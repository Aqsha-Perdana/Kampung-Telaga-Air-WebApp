<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\PaketWisata;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
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

    protected function getCartQuery()
    {
        $identifier = $this->getCartIdentifier();

        if ($identifier['type'] === 'user_id') {
            return Cart::where('user_id', $identifier['value']);
        }

        return Cart::where('session_id', $identifier['value'])->whereNull('user_id');
    }

    public static function mergeGuestCartToUser($userId)
    {
        if (!session()->has('cart_session_id')) {
            return;
        }

        $sessionId = session('cart_session_id');
        $guestCartItems = Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestCartItems as $guestItem) {
            $existingItem = Cart::where('user_id', $userId)
                ->where('id_paket', $guestItem->id_paket)
                ->where('tanggal_keberangkatan', $guestItem->tanggal_keberangkatan)
                ->first();

            if ($existingItem) {
                $existingItem->jumlah_peserta += $guestItem->jumlah_peserta;
                $existingItem->subtotal = $existingItem->harga_satuan;
                $existingItem->save();
                $guestItem->delete();
            } else {
                $guestItem->update([
                    'user_id' => $userId,
                    'session_id' => null,
                ]);
            }
        }

        session()->forget('cart_session_id');
    }

    public function index()
    {
        $cartItems = $this->getCartQuery()->with('paket')->get();
        $total = $cartItems->sum('subtotal');

        return view('landing.cart', compact('cartItems', 'total'));
    }

    public function add(Request $request)
    {
        if (!Auth::check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in first.',
                ], 401);
            }

            return redirect()->route('wisatawan.login')
                ->with('error', 'Please log in first to add the package to your cart.');
        }

        $validated = $request->validate([
            'id_paket' => 'required|exists:paket_wisatas,id_paket',
            'jumlah_peserta' => 'required|integer|min:1',
            'tanggal_keberangkatan' => 'required|date|after:+2 days',
            'catatan' => 'nullable|string|max:500',
        ]);

        $paket = PaketWisata::findOrFail($validated['id_paket']);
        $this->ensureParticipantRules($paket, (int) $validated['jumlah_peserta']);

        if ($paket->status !== 'aktif') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This tour package is not available.',
                ], 400);
            }

            return redirect()->back()->with('error', 'This tour package is not available.');
        }

        $identifier = $this->getCartIdentifier();
        $existingCart = $this->getCartQuery()
            ->where('id_paket', $validated['id_paket'])
            ->where('tanggal_keberangkatan', $validated['tanggal_keberangkatan'])
            ->first();

        if ($existingCart) {
            $newParticipants = $existingCart->jumlah_peserta + (int) $validated['jumlah_peserta'];
            $this->ensureParticipantRules($paket, $newParticipants);

            $existingCart->jumlah_peserta = $newParticipants;
            $existingCart->subtotal = $existingCart->harga_satuan;

            if (!empty($validated['catatan'])) {
                $existingCart->catatan = $validated['catatan'];
            }

            $existingCart->save();
            app(AdminNotificationService::class)->notifyCartAdded($existingCart, [
                'origin' => 'IP ' . (string) $request->ip(),
                'source_ip' => (string) $request->ip(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Participant count has been updated in your cart.',
                    'paket_nama' => $paket->nama_paket,
                ]);
            }

            return redirect()->back()->with('success', 'Participant count has been updated in your cart.');
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

        $cart = Cart::create($cartData);
        app(AdminNotificationService::class)->notifyCartAdded($cart, [
            'origin' => 'IP ' . (string) $request->ip(),
            'source_ip' => (string) $request->ip(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'The package has been added to your cart.',
                'paket_nama' => $paket->nama_paket,
            ]);
        }

        return redirect()->back()->with('success', 'The package has been added to your cart.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'jumlah_peserta' => 'required|integer|min:1',
            'tanggal_keberangkatan' => 'required|date|after:+2 days',
            'catatan' => 'nullable|string|max:500',
        ], [
            'tanggal_keberangkatan.after' => 'The departure date is at least 3 days from now.',
        ]);

        $cartItem = $this->getCartQuery()->with('paket')->findOrFail($id);
        $this->ensureParticipantRules($cartItem->paket, (int) $validated['jumlah_peserta']);

        $cartItem->update([
            'jumlah_peserta' => $validated['jumlah_peserta'],
            'tanggal_keberangkatan' => $validated['tanggal_keberangkatan'],
            'catatan' => $validated['catatan'],
            'subtotal' => $cartItem->harga_satuan,
        ]);

        return redirect()->back()->with('success', 'Your cart has been updated.');
    }

    public function remove($id)
    {
        $cartItem = $this->getCartQuery()->findOrFail($id);
        $cartItem->delete();

        return redirect()->back()->with('success', 'Item removed from your cart.');
    }

    public function clear()
    {
        $this->getCartQuery()->delete();

        return redirect()->back()->with('success', 'Your cart has been emptied.');
    }

    public function count()
    {
        $query = $this->getCartQuery();
        $cartCount = $query->count();
        $participantsCount = (clone $query)->sum('jumlah_peserta');

        return response()->json([
            'count' => $cartCount,
            'participants' => (int) $participantsCount,
        ]);
    }

    public function checkItem(Request $request)
    {
        $exists = $this->getCartQuery()
            ->where('id_paket', $request->id_paket)
            ->where('tanggal_keberangkatan', $request->tanggal_keberangkatan)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    private function ensureParticipantRules(PaketWisata $paket, int $participants): void
    {
        $minimum = max((int) ($paket->minimum_participants ?? 1), 1);
        $maximum = $paket->maximum_participants ? (int) $paket->maximum_participants : null;

        if ($participants < $minimum) {
            throw ValidationException::withMessages([
                'jumlah_peserta' => 'This package requires at least ' . $minimum . ' participant' . ($minimum > 1 ? 's' : '') . '.',
            ]);
        }

        if ($maximum !== null && $participants > $maximum) {
            throw ValidationException::withMessages([
                'jumlah_peserta' => 'This package allows up to ' . $maximum . ' participant' . ($maximum > 1 ? 's' : '') . ' per booking.',
            ]);
        }
    }
}
