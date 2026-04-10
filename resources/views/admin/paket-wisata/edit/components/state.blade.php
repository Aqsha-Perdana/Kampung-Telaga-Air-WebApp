@php
    $hasOldDestinasi = session()->hasOldInput('destinasi_ids');
    $hasOldHomestay = session()->hasOldInput('homestay_ids');
    $hasOldCulinary = session()->hasOldInput('culinary_paket_ids');
    $hasOldBoat = session()->hasOldInput('boat_ids');
    $hasOldKiosk = session()->hasOldInput('kiosk_ids');
    $hasOldItinerary = session()->hasOldInput('itinerary_judul');
    $selectedDestinasis = $paketWisata->destinasis->keyBy('id_destinasi');
    $selectedHomestays = $paketWisata->homestays->keyBy('id_homestay');
    $selectedCulinaries = $paketWisata->paketCulinaries->keyBy('id');
    $selectedBoats = $paketWisata->boats->keyBy('id');
    $selectedKiosks = $paketWisata->kiosks->keyBy('id_kiosk');
    $selectedDestinasiIds = collect($hasOldDestinasi ? old('destinasi_ids', []) : $paketWisata->destinasis->pluck('id_destinasi')->all())->map(fn($id) => (string) $id)->all();
    $selectedHomestayIds = collect($hasOldHomestay ? old('homestay_ids', []) : $paketWisata->homestays->pluck('id_homestay')->all())->map(fn($id) => (string) $id)->all();
    $selectedCulinaryIds = collect($hasOldCulinary ? old('culinary_paket_ids', []) : $paketWisata->paketCulinaries->pluck('id')->all())->map(fn($id) => (string) $id)->all();
    $selectedBoatIds = collect($hasOldBoat ? old('boat_ids', []) : $paketWisata->boats->pluck('id')->all())->map(fn($id) => (string) $id)->all();
    $selectedKioskIds = collect($hasOldKiosk ? old('kiosk_ids', []) : $paketWisata->kiosks->pluck('id_kiosk')->all())->map(fn($id) => (string) $id)->all();
    $destinasiHariMap = $hasOldDestinasi
        ? collect(old('destinasi_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('destinasi_hari.' . $index, 1)])->all()
        : $paketWisata->destinasis->mapWithKeys(fn($destinasi) => [(string) $destinasi->id_destinasi => (int) ($destinasi->pivot->hari_ke ?? 1)])->all();
    $homestayMalamMap = $hasOldHomestay
        ? collect(old('homestay_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('homestay_malam.' . $index, 1)])->all()
        : $paketWisata->homestays->mapWithKeys(fn($homestay) => [(string) $homestay->id_homestay => (int) ($homestay->pivot->jumlah_malam ?? 1)])->all();
    $culinaryHariMap = $hasOldCulinary
        ? collect(old('culinary_paket_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('culinary_hari.' . $index, 1)])->all()
        : $paketWisata->paketCulinaries->mapWithKeys(fn($culinary) => [(string) $culinary->id => (int) ($culinary->pivot->hari_ke ?? 1)])->all();
    $boatHariMap = $hasOldBoat
        ? collect(old('boat_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('boat_hari.' . $index, 1)])->all()
        : $paketWisata->boats->mapWithKeys(fn($boat) => [(string) $boat->id => (int) ($boat->pivot->hari_ke ?? 1)])->all();
    $kioskHariMap = $hasOldKiosk
        ? collect(old('kiosk_ids', []))->mapWithKeys(fn($id, $index) => [(string) $id => (int) old('kiosk_hari.' . $index, 1)])->all()
        : $paketWisata->kiosks->mapWithKeys(fn($kiosk) => [(string) $kiosk->id_kiosk => (int) ($kiosk->pivot->hari_ke ?? 1)])->all();
    $itineraryItems = $hasOldItinerary
        ? collect(old('itinerary_judul', []))->map(function ($judul, $index) {
            return [
                'hari_ke' => (int) old('itinerary_hari.' . $index, $index + 1),
                'judul_hari' => $judul,
                'deskripsi_kegiatan' => old('itinerary_deskripsi.' . $index, ''),
            ];
        })->all()
        : $paketWisata->itineraries
            ->sortBy('hari_ke')
            ->map(fn($itinerary) => [
                'hari_ke' => (int) $itinerary->hari_ke,
                'judul_hari' => $itinerary->judul_hari,
                'deskripsi_kegiatan' => $itinerary->deskripsi_kegiatan,
            ])->values()->all();
    $modalAwal = (float) $paketWisata->harga_total;
    $hargaFinalAwal = (float) $paketWisata->harga_final;
    $profitAwal = $hargaFinalAwal - $modalAwal;
    $pricingBufferAwal = $modalAwal * package_fee_buffer_percentage();
    $netProfitAwal = $profitAwal - $pricingBufferAwal;
    $profitPersenAwal = $modalAwal > 0 ? (($profitAwal / $modalAwal) * 100) : 0;
@endphp
