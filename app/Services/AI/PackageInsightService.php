<?php

namespace App\Services\AI;

use App\Models\AnalyticsDailyPackageSnapshot;
use App\Models\PaketWisata;
use App\Services\PackageRecommendationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PackageInsightService
{
    public function __construct(private readonly PackageRecommendationService $recommendationService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function overview(int $days = 30): array
    {
        return $this->snapshotOverview($days) ?? $this->liveOverview($days);
    }

    /**
     * @return array<string, mixed>
     */
    public function liveOverview(int $days = 30): array
    {
        $since = Carbon::now()->subDays(max($days - 1, 0));

        $topPackage = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->where('orders.created_at', '>=', $since)
            ->select(
                'order_items.id_paket',
                DB::raw('MAX(order_items.nama_paket) as nama_paket'),
                DB::raw('COUNT(DISTINCT order_items.id_order) as total_orders'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(COALESCE(order_items.company_profit_total, order_items.subtotal - COALESCE(order_items.vendor_cost_total, 0))) as total_profit')
            )
            ->groupBy('order_items.id_paket')
            ->orderByDesc(DB::raw('SUM(COALESCE(order_items.company_profit_total, order_items.subtotal - COALESCE(order_items.vendor_cost_total, 0)))'))
            ->first();

        $recommendations = $this->recommendationService->getRecommendations();
        $idleTotal = collect([
            $recommendations['boats']['never_used']->count(),
            $recommendations['homestays']['never_used']->count(),
            $recommendations['destinations']['never_used']->count(),
            $recommendations['culinaries']['never_used']->count(),
            $recommendations['kiosks']['never_used']->count(),
        ])->sum();

        return [
            'period_start' => $since->toDateString(),
            'period_end' => Carbon::now()->toDateString(),
            'total_packages' => (int) PaketWisata::count(),
            'active_packages' => (int) PaketWisata::query()->where('status', 'aktif')->count(),
            'top_package' => $topPackage ? [
                'id' => (string) $topPackage->id_paket,
                'name' => (string) $topPackage->nama_paket,
                'orders' => (int) $topPackage->total_orders,
                'revenue' => (float) $topPackage->total_revenue,
                'profit' => (float) $topPackage->total_profit,
            ] : null,
            'idle_resource_candidates' => (int) $idleTotal,
            'source' => 'live_query',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function packageDetail(string $idPaket): ?array
    {
        $package = PaketWisata::query()
            ->with(['boats', 'homestays', 'paketCulinaries.culinary', 'kiosks', 'destinasis'])
            ->where('id_paket', $idPaket)
            ->first();

        if (!$package) {
            return null;
        }

        $orderStats = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('order_items.id_paket', $idPaket)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->select(
                DB::raw('COUNT(DISTINCT order_items.id_order) as total_orders'),
                DB::raw('SUM(order_items.jumlah_peserta) as total_participants'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(COALESCE(order_items.company_profit_total, order_items.subtotal - COALESCE(order_items.vendor_cost_total, 0))) as total_profit')
            )
            ->first();

        return [
            'type' => 'package',
            'id' => (string) $package->id_paket,
            'name' => (string) $package->nama_paket,
            'status' => (string) $package->status,
            'duration_days' => (int) $package->durasi_hari,
            'participant_range' => (string) $package->participant_range_label,
            'selling_price' => (float) $package->harga_final,
            'cost_price' => (float) $package->harga_total,
            'margin_percent' => round((float) $package->getProfitMargin(), 1),
            'resources' => [
                'boats' => $package->boats->pluck('nama')->values()->all(),
                'homestays' => $package->homestays->pluck('nama')->values()->all(),
                'culinaries' => $package->paketCulinaries->map(fn ($item) => $item->nama_paket ?: optional($item->culinary)->nama)->filter()->values()->all(),
                'kiosks' => $package->kiosks->pluck('nama')->values()->all(),
                'destinations' => $package->destinasis->pluck('nama')->values()->all(),
            ],
            'performance' => [
                'total_orders' => (int) ($orderStats->total_orders ?? 0),
                'total_participants' => (int) ($orderStats->total_participants ?? 0),
                'total_revenue' => (float) ($orderStats->total_revenue ?? 0),
                'total_profit' => (float) ($orderStats->total_profit ?? 0),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function snapshotOverview(int $days): ?array
    {
        if (!Schema::hasTable('analytics_daily_package_snapshots')) {
            return null;
        }

        $startDate = Carbon::today()->subDays($days - 1)->toDateString();
        $rows = AnalyticsDailyPackageSnapshot::query()
            ->where('snapshot_date', '>=', $startDate)
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $grouped = $rows->groupBy('package_id')->map(function ($items) {
            return [
                'id' => (string) $items->first()->package_id,
                'name' => (string) $items->first()->package_name,
                'orders' => (int) $items->sum('total_orders'),
                'participants' => (int) $items->sum('total_participants'),
                'revenue' => (float) $items->sum('total_revenue'),
                'profit' => (float) $items->sum('total_profit'),
            ];
        })->sortByDesc('profit')->values();

        $recommendations = $this->recommendationService->getRecommendations();
        $idleTotal = collect([
            $recommendations['boats']['never_used']->count(),
            $recommendations['homestays']['never_used']->count(),
            $recommendations['destinations']['never_used']->count(),
            $recommendations['culinaries']['never_used']->count(),
            $recommendations['kiosks']['never_used']->count(),
        ])->sum();

        return [
            'period_start' => $startDate,
            'period_end' => Carbon::today()->toDateString(),
            'total_packages' => (int) PaketWisata::count(),
            'active_packages' => (int) PaketWisata::query()->where('status', 'aktif')->count(),
            'top_package' => $grouped->first(),
            'idle_resource_candidates' => (int) $idleTotal,
            'source' => 'daily_snapshots',
        ];
    }
}
