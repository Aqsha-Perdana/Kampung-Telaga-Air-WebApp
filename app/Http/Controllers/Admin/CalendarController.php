<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
}