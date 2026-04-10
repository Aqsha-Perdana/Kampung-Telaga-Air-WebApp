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
use App\Services\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaketWisataController extends Controller
{
    protected $recommendationService;
    protected $contentGenerator;
    protected $priceCalculator;

    public function __construct(
        PackageRecommendationService $recommendationService,
        ContentGeneratorService $contentGenerator,
        PriceCalculator $priceCalculator
    ) {
        $this->recommendationService = $recommendationService;
        $this->contentGenerator = $contentGenerator;
        $this->priceCalculator = $priceCalculator;
    }

    public function index()
    {
        $paketWisatas = PaketWisata::withCount(['destinasis', 'homestays', 'paketCulinaries', 'boats', 'kiosks'])
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
        $formState = $this->buildPackageFormState();

        return view('admin.paket-wisata.create', compact(
            'destinasis',
            'homestays',
            'culinaries',
            'boats',
            'kiosks',
            'recommendations',
            'recommendationStats'
        ) + $formState);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePackageRequest($request);

        DB::beginTransaction();
        try {
            $hargaModal = $this->calculateCostPrice($request);
            $targetProfit = (float) ($validated['target_profit'] ?? 0);
            $hargaJual = (float) $validated['harga_jual'];

            if ($hargaJual <= 0 && $targetProfit > 0) {
                $pricing = $this->priceCalculator->calculateSellingPrice($hargaModal, $targetProfit);
                $hargaJual = (float) $pricing['selling_price'];
            }

            $hargaFinal = $this->calculateFinalPrice(
                $hargaJual,
                $validated['tipe_diskon'],
                $validated['diskon_nominal'] ?? 0,
                $validated['diskon_persen'] ?? 0
            );

            // Handle foto thumbnail upload
            $fotoPath = null;
            if ($request->hasFile('foto_thumbnail')) {
                $fotoPath = $request->file('foto_thumbnail')
                    ->store('paket-wisata', 'public');
            }

            $paketWisata = PaketWisata::create([
                'nama_paket'           => $validated['nama_paket'],
                'durasi_hari'          => $validated['durasi_hari'],
                'minimum_participants' => $validated['minimum_participants'],
                'maximum_participants' => $validated['maximum_participants'] ?? null,
                'deskripsi'            => $validated['deskripsi'],
                'foto_thumbnail'       => $fotoPath,
                'harga_total'          => $hargaModal,
                'harga_jual'           => $hargaJual,
                'tipe_diskon'          => $validated['tipe_diskon'],
                'diskon_nominal'       => $validated['tipe_diskon'] === 'nominal' ? ($validated['diskon_nominal'] ?? 0) : 0,
                'diskon_persen'        => $validated['tipe_diskon'] === 'persen' ? ($validated['diskon_persen'] ?? 0) : 0,
                'harga_final'          => $hargaFinal,
                'status'               => $validated['status'],
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
        $recommendations = $this->recommendationService->getRecommendations();
        $recommendationStats = $this->recommendationService->getSummaryStats();
        $formState = $this->buildPackageFormState($paketWisata);

        return view('admin.paket-wisata.edit', compact(
            'paketWisata',
            'destinasis',
            'homestays',
            'culinaries',
            'boats',
            'kiosks',
            'recommendations',
            'recommendationStats'
        ) + $formState);
    }

    public function update(Request $request, $id)
    {
        $paketWisata = PaketWisata::findOrFail($id);
        $validated = $this->validatePackageRequest($request);

        DB::beginTransaction();
        try {
            $hargaModal = $this->calculateCostPrice($request);
            $targetProfit = (float) ($validated['target_profit'] ?? 0);
            $hargaJual = (float) $validated['harga_jual'];

            if ($hargaJual <= 0 && $targetProfit > 0) {
                $pricing = $this->priceCalculator->calculateSellingPrice($hargaModal, $targetProfit);
                $hargaJual = (float) $pricing['selling_price'];
            }

            $hargaFinal = $this->calculateFinalPrice(
                $hargaJual,
                $validated['tipe_diskon'],
                $validated['diskon_nominal'] ?? 0,
                $validated['diskon_persen'] ?? 0
            );

            // Handle foto thumbnail upload
            $fotoPath = $paketWisata->foto_thumbnail; // keep existing
            if ($request->hasFile('foto_thumbnail')) {
                // Hapus foto lama jika ada
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }
                $fotoPath = $request->file('foto_thumbnail')
                    ->store('paket-wisata', 'public');
            }

            $paketWisata->update([
                'nama_paket'           => $validated['nama_paket'],
                'durasi_hari'          => $validated['durasi_hari'],
                'minimum_participants' => $validated['minimum_participants'],
                'maximum_participants' => $validated['maximum_participants'] ?? null,
                'deskripsi'            => $validated['deskripsi'],
                'foto_thumbnail'       => $fotoPath,
                'harga_total'          => $hargaModal,
                'harga_jual'           => $hargaJual,
                'tipe_diskon'          => $validated['tipe_diskon'],
                'diskon_nominal'       => $validated['tipe_diskon'] === 'nominal' ? ($validated['diskon_nominal'] ?? 0) : 0,
                'diskon_persen'        => $validated['tipe_diskon'] === 'persen' ? ($validated['diskon_persen'] ?? 0) : 0,
                'harga_final'          => $hargaFinal,
                'status'               => $validated['status'],
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

        // Proteksi: tolak hapus jika ada order aktif
        $hasActiveOrders = \App\Models\OrderItem::where('id_paket', $id)
            ->whereHas('order', fn($q) => $q->whereIn('status', ['pending', 'paid', 'refund_requested']))
            ->exists();

        if ($hasActiveOrders) {
            return redirect()->back()
                ->with('error', 'Cannot delete this package: there are active or paid orders associated with it.');
        }

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
            'deskripsi' => 'required|string',
            'foto_thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:aktif,nonaktif',
            'harga_jual' => 'required|numeric|min:0',
            'target_profit' => 'nullable|numeric|min:0',
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
        $hargaModal = $request->filled('cost_price')
            ? (float) $request->input('cost_price')
            : (float) $this->calculateCostPrice($request);
        $targetProfit = (float) $request->input('target_profit', 0);
        $paymentMethod = (string) $request->input('payment_method', config('payment.default', 'stripe'));
        $pricing = $this->priceCalculator->calculateSellingPrice($hargaModal, $targetProfit, $paymentMethod);

        return response()->json([
            'harga_total' => round($hargaModal, 2),
            'target_profit' => round($targetProfit, 2),
            'payment_method' => $paymentMethod,
            'selling_price' => $pricing['selling_price'],
            'estimated_fee' => $pricing['estimated_fee'],
            'net_profit' => $pricing['net_profit'],
            'raw_price' => $pricing['raw_price'],
            'harga_jual' => $pricing['selling_price'],
            'profit' => $pricing['net_profit'],
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

    private function buildPackageFormState(?PaketWisata $paketWisata = null): array
    {
        $hasPackage = $paketWisata !== null;
        $hasOldDestinasi = session()->hasOldInput('destinasi_ids');
        $hasOldHomestay = session()->hasOldInput('homestay_ids');
        $hasOldCulinary = session()->hasOldInput('culinary_paket_ids');
        $hasOldBoat = session()->hasOldInput('boat_ids');
        $hasOldKiosk = session()->hasOldInput('kiosk_ids');
        $hasOldItinerary = session()->hasOldInput('itinerary_judul');

        $selectedDestinasiIds = collect($hasOldDestinasi ? old('destinasi_ids', []) : ($hasPackage ? $paketWisata->destinasis->pluck('id_destinasi')->all() : []))
            ->map(fn($id) => (string) $id)
            ->all();
        $selectedHomestayIds = collect($hasOldHomestay ? old('homestay_ids', []) : ($hasPackage ? $paketWisata->homestays->pluck('id_homestay')->all() : []))
            ->map(fn($id) => (string) $id)
            ->all();
        $selectedCulinaryIds = collect($hasOldCulinary ? old('culinary_paket_ids', []) : ($hasPackage ? $paketWisata->paketCulinaries->pluck('id')->all() : []))
            ->map(fn($id) => (string) $id)
            ->all();
        $selectedBoatIds = collect($hasOldBoat ? old('boat_ids', []) : ($hasPackage ? $paketWisata->boats->pluck('id')->all() : []))
            ->map(fn($id) => (string) $id)
            ->all();
        $selectedKioskIds = collect($hasOldKiosk ? old('kiosk_ids', []) : ($hasPackage ? $paketWisata->kiosks->pluck('id_kiosk')->all() : []))
            ->map(fn($id) => (string) $id)
            ->all();

        $destinasiHariMap = $hasOldDestinasi
            ? collect(old('destinasi_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('destinasi_hari.' . $index, 1)])->all()
            : ($hasPackage
                ? $paketWisata->destinasis->mapWithKeys(fn($destinasi) => [(string) $destinasi->id_destinasi => (int) ($destinasi->pivot->hari_ke ?? 1)])->all()
                : []);

        $homestayMalamMap = $hasOldHomestay
            ? collect(old('homestay_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('homestay_malam.' . $index, 1)])->all()
            : ($hasPackage
                ? $paketWisata->homestays->mapWithKeys(fn($homestay) => [(string) $homestay->id_homestay => (int) ($homestay->pivot->jumlah_malam ?? 1)])->all()
                : []);

        $culinaryHariMap = $hasOldCulinary
            ? collect(old('culinary_paket_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('culinary_hari.' . $index, 1)])->all()
            : ($hasPackage
                ? $paketWisata->paketCulinaries->mapWithKeys(fn($culinary) => [(string) $culinary->id => (int) ($culinary->pivot->hari_ke ?? 1)])->all()
                : []);

        $boatHariMap = $hasOldBoat
            ? collect(old('boat_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('boat_hari.' . $index, 1)])->all()
            : ($hasPackage
                ? $paketWisata->boats->mapWithKeys(fn($boat) => [(string) $boat->id => (int) ($boat->pivot->hari_ke ?? 1)])->all()
                : []);

        $kioskHariMap = $hasOldKiosk
            ? collect(old('kiosk_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('kiosk_hari.' . $index, 1)])->all()
            : ($hasPackage
                ? $paketWisata->kiosks->mapWithKeys(fn($kiosk) => [(string) $kiosk->id_kiosk => (int) ($kiosk->pivot->hari_ke ?? 1)])->all()
                : []);

        $itineraryItems = $hasOldItinerary
            ? collect(old('itinerary_judul', []))->map(function ($judul, $index) {
                return [
                    'hari_ke' => (int) old('itinerary_hari.' . $index, $index + 1),
                    'judul_hari' => $judul,
                    'deskripsi_kegiatan' => old('itinerary_deskripsi.' . $index, ''),
                ];
            })->all()
            : ($hasPackage
                ? $paketWisata->itineraries
                    ->sortBy('hari_ke')
                    ->map(fn($itinerary) => [
                        'hari_ke' => (int) $itinerary->hari_ke,
                        'judul_hari' => $itinerary->judul_hari,
                        'deskripsi_kegiatan' => $itinerary->deskripsi_kegiatan,
                    ])->values()->all()
                : []);

        $modalAwal = $hasPackage ? (float) $paketWisata->harga_total : 0;
        $hargaFinalAwal = $hasPackage ? (float) $paketWisata->harga_final : 0;
        $profitAwal = $hargaFinalAwal - $modalAwal;
        $pricingBufferAwal = $modalAwal * package_fee_buffer_percentage();
        $netProfitAwal = $profitAwal - $pricingBufferAwal;
        $profitPersenAwal = $modalAwal > 0 ? (($profitAwal / $modalAwal) * 100) : 0;

        return compact(
            'selectedDestinasiIds',
            'selectedHomestayIds',
            'selectedCulinaryIds',
            'selectedBoatIds',
            'selectedKioskIds',
            'destinasiHariMap',
            'homestayMalamMap',
            'culinaryHariMap',
            'boatHariMap',
            'kioskHariMap',
            'itineraryItems',
            'modalAwal',
            'hargaFinalAwal',
            'profitAwal',
            'pricingBufferAwal',
            'netProfitAwal',
            'profitPersenAwal'
        );
    }
}
