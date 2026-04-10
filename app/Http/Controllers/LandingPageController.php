<?php

namespace App\Http\Controllers;

use App\Models\Destinasi;
use App\Models\PaketWisata;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LandingPageController extends Controller
{
    public function index()
    {
        $destinasis = Cache::remember('landing.home.destinations.v2', now()->addMinutes(5), function () {
            return Destinasi::query()
                ->select(['id_destinasi', 'nama', 'deskripsi'])
                ->with([
                    'fotos:id,id_destinasi,foto,urutan',
                    'footage360:id_footage360,id_destinasi,urutan',
                ])
                ->withCount('footage360')
                ->take(6)
                ->get();
        });

        $packageVersion = $this->packageCacheVersion();

        $paketWisatas = Cache::remember("landing.home.packages.{$packageVersion}", now()->addMinutes(5), function () {
            return PaketWisata::query()
                ->select([
                    'id_paket',
                    'nama_paket',
                    'durasi_hari',
                    'deskripsi',
                    'harga_jual',
                    'harga_final',
                    'diskon_nominal',
                    'diskon_persen',
                    'status',
                ])
                ->with([
                    'destinasis:id_destinasi,nama',
                    'homestays:id_homestay,nama',
                    'itineraries:id,id_paket,hari_ke,judul_hari',
                ])
                ->withCount(['destinasis', 'homestays', 'paketCulinaries', 'boats', 'kiosks', 'itineraries'])
                ->where('status', 'aktif')
                ->latest()
                ->take(3)
                ->get();
        });

        return view('landing.home', compact('destinasis', 'paketWisatas'));
    }
    
    public function destinasi()
    {
        $page = max((int) request('page', 1), 1);

        $destinasis = Cache::remember("landing.destinations.page.{$page}.v2", now()->addMinutes(5), function () {
            return Destinasi::query()
                ->select(['id_destinasi', 'nama', 'deskripsi'])
                ->with('fotos:id,id_destinasi,foto,urutan')
                ->paginate(9);
        });

        return view('landing.destinasi', compact('destinasis'));
    }

     public function detailDestinasi($id)
    {
        $destinasi = Cache::remember("landing.destination.{$id}.v2", now()->addMinutes(5), function () use ($id) {
            return Destinasi::query()
                ->select(['id_destinasi', 'nama', 'lokasi', 'deskripsi'])
                ->with('fotos:id,id_destinasi,foto,urutan')
                ->findOrFail($id);
        });

        $rekomendasiDestinasi = Cache::remember("landing.destination.{$id}.recommendations.v2", now()->addMinutes(5), function () use ($id) {
            return Destinasi::query()
                ->select(['id_destinasi', 'nama', 'deskripsi'])
                ->with('fotos:id,id_destinasi,foto,urutan')
                ->where('id_destinasi', '!=', $id)
                ->inRandomOrder()
                ->take(3)
                ->get();
        });

        $paketTerkait = Cache::remember("landing.destination.{$id}.packages.v2", now()->addMinutes(5), function () use ($id) {
            return PaketWisata::query()
                ->select([
                    'id_paket',
                    'nama_paket',
                    'durasi_hari',
                    'deskripsi',
                    'harga_final',
                    'status',
                ])
                ->with([
                    'destinasis:id_destinasi,nama',
                    'homestays:id_homestay,nama',
                ])
                ->withCount(['destinasis', 'homestays', 'paketCulinaries'])
                ->whereHas('destinasis', function ($query) use ($id) {
                    $query->where('destinasis.id_destinasi', $id);
                })
                ->where('status', 'aktif')
                ->latest()
                ->take(3)
                ->get();
        });

        return view('landing.detail-destinasi', compact('destinasi', 'rekomendasiDestinasi', 'paketTerkait'));
    }
    
    public function paketWisata(Request $request)
    {
        $packageVersion = $this->packageCacheVersion();
        $cacheKey = 'landing.packages.' . md5(json_encode([
            'version' => $packageVersion,
            'search' => (string) $request->input('search', ''),
            'page' => max((int) $request->input('page', 1), 1),
        ]));

        $paketWisata = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            $query = PaketWisata::query()
                ->select([
                    'id_paket',
                    'nama_paket',
                    'durasi_hari',
                    'minimum_participants',
                    'maximum_participants',
                    'deskripsi',
                    'foto_thumbnail',
                    'harga_jual',
                    'diskon_nominal',
                    'diskon_persen',
                    'harga_final',
                    'status',
                    'created_at',
                ])
                ->withCount(['destinasis', 'homestays', 'boats'])
                ->where('status', 'aktif');

            if ($request->filled('search')) {
                $search = (string) $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nama_paket', 'like', '%' . $search . '%')
                        ->orWhere('deskripsi', 'like', '%' . $search . '%');
                });
            }

            return $query->orderByDesc('created_at')->paginate(6)->withQueryString();
        });

        return view('landing.paket-wisata.index', compact('paketWisata'));
    }
    
    public function detailPaket($id)
    {
        $packageVersion = $this->packageCacheVersion($id);

        $paket = Cache::remember("landing.package.{$id}.{$packageVersion}", now()->addMinutes(5), function () use ($id) {
            return PaketWisata::query()
                ->select([
                    'id_paket',
                    'nama_paket',
                    'durasi_hari',
                    'minimum_participants',
                    'maximum_participants',
                    'deskripsi',
                    'foto_thumbnail',
                    'harga_jual',
                    'diskon_nominal',
                    'diskon_persen',
                    'harga_final',
                    'status',
                ])
                ->with([
                    'destinasis' => fn ($query) => $query
                        ->select(['destinasis.id_destinasi', 'destinasis.nama', 'destinasis.lokasi'])
                        ->with(['fotos' => fn ($fotoQuery) => $fotoQuery->select(['id', 'id_destinasi', 'foto', 'urutan'])]),
                    'homestays' => fn ($query) => $query->select(['homestays.id_homestay', 'homestays.nama', 'homestays.kapasitas', 'homestays.harga_per_malam']),
                    'paketCulinaries' => fn ($query) => $query
                        ->select([
                            'paket_culinaries.id',
                            'paket_culinaries.id_culinary',
                            'paket_culinaries.nama_paket',
                            'paket_culinaries.kapasitas',
                            'paket_culinaries.harga',
                            'paket_culinaries.deskripsi_paket',
                        ])
                        ->with(['culinary' => fn ($culinaryQuery) => $culinaryQuery->select(['id_culinary', 'nama'])]),
                    'boats' => fn ($query) => $query->select(['boats.id_boat', 'boats.nama', 'boats.kapasitas', 'boats.harga_sewa']),
                    'kiosks' => fn ($query) => $query->select(['kiosks.id_kiosk', 'kiosks.nama', 'kiosks.kapasitas', 'kiosks.harga_per_paket']),
                    'itineraries' => fn ($query) => $query
                        ->select(['id', 'id_paket', 'hari_ke', 'judul_hari', 'deskripsi_kegiatan'])
                        ->orderBy('hari_ke'),
                ])
                ->where('status', 'aktif')
                ->findOrFail($id);
        });
        
        return view('landing.detail-paket', compact('paket'));
    }

    private function packageCacheVersion(?string $packageId = null): string
    {
        $query = PaketWisata::query();

        if ($packageId !== null) {
            $query->where('id_paket', $packageId);
        }

        $latestUpdatedAt = $query->max('updated_at');
        $count = (clone $query)->count();

        return md5(json_encode([
            'updated_at' => $latestUpdatedAt ? Carbon::parse($latestUpdatedAt)->format('Y-m-d H:i:s') : null,
            'count' => $count,
        ]));
    }
}
