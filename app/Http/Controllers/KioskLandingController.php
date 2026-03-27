<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kiosk;
use Illuminate\Support\Facades\Cache;

class KioskLandingController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'landing.kiosks.' . md5(json_encode([
            'search' => (string) $request->input('search', ''),
            'harga_min' => (string) $request->input('harga_min', ''),
            'harga_max' => (string) $request->input('harga_max', ''),
            'sort' => (string) $request->input('sort', 'created_at'),
            'order' => (string) $request->input('order', 'desc'),
            'page' => max((int) $request->input('page', 1), 1),
        ]));

        $kiosks = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            $query = Kiosk::query()
                ->select(['id_kiosk', 'nama', 'kapasitas', 'harga_per_paket', 'deskripsi', 'created_at'])
                ->with(['fotos:id,id_kiosk,foto,urutan'])
                ->withCount('fotos');

            if ($request->filled('search')) {
                $search = (string) $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }

            if ($request->filled('harga_min')) {
                $query->where('harga_per_paket', '>=', $request->input('harga_min'));
            }

            if ($request->filled('harga_max')) {
                $query->where('harga_per_paket', '<=', $request->input('harga_max'));
            }

            $sortBy = (string) $request->get('sort', 'created_at');
            $sortOrder = (string) $request->get('order', 'desc');

            if ($sortBy === 'harga_murah') {
                $query->orderBy('harga_per_paket', 'asc');
            } elseif ($sortBy === 'harga_mahal') {
                $query->orderBy('harga_per_paket', 'desc');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            return $query->paginate(12)->withQueryString();
        });
        
        return view('landing.kiosk.index', compact('kiosks'));
    }

    public function show($id_kiosk)
    {
        $kiosk = Cache::remember("landing.kiosk.{$id_kiosk}.v2", now()->addMinutes(5), function () use ($id_kiosk) {
            return Kiosk::query()
                ->where('id_kiosk', $id_kiosk)
                ->with(['fotos:id,id_kiosk,foto,urutan'])
                ->withCount('fotos')
                ->firstOrFail();
        });

        $relatedKiosks = Cache::remember("landing.kiosk.{$id_kiosk}.related.v2", now()->addMinutes(5), function () use ($id_kiosk) {
            return Kiosk::query()
                ->select(['id_kiosk', 'nama', 'kapasitas', 'harga_per_paket', 'deskripsi'])
                ->where('id_kiosk', '!=', $id_kiosk)
                ->with(['fotos:id,id_kiosk,foto,urutan'])
                ->withCount('fotos')
                ->inRandomOrder()
                ->take(3)
                ->get();
        });
        
        return view('landing.kiosk.show', compact('kiosk', 'relatedKiosks'));
    }
}
