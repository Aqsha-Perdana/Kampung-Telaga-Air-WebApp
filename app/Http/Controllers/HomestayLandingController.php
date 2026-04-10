<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Homestay;
use Illuminate\Support\Facades\Cache;

class HomestayLandingController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'landing.homestays.' . md5(json_encode([
            'search' => (string) $request->input('search', ''),
            'kapasitas' => (string) $request->input('kapasitas', ''),
            'harga_max' => (string) $request->input('harga_max', ''),
            'sort' => (string) $request->input('sort', 'created_at'),
            'order' => (string) $request->input('order', 'desc'),
            'page' => max((int) $request->input('page', 1), 1),
        ]));

        $homestays = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            $query = Homestay::query()
                ->select(['id_homestay', 'nama', 'kapasitas', 'harga_per_malam', 'foto', 'is_active', 'created_at'])
                ->where('is_active', true)
                ->withCount('paketWisatas');

            if ($request->filled('search')) {
                $search = (string) $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('id_homestay', 'like', "%{$search}%");
                });
            }

            if ($request->filled('kapasitas')) {
                $query->where('kapasitas', '>=', $request->input('kapasitas'));
            }

            if ($request->filled('harga_max')) {
                $query->where('harga_per_malam', '<=', $request->input('harga_max'));
            }

            $sortBy = (string) $request->get('sort', 'created_at');
            $sortOrder = (string) $request->get('order', 'desc');

            if ($sortBy === 'harga_murah') {
                $query->orderBy('harga_per_malam', 'asc');
            } elseif ($sortBy === 'harga_mahal') {
                $query->orderBy('harga_per_malam', 'desc');
            } elseif ($sortBy === 'kapasitas') {
                $query->orderBy('kapasitas', 'desc');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            return $query->paginate(12)->withQueryString();
        });
        
        return view('landing.homestay.index', compact('homestays'));
    }

    public function show($id_homestay)
    {
        $homestay = Cache::remember("landing.homestay.{$id_homestay}.v2", now()->addMinutes(5), function () use ($id_homestay) {
            return Homestay::query()
                ->where('id_homestay', $id_homestay)
                ->where('is_active', true)
                ->with('paketWisatas')
                ->withCount('paketWisatas')
                ->firstOrFail();
        });

        $relatedHomestays = Cache::remember("landing.homestay.{$id_homestay}.related.v2", now()->addMinutes(5), function () use ($id_homestay) {
            return Homestay::query()
                ->select(['id_homestay', 'nama', 'kapasitas', 'harga_per_malam', 'foto', 'is_active'])
                ->where('id_homestay', '!=', $id_homestay)
                ->where('is_active', true)
                ->withCount('paketWisatas')
                ->inRandomOrder()
                ->take(3)
                ->get();
        });
        
        return view('landing.homestay.show', compact('homestay', 'relatedHomestays'));
    }
}
