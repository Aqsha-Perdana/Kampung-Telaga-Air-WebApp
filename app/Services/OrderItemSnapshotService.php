<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderItemSnapshotService
{
    private array $livePackageSnapshotCache = [];

    public function buildPackageSnapshot(string $paketId, float $packagePrice): array
    {
        $cacheKey = $paketId . '|' . number_format($packagePrice, 2, '.', '');

        if (isset($this->livePackageSnapshotCache[$cacheKey])) {
            return $this->livePackageSnapshotCache[$cacheKey];
        }

        $snapshot = [
            'boat_total' => 0.0,
            'boat_items' => [],
            'homestay_total' => 0.0,
            'homestay_items' => [],
            'culinary_total' => 0.0,
            'culinary_items' => [],
            'kiosk_total' => 0.0,
            'kiosk_items' => [],
            'vendor_total' => 0.0,
            'company_profit' => $packagePrice,
        ];

        $boats = DB::table('paket_wisata_boat')
            ->join('boats', 'paket_wisata_boat.id_boat', '=', 'boats.id')
            ->where('paket_wisata_boat.id_paket', $paketId)
            ->select('boats.id_boat', 'boats.nama', 'boats.harga_sewa', 'paket_wisata_boat.hari_ke')
            ->get();

        foreach ($boats as $boat) {
            $total = (float) $boat->harga_sewa;
            $snapshot['boat_total'] += $total;
            $snapshot['boat_items'][] = [
                'resource_id' => (string) $boat->id_boat,
                'nama' => $boat->nama,
                'hari_ke' => (int) $boat->hari_ke,
                'harga_per_hari' => $total,
                'unit_price' => $total,
                'quantity' => 1,
                'revenue' => $total,
                'total' => $total,
            ];
        }

        $homestays = DB::table('paket_wisata_homestay')
            ->join('homestays', 'paket_wisata_homestay.id_homestay', '=', 'homestays.id_homestay')
            ->where('paket_wisata_homestay.id_paket', $paketId)
            ->select('homestays.id_homestay', 'homestays.nama', 'homestays.harga_per_malam', 'paket_wisata_homestay.jumlah_malam')
            ->get();

        foreach ($homestays as $homestay) {
            $unitPrice = (float) $homestay->harga_per_malam;
            $quantity = (int) $homestay->jumlah_malam;
            $total = $unitPrice * $quantity;
            $snapshot['homestay_total'] += $total;
            $snapshot['homestay_items'][] = [
                'resource_id' => (string) $homestay->id_homestay,
                'nama' => $homestay->nama,
                'jumlah_malam' => $quantity,
                'harga_per_malam' => $unitPrice,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'revenue' => $total,
                'total' => $total,
            ];
        }

        $culinaries = DB::table('paket_wisata_culinary')
            ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->where('paket_wisata_culinary.id_paket', $paketId)
            ->select(
                'culinaries.id_culinary',
                'culinaries.nama',
                'paket_culinaries.id as variant_id',
                'paket_culinaries.nama_paket as variant_name',
                'paket_culinaries.harga',
                'paket_culinaries.kapasitas',
                'paket_wisata_culinary.hari_ke'
            )
            ->get();

        foreach ($culinaries as $culinary) {
            $total = (float) $culinary->harga;
            $snapshot['culinary_total'] += $total;
            $snapshot['culinary_items'][] = [
                'resource_id' => (string) $culinary->id_culinary,
                'variant_id' => (int) $culinary->variant_id,
                'nama' => $culinary->nama,
                'variant_name' => $culinary->variant_name,
                'hari_ke' => (int) $culinary->hari_ke,
                'kapasitas' => (int) $culinary->kapasitas,
                'harga' => $total,
                'unit_price' => $total,
                'quantity' => 1,
                'revenue' => $total,
                'total' => $total,
            ];
        }

        $kiosks = DB::table('paket_wisata_kiosk')
            ->join('kiosks', 'paket_wisata_kiosk.id_kiosk', '=', 'kiosks.id_kiosk')
            ->where('paket_wisata_kiosk.id_paket', $paketId)
            ->select('kiosks.id_kiosk', 'kiosks.nama', 'kiosks.harga_per_paket', 'paket_wisata_kiosk.hari_ke')
            ->get();

        foreach ($kiosks as $kiosk) {
            $total = (float) $kiosk->harga_per_paket;
            $snapshot['kiosk_total'] += $total;
            $snapshot['kiosk_items'][] = [
                'resource_id' => (string) $kiosk->id_kiosk,
                'nama' => $kiosk->nama,
                'hari_ke' => (int) $kiosk->hari_ke,
                'harga_per_paket' => $total,
                'unit_price' => $total,
                'quantity' => 1,
                'revenue' => $total,
                'total' => $total,
            ];
        }

        $snapshot['vendor_total'] = $snapshot['boat_total']
            + $snapshot['homestay_total']
            + $snapshot['culinary_total']
            + $snapshot['kiosk_total'];

        $snapshot['company_profit'] = $packagePrice - $snapshot['vendor_total'];

        $this->livePackageSnapshotCache[$cacheKey] = $snapshot;

        return $snapshot;
    }

    public function breakdownFromOrderItem(object|array $item): array
    {
        $row = (object) $item;

        $hasSnapshotTotals = isset($row->vendor_cost_total);
        $hasSnapshotItems = isset($row->boat_cost_items) || isset($row->homestay_cost_items)
            || isset($row->culinary_cost_items) || isset($row->kiosk_cost_items);

        if ($hasSnapshotTotals || $hasSnapshotItems) {
            $breakdown = [
                'boat_total' => (float) ($row->boat_cost_total ?? 0),
                'boat_items' => $this->decodeSnapshotItems($row->boat_cost_items ?? null),
                'homestay_total' => (float) ($row->homestay_cost_total ?? 0),
                'homestay_items' => $this->decodeSnapshotItems($row->homestay_cost_items ?? null),
                'culinary_total' => (float) ($row->culinary_cost_total ?? 0),
                'culinary_items' => $this->decodeSnapshotItems($row->culinary_cost_items ?? null),
                'kiosk_total' => (float) ($row->kiosk_cost_total ?? 0),
                'kiosk_items' => $this->decodeSnapshotItems($row->kiosk_cost_items ?? null),
                'vendor_total' => (float) ($row->vendor_cost_total ?? 0),
                'company_profit' => (float) ($row->company_profit_total ?? ((float) ($row->subtotal ?? 0) - (float) ($row->vendor_cost_total ?? 0))),
            ];

            if ($breakdown['vendor_total'] <= 0.0) {
                $breakdown['vendor_total'] = $breakdown['boat_total']
                    + $breakdown['homestay_total']
                    + $breakdown['culinary_total']
                    + $breakdown['kiosk_total'];
            }

            return $breakdown;
        }

        return $this->buildPackageSnapshot((string) $row->id_paket, (float) ($row->subtotal ?? $row->harga_satuan ?? 0));
    }

    public function aggregateResourceEntries(iterable $orderItems, string $type): Collection
    {
        $config = $this->resourceTypeConfig($type);
        $entries = [];

        foreach ($orderItems as $orderItem) {
            $orderItemObject = (object) $orderItem;
            $breakdown = $this->breakdownFromOrderItem($orderItemObject);

            foreach ($breakdown[$config['items_key']] as $resourceItem) {
                $resourceId = (string) ($resourceItem['resource_id'] ?? $resourceItem['variant_id'] ?? md5(json_encode($resourceItem)));

                if (!isset($entries[$resourceId])) {
                    $entries[$resourceId] = [
                        'id' => $resourceId,
                        'name' => (string) ($resourceItem['nama'] ?? '-'),
                        'type' => $config['label'],
                        'price_per_unit' => (float) ($resourceItem[$config['price_key']] ?? $resourceItem['unit_price'] ?? 0),
                        'unit_name' => $config['unit_name'],
                        'usage_count' => 0,
                        'total_orders' => [],
                        'total_participants' => 0,
                        'total_revenue' => 0.0,
                        'transactions' => [],
                    ];

                    if ($config['units_key'] !== null) {
                        $entries[$resourceId]['total_units'] = 0;
                    }
                }

                $entries[$resourceId]['usage_count']++;
                $entries[$resourceId]['total_orders'][(string) $orderItemObject->id_order] = true;
                $entries[$resourceId]['total_participants'] += (int) ($orderItemObject->jumlah_peserta ?? 0);
                $entries[$resourceId]['total_revenue'] += (float) ($resourceItem['total'] ?? $resourceItem['revenue'] ?? 0);
                $entries[$resourceId]['price_per_unit'] = (float) ($resourceItem[$config['price_key']] ?? $resourceItem['unit_price'] ?? $entries[$resourceId]['price_per_unit']);

                if ($config['units_key'] !== null) {
                    $entries[$resourceId]['total_units'] += (int) ($resourceItem[$config['units_key']] ?? $resourceItem['quantity'] ?? 0);
                }

                $transaction = (object) [
                    'id_order' => $orderItemObject->id_order,
                    'customer_name' => $orderItemObject->customer_name ?? null,
                    'created_at' => $orderItemObject->created_at ?? null,
                    'nama_paket' => $orderItemObject->nama_paket ?? null,
                    'tanggal_keberangkatan' => $orderItemObject->tanggal_keberangkatan ?? null,
                    'jumlah_peserta' => (int) ($orderItemObject->jumlah_peserta ?? 0),
                    'resource_revenue' => (float) ($resourceItem['total'] ?? $resourceItem['revenue'] ?? 0),
                ];

                if ($type === 'boats') {
                    $transaction->hari_ke = $resourceItem['hari_ke'] ?? null;
                }

                if ($type === 'homestays') {
                    $transaction->jumlah_malam = $resourceItem['jumlah_malam'] ?? $resourceItem['quantity'] ?? 0;
                }

                if ($type === 'culinary') {
                    $transaction->hari_ke = $resourceItem['hari_ke'] ?? null;
                    $transaction->variant_name = $resourceItem['variant_name'] ?? null;
                    $transaction->variant_price = (float) ($resourceItem['total'] ?? $resourceItem['revenue'] ?? 0);
                }

                if ($type === 'kiosks') {
                    $transaction->hari_ke = $resourceItem['hari_ke'] ?? null;
                }

                $entries[$resourceId]['transactions'][] = $transaction;
            }
        }

        return collect($entries)
            ->map(function (array $entry) {
                $entry['total_orders'] = count($entry['total_orders']);
                $entry['transactions'] = collect($entry['transactions'])->sortByDesc('created_at')->values();

                if ($entry['type'] === 'Culinary' && $entry['usage_count'] > 0) {
                    $entry['price_per_unit'] = round($entry['total_revenue'] / $entry['usage_count'], 2);
                }

                return $entry;
            })
            ->sortByDesc('total_revenue')
            ->values();
    }

    private function decodeSnapshotItems(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function resourceTypeConfig(string $type): array
    {
        return match ($type) {
            'boats' => [
                'label' => 'Boat',
                'items_key' => 'boat_items',
                'unit_name' => 'day',
                'units_key' => null,
                'price_key' => 'harga_per_hari',
            ],
            'homestays' => [
                'label' => 'Homestay',
                'items_key' => 'homestay_items',
                'unit_name' => 'night',
                'units_key' => 'jumlah_malam',
                'price_key' => 'harga_per_malam',
            ],
            'culinary' => [
                'label' => 'Culinary',
                'items_key' => 'culinary_items',
                'unit_name' => 'package',
                'units_key' => null,
                'price_key' => 'harga',
            ],
            'kiosks' => [
                'label' => 'Kiosk',
                'items_key' => 'kiosk_items',
                'unit_name' => 'package',
                'units_key' => null,
                'price_key' => 'harga_per_paket',
            ],
            default => throw new \InvalidArgumentException('Unsupported resource type: ' . $type),
        };
    }
}
