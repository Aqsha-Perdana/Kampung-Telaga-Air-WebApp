<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Culinary;
use Illuminate\Support\Facades\Cache;

class CulinaryLandingController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'landing.culinaries.' . md5(json_encode([
            'search' => (string) $request->input('search', ''),
            'lokasi' => (string) $request->input('lokasi', ''),
            'sort' => (string) $request->input('sort', 'created_at'),
            'order' => (string) $request->input('order', 'desc'),
            'page' => max((int) $request->input('page', 1), 1),
        ]));

        $culinaries = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            $query = Culinary::query()
                ->select(['id_culinary', 'nama', 'lokasi', 'deskripsi', 'created_at'])
                ->with(['fotos:id,id_culinary,foto,urutan'])
                ->withCount(['fotos', 'pakets'])
                ->withMin('pakets', 'harga');

            if ($request->filled('search')) {
                $search = (string) $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }

            if ($request->filled('lokasi')) {
                $query->where('lokasi', 'like', '%' . $request->input('lokasi') . '%');
            }

            $sortBy = (string) $request->get('sort', 'created_at');
            $sortOrder = (string) $request->get('order', 'desc');

            if ($sortBy === 'nama') {
                $query->orderBy('nama', 'asc');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            return $query->paginate(12)->withQueryString();
        });

        $locations = Cache::remember('landing.culinaries.locations.v2', now()->addMinutes(10), function () {
            return Culinary::query()
                ->select('lokasi')
                ->distinct()
                ->whereNotNull('lokasi')
                ->pluck('lokasi');
        });
        
        return view('landing.culinary.index', compact('culinaries', 'locations'));
    }

    public function show($id_culinary)
    {
        $culinary = Cache::remember("landing.culinary.{$id_culinary}.v2", now()->addMinutes(5), function () use ($id_culinary) {
            return Culinary::query()
                ->where('id_culinary', $id_culinary)
                ->with(['fotos:id,id_culinary,foto,urutan', 'pakets'])
                ->withCount(['fotos', 'pakets'])
                ->withMin('pakets', 'harga')
                ->firstOrFail();
        });

        $relatedCulinaries = Cache::remember("landing.culinary.{$id_culinary}.related.v2", now()->addMinutes(5), function () use ($id_culinary) {
            return Culinary::query()
                ->select(['id_culinary', 'nama', 'lokasi', 'deskripsi'])
                ->where('id_culinary', '!=', $id_culinary)
                ->with(['fotos:id,id_culinary,foto,urutan'])
                ->withCount(['fotos', 'pakets'])
                ->withMin('pakets', 'harga')
                ->inRandomOrder()
                ->take(3)
                ->get();
        });
        
        return view('landing.culinary.show', compact('culinary', 'relatedCulinaries'));
    }
}
