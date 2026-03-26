<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalendarController extends Controller
{
    /**
     * Display the calendar page
     */
    public function index()
    {
        return view('admin.calendar.index');
    }

    /**
     * Get calendar events (AJAX endpoint)
     */
    public function getEvents(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        // Ambil order items yang memiliki tanggal keberangkatan dalam range
        $orderItems = OrderItem::with(['order', 'paket'])
            ->whereHas('order', function($query) {
                $query->whereIn('status', ['paid', 'confirmed', 'completed']);
            })
            ->whereBetween('tanggal_keberangkatan', [$start, $end])
            ->orderBy('tanggal_keberangkatan', 'asc')
            ->get();

        // Group by tanggal untuk menghitung total peserta dan paket per hari
        $eventsByDate = $orderItems->groupBy(function($item) {
            return Carbon::parse($item->tanggal_keberangkatan)->format('Y-m-d');
        });

        $events = [];

        foreach ($eventsByDate as $date => $items) {
            $totalPeserta = $items->sum('jumlah_peserta');
            $totalPaket = $items->count();
            $totalRevenue = $items->sum('subtotal');

            // Buat event untuk FullCalendar
            $events[] = [
                'id' => 'date-' . $date,
                'title' => "{$totalPaket} Paket • {$totalPeserta} Peserta",
                'start' => $date,
                'backgroundColor' => $this->getColorByCount($totalPaket),
                'borderColor' => $this->getColorByCount($totalPaket),
                'extendedProps' => [
                    'totalPaket' => $totalPaket,
                    'totalPeserta' => $totalPeserta,
                    'totalRevenue' => $totalRevenue,
                    'items' => $items->map(function($item) {
                        return [
                            'id_order' => $item->id_order,
                            'nama_paket' => $item->nama_paket,
                            'jumlah_peserta' => $item->jumlah_peserta,
                            'customer_name' => $item->order->customer_name,
                            'customer_phone' => $item->order->customer_phone,
                            'subtotal' => $item->subtotal,
                            'status' => $item->order->status,
                            'order_date' => $item->order->created_at->format('d M Y H:i'),
                        ];
                    })
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Get detail for specific date (AJAX endpoint)
     */
    public function getDateDetail(Request $request)
    {
        $date = $request->input('date');

        $orderItems = OrderItem::with(['order', 'paket'])
            ->whereHas('order', function($query) {
                $query->whereIn('status', ['paid', 'confirmed', 'completed']);
            })
            ->whereDate('tanggal_keberangkatan', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPeserta = $orderItems->sum('jumlah_peserta');
        $totalRevenue = $orderItems->sum('subtotal');

        return response()->json([
            'date' => Carbon::parse($date)->format('d F Y'),
            'totalPaket' => $orderItems->count(),
            'totalPeserta' => $totalPeserta,
            'totalRevenue' => $totalRevenue,
            'items' => $orderItems->map(function($item) {
                return [
                    'id_order' => $item->id_order,
                    'nama_paket' => $item->nama_paket,
                    'durasi_hari' => $item->durasi_hari,
                    'jumlah_peserta' => $item->jumlah_peserta,
                    'customer_name' => $item->order->customer_name,
                    'customer_email' => $item->order->customer_email,
                    'customer_phone' => $item->order->customer_phone,
                    'subtotal' => $item->subtotal,
                    'formatted_subtotal' => 'RM ' . number_format($item->subtotal, 2),
                    'status' => $item->order->status,
                    'status_label' => $this->getStatusLabel($item->order->status),
                    'order_date' => $item->order->created_at->format('d M Y H:i'),
                    'catatan' => $item->catatan,
                ];
            })
        ]);
    }

    /**
     * Get statistics for the calendar view
     */
    public function getStatistics(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $orderItems = OrderItem::with('order')
            ->whereHas('order', function($query) {
                $query->whereIn('status', ['paid', 'confirmed', 'completed']);
            })
            ->whereBetween('tanggal_keberangkatan', [$startDate, $endDate])
            ->get();

        return response()->json([
            'totalBookings' => $orderItems->count(),
            'totalPeserta' => $orderItems->sum('jumlah_peserta'),
            'totalRevenue' => $orderItems->sum('subtotal'),
            'uniqueDates' => $orderItems->pluck('tanggal_keberangkatan')->unique()->count(),
        ]);
    }

    /**
     * Determine color based on package count
     */
    private function getColorByCount($count)
    {
        if ($count >= 10) return '#dc3545'; // Red - High
        if ($count >= 5) return '#fd7e14';  // Orange - Medium
        if ($count >= 3) return '#ffc107';  // Yellow - Low-Medium
        return '#28a745'; // Green - Low
    }

    /**
     * Get status label in Indonesian
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'confirmed' => 'Dikonfirmasi',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get conflict alerts and warnings
     */
    public function getConflictAlerts(Request $request)
    {
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->addDays(7)->format('Y-m-d'));
        
        $alerts = [
            'critical' => [],
            'warnings' => [],
            'capacity_issues' => []
        ];
        
        // Get all confirmed orders in date range
        $orderItems = OrderItem::with(['order', 'paket'])
            ->whereHas('order', function($query) {
                $query->whereIn('status', ['paid', 'confirmed']);
            })
            ->whereBetween('tanggal_keberangkatan', [$startDate, $endDate])
            ->get();
        
        // Check for conflicts for each date
        $dates = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        
        while ($currentDate <= $endDateCarbon) {
            $dateStr = $currentDate->format('Y-m-d');
            $dates[] = $dateStr;
            
            // Check resource conflicts for this date
            $conflicts = $this->checkResourceConflicts($dateStr);
            
            if (!empty($conflicts['overbooking'])) {
                $alerts['critical'] = array_merge($alerts['critical'], $conflicts['overbooking']);
            }
            
            if (!empty($conflicts['high_capacity'])) {
                $alerts['warnings'] = array_merge($alerts['warnings'], $conflicts['high_capacity']);
            }
            
            // Check capacity issues
            $capacityData = $this->getResourceAvailabilityData($dateStr);
            $capacityIssues = [];
            
            foreach (['boats', 'homestays', 'culinaries', 'kiosks'] as $resourceType) {
                if ($capacityData[$resourceType]['utilization_percent'] >= 90) {
                    $capacityIssues[] = [
                        'type' => $resourceType,
                        'utilization' => $capacityData[$resourceType]['utilization_percent'],
                        'available' => $capacityData[$resourceType]['available'],
                        'total' => $capacityData[$resourceType]['total']
                    ];
                }
            }
            
            if (!empty($capacityIssues)) {
                $alerts['capacity_issues'][] = [
                    'date' => $dateStr,
                    'formatted_date' => Carbon::parse($dateStr)->format('M d, Y'),
                    'resources' => $capacityIssues
                ];
            }
            
            $currentDate->addDay();
        }
        
        // Get upcoming capacity summary (next 7 days)
        $upcomingSummary = [];
        foreach ($dates as $date) {
            $capacityData = $this->getResourceAvailabilityData($date);
            $hasIssue = false;
            
            foreach (['boats', 'homestays', 'culinaries', 'kiosks'] as $resourceType) {
                if ($capacityData[$resourceType]['utilization_percent'] >= 70) {
                    $hasIssue = true;
                    break;
                }
            }
            
            if ($hasIssue) {
                $upcomingSummary[] = [
                    'date' => $date,
                    'formatted_date' => Carbon::parse($date)->format('M d'),
                    'boats' => $capacityData['boats']['utilization_percent'],
                    'homestays' => $capacityData['homestays']['utilization_percent'],
                    'culinaries' => $capacityData['culinaries']['utilization_percent'],
                    'kiosks' => $capacityData['kiosks']['utilization_percent'],
                ];
            }
        }
        
        return response()->json([
            'alerts' => $alerts,
            'upcoming_summary' => $upcomingSummary,
            'total_critical' => count($alerts['critical']),
            'total_warnings' => count($alerts['warnings']),
            'total_capacity_issues' => count($alerts['capacity_issues']),
        ]);
    }
    
    /**
     * Check for resource conflicts on a specific date
     */
    private function checkResourceConflicts($date)
    {
        $conflicts = [
            'overbooking' => [],
            'high_capacity' => []
        ];
        
        // Check Boats
        $boatConflicts = $this->checkResourceTypeConflicts($date, 'boats', 'paket_wisata_boat', 'id');
        if (!empty($boatConflicts['overbooking'])) {
            $conflicts['overbooking'] = array_merge($conflicts['overbooking'], $boatConflicts['overbooking']);
        }
        if (!empty($boatConflicts['high_capacity'])) {
            $conflicts['high_capacity'] = array_merge($conflicts['high_capacity'], $boatConflicts['high_capacity']);
        }
        
        // Check Homestays
        $homestayConflicts = $this->checkResourceTypeConflicts($date, 'homestays', 'paket_wisata_homestay', 'id_homestay');
        if (!empty($homestayConflicts['overbooking'])) {
            $conflicts['overbooking'] = array_merge($conflicts['overbooking'], $homestayConflicts['overbooking']);
        }
        if (!empty($homestayConflicts['high_capacity'])) {
            $conflicts['high_capacity'] = array_merge($conflicts['high_capacity'], $homestayConflicts['high_capacity']);
        }
        
        // Check Culinaries
        $culinaryConflicts = $this->checkResourceTypeConflicts($date, 'culinaries', 'paket_wisata_culinary', 'id_culinary');
        if (!empty($culinaryConflicts['overbooking'])) {
            $conflicts['overbooking'] = array_merge($conflicts['overbooking'], $culinaryConflicts['overbooking']);
        }
        if (!empty($culinaryConflicts['high_capacity'])) {
            $conflicts['high_capacity'] = array_merge($conflicts['high_capacity'], $culinaryConflicts['high_capacity']);
        }
        
        // Check Kiosks
        $kioskConflicts = $this->checkResourceTypeConflicts($date, 'kiosks', 'paket_wisata_kiosk', 'id_kiosk');
        if (!empty($kioskConflicts['overbooking'])) {
            $conflicts['overbooking'] = array_merge($conflicts['overbooking'], $kioskConflicts['overbooking']);
        }
        if (!empty($kioskConflicts['high_capacity'])) {
            $conflicts['high_capacity'] = array_merge($conflicts['high_capacity'], $kioskConflicts['high_capacity']);
        }
        
        return $conflicts;
    }
    
    /**
     * Check conflicts for a specific resource type
     */
    private function checkResourceTypeConflicts($date, $tableName, $pivotTable, $resourceIdColumn)
    {
        $conflicts = [
            'overbooking' => [],
            'high_capacity' => []
        ];

        $baseQuery = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed'])
            ->where(function ($query) use ($date) {
                $query->whereRaw(
                    '? BETWEEN order_items.tanggal_keberangkatan AND DATE_ADD(order_items.tanggal_keberangkatan, INTERVAL (order_items.durasi_hari - 1) DAY)',
                    [$date]
                );
            });

        if ($tableName === 'culinaries') {
            $bookings = $baseQuery
                ->join('paket_wisata_culinary', 'order_items.id_paket', '=', 'paket_wisata_culinary.id_paket')
                ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
                ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
                ->select(
                    'culinaries.id_culinary',
                    'culinaries.nama as resource_name',
                    DB::raw('COALESCE(paket_culinaries.kapasitas, 0) as resource_capacity'),
                    'order_items.id_order',
                    'order_items.nama_paket',
                    'order_items.jumlah_peserta',
                    'order_items.tanggal_keberangkatan',
                    'orders.customer_name',
                    'orders.customer_phone'
                )
                ->get();
        } else {
            $bookings = $baseQuery
                ->join($pivotTable, 'order_items.id_paket', '=', $pivotTable . '.id_paket')
                ->join($tableName, $pivotTable . '.' . $resourceIdColumn, '=', $tableName . '.' . $resourceIdColumn)
                ->select(
                    $tableName . '.' . $resourceIdColumn,
                    $tableName . '.nama as resource_name',
                    DB::raw('COALESCE(' . $tableName . '.kapasitas, 0) as resource_capacity'),
                    'order_items.id_order',
                    'order_items.nama_paket',
                    'order_items.jumlah_peserta',
                    'order_items.tanggal_keberangkatan',
                    'orders.customer_name',
                    'orders.customer_phone'
                )
                ->get();
        }
        
        // Group by resource to find double bookings
        $resourceGroups = $bookings->groupBy($resourceIdColumn);
        
        foreach ($resourceGroups as $resourceId => $resourceBookings) {
            $resourceCapacity = (int) ($resourceBookings->first()->resource_capacity ?? 0);
            $totalBookedPax = (int) $resourceBookings->sum('jumlah_peserta');

            if ($resourceBookings->count() > 1) {
                if ($resourceCapacity > 0 && $totalBookedPax <= $resourceCapacity) {
                    continue;
                }

                $conflicts['overbooking'][] = [
                    'date' => $date,
                    'formatted_date' => Carbon::parse($date)->format('M d, Y'),
                    'resource_type' => $this->getSingularResourceType($tableName),
                    'resource_name' => $resourceBookings->first()->resource_name,
                    'resource_id' => $resourceId,
                    'resource_capacity' => $resourceCapacity,
                    'booked_pax' => $totalBookedPax,
                    'over_capacity' => max($totalBookedPax - $resourceCapacity, 0),
                    'bookings' => $resourceBookings->map(function($booking) {
                        return [
                            'order_id' => $booking->id_order,
                            'package_name' => $booking->nama_paket,
                            'pax' => $booking->jumlah_peserta,
                            'customer_name' => $booking->customer_name,
                            'customer_phone' => $booking->customer_phone,
                            'departure_date' => Carbon::parse($booking->tanggal_keberangkatan)->format('M d, Y'),
                        ];
                    })->toArray()
                ];
            } else if ($resourceBookings->count() == 1) {
                $booking = $resourceBookings->first();
                if ($booking->resource_capacity > 0 && $booking->jumlah_peserta > $booking->resource_capacity) {
                    $conflicts['high_capacity'][] = [
                        'date' => $date,
                        'formatted_date' => Carbon::parse($date)->format('M d, Y'),
                        'resource_type' => $this->getSingularResourceType($tableName),
                        'resource_name' => $booking->resource_name,
                        'resource_capacity' => $booking->resource_capacity,
                        'booked_pax' => $booking->jumlah_peserta,
                        'over_capacity' => $booking->jumlah_peserta - $booking->resource_capacity,
                        'order_id' => $booking->id_order,
                        'package_name' => $booking->nama_paket,
                        'customer_name' => $booking->customer_name,
                    ];
                }
            }
        }
        
        return $conflicts;
    }
    
    /**
     * Get resource availability data (helper method)
     */
    private function getResourceAvailabilityData($date)
    {
        return $this->buildResourceAvailabilitySnapshot($date);
    }

    /**
     * Get resource availability for a specific date or date range
     */
    public function getResourceAvailability(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        return response()->json(
            array_merge(
                ['date' => Carbon::parse($date)->format('Y-m-d')],
                $this->buildResourceAvailabilitySnapshot($date)
            )
        );
    }

    /**
     * Build resource availability snapshot.
     */
    private function buildResourceAvailabilitySnapshot($date): array
    {
        $dateString = Carbon::parse($date)->format('Y-m-d');
        $activeStatuses = ['paid', 'confirmed', 'completed'];

        $activeBoatsQuery = $this->applyActiveScope(DB::table('boats'), 'boats');
        $activeHomestaysQuery = $this->applyActiveScope(DB::table('homestays'), 'homestays');
        $activeKiosksQuery = $this->applyActiveScope(DB::table('kiosks'), 'kiosks');
        $activeCulinariesQuery = $this->applyActiveScope(DB::table('culinaries'), 'culinaries');

        $totalBoats = (clone $activeBoatsQuery)->count();
        $totalHomestays = (clone $activeHomestaysQuery)->count();
        $totalKiosks = (clone $activeKiosksQuery)->count();
        $totalCulinaries = (clone $activeCulinariesQuery)->count();

        $totalBoatCapacity = (float) ((clone $activeBoatsQuery)->sum('kapasitas') ?? 0);
        $totalHomestayCapacity = (float) ((clone $activeHomestaysQuery)->sum('kapasitas') ?? 0);
        $totalKioskCapacity = (float) ((clone $activeKiosksQuery)->sum('kapasitas') ?? 0);

        // Culinaries store capacity at paket_culinaries level; use max package capacity per culinary.
        $culinaryCapacityMap = DB::table('paket_culinaries')
            ->select('id_culinary', DB::raw('MAX(kapasitas) as kapasitas'))
            ->groupBy('id_culinary')
            ->pluck('kapasitas', 'id_culinary');
        $totalCulinaryCapacity = (float) $culinaryCapacityMap->sum();

        $baseBookingQuery = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', $activeStatuses)
            ->where(function ($query) use ($dateString) {
                $query->whereRaw(
                    '? BETWEEN order_items.tanggal_keberangkatan AND DATE_ADD(order_items.tanggal_keberangkatan, INTERVAL (order_items.durasi_hari - 1) DAY)',
                    [$dateString]
                );
            });

        $bookedBoatIds = (clone $baseBookingQuery)
            ->join('paket_wisata_boat', 'order_items.id_paket', '=', 'paket_wisata_boat.id_paket')
            ->distinct()
            ->pluck('paket_wisata_boat.id_boat');

        $bookedHomestayIds = (clone $baseBookingQuery)
            ->join('paket_wisata_homestay', 'order_items.id_paket', '=', 'paket_wisata_homestay.id_paket')
            ->distinct()
            ->pluck('paket_wisata_homestay.id_homestay');

        $bookedCulinaryIds = (clone $baseBookingQuery)
            ->join('paket_wisata_culinary', 'order_items.id_paket', '=', 'paket_wisata_culinary.id_paket')
            ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
            ->distinct()
            ->pluck('paket_culinaries.id_culinary');

        $bookedKioskIds = (clone $baseBookingQuery)
            ->join('paket_wisata_kiosk', 'order_items.id_paket', '=', 'paket_wisata_kiosk.id_paket')
            ->distinct()
            ->pluck('paket_wisata_kiosk.id_kiosk');

        $bookedBoats = $bookedBoatIds->count();
        $bookedHomestays = $bookedHomestayIds->count();
        $bookedCulinaries = $bookedCulinaryIds->count();
        $bookedKiosks = $bookedKioskIds->count();

        $bookedBoatCapacity = (float) DB::table('boats')
            ->whereIn('id', $bookedBoatIds)
            ->sum('kapasitas');

        $bookedHomestayCapacity = (float) DB::table('homestays')
            ->whereIn('id_homestay', $bookedHomestayIds)
            ->sum('kapasitas');

        $bookedKioskCapacity = (float) DB::table('kiosks')
            ->whereIn('id_kiosk', $bookedKioskIds)
            ->sum('kapasitas');

        $bookedCulinaryCapacity = (float) $bookedCulinaryIds->sum(function ($id) use ($culinaryCapacityMap) {
            return (int) ($culinaryCapacityMap[$id] ?? 0);
        });

        $availableBoatsQuery = clone $activeBoatsQuery;
        if ($bookedBoatIds->isNotEmpty()) {
            $availableBoatsQuery->whereNotIn('id', $bookedBoatIds);
        }

        $availableHomestaysQuery = clone $activeHomestaysQuery;
        if ($bookedHomestayIds->isNotEmpty()) {
            $availableHomestaysQuery->whereNotIn('id_homestay', $bookedHomestayIds);
        }

        $availableKiosksQuery = clone $activeKiosksQuery;
        if ($bookedKioskIds->isNotEmpty()) {
            $availableKiosksQuery->whereNotIn('id_kiosk', $bookedKioskIds);
        }

        $availableCulinariesQuery = clone $activeCulinariesQuery;
        if ($bookedCulinaryIds->isNotEmpty()) {
            $availableCulinariesQuery->whereNotIn('id_culinary', $bookedCulinaryIds);
        }

        $availableBoats = $availableBoatsQuery
            ->select('id_boat', 'nama', 'kapasitas')
            ->orderBy('nama')
            ->get();

        $availableHomestays = $availableHomestaysQuery
            ->select('id_homestay', 'nama', 'kapasitas')
            ->orderBy('nama')
            ->get();

        $availableKiosks = $availableKiosksQuery
            ->select('id_kiosk', 'nama', 'kapasitas')
            ->orderBy('nama')
            ->get();

        $availableCulinaries = $availableCulinariesQuery
            ->select('id_culinary', 'nama', 'lokasi')
            ->orderBy('nama')
            ->get()
            ->map(function ($item) use ($culinaryCapacityMap) {
                $item->kapasitas = (int) ($culinaryCapacityMap[$item->id_culinary] ?? 0);
                return $item;
            });

        return [
            'boats' => $this->formatResourcePayload(
                $totalBoats,
                $bookedBoats,
                $totalBoatCapacity,
                $bookedBoatCapacity,
                $availableBoats
            ),
            'homestays' => $this->formatResourcePayload(
                $totalHomestays,
                $bookedHomestays,
                $totalHomestayCapacity,
                $bookedHomestayCapacity,
                $availableHomestays
            ),
            'culinaries' => $this->formatResourcePayload(
                $totalCulinaries,
                $bookedCulinaries,
                $totalCulinaryCapacity,
                $bookedCulinaryCapacity,
                $availableCulinaries
            ),
            'kiosks' => $this->formatResourcePayload(
                $totalKiosks,
                $bookedKiosks,
                $totalKioskCapacity,
                $bookedKioskCapacity,
                $availableKiosks
            ),
        ];
    }

    private function formatResourcePayload(
        int $total,
        int $booked,
        float $totalCapacity,
        float $bookedCapacity,
        $availableList
    ): array {
        $available = max($total - $booked, 0);
        $availableCapacity = max($totalCapacity - $bookedCapacity, 0);

        return [
            'total' => $total,
            'booked' => $booked,
            'available' => $available,
            'total_capacity' => (int) round($totalCapacity),
            'booked_capacity' => (int) round($bookedCapacity),
            'available_capacity' => (int) round($availableCapacity),
            'utilization_percent' => $total > 0 ? round(($booked / $total) * 100, 1) : 0,
            'available_list' => $availableList,
        ];
    }

    private function applyActiveScope($query, string $table)
    {
        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query;
    }

    private function getSingularResourceType(string $tableName): string
    {
        return [
            'boats' => 'boat',
            'homestays' => 'homestay',
            'culinaries' => 'culinary',
            'kiosks' => 'kiosk',
        ][$tableName] ?? rtrim($tableName, 's');
    }
}
