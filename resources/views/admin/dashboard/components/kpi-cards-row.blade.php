  <!--  Row 1: KPI Cards -->
  <div class="row">
    <!-- Total Revenue Card -->
    <div class="col-lg-3 col-md-6">
      <div class="card-modern animate-fade-in-up">
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="flex-shrink-0">
              <div class="icon-box-modern bg-gradient-primary-soft">
                <i class="ti ti-currency-dollar"></i>
              </div>
            </div>
            <div class="ms-3">
              <h6 class="stat-label">Total Revenue</h6>
            </div>
          </div>
          <div>
            <h3 class="stat-value">{{ format_ringgit($totalRevenue ?? 0) }}</h3>
            <div class="d-flex align-items-center mt-2">
              <span class="badge {{ ($revenueGrowth ?? 0) >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-modern me-2">
                {{ ($revenueGrowth ?? 0) >= 0 ? '↑' : '↓' }} {{ number_format(abs($revenueGrowth ?? 0), 1) }}%
              </span>
              <span class="text-muted small">vs last month</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Active Tour Packages Card -->
  <div class="col-lg-3 col-md-6">
      <div class="card-modern animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="flex-shrink-0">
              <div class="icon-box-modern bg-gradient-success-soft">
                <i class="ti ti-package"></i>
              </div>
            </div>
            <div class="ms-3">
              <h6 class="stat-label">Active Packages</h6>
            </div>
          </div>
          <div>
            <h3 class="stat-value">{{ $resourceMetrics['packages']['total'] }}</h3>
            <div class="d-flex align-items-center mt-2">
              <span class="badge bg-success-subtle text-success badge-modern me-2">
                {{ $resourceMetrics['packages']['distribution_score'] }}% Dist
              </span>
              <span class="text-muted small">
                {{ $resourceMetrics['packages']['sold'] }} Sold / {{ $resourceMetrics['packages']['unsold'] }} Avail
              </span>
            </div>
          </div>
        </div>
      </div>
  </div>

  <!-- Total Participants Card -->
  <div class="col-lg-3 col-md-6">
      <div class="card-modern animate-fade-in-up" style="animation-delay: 0.2s;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="flex-shrink-0">
              <div class="icon-box-modern bg-gradient-warning-soft">
                <i class="ti ti-users"></i>
              </div>
            </div>
            <div class="ms-3">
              <h6 class="stat-label">Total Participants</h6>
            </div>
          </div>
          <div>
            @php
              // Fallback Calculation to match original logic exactly
              $totalParticipants = $packageSales->sum('total_participants');
              $thisMonthParticipants = DB::table('order_items')
                ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
                ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
                ->where('orders.created_at', '>=', Carbon\Carbon::now()->startOfMonth())
                ->sum('order_items.jumlah_peserta');
            @endphp
            <h3 class="stat-value">{{ number_format($totalParticipants) }}</h3>
            <div class="d-flex align-items-center mt-2">
              <span class="badge bg-warning-subtle text-warning badge-modern me-2">
                All Time
              </span>
              <span class="text-muted small">+{{ number_format($thisMonthParticipants) }} this month</span>
            </div>
          </div>
        </div>
      </div>
  </div>

  <!-- Average Order Value Card -->
  <div class="col-lg-3 col-md-6">
      <div class="card-modern animate-fade-in-up" style="animation-delay: 0.3s;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="flex-shrink-0">
              <div class="icon-box-modern bg-gradient-danger-soft">
                <i class="ti ti-receipt"></i>
              </div>
            </div>
            <div class="ms-3">
              <h6 class="stat-label">Avg Order Value</h6>
            </div>
          </div>
          <div>
            @php
              $paidOrders = DB::table('orders')->where('status', 'paid')->count();
              $avgOrderValue = $paidOrders > 0 ? $totalRevenue / $paidOrders : 0;
              $thisMonthOrders = DB::table('orders')
                ->where('status', 'paid')
                ->where('created_at', '>=', Carbon\Carbon::now()->startOfMonth())
                ->count();
              $avgThisMonth = $thisMonthOrders > 0 ? $revenueThisMonth / $thisMonthOrders : 0;
            @endphp
            <h3 class="stat-value">{{ format_ringgit($avgOrderValue) }}</h3>
            <div class="d-flex align-items-center mt-2">
              <span class="badge {{ ($avgOrderValueThisMonth ?? 0) >= ($avgOrderValue ?? 0) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-modern me-2">
                {{ ($avgOrderValueThisMonth ?? 0) >= ($avgOrderValue ?? 0) ? '↑' : '↓' }} 
                {{ ($avgOrderValue ?? 0) > 0 ? number_format(((($avgOrderValueThisMonth ?? 0) - ($avgOrderValue ?? 0)) / ($avgOrderValue ?? 0)) * 100, 1) : '0' }}%
              </span>
              <span class="text-muted small">trend</span>
            </div>
          </div>
        </div>
      </div>
  </div>
