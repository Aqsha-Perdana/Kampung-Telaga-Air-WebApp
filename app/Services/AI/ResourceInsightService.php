<?php

namespace App\Services\AI;

use App\Models\AnalyticsDailyResourceSnapshot;
use App\Models\Boat;
use App\Models\Culinary;
use App\Models\Homestay;
use App\Models\Kiosk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResourceInsightService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?string $date = null): array
    {
        $date = $date ?: Carbon::today()->toDateString();

        return $this->snapshotOverview($date) ?? $this->liveOverview($date);
    }

    /**
     * @return array<string, mixed>
     */
    public function liveOverview(?string $date = null): array
    {
        $date = $date ?: Carbon::today()->toDateString();
        $dateLabel = Carbon::parse($date)->format('d M Y');

        $activeBoats = Boat::query()->where('is_active', true);
        $activeHomestays = Homestay::query()->where('is_active', true);
        $activeCulinaries = Culinary::query();
        $activeKiosks = Kiosk::query();

        $baseBookingQuery = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->where(function ($query) use ($date) {
                $query->whereRaw(
                    '? BETWEEN order_items.tanggal_keberangkatan AND DATE_ADD(order_items.tanggal_keberangkatan, INTERVAL (order_items.durasi_hari - 1) DAY)',
                    [$date]
                );
            });

        return [
            'date' => $date,
            'date_label' => $dateLabel,
            'boats' => [
                'total' => (int) (clone $activeBoats)->count(),
                'active_capacity' => (int) ((clone $activeBoats)->sum('kapasitas') ?? 0),
                'booked' => (int) ((clone $baseBookingQuery)->join('paket_wisata_boat', 'order_items.id_paket', '=', 'paket_wisata_boat.id_paket')->distinct()->count('paket_wisata_boat.id_boat')),
                'utilization_percent' => $this->utilizationPercent((int) (clone $activeBoats)->count(), (int) ((clone $baseBookingQuery)->join('paket_wisata_boat', 'order_items.id_paket', '=', 'paket_wisata_boat.id_paket')->distinct()->count('paket_wisata_boat.id_boat'))),
            ],
            'homestays' => [
                'total' => (int) (clone $activeHomestays)->count(),
                'active_capacity' => (int) ((clone $activeHomestays)->sum('kapasitas') ?? 0),
                'booked' => (int) ((clone $baseBookingQuery)->join('paket_wisata_homestay', 'order_items.id_paket', '=', 'paket_wisata_homestay.id_paket')->distinct()->count('paket_wisata_homestay.id_homestay')),
                'utilization_percent' => $this->utilizationPercent((int) (clone $activeHomestays)->count(), (int) ((clone $baseBookingQuery)->join('paket_wisata_homestay', 'order_items.id_paket', '=', 'paket_wisata_homestay.id_paket')->distinct()->count('paket_wisata_homestay.id_homestay'))),
            ],
            'culinaries' => [
                'total' => (int) (clone $activeCulinaries)->count(),
                'active_capacity' => 0,
                'booked' => (int) ((clone $baseBookingQuery)->join('paket_wisata_culinary', 'order_items.id_paket', '=', 'paket_wisata_culinary.id_paket')->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')->distinct()->count('paket_culinaries.id_culinary')),
                'utilization_percent' => $this->utilizationPercent((int) (clone $activeCulinaries)->count(), (int) ((clone $baseBookingQuery)->join('paket_wisata_culinary', 'order_items.id_paket', '=', 'paket_wisata_culinary.id_paket')->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')->distinct()->count('paket_culinaries.id_culinary'))),
            ],
            'kiosks' => [
                'total' => (int) (clone $activeKiosks)->count(),
                'active_capacity' => (int) ((clone $activeKiosks)->sum('kapasitas') ?? 0),
                'booked' => (int) ((clone $baseBookingQuery)->join('paket_wisata_kiosk', 'order_items.id_paket', '=', 'paket_wisata_kiosk.id_paket')->distinct()->count('paket_wisata_kiosk.id_kiosk')),
                'utilization_percent' => $this->utilizationPercent((int) (clone $activeKiosks)->count(), (int) ((clone $baseBookingQuery)->join('paket_wisata_kiosk', 'order_items.id_paket', '=', 'paket_wisata_kiosk.id_paket')->distinct()->count('paket_wisata_kiosk.id_kiosk'))),
            ],
            'source' => 'live_query',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function entityDetail(string $type, string $entityId): ?array
    {
        return match ($type) {
            'boat' => $this->boatDetail($entityId),
            'homestay' => $this->homestayDetail($entityId),
            'culinary' => $this->culinaryDetail($entityId),
            'kiosk' => $this->kioskDetail($entityId),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function snapshotOverview(string $date): ?array
    {
        if (!Schema::hasTable('analytics_daily_resource_snapshots')) {
            return null;
        }

        $rows = AnalyticsDailyResourceSnapshot::query()
            ->where('snapshot_date', Carbon::parse($date)->toDateString())
            ->get()
            ->keyBy('resource_type');

        if ($rows->count() < 4) {
            return null;
        }

        $build = function (string $type) use ($rows) {
            $row = $rows->get($type);

            return [
                'total' => (int) ($row->total_resources ?? 0),
                'booked' => (int) ($row->booked_resources ?? 0),
                'active_capacity' => (int) ($row->active_capacity ?? 0),
                'utilization_percent' => (float) ($row->utilization_percent ?? 0),
            ];
        };

        return [
            'date' => Carbon::parse($date)->toDateString(),
            'date_label' => Carbon::parse($date)->format('d M Y'),
            'boats' => $build('boat'),
            'homestays' => $build('homestay'),
            'culinaries' => $build('culinary'),
            'kiosks' => $build('kiosk'),
            'source' => 'daily_snapshots',
        ];
    }

    private function utilizationPercent(int $total, int $booked): float
    {
        return $total > 0 ? round(($booked / $total) * 100, 1) : 0.0;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function boatDetail(string $entityId): ?array
    {
        $boat = Boat::query()->where('id_boat', $entityId)->first();
        if (!$boat) {
            return null;
        }

        $packageCount = DB::table('paket_wisata_boat')->where('id_boat', $boat->id)->distinct()->count('id_paket');
        $orderCount = DB::table('paket_wisata_boat')
            ->join('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_boat.id_boat', $boat->id)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->distinct()
            ->count('orders.id_order');

        return [
            'type' => 'boat',
            'id' => (string) $boat->id_boat,
            'name' => (string) $boat->nama,
            'capacity' => (int) $boat->kapasitas,
            'price' => (float) $boat->harga_sewa,
            'status' => $boat->is_active ? 'active' : 'inactive',
            'package_count' => (int) $packageCount,
            'order_count' => (int) $orderCount,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function homestayDetail(string $entityId): ?array
    {
        $homestay = Homestay::query()->where('id_homestay', $entityId)->first();
        if (!$homestay) {
            return null;
        }

        $packageCount = DB::table('paket_wisata_homestay')->where('id_homestay', $homestay->id_homestay)->distinct()->count('id_paket');
        $orderCount = DB::table('paket_wisata_homestay')
            ->join('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_homestay.id_homestay', $homestay->id_homestay)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->distinct()
            ->count('orders.id_order');

        return [
            'type' => 'homestay',
            'id' => (string) $homestay->id_homestay,
            'name' => (string) $homestay->nama,
            'capacity' => (int) $homestay->kapasitas,
            'price' => (float) $homestay->harga_per_malam,
            'status' => $homestay->is_active ? 'active' : 'inactive',
            'package_count' => (int) $packageCount,
            'order_count' => (int) $orderCount,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function culinaryDetail(string $entityId): ?array
    {
        $culinary = Culinary::query()->where('id_culinary', $entityId)->first();
        if (!$culinary) {
            return null;
        }

        $packageCount = DB::table('paket_culinaries')->where('id_culinary', $culinary->id_culinary)->count();
        $orderCount = DB::table('paket_culinaries')
            ->join('paket_wisata_culinary', 'paket_culinaries.id', '=', 'paket_wisata_culinary.id_paket_culinary')
            ->join('order_items', 'paket_wisata_culinary.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_culinaries.id_culinary', $culinary->id_culinary)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->distinct()
            ->count('orders.id_order');

        return [
            'type' => 'culinary',
            'id' => (string) $culinary->id_culinary,
            'name' => (string) $culinary->nama,
            'location' => (string) ($culinary->lokasi ?? ''),
            'package_count' => (int) $packageCount,
            'order_count' => (int) $orderCount,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function kioskDetail(string $entityId): ?array
    {
        $kiosk = Kiosk::query()->where('id_kiosk', $entityId)->first();
        if (!$kiosk) {
            return null;
        }

        $packageCount = DB::table('paket_wisata_kiosk')->where('id_kiosk', $kiosk->id_kiosk)->distinct()->count('id_paket');
        $orderCount = DB::table('paket_wisata_kiosk')
            ->join('order_items', 'paket_wisata_kiosk.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_kiosk.id_kiosk', $kiosk->id_kiosk)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->distinct()
            ->count('orders.id_order');

        return [
            'type' => 'kiosk',
            'id' => (string) $kiosk->id_kiosk,
            'name' => (string) $kiosk->nama,
            'capacity' => (int) $kiosk->kapasitas,
            'price' => (float) $kiosk->harga_per_paket,
            'package_count' => (int) $packageCount,
            'order_count' => (int) $orderCount,
        ];
    }
}
