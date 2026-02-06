<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kiosk;

class KioskLandingController extends Controller
{
    public function index(Request $request)
    {
        $query = Kiosk::query();

        // Eager load fotos
        $query->with('fotos');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan harga
        if ($request->has('harga_min') && $request->harga_min != '') {
            $query->where('harga_per_paket', '>=', $request->harga_min);
        }

        if ($request->has('harga_max') && $request->harga_max != '') {
            $query->where('harga_per_paket', '<=', $request->harga_max);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        if ($sortBy == 'harga_murah') {
            $query->orderBy('harga_per_paket', 'asc');
        } elseif ($sortBy == 'harga_mahal') {
            $query->orderBy('harga_per_paket', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $kiosks = $query->paginate(12);
        
        return view('landing.kiosk.index', compact('kiosks'));
    }

    public function show($id_kiosk)
    {
        // Ambil kiosk berdasarkan id_kiosk
        $kiosk = Kiosk::where('id_kiosk', $id_kiosk)
                     ->with('fotos')
                     ->firstOrFail();
        
        // Ambil kiosk lain sebagai rekomendasi (exclude kiosk saat ini)
        $relatedKiosks = Kiosk::where('id_kiosk', '!=', $id_kiosk)
                             ->with('fotos')
                             ->inRandomOrder()
                             ->take(3)
                             ->get();
        
        return view('landing.kiosk.show', compact('kiosk', 'relatedKiosks'));
    }
}