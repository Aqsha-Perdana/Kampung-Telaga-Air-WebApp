<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Homestay;

class HomestayLandingController extends Controller
{
    public function index(Request $request)
    {
        $query = Homestay::query();

        // Filter hanya yang aktif
        $query->where('is_active', true);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('id_homestay', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan kapasitas
        if ($request->has('kapasitas') && $request->kapasitas != '') {
            $query->where('kapasitas', '>=', $request->kapasitas);
        }

        // Filter berdasarkan harga
        if ($request->has('harga_max') && $request->harga_max != '') {
            $query->where('harga_per_malam', '<=', $request->harga_max);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        if ($sortBy == 'harga_murah') {
            $query->orderBy('harga_per_malam', 'asc');
        } elseif ($sortBy == 'harga_mahal') {
            $query->orderBy('harga_per_malam', 'desc');
        } elseif ($sortBy == 'kapasitas') {
            $query->orderBy('kapasitas', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $homestays = $query->paginate(12);
        
        return view('landing.homestay.index', compact('homestays'));
    }

    public function show($id_homestay)
    {
        // Ambil homestay berdasarkan id_homestay
        $homestay = Homestay::where('id_homestay', $id_homestay)
                           ->where('is_active', true)
                           ->with('paketWisatas')
                           ->firstOrFail();
        
        // Ambil homestay lain sebagai rekomendasi (exclude homestay saat ini)
        $relatedHomestays = Homestay::where('id_homestay', '!=', $id_homestay)
                                   ->where('is_active', true)
                                   ->inRandomOrder()
                                   ->take(3)
                                   ->get();
        
        return view('landing.homestay.show', compact('homestay', 'relatedHomestays'));
    }
}