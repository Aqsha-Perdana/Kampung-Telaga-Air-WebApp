<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Culinary;
use App\Models\Destinasi;
use App\Models\Homestay;
use App\Models\Kiosk;
use App\Models\PaketCulinary;
use App\Models\PaketWisata;
use App\Models\PaketWisataItinerary;
use App\Services\ContentGeneratorService;
use App\Services\PackageRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaketWisataController extends Controller
{
    protected $recommendationService;
    protected $contentGenerator;

    public function __construct(PackageRecommendationService $recommendationService, ContentGeneratorService $contentGenerator)
    {
        $this->recommendationService = $recommendationService;
        $this->contentGenerator = $contentGenerator;
    }

    public function index()
    {
        $paketWisatas = PaketWisata::with(['destinasis', 'homestays', 'paketCulinaries', 'boats', 'kiosks'])
            ->latest()
            ->paginate(10);

        return view('admin.paket-wisata.index', compact('paketWisatas'));
    }

    public function create()
    {
        $destinasis = Destinasi::all();
        $homestays = Homestay::all();
        $culinaries = Culinary::with('pakets')->get();
        $boats = Boat::all();
        $kiosks = Kiosk::all();
        $recommendations = $this->recommendationService->getRecommendations();
        $recommendationStats = $this->recommendationService->getSummaryStats();

        return view('admin.paket-wisata.create', compact(
            'destinasis',
            'homestays',
            'culinaries',
            'boats',
            'kiosks',
            'recommendations',
            'recommendationStats'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePackageRequest($request);

        DB::beginTransaction();
        try {
            $hargaModal = $this->calculateCostPrice($request);
            $hargaJual = $validated['harga_jual'];
            $hargaFinal = $this->calculateFinalPrice(
                $hargaJual,
                $validated['tipe_diskon'],
                $validated['diskon_nominal'] ?? 0,
                $validated['diskon_persen'] ?? 0
            );

            $paketWisata = PaketWisata::create([
                'nama_paket' => $validated['nama_paket'],
                'durasi_hari' => $validated['durasi_hari'],
                'minimum_participants' => $validated['minimum_participants'],
                'maximum_participants' => $validated['maximum_participants'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'harga_total' => $hargaModal,
                'harga_jual' => $hargaJual,
                'tipe_diskon' => $validated['tipe_diskon'],
                'diskon_nominal' => $validated['tipe_diskon'] === 'nominal' ? ($validated['diskon_nominal'] ?? 0) : 0,
                'diskon_persen' => $validated['tipe_diskon'] === 'persen' ? ($validated['diskon_persen'] ?? 0) : 0,
                'harga_final' => $hargaFinal,
                'status' => $validated['status'],
            ]);

            $this->syncPackageRelations($paketWisata, $request, false);
            $this->syncItineraries($paketWisata, $request);

            DB::commit();

            return redirect()->route('paket-wisata.index')
                ->with('success', 'Tour package created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create tour package: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $paketWisata = PaketWisata::with([
            'destinasis',
            'homestays',
            'paketCulinaries.culinary',
            'boats',
            'kiosks',
            'itineraries',
        ])->findOrFail($id);

        return view('admin.paket-wisata.show', compact('paketWisata'));
    }

    public function edit($id)
    {
        $paketWisata = PaketWisata::with([
            'destinasis',
            'homestays',
            'paketCulinaries',
            'boats',
            'kiosks',
            'itineraries',
        ])->findOrFail($id);

        $destinasis = Destinasi::all();
        $homestays = Homestay::all();
        $culinaries = Culinary::with('pakets')->get();
        $boats = Boat::all();
        $kiosks = Kiosk::all();

        return view('admin.paket-wisata.edit', compact('paketWisata', 'destinasis', 'homestays', 'culinaries', 'boats', 'kiosks'));
    }

    public function update(Request $request, $id)
    {
        $paketWisata = PaketWisata::findOrFail($id);
        $validated = $this->validatePackageRequest($request);

        DB::beginTransaction();
        try {
            $hargaModal = $this->calculateCostPrice($request);
            $hargaJual = $validated['harga_jual'];
            $hargaFinal = $this->calculateFinalPrice(
                $hargaJual,
                $validated['tipe_diskon'],
                $validated['diskon_nominal'] ?? 0,
                $validated['diskon_persen'] ?? 0
            );

            $paketWisata->update([
                'nama_paket' => $validated['nama_paket'],
                'durasi_hari' => $validated['durasi_hari'],
                'minimum_participants' => $validated['minimum_participants'],
                'maximum_participants' => $validated['maximum_participants'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'harga_total' => $hargaModal,
                'harga_jual' => $hargaJual,
                'tipe_diskon' => $validated['tipe_diskon'],
                'diskon_nominal' => $validated['tipe_diskon'] === 'nominal' ? ($validated['diskon_nominal'] ?? 0) : 0,
                'diskon_persen' => $validated['tipe_diskon'] === 'persen' ? ($validated['diskon_persen'] ?? 0) : 0,
                'harga_final' => $hargaFinal,
                'status' => $validated['status'],
            ]);

            $this->syncPackageRelations($paketWisata, $request, true);

            $paketWisata->itineraries()->delete();
            $this->syncItineraries($paketWisata, $request);

            DB::commit();

            return redirect()->route('paket-wisata.index')
                ->with('success', 'Tour package updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update tour package: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $paketWisata = PaketWisata::findOrFail($id);

        try {
            $paketWisata->delete();

            return redirect()->route('paket-wisata.index')
                ->with('success', 'Tour package deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete tour package: ' . $e->getMessage());
        }
    }

    private function validatePackageRequest(Request $request): array
    {
        return $request->validate([
            'nama_paket' => 'required|string|max:255',
            'durasi_hari' => 'required|integer|min:1',
            'minimum_participants' => 'required|integer|min:1|max:500',
            'maximum_participants' => [
                'nullable',
                'integer',
                'max:500',
                Rule::when($request->filled('minimum_participants'), 'gte:minimum_participants'),
            ],
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
            'harga_jual' => 'required|numeric|min:0',
            'tipe_diskon' => 'required|in:nominal,persen,none',
            'diskon_nominal' => 'nullable|numeric|min:0',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
            'destinasi_ids' => 'nullable|array',
            'destinasi_hari.*' => 'nullable|integer|min:1',
            'homestay_ids' => 'nullable|array',
            'homestay_malam.*' => 'nullable|integer|min:1',
            'culinary_paket_ids' => 'nullable|array',
            'culinary_hari.*' => 'nullable|integer|min:1',
            'boat_ids' => 'nullable|array',
            'boat_hari.*' => 'nullable|integer|min:1',
            'kiosk_ids' => 'nullable|array',
            'kiosk_hari.*' => 'nullable|integer|min:1',
            'itinerary_hari.*' => 'nullable|integer|min:1',
            'itinerary_judul.*' => 'nullable|string',
            'itinerary_deskripsi.*' => 'nullable|string',
        ], [
            'maximum_participants.gte' => 'Maximum participants must be greater than or equal to minimum participants.',
        ]);
    }

    private function syncPackageRelations(PaketWisata $paketWisata, Request $request, bool $detachFirst): void
    {
        if ($detachFirst) {
            $paketWisata->destinasis()->detach();
            $paketWisata->homestays()->detach();
            $paketWisata->paketCulinaries()->detach();
            $paketWisata->boats()->detach();
            $paketWisata->kiosks()->detach();
        }

        if ($request->has('destinasi_ids')) {
            foreach ($request->destinasi_ids as $index => $destinasiId) {
                $paketWisata->destinasis()->attach($destinasiId, [
                    'hari_ke' => $request->destinasi_hari[$index] ?? 1,
                ]);
            }
        }

        if ($request->has('homestay_ids')) {
            foreach ($request->homestay_ids as $index => $homestayId) {
                $paketWisata->homestays()->attach($homestayId, [
                    'jumlah_malam' => $request->homestay_malam[$index] ?? 1,
                ]);
            }
        }

        if ($request->has('culinary_paket_ids')) {
            foreach ($request->culinary_paket_ids as $index => $paketCulinaryId) {
                $paketWisata->paketCulinaries()->attach($paketCulinaryId, [
                    'hari_ke' => $request->culinary_hari[$index] ?? 1,
                ]);
            }
        }

        if ($request->has('boat_ids')) {
            foreach ($request->boat_ids as $index => $boatId) {
                $paketWisata->boats()->attach($boatId, [
                    'hari_ke' => $request->boat_hari[$index] ?? 1,
                ]);
            }
        }

        if ($request->has('kiosk_ids')) {
            foreach ($request->kiosk_ids as $index => $kioskId) {
                $paketWisata->kiosks()->attach($kioskId, [
                    'hari_ke' => $request->kiosk_hari[$index] ?? 1,
                ]);
            }
        }
    }

    private function syncItineraries(PaketWisata $paketWisata, Request $request): void
    {
        if (!$request->has('itinerary_judul')) {
            return;
        }

        foreach ($request->itinerary_judul as $index => $judul) {
            if (!empty($judul)) {
                PaketWisataItinerary::create([
                    'id_paket' => $paketWisata->id_paket,
                    'hari_ke' => $request->itinerary_hari[$index],
                    'judul_hari' => $judul,
                    'deskripsi_kegiatan' => $request->itinerary_deskripsi[$index] ?? '',
                ]);
            }
        }
    }

    private function calculateCostPrice(Request $request)
    {
        $total = 0;

        if ($request->has('homestay_ids')) {
            foreach ($request->homestay_ids as $index => $homestayId) {
                $homestay = Homestay::find($homestayId);
                if ($homestay) {
                    $malam = $request->homestay_malam[$index] ?? 1;
                    $total += $homestay->harga_per_malam * $malam;
                }
            }
        }

        if ($request->has('culinary_paket_ids')) {
            foreach ($request->culinary_paket_ids as $index => $paketCulinaryId) {
                $paketCulinary = PaketCulinary::find($paketCulinaryId);
                if ($paketCulinary) {
                    $hari = $request->culinary_hari[$index] ?? 1;
                    $total += $paketCulinary->harga * $hari;
                }
            }
        }

        if ($request->has('boat_ids')) {
            foreach ($request->boat_ids as $index => $boatId) {
                $boat = Boat::find($boatId);
                if ($boat) {
                    $hari = $request->boat_hari[$index] ?? 1;
                    $total += $boat->harga_sewa * $hari;
                }
            }
        }

        if ($request->has('kiosk_ids')) {
            foreach ($request->kiosk_ids as $index => $kioskId) {
                $kiosk = Kiosk::find($kioskId);
                if ($kiosk) {
                    $hari = $request->kiosk_hari[$index] ?? 1;
                    $total += $kiosk->harga_per_paket * $hari;
                }
            }
        }

        return $total;
    }

    private function calculateFinalPrice($hargaJual, $tipeDiskon, $diskonNominal, $diskonPersen)
    {
        if ($tipeDiskon === 'nominal') {
            return max(0, $hargaJual - $diskonNominal);
        } elseif ($tipeDiskon === 'persen') {
            return max(0, $hargaJual - ($hargaJual * $diskonPersen / 100));
        }

        return $hargaJual;
    }

    public function calculatePrice(Request $request)
    {
        $hargaModal = $this->calculateCostPrice($request);
        $hargaJual = $request->harga_jual ?? 0;
        $tipeDiskon = $request->tipe_diskon ?? 'none';
        $diskonNominal = $request->diskon_nominal ?? 0;
        $diskonPersen = $request->diskon_persen ?? 0;

        $hargaFinal = $this->calculateFinalPrice($hargaJual, $tipeDiskon, $diskonNominal, $diskonPersen);
        $profit = $hargaFinal - $hargaModal;
        $profitPersen = $hargaModal > 0 ? (($profit / $hargaModal) * 100) : 0;

        return response()->json([
            'harga_total' => $hargaModal,
            'harga_jual' => $hargaJual,
            'harga_final' => $hargaFinal,
            'profit' => $profit,
            'profit_persen' => round($profitPersen, 2),
        ]);
    }

    public function generateContent(Request $request)
    {
        try {
            $data = $request->all();
            $description = $this->contentGenerator->generateDescription($data);
            $itinerary = $this->contentGenerator->generateItinerary($data);

            return response()->json([
                'success' => true,
                'description' => $description,
                'itinerary' => $itinerary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate content: ' . $e->getMessage(),
            ], 500);
        }
    }
}
