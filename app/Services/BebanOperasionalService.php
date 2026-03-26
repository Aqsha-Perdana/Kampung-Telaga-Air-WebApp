<?php

namespace App\Services;

use App\Models\BebanOperasional;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BebanOperasionalService
{
    public const KATEGORI_LIST = [
        'Salaries and Wages',
        'Electricity',
        'Water',
        'Telephone and Internet',
        'Building Rent',
        'Office Supplies',
        'Transportation',
        'Maintenance and Repairs',
        'Insurance',
        'Taxes and Licenses',
        'Advertising and Marketing',
        'Professional Fees',
        'Depreciation',
        'Bank Charges',
        'Utilities',
        'Others',
    ];

    public const METODE_PEMBAYARAN = ['Cash', 'Bank Transfer'];

    public function getIndexData(array $filters): array
    {
        $baseQuery = $this->buildFilteredQuery($filters);

        $bebans = (clone $baseQuery)->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();
        $totalBeban = (clone $baseQuery)->sum('jumlah');

        $kategoriSummary = (clone $baseQuery)
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->groupBy('kategori')
            ->get();

        $metodeSummary = (clone $baseQuery)
            ->select('metode_pembayaran', DB::raw('SUM(jumlah) as total'))
            ->groupBy('metode_pembayaran')
            ->get();

        return [
            'bebans' => $bebans,
            'totalBeban' => $totalBeban,
            'kategoriList' => self::KATEGORI_LIST,
            'metodePembayaran' => self::METODE_PEMBAYARAN,
            'kategoriSummary' => $kategoriSummary,
            'metodeSummary' => $metodeSummary,
        ];
    }

    public function create(array $validated, $uploadedBukti = null): BebanOperasional
    {
        if ($uploadedBukti) {
            $validated['bukti_pembayaran'] = $uploadedBukti->store('bukti-pembayaran', 'public');
        }

        return BebanOperasional::create($validated);
    }

    public function update(BebanOperasional $bebanOperasional, array $validated, $uploadedBukti = null): BebanOperasional
    {
        if ($uploadedBukti) {
            if ($bebanOperasional->bukti_pembayaran) {
                Storage::disk('public')->delete($bebanOperasional->bukti_pembayaran);
            }

            $validated['bukti_pembayaran'] = $uploadedBukti->store('bukti-pembayaran', 'public');
        }

        $bebanOperasional->update($validated);

        return $bebanOperasional;
    }

    public function delete(BebanOperasional $bebanOperasional): void
    {
        if ($bebanOperasional->bukti_pembayaran) {
            Storage::disk('public')->delete($bebanOperasional->bukti_pembayaran);
        }

        $bebanOperasional->delete();
    }

    private function buildFilteredQuery(array $filters): Builder
    {
        $query = BebanOperasional::query();

        if (!empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        if (!empty($filters['tanggal_mulai']) && !empty($filters['tanggal_akhir'])) {
            $query->whereBetween('tanggal', [$filters['tanggal_mulai'], $filters['tanggal_akhir']]);
        }

        if (!empty($filters['metode_pembayaran'])) {
            $query->where('metode_pembayaran', $filters['metode_pembayaran']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', '%' . $search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $search . '%')
                    ->orWhere('nomor_referensi', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }
}
