<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Culinary;

class CulinaryLandingController extends Controller
{
    public function index(Request $request)
    {
        $query = Culinary::query();

        // Eager load fotos dan pakets
        $query->with(['fotos', 'pakets']);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan lokasi
        if ($request->has('lokasi') && $request->lokasi != '') {
            $query->where('lokasi', 'like', "%{$request->lokasi}%");
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        if ($sortBy == 'nama') {
            $query->orderBy('nama', 'asc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $culinaries = $query->paginate(12);
        
        // Get unique locations for filter
        $locations = Culinary::select('lokasi')
                            ->distinct()
                            ->whereNotNull('lokasi')
                            ->pluck('lokasi');
        
        return view('landing.culinary.index', compact('culinaries', 'locations'));
    }

    public function show($id_culinary)
    {
        // Ambil culinary berdasarkan id_culinary
        $culinary = Culinary::where('id_culinary', $id_culinary)
                           ->with(['fotos', 'pakets'])
                           ->firstOrFail();
        
        // Ambil culinary lain sebagai rekomendasi (exclude culinary saat ini)
        $relatedCulinaries = Culinary::where('id_culinary', '!=', $id_culinary)
                                    ->with('fotos')
                                    ->inRandomOrder()
                                    ->take(3)
                                    ->get();
        
        return view('landing.culinary.show', compact('culinary', 'relatedCulinaries'));
    }
}