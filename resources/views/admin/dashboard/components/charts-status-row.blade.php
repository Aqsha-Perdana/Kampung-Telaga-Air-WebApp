  <!--  Row 4: Charts & Performance (Moved UP for less scrolling) -->
  <div class="row mt-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
      <div class="card-modern animate-fade-in-up h-100" style="animation-delay: 0.55s;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="ti ti-chart-line me-2 text-primary"></i>Revenue Trend (Last 12 Months)
            </h5>
            <span class="badge bg-primary-subtle text-primary">Monthly</span>
          </div>
          {{-- Inline KPI Summary Chips --}}
          @php
            $totalRevenue12 = $revenueTrend->sum('revenue');
            $bestMonth = $revenueTrend->sortByDesc('revenue')->first();
            $worstMonth = $revenueTrend->where('revenue', '>', 0)->sortBy('revenue')->first();
            $avgMonthly = $revenueTrend->count() > 0 ? $totalRevenue12 / $revenueTrend->count() : 0;
          @endphp
          <div class="d-flex flex-wrap gap-2 mb-3">
            <div class="d-inline-flex align-items-center bg-primary bg-opacity-10 rounded-pill px-3 py-1">
              <i class="ti ti-sum me-1 text-primary" style="font-size: .85rem;"></i>
              <small class="fw-semibold text-primary">Total: {{ format_ringgit($totalRevenue12) }}</small>
            </div>
            <div class="d-inline-flex align-items-center bg-success bg-opacity-10 rounded-pill px-3 py-1">
              <i class="ti ti-arrow-up me-1 text-success" style="font-size: .85rem;"></i>
              <small class="fw-semibold text-success">Best: {{ $bestMonth->month ?? '-' }} ({{ format_ringgit($bestMonth->revenue ?? 0) }})</small>
            </div>
            <div class="d-inline-flex align-items-center bg-warning bg-opacity-10 rounded-pill px-3 py-1">
              <i class="ti ti-trending-up me-1 text-warning" style="font-size: .85rem;"></i>
              <small class="fw-semibold text-warning">Avg: {{ format_ringgit($avgMonthly) }}/mo</small>
            </div>
          </div>
          <div id="revenueChart"></div>
        </div>
      </div>
    </div>
    
    <!-- Order Status Distribution -->
    <div class="col-lg-4">
      <div class="card-modern animate-fade-in-up h-100" style="animation-delay: 0.6s;">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-3 text-dark">
            <i class="ti ti-chart-donut-3 me-2 text-info"></i>Order Status
          </h5>
          {{-- Total Orders Big Number --}}
          @php
            $totalOrdersAll = $ordersByStatus->sum('count');
            $totalRevenueByStatus = [];
            foreach($ordersByStatus as $st) {
              $totalRevenueByStatus[$st->status] = \DB::table('orders')->where('status', $st->status)->sum('total_amount');
            }
          @endphp
          <div class="text-center mb-2">
            <h2 class="fw-bolder mb-0 text-dark">{{ number_format($totalOrdersAll) }}</h2>
            <small class="text-muted">Total Orders</small>
          </div>
          <div id="distributionChart"></div>
          <div class="mt-3">
            @foreach($ordersByStatus as $status)
            @php
              $pct = $totalOrdersAll > 0 ? round(($status->count / $totalOrdersAll) * 100, 1) : 0;
              $statusColor = match($status->status) {
                'paid', 'completed' => 'success',
                'pending', 'refund_requested' => 'warning',
                'cancelled', 'failed' => 'danger',
                'refunded' => 'primary',
                'confirmed' => 'info',
                default => 'secondary',
              };
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-2 py-1 px-2 rounded" style="background: rgba(0,0,0,.02);">
              <div class="d-flex align-items-center">
                <span class="bg-{{ $statusColor }} rounded-circle me-2" style="width: 8px; height: 8px; display: inline-block;"></span>
                <span class="text-capitalize fw-medium small">{{ $status->status }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">{{ format_ringgit($totalRevenueByStatus[$status->status] ?? 0) }}</span>
                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} fw-semibold">
                  {{ $status->count }} ({{ $pct }}%)
                </span>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
