<?php

namespace App\Http\Controllers;

use App\Models\Destinasi;
use App\Models\PaketWisata;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        // Ambil destinasi populer (6 destinasi)
        $destinasis = Destinasi::with('fotos')
                               ->take(6)
                               ->get();
        
        // Ambil paket wisata aktif (3 paket)
        $paketWisatas = PaketWisata::with(['destinasis', 'homestays', 'paketCulinaries', 'boats', 'kiosks'])
                                   ->where('status', 'aktif')
                                   ->take(3)
                                   ->get();
        
        return view('landing.home', compact('destinasis', 'paketWisatas'));
    }
    
    public function destinasi()
    {
        $destinasis = Destinasi::with('fotos')->paginate(9);
        return view('landing.destinasi', compact('destinasis'));
    }

     public function detailDestinasi($id)
    {
        $destinasi = Destinasi::with('fotos')->findOrFail($id);
        
        // Ambil destinasi lain sebagai rekomendasi (exclude current)
        $rekomendasiDestinasi = Destinasi::with('fotos')
                                         ->where('id_destinasi', '!=', $id)
                                         ->inRandomOrder()
                                         ->take(3)
                                         ->get();
        
        // Ambil paket wisata yang include destinasi ini
        $paketTerkait = PaketWisata::with(['destinasis', 'homestays', 'paketCulinaries'])
                                   ->whereHas('destinasis', function($query) use ($id) {
                                       $query->where('destinasis.id_destinasi', $id);
                                   })
                                   ->where('status', 'aktif')
                                   ->take(3)
                                   ->get();
        
        return view('landing.detail-destinasi', compact('destinasi', 'rekomendasiDestinasi', 'paketTerkait'));
    }
    
    public function paketWisata(Request $request)
    {
        $query = PaketWisata::with(['destinasis', 'homestays', 'paketCulinaries', 'boats', 'kiosks'])
            ->where('status', 'aktif');

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_paket', 'like', '%' . $search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $search . '%');
            });
        }

        $paketWisata = $query->orderByDesc('created_at')->paginate(6)->withQueryString();

        return view('landing.paket-wisata.index', compact('paketWisata'));
    }
    
    public function detailPaket($id)
    {
        $paket = PaketWisata::with([
            'destinasis.fotos',
            'homestays',
            'paketCulinaries.culinary',
            'boats',
            'kiosks',
            'itineraries'
        ])->where('status', 'aktif')
          ->findOrFail($id);
        
        return view('landing.detail-paket', compact('paket'));
    }
}
