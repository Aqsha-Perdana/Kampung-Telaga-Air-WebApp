<?php

namespace App\Services\FinancialReport;

use App\Services\OrderItemSnapshotService;
use Illuminate\Support\Facades\DB;

class OrderCostCalculator
{
    public function __construct(private readonly OrderItemSnapshotService $snapshotService)
    {
    }

    public function calculateOrderCost(string $orderId): array
    {
        $orderItems = DB::table('order_items')
            ->where('id_order', $orderId)
            ->get();

        $totalCost = 0.0;
        $breakdown = [
            'boats' => ['items' => [], 'total' => 0.0],
            'homestays' => ['items' => [], 'total' => 0.0],
            'culinary' => ['items' => [], 'total' => 0.0],
            'kiosks' => ['items' => [], 'total' => 0.0],
        ];

        foreach ($orderItems as $item) {
            $snapshot = $this->snapshotService->breakdownFromOrderItem($item);
            $itemTotal = (float) ($snapshot['vendor_total'] ?? 0);

            if ($itemTotal <= 0.0) {
                $itemTotal = (float) ($snapshot['boat_total'] ?? 0)
                    + (float) ($snapshot['homestay_total'] ?? 0)
                    + (float) ($snapshot['culinary_total'] ?? 0)
                    + (float) ($snapshot['kiosk_total'] ?? 0);
            }

            $totalCost += $itemTotal;

            $breakdown['boats']['total'] += (float) ($snapshot['boat_total'] ?? 0);
            $breakdown['boats']['items'] = array_merge(
                $breakdown['boats']['items'],
                $this->mapItems($snapshot['boat_items'] ?? [], 'harga_per_hari', 'hari_ke')
            );

            $breakdown['homestays']['total'] += (float) ($snapshot['homestay_total'] ?? 0);
            $breakdown['homestays']['items'] = array_merge(
                $breakdown['homestays']['items'],
                $this->mapItems($snapshot['homestay_items'] ?? [], 'harga_per_malam', 'jumlah_malam')
            );

            $breakdown['culinary']['total'] += (float) ($snapshot['culinary_total'] ?? 0);
            $breakdown['culinary']['items'] = array_merge(
                $breakdown['culinary']['items'],
                $this->mapItems($snapshot['culinary_items'] ?? [], 'harga', null)
            );

            $breakdown['kiosks']['total'] += (float) ($snapshot['kiosk_total'] ?? 0);
            $breakdown['kiosks']['items'] = array_merge(
                $breakdown['kiosks']['items'],
                $this->mapItems($snapshot['kiosk_items'] ?? [], 'harga_per_paket', 'hari_ke')
            );
        }

        return [
            'total' => $totalCost,
            'breakdown' => $breakdown,
        ];
    }

    public function getCostByType(iterable $orders, string $type): float
    {
        $total = 0.0;

        foreach ($orders as $order) {
            if (($order->status ?? null) === 'refunded') {
                continue;
            }

            $orderCost = $this->calculateOrderCost((string) $order->id_order);
            $total += (float) ($orderCost['breakdown'][$type]['total'] ?? 0);
        }

        return $total;
    }

    private function mapItems(array $items, string $unitPriceKey, ?string $quantityKey): array
    {
        return array_map(function (array $item) use ($unitPriceKey, $quantityKey) {
            return [
                'name' => $item['nama'] ?? '-',
                'unit_price' => (float) ($item[$unitPriceKey] ?? $item['unit_price'] ?? 0),
                'quantity' => $quantityKey ? (int) ($item[$quantityKey] ?? $item['quantity'] ?? 1) : (int) ($item['quantity'] ?? 1),
                'total' => (float) ($item['total'] ?? $item['revenue'] ?? 0),
            ];
        }, $items);
    }
}
