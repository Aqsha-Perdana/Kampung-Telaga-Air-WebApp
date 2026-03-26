<?php

namespace App\Services\FinancialReport;

use App\Services\OrderItemSnapshotService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OwnerReportService
{
    public function __construct(private readonly OrderItemSnapshotService $snapshotService)
    {
    }

    public function getOwnerSummary($startDate, $endDate): array
    {
        $orderItems = $this->getOrderItemsInRange($startDate, $endDate);

        return [
            'boats' => $this->buildSummaryEntries('boat', $orderItems),
            'homestays' => $this->buildSummaryEntries('homestay', $orderItems),
            'culinary' => $this->buildSummaryEntries('culinary', $orderItems),
            'kiosks' => $this->buildSummaryEntries('kiosk', $orderItems),
        ];
    }

    public function getOwnerDetail(string $type, $id, $startDate, $endDate): ?array
    {
        $orderItems = $this->getOrderItemsInRange($startDate, $endDate);
        $resourceType = $this->resourceTypeName($type);

        if ($resourceType === null) {
            return null;
        }

        $resourceMeta = $this->findResourceMeta($type, (string) $id);

        if ($resourceMeta === null) {
            return null;
        }

        $entry = $this->snapshotService
            ->aggregateResourceEntries($orderItems, $resourceType)
            ->firstWhere('id', (string) $id);

        if (is_array($entry)) {
            return array_merge($resourceMeta, $entry);
        }

        return $this->emptyResourceEntry($resourceMeta);
    }

    private function getOrderItemsInRange($startDate, $endDate)
    {
        [$startAt, $endAt] = $this->normalizeDateTimeRange($startDate, $endDate);

        return DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$startAt, $endAt])
            ->select(
                'order_items.*',
                'orders.customer_name',
                'orders.created_at'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();
    }

    private function normalizeDateTimeRange($startDate, $endDate): array
    {
        return [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ];
    }

    private function buildSummaryEntries(string $type, $orderItems): array
    {
        $resourceType = $this->resourceTypeName($type);
        $resourceMeta = $this->getResourceMetaList($type)->keyBy('id');
        $usageEntries = $this->snapshotService->aggregateResourceEntries($orderItems, $resourceType)->keyBy('id');

        return $resourceMeta
            ->map(function (array $meta) use ($usageEntries) {
                $usage = $usageEntries->get($meta['id']);

                if (is_array($usage)) {
                    return array_merge($meta, $usage);
                }

                return $this->emptyResourceEntry($meta);
            })
            ->sortByDesc('total_revenue')
            ->values()
            ->all();
    }

    private function getResourceMetaList(string $type)
    {
        return match ($type) {
            'boat' => DB::table('boats')
                ->where('is_active', 1)
                ->select('id_boat as id', 'nama as name', 'harga_sewa as price_per_unit')
                ->orderBy('nama')
                ->get()
                ->map(fn ($item) => [
                    'id' => (string) $item->id,
                    'name' => $item->name,
                    'type' => 'Boat',
                    'price_per_unit' => (float) $item->price_per_unit,
                    'unit_name' => 'day',
                ]),
            'homestay' => DB::table('homestays')
                ->where('is_active', 1)
                ->select('id_homestay as id', 'nama as name', 'harga_per_malam as price_per_unit')
                ->orderBy('nama')
                ->get()
                ->map(fn ($item) => [
                    'id' => (string) $item->id,
                    'name' => $item->name,
                    'type' => 'Homestay',
                    'price_per_unit' => (float) $item->price_per_unit,
                    'unit_name' => 'night',
                ]),
            'culinary' => DB::table('culinaries')
                ->select('id_culinary as id', 'nama as name')
                ->orderBy('nama')
                ->get()
                ->map(fn ($item) => [
                    'id' => (string) $item->id,
                    'name' => $item->name,
                    'type' => 'Culinary',
                    'price_per_unit' => 0.0,
                    'unit_name' => 'package',
                ]),
            'kiosk' => DB::table('kiosks')
                ->select('id_kiosk as id', 'nama as name', 'harga_per_paket as price_per_unit')
                ->orderBy('nama')
                ->get()
                ->map(fn ($item) => [
                    'id' => (string) $item->id,
                    'name' => $item->name,
                    'type' => 'Kiosk',
                    'price_per_unit' => (float) $item->price_per_unit,
                    'unit_name' => 'package',
                ]),
            default => collect(),
        };
    }

    private function findResourceMeta(string $type, string $id): ?array
    {
        return $this->getResourceMetaList($type)
            ->first(fn (array $item) => $item['id'] === $id);
    }

    private function emptyResourceEntry(array $meta): array
    {
        $entry = array_merge($meta, [
            'usage_count' => 0,
            'total_orders' => 0,
            'total_participants' => 0,
            'total_revenue' => 0.0,
            'transactions' => collect(),
        ]);

        if ($meta['unit_name'] === 'night') {
            $entry['total_units'] = 0;
        }

        return $entry;
    }

    private function resourceTypeName(string $type): ?string
    {
        return match ($type) {
            'boat' => 'boats',
            'homestay' => 'homestays',
            'culinary' => 'culinary',
            'kiosk' => 'kiosks',
            default => null,
        };
    }
}
