<?php

namespace App\Http\Controllers;
use App\Models\PaketWisata;

use Illuminate\Http\Request;

class PaketWisataLandingController extends Controller
{
     public function index(Request $request)
    {
        $query = PaketWisata::query();

        // Eager load relasi untuk performa lebih baik
        $query->with(['destinasis', 'homestays', 'paketCulinaries', 'boats', 'kiosks']);

        // Filter hanya paket yang aktif
        $query->where('status', 'aktif');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_paket', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Sorting
        $query->orderBy('created_at', 'desc');

        // Pagination
        $paketWisata = $query->paginate(12);
        
        return view('landing.paket-wisata.index', compact('paketWisata'));
    }

    public function show($id_paket)
    {
        // Ambil paket wisata berdasarkan id_paket dengan semua relasi
        $paket = PaketWisata::where('id_paket', $id_paket)
                           ->where('status', 'aktif')
                           ->with([
                               'destinasis' => function($query) {
                                   $query->orderBy('paket_wisata_destinasi.hari_ke', 'asc');
                               },
                               'homestays',
                               'paketCulinaries',
                               'boats',
                               'kiosks',
                               'itineraries'
                           ])
                           ->firstOrFail();
        
        return view('landing.paket-wisata.show', compact('paket'));
    }
}

