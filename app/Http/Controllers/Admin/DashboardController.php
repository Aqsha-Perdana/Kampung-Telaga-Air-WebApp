<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $cacheKey = 'admin.dashboard.payload.v3';
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, now()->addSeconds(45), function () {
            $kpi = $this->dashboardService->getKPIMetrics();

            // 2. Trend & Top Charts
            $revenueTrend = $this->dashboardService->getRevenueTrend();
            $topPackages = $this->dashboardService->getTopPackages();
            $recentOrders = $this->dashboardService->getRecentOrders();

            // 3. Operational Stuff
            $resources = $this->dashboardService->getResourceStats();

            // 4. Sales Analysis
            $salesData = $this->dashboardService->getSalesAnalysis();

            // 5. Advanced Metrics
            $metrics = $this->dashboardService->calculateResourceMetrics($salesData);

            // 6. New Enhanced Features
            $revenueByCategory = $this->dashboardService->getRevenueByCategory();
            $upcomingBookings = $this->dashboardService->getUpcomingBookings();
            $attentionItems = $this->dashboardService->getAttentionItems();

            // Flatten data for view
            return [
                // KPI
                'totalRevenue' => $kpi['totalRevenue'] ?? 0,
                'revenueThisMonth' => $kpi['revenueThisMonth'] ?? 0,
                'revenueLastMonth' => $kpi['revenueLastMonth'] ?? 0,
                'revenueGrowth' => $kpi['revenueGrowth'] ?? 0,
                'avgOrderValue' => $kpi['avgOrderValue'] ?? 0,
                'avgOrderValueThisMonth' => $kpi['avgOrderValueThisMonth'] ?? 0,
                'avgOrderGrowth' => $kpi['avgOrderGrowth'] ?? 0,
                'totalOrders' => $kpi['totalOrders'] ?? 0,
                'ordersThisMonth' => $kpi['ordersThisMonth'] ?? 0,
                'ordersGrowth' => $kpi['ordersGrowth'] ?? 0,
                'pendingPayments' => $kpi['pendingPayments'] ?? 0,
                'completedOrders' => $kpi['completedOrders'] ?? 0,
                'ordersByStatus' => $kpi['ordersByStatus'] ?? collect([]),
                'totalParticipants' => $kpi['totalParticipants'] ?? 0,
                'thisMonthParticipants' => $kpi['thisMonthParticipants'] ?? 0,

                // Charts
                'revenueTrend' => $revenueTrend,
                'topPackages' => $topPackages,
                'recentOrders' => $recentOrders,

                // Resources
                'operationalExpenses' => $resources['operationalExpenses'] ?? 0,
                'totalHomestays' => $resources['totalHomestays'] ?? 0,
                'bookedHomestays' => $resources['bookedHomestays'] ?? 0,
                'totalBoats' => $resources['totalBoats'] ?? 0,
                'bookedBoats' => $resources['bookedBoats'] ?? 0,
                'upcomingDepartures' => $resources['upcomingDepartures'] ?? collect([]),

                // Sales Analysis
                'packageSales' => $salesData['packageSales'],
                'boatSales' => $salesData['boatSales'],
                'homestaySales' => $salesData['homestaySales'],
                'culinarySales' => $salesData['culinarySales'],
                'kioskSales' => $salesData['kioskSales'],

                // Metrics
                'resourceMetrics' => $metrics['resourceMetrics'] ?? [],
                'unsoldPackages' => $metrics['unsoldPackages'] ?? collect([]),
                'unsoldBoats' => $metrics['unsoldBoats'] ?? collect([]),
                'unsoldHomestays' => $metrics['unsoldHomestays'] ?? collect([]),
                'unsoldCulinary' => $metrics['unsoldCulinary'] ?? collect([]),
                'unsoldKiosks' => $metrics['unsoldKiosks'] ?? collect([]),
                'lowPerformingPackages' => $metrics['lowPerformingPackages'] ?? collect([]),
                'lowPerformingBoats' => $metrics['lowPerformingBoats'] ?? collect([]),
                'lowPerformingHomestays' => $metrics['lowPerformingHomestays'] ?? collect([]),
                'lowPerformingCulinary' => $metrics['lowPerformingCulinary'] ?? collect([]),
                'lowPerformingKiosks' => $metrics['lowPerformingKiosks'] ?? collect([]),

                // New Enhanced Features
                'revenueByCategory' => $revenueByCategory,
                'upcomingBookings' => $upcomingBookings,
                'stalePendingOrders' => $attentionItems['stalePendingOrders'] ?? collect([]),
                'stalePendingCount' => $attentionItems['stalePendingCount'] ?? 0,
                'neverSoldPackagesCount' => $attentionItems['neverSoldPackages'] ?? 0,
                'neverSoldBoatsCount' => $attentionItems['neverSoldBoats'] ?? 0,
                'neverSoldHomestaysCount' => $attentionItems['neverSoldHomestays'] ?? 0,
            ];
        });

        return view('admin.dashboard', $data);
    }
}
