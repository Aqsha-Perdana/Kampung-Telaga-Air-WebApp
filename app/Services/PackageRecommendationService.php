<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PackageRecommendationService
{
    /**
     * Get all recommendations for package creation
     */
    public function getRecommendations()
    {
        return [
            'boats' => $this->analyzeBoats(),
            'homestays' => $this->analyzeHomestays(),
            'destinations' => $this->analyzeDestinations(),
            'culinaries' => $this->analyzeCulinaries(),
            'kiosks' => $this->analyzeKiosks(),
            'suggested_combo' => $this->generateSuggestedCombo(),
        ];
    }

    /**
     * Analyze boats and return recommendations
     */
    private function analyzeBoats()
    {
        $avgSales = $this->getAverageSales('paket_wisata_boat', 'id_boat');
        
        // Never used in any package
        $neverUsed = DB::table('boats')
            ->leftJoin('paket_wisata_boat', 'boats.id', '=', 'paket_wisata_boat.id_boat')
            ->whereNull('paket_wisata_boat.id_boat')
            ->where('boats.is_active', 1)
            ->select('boats.*')
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'boat', 'Never included in any package', 100));

        // In package but never sold (across ALL packages the boat is in)
        $soldBoatIds = DB::table('paket_wisata_boat')
            ->join('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->pluck('paket_wisata_boat.id_boat')
            ->unique();

        $neverSold = DB::table('boats')
            ->join('paket_wisata_boat', 'boats.id', '=', 'paket_wisata_boat.id_boat')
            ->whereNotIn('boats.id', $soldBoatIds)
            ->where('boats.is_active', 1)
            ->select('boats.*')
            ->distinct()
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'boat', 'In packages but never sold', 80));

        // Low performing (below average)
        $lowPerforming = $this->getLowPerformingBoats($avgSales);

        return [
            'never_used' => $neverUsed,
            'never_sold' => $neverSold,
            'low_performing' => $lowPerforming,
            'avg_sales' => $avgSales,
        ];
    }

    /**
     * Analyze homestays and return recommendations
     */
    private function analyzeHomestays()
    {
        $avgSales = $this->getAverageSalesHomestay();
        
        // Never used in any package
        $neverUsed = DB::table('homestays')
            ->leftJoin('paket_wisata_homestay', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
            ->whereNull('paket_wisata_homestay.id_homestay')
            ->where('homestays.is_active', 1)
            ->select('homestays.*')
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'homestay', 'Never included in any package', 100));

        // In package but never sold (across ALL packages the homestay is in)
        $soldHomestayIds = DB::table('paket_wisata_homestay')
            ->join('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->pluck('paket_wisata_homestay.id_homestay')
            ->unique();

        $neverSold = DB::table('homestays')
            ->join('paket_wisata_homestay', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
            ->whereNotIn('homestays.id_homestay', $soldHomestayIds)
            ->where('homestays.is_active', 1)
            ->select('homestays.*')
            ->distinct()
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'homestay', 'In packages but never sold', 80));

        // Low performing
        $lowPerforming = $this->getLowPerformingHomestays($avgSales);

        return [
            'never_used' => $neverUsed,
            'never_sold' => $neverSold,
            'low_performing' => $lowPerforming,
            'avg_sales' => $avgSales,
        ];
    }

    /**
     * Analyze destinations and return recommendations
     */
    private function analyzeDestinations()
    {
        // Never used in any package
        $neverUsed = DB::table('destinasis')
            ->leftJoin('paket_wisata_destinasi', 'destinasis.id_destinasi', '=', 'paket_wisata_destinasi.id_destinasi')
            ->whereNull('paket_wisata_destinasi.id_destinasi')
            ->select('destinasis.*')
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'destination', 'Never included in any package', 100));

        // In package but never sold (across ALL packages the destination is in)
        $soldDestinationIds = DB::table('paket_wisata_destinasi')
            ->join('order_items', 'paket_wisata_destinasi.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->pluck('paket_wisata_destinasi.id_destinasi')
            ->unique();

        $neverSold = DB::table('destinasis')
            ->join('paket_wisata_destinasi', 'destinasis.id_destinasi', '=', 'paket_wisata_destinasi.id_destinasi')
            ->whereNotIn('destinasis.id_destinasi', $soldDestinationIds)
            ->select('destinasis.*')
            ->distinct()
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'destination', 'In packages but never sold', 80));

        return [
            'never_used' => $neverUsed,
            'never_sold' => $neverSold,
            'low_performing' => collect([]),
        ];
    }

    /**
     * Analyze culinaries and return recommendations
     */
    private function analyzeCulinaries()
    {
        // Never used in any package
        $neverUsed = DB::table('paket_culinaries')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->leftJoin('paket_wisata_culinary', 'paket_culinaries.id', '=', 'paket_wisata_culinary.id_paket_culinary')
            ->whereNull('paket_wisata_culinary.id_paket_culinary')
            ->select('paket_culinaries.*', 'culinaries.nama as culinary_nama')
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'culinary', 'Never included in any package', 100));

        // In package but never sold (across ALL packages the culinary is in)
        $soldCulinaryIds = DB::table('paket_wisata_culinary')
            ->join('order_items', 'paket_wisata_culinary.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->pluck('paket_wisata_culinary.id_paket_culinary')
            ->unique();

        $neverSold = DB::table('paket_culinaries')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->join('paket_wisata_culinary', 'paket_culinaries.id', '=', 'paket_wisata_culinary.id_paket_culinary')
            ->whereNotIn('paket_culinaries.id', $soldCulinaryIds)
            ->select('paket_culinaries.*', 'culinaries.nama as culinary_nama')
            ->distinct()
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'culinary', 'In packages but never sold', 80));

        return [
            'never_used' => $neverUsed,
            'never_sold' => $neverSold,
            'low_performing' => collect([]),
        ];
    }

    /**
     * Analyze kiosks and return recommendations
     */
    private function analyzeKiosks()
    {
        // Never used in any package
        $neverUsed = DB::table('kiosks')
            ->leftJoin('paket_wisata_kiosk', 'kiosks.id_kiosk', '=', 'paket_wisata_kiosk.id_kiosk')
            ->whereNull('paket_wisata_kiosk.id_kiosk')
            ->select('kiosks.*')
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'kiosk', 'Never included in any package', 100));

        // In package but never sold (across ALL packages the kiosk is in)
        $soldKioskIds = DB::table('paket_wisata_kiosk')
            ->join('order_items', 'paket_wisata_kiosk.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->pluck('paket_wisata_kiosk.id_kiosk')
            ->unique();

        $neverSold = DB::table('kiosks')
            ->join('paket_wisata_kiosk', 'kiosks.id_kiosk', '=', 'paket_wisata_kiosk.id_kiosk')
            ->whereNotIn('kiosks.id_kiosk', $soldKioskIds)
            ->select('kiosks.*')
            ->distinct()
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'kiosk', 'In packages but never sold', 80));

        return [
            'never_used' => $neverUsed,
            'never_sold' => $neverSold,
            'low_performing' => collect([]),
        ];
    }

    /**
     * Generate a suggested combination of unused resources
     */
    private function generateSuggestedCombo()
    {
        $combo = [];

        // Pick one unused boat (if available)
        $unusedBoat = DB::table('boats')
            ->leftJoin('paket_wisata_boat', 'boats.id', '=', 'paket_wisata_boat.id_boat')
            ->whereNull('paket_wisata_boat.id_boat')
            ->where('boats.is_active', 1)
            ->first();
        
        if ($unusedBoat) {
            $combo['boat'] = $this->formatResource($unusedBoat, 'boat', 'Suggested for new package', 100);
        }

        // Pick one unused homestay (if available)
        $unusedHomestay = DB::table('homestays')
            ->leftJoin('paket_wisata_homestay', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
            ->whereNull('paket_wisata_homestay.id_homestay')
            ->where('homestays.is_active', 1)
            ->first();
        
        if ($unusedHomestay) {
            $combo['homestay'] = $this->formatResource($unusedHomestay, 'homestay', 'Suggested for new package', 100);
        }

        // Pick one unused destination (if available)
        $unusedDestination = DB::table('destinasis')
            ->leftJoin('paket_wisata_destinasi', 'destinasis.id_destinasi', '=', 'paket_wisata_destinasi.id_destinasi')
            ->whereNull('paket_wisata_destinasi.id_destinasi')
            ->first();
        
        if ($unusedDestination) {
            $combo['destination'] = $this->formatResource($unusedDestination, 'destination', 'Suggested for new package', 100);
        }

        // Calculate estimated cost if combo has items
        $estimatedCost = 0;
        if (isset($combo['boat'])) {
            $estimatedCost += $combo['boat']['price'] ?? 0;
        }
        if (isset($combo['homestay'])) {
            $estimatedCost += $combo['homestay']['price'] ?? 0;
        }

        return [
            'items' => $combo,
            'estimated_cost' => $estimatedCost,
            'has_suggestions' => count($combo) > 0,
        ];
    }

    /**
     * Format resource data for consistent output
     */
    private function formatResource($item, $type, $reason, $priority)
    {
        $name = $item->nama ?? $item->nama_paket ?? $item->nama_destinasi ?? 'Unknown';
        $id = $item->id ?? $item->id_boat ?? $item->id_homestay ?? $item->id_destinasi ?? $item->id_kiosk ?? null;
        
        $price = 0;
        switch ($type) {
            case 'boat':
                $price = $item->harga_sewa ?? 0;
                $id = $item->id;
                break;
            case 'homestay':
                $price = $item->harga_per_malam ?? 0;
                $id = $item->id_homestay;
                break;
            case 'kiosk':
                $price = $item->harga_per_paket ?? 0;
                $id = $item->id_kiosk;
                break;
            case 'culinary':
                $price = $item->harga ?? 0;
                $id = $item->id;
                $name = ($item->culinary_nama ?? '') . ' - ' . ($item->nama_paket ?? '');
                break;
            case 'destination':
                $id = $item->id_destinasi;
                break;
        }

        return [
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'price' => $price,
            'formatted_price' => 'RM ' . number_format($price, 2),
            'reason' => $reason,
            'priority' => $priority,
            'capacity' => $item->kapasitas ?? null,
            'foto' => $item->foto ?? null,
        ];
    }

    /**
     * Get average sales for a resource type
     */
    private function getAverageSales($pivotTable, $idColumn)
    {
        $result = DB::table($pivotTable)
            ->join('order_items', "{$pivotTable}.id_paket", '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->select("{$pivotTable}.{$idColumn}", DB::raw('COUNT(DISTINCT orders.id_order) as sales_count'))
            ->groupBy("{$pivotTable}.{$idColumn}")
            ->get();

        return $result->count() > 0 ? $result->avg('sales_count') : 0;
    }

    /**
     * Get average sales for homestays
     */
    private function getAverageSalesHomestay()
    {
        $result = DB::table('paket_wisata_homestay')
            ->join('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->select('paket_wisata_homestay.id_homestay', DB::raw('COUNT(DISTINCT orders.id_order) as sales_count'))
            ->groupBy('paket_wisata_homestay.id_homestay')
            ->get();

        return $result->count() > 0 ? $result->avg('sales_count') : 0;
    }

    /**
     * Get low performing boats
     */
    private function getLowPerformingBoats($avgSales)
    {
        if ($avgSales <= 0) return collect([]);

        return DB::table('boats')
            ->join('paket_wisata_boat', 'boats.id', '=', 'paket_wisata_boat.id_boat')
            ->leftJoin('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.id_order', '=', 'orders.id_order')
                     ->whereIn('orders.status', ['paid', 'confirmed', 'completed']);
            })
            ->where('boats.is_active', 1)
            ->select('boats.*', DB::raw('COUNT(DISTINCT orders.id_order) as sales_count'))
            ->groupBy('boats.id', 'boats.id_boat', 'boats.nama', 'boats.kapasitas', 'boats.harga_sewa', 'boats.foto', 'boats.is_active', 'boats.created_at', 'boats.updated_at')
            ->havingRaw('COUNT(DISTINCT orders.id_order) > 0 AND COUNT(DISTINCT orders.id_order) < ?', [$avgSales])
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'boat', "Low sales ({$item->sales_count} orders, avg: " . round($avgSales, 1) . ")", 60));
    }

    /**
     * Get low performing homestays
     */
    private function getLowPerformingHomestays($avgSales)
    {
        if ($avgSales <= 0) return collect([]);

        return DB::table('homestays')
            ->join('paket_wisata_homestay', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
            ->leftJoin('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.id_order', '=', 'orders.id_order')
                     ->whereIn('orders.status', ['paid', 'confirmed', 'completed']);
            })
            ->where('homestays.is_active', 1)
            ->select('homestays.*', DB::raw('COUNT(DISTINCT orders.id_order) as sales_count'))
            ->groupBy('homestays.id', 'homestays.id_homestay', 'homestays.nama', 'homestays.kapasitas', 'homestays.harga_per_malam', 'homestays.foto', 'homestays.is_active', 'homestays.created_at', 'homestays.updated_at')
            ->havingRaw('COUNT(DISTINCT orders.id_order) > 0 AND COUNT(DISTINCT orders.id_order) < ?', [$avgSales])
            ->get()
            ->map(fn($item) => $this->formatResource($item, 'homestay', "Low sales ({$item->sales_count} orders, avg: " . round($avgSales, 1) . ")", 60));
    }

    /**
     * Get summary statistics
     */
    public function getSummaryStats()
    {
        $recommendations = $this->getRecommendations();
        
        $totalUnused = 0;
        $totalNeverSold = 0;
        $totalLowPerforming = 0;

        foreach (['boats', 'homestays', 'destinations', 'culinaries', 'kiosks'] as $category) {
            if (isset($recommendations[$category])) {
                $totalUnused += count($recommendations[$category]['never_used']);
                $totalNeverSold += count($recommendations[$category]['never_sold']);
                $totalLowPerforming += count($recommendations[$category]['low_performing'] ?? []);
            }
        }

        return [
            'total_unused' => $totalUnused,
            'total_never_sold' => $totalNeverSold,
            'total_low_performing' => $totalLowPerforming,
            'total_recommendations' => $totalUnused + $totalNeverSold + $totalLowPerforming,
            'has_suggestions' => $recommendations['suggested_combo']['has_suggestions'],
        ];
    }
}
