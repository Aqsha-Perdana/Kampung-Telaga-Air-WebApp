  {{-- Pass data to JS --}}
  <script>
    window.revenueTrendData = {
        months: @json($revenueTrend->pluck('month')),
        revenue: @json($revenueTrend->pluck('revenue'))
    };
    window.ordersByStatusData = {
        labels: @json($ordersByStatus->pluck('status')),
        counts: @json($ordersByStatus->pluck('count'))
    };
    window.revenueByCategoryData = {
        labels: ['Boats', 'Homestays', 'Culinary', 'Kiosk'],
        values: [
            {{ $revenueByCategory['boats'] ?? 0 }},
            {{ $revenueByCategory['homestays'] ?? 0 }},
            {{ $revenueByCategory['culinary'] ?? 0 }},
            {{ $revenueByCategory['kiosk'] ?? 0 }}
        ]
    };
  </script>
  @vite('resources/js/dashboard.js')
