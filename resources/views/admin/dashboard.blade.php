  @extends('layout.sidebar')

  @section('content')

  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4764e7ff 0%, #7cb1e7ff 100%); border-radius: 15px;">
        <div class="card-body py-4 px-4">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <h3 class="mb-2 fw-bold text-white">
                <i class="ti ti-sparkles me-2"></i>
                Halo, Welcome to Dashboard Monitoring Kampung Telaga Air
              </h3>
              <p class="mb-0 text-white-50 fs-4">
                <i class="ti ti-calendar me-1"></i>
                {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
              </p>
            </div>
            <div class="d-none d-md-block">
              <div class="round-80 d-flex align-items-center justify-content-center rounded-circle" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);">
                <i class="ti ti-user-circle text-white" style="font-size: 3rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!--  Row 1: KPI Cards -->
  <div class="row">
    <!-- Total Revenue Card -->
    <div class="col-lg-3 col-md-6">
      <div class="card overflow-hidden">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="round-40 d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle">
                <i class="ti ti-currency-dollar text-primary fs-7"></i>
              </div>
            </div>
            <div class="ms-3">
              <h6 class="mb-0 fw-semibold">Total Revenue</h6>
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-between mt-4">
            <div>
              <h3 class="mb-1 fw-semibold fs-7">{{ format_ringgit($totalRevenue) }}</h3>
              <p class="mb-0 text-muted">This Month: {{ format_ringgit($revenueThisMonth) }}</p>
              <span class="badge {{ $revenueGrowth >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-2 mt-1">
                {{ $revenueGrowth >= 0 ? '+' : '' }}{{ format_ringgit($revenueGrowth) }}%
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Active Tour Packages Card -->
  <div class="col-lg-3 col-md-6">
    <div class="card overflow-hidden">
      <div class="card-body p-4">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <div class="round-40 d-flex align-items-center justify-content-center rounded-circle bg-success-subtle">
              <i class="ti ti-package text-success fs-7"></i>
            </div>
          </div>
          <div class="ms-3">
            <h6 class="mb-0 fw-semibold">Active Packages</h6>
          </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mt-4">
          <div>
            <h3 class="mb-1 fw-semibold fs-7">{{ $resourceMetrics['packages']['total'] }}</h3>
            <p class="mb-0 text-muted">Sold: {{ $resourceMetrics['packages']['sold'] }} | Unsold: {{ $resourceMetrics['packages']['unsold'] }}</p>
            <span class="badge bg-success-subtle text-success fs-2 mt-1">
              {{ $resourceMetrics['packages']['distribution_score'] }}% Distribution
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Participants Card -->
  <div class="col-lg-3 col-md-6">
    <div class="card overflow-hidden">
      <div class="card-body p-4">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <div class="round-40 d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle">
              <i class="ti ti-users text-warning fs-7"></i>
            </div>
          </div>
          <div class="ms-3">
            <h6 class="mb-0 fw-semibold">Total Participants</h6>
          </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mt-4">
          <div>
            @php
              $totalParticipants = $packageSales->sum('total_participants');
              $thisMonthParticipants = DB::table('order_items')
                ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
                ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
                ->where('orders.created_at', '>=', Carbon\Carbon::now()->startOfMonth())
                ->sum('order_items.jumlah_peserta');
            @endphp
            <h3 class="mb-1 fw-semibold fs-7">{{ number_format($totalParticipants) }}</h3>
            <p class="mb-0 text-muted">This Month: {{ number_format($thisMonthParticipants) }}</p>
            <span class="badge bg-warning-subtle text-warning fs-2 mt-1">
              All Time
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Average Order Value Card -->
  <div class="col-lg-3 col-md-6">
    <div class="card overflow-hidden">
      <div class="card-body p-4">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <div class="round-40 d-flex align-items-center justify-content-center rounded-circle bg-danger-subtle">
              <i class="ti ti-receipt text-danger fs-7"></i>
            </div>
          </div>
          <div class="ms-3">
            <h6 class="mb-0 fw-semibold">Avg Order Value</h6>
          </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mt-4">
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
            <h3 class="mb-1 fw-semibold fs-7">{{ format_ringgit($avgOrderValue) }}</h3>
            <p class="mb-0 text-muted">This Month: {{ format_ringgit($avgThisMonth) }}</p>
            <span class="badge {{ $avgThisMonth >= $avgOrderValue ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-2 mt-1">
              {{ $avgThisMonth >= $avgOrderValue ? '↑' : '↓' }} 
              {{ $avgOrderValue > 0 ? number_format((($avgThisMonth - $avgOrderValue) / $avgOrderValue) * 100, 1) : '0' }}%
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--  Row 6: Recent Orders & Top Packages -->
  <div class="row mt-4">
    <!-- Recent Orders -->
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title fw-semibold mb-0">Recent Orders</h5>
            <span class="badge bg-light text-dark">
              Showing {{ $recentOrders->firstItem() ?? 0 }} - {{ $recentOrders->lastItem() ?? 0 }} of {{ $recentOrders->total() }}
            </span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Package(s)</th>
                  <th class="text-center">Participants</th>
                  <th class="text-end">Amount</th>
                  <th class="text-center">Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentOrders as $order)
                <tr>
                  <td><span class="badge bg-light text-dark">{{ $order->id_order }}</span></td>
                  <td class="fw-semibold">{{ $order->customer_name }}</td>
                  <td>
                    @if($order->packages)
                      @php
                        $packageList = explode(', ', $order->packages);
                        $displayPackages = array_slice($packageList, 0, 2);
                        $remainingCount = count($packageList) - 2;
                      @endphp
                      
                      @foreach($displayPackages as $package)
                        <span class="badge bg-primary-subtle text-primary me-1 mb-1">{{ $package }}</span>
                      @endforeach
                      
                      @if($remainingCount > 0)
                        <span class="badge bg-secondary-subtle text-secondary">+{{ $remainingCount }} more</span>
                      @endif
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <span class="badge bg-info-subtle text-info">
                      {{ DB::table('order_items')->where('id_order', $order->id_order)->sum('jumlah_peserta') }}
                    </span>
                  </td>
                  <td class="text-end fw-semibold">{{ format_ringgit($order->total_amount) }}</td>
                  <td class="text-center">
                    <span class="badge 
                      @if($order->status == 'paid') bg-success
                      @elseif($order->status == 'pending') bg-warning
                      @elseif($order->status == 'cancelled') bg-danger
                      @else bg-info
                      @endif">
                      {{ ucfirst($order->status) }}
                    </span>
                  </td>
                  <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">No orders yet</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          @if($recentOrders->hasPages())
          <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <div class="text-muted small">
              Showing {{ $recentOrders->firstItem() }} to {{ $recentOrders->lastItem() }} of {{ $recentOrders->total() }} entries
            </div>
            <div>
              {{ $recentOrders->links('pagination::bootstrap-5') }}
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>


  <!--  Row 3: Detailed Resource Sales Tables with Tabs -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title fw-semibold mb-4">Detailed Resource Sales Performance</h5>
          
          <ul class="nav nav-pills mb-3" id="resourceTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="packages-tab" data-bs-toggle="pill" data-bs-target="#packages" type="button">
                  <i class="ti ti-package me-1"></i> Tour Packages ({{ $packageSales->count() }})
              </button>
              </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="boats-tab" data-bs-toggle="pill" data-bs-target="#boats" type="button">
                <i class="ti ti-anchor me-1"></i> Boats ({{ $boatSales->count() }})
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="homestays-tab" data-bs-toggle="pill" data-bs-target="#homestays" type="button">
                <i class="ti ti-home me-1"></i> Homestays ({{ $homestaySales->count() }})
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="culinary-tab" data-bs-toggle="pill" data-bs-target="#culinary" type="button">
                <i class="ti ti-tools-kitchen-2 me-1"></i> Culinary ({{ $culinarySales->count() }})
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="kiosks-tab" data-bs-toggle="pill" data-bs-target="#kiosks" type="button">
                <i class="ti ti-building-store me-1"></i> Kiosks ({{ $kioskSales->count() }})
              </button>
            </li>
          </ul>
          
          <div class="tab-content" id="resourceTabContent">

          <!-- TOUR PACKAGES TAB -->
              <div class="tab-pane fade show active" id="packages" role="tabpanel">
              <div class="table-responsive">
                  <table class="table table-hover table-bordered align-middle">
                  <thead class="table-light">
                      <tr>
                      <th>ID</th>
                      <th>Package Name</th>
                      <th class="text-center">Duration</th>
                      <th class="text-end">Price</th>
                      <th class="text-center">Sales Count</th>
                      <th class="text-end">Participants</th>
                      <th class="text-end">Revenue</th>
                      <th class="text-center">Status</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($packageSales as $package)
                      <tr class="{{ $package->total_sales == 0 ? 'table-danger' : '' }}">
                      <td><span class="badge bg-primary">{{ $package->id_paket }}</span></td>
                      <td class="fw-semibold">{{ $package->nama_paket }}</td>
                      <td class="text-center">{{ $package->durasi_hari }} days</td>
                      <td class="text-end">{{ format_ringgit($package->harga_final) }}</td>
                      <td class="text-center">
                          <span class="badge {{ $package->total_sales > 0 ? 'bg-success' : 'bg-danger' }}">
                          {{ $package->total_sales }}
                          </span>
                      </td>
                      <td class="text-center">{{ $package->total_participants }}</td>
                      <td class="text-end fw-bold">{{ format_ringgit($package->total_revenue) }}</td>
                      <td class="text-center">
                          @if($package->total_sales == 0)
                          <span class="badge bg-danger">Never Sold</span>
                          @elseif($package->total_sales < $resourceMetrics['packages']['avg_sales'])
                          <span class="badge bg-warning">Low Performance</span>
                          @else
                          <span class="badge bg-success">Good Performance</span>
                          @endif
                      </td>
                      </tr>
                      @empty
                      <tr><td colspan="9" class="text-center text-muted">No package data available</td></tr>
                      @endforelse
                  </tbody>
                  </table>
              </div>
              </div>
            <!-- BOATS TAB -->
            <div class="tab-pane fade" id="boats" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>ID</th>
                      <th>Boat Name</th>
                      <th class="text-center">Capacity</th>
                      <th class="text-end">Price/Day</th>
                      <th class="text-center">Sales Count</th>
                      <th class="text-center">Participants</th>
                      <th class="text-end">Boats Revenue</th>
                      <th class="text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($boatSales as $boat)
                    <tr class="{{ $boat->total_sales == 0 ? 'table-danger' : '' }}">
                      <td><span class="badge bg-primary">{{ $boat->id_boat }}</span></td>
                      <td class="fw-semibold">{{ $boat->nama }}</td>
                      <td class="text-center">{{ $boat->kapasitas }}</td>
                      <td class="text-end"> {{ format_ringgit($boat->harga_sewa) }}</td>
                      <td class="text-center">
                        <span class="badge {{ $boat->total_sales > 0 ? 'bg-success' : 'bg-danger' }}">
                          {{ $boat->total_sales }}
                        </span>
                      </td>
                      <td class="text-center">{{ $boat->total_participants }}</td>
                      <td class="text-end fw-bold">{{ format_ringgit($boat->total_revenue) }}</td>
                      <td class="text-center">
                        @if($boat->total_sales == 0)
                          <span class="badge bg-danger">Never Sold</span>
                        @elseif($boat->total_sales < $resourceMetrics['boats']['avg_sales'])
                          <span class="badge bg-warning">Low Performance</span>
                        @else
                          <span class="badge bg-success">Good Performance</span>
                        @endif
                      </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">No boat data available</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- HOMESTAYS TAB -->
            <div class="tab-pane fade" id="homestays" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>ID</th>
                      <th>Homestay Name</th>
                      <th class="text-center">Capacity</th>
                      <th class="text-end">Price/Night</th>
                      <th class="text-center">Sales Count</th>>
                      <th class="text-end">Homestays Revenue</th>
                      <th class="text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($homestaySales as $homestay)
                    <tr class="{{ $homestay->total_sales == 0 ? 'table-danger' : '' }}">
                      <td><span class="badge bg-success">{{ $homestay->id_homestay }}</span></td>
                      <td class="fw-semibold">{{ $homestay->nama }}</td>
                      <td class="text-center">{{ $homestay->kapasitas }}</td>
                      <td class="text-end"> {{ format_ringgit($homestay->harga_per_malam) }}</td>
                      <td class="text-center">
                        <span class="badge {{ $homestay->total_sales > 0 ? 'bg-success' : 'bg-danger' }}">
                          {{ $homestay->total_sales }}
                        </span>
                      </td>
                      <td class="text-end fw-bold"> {{ format_ringgit($homestay->total_revenue) }}</td>
                      <td class="text-center">
                        @if($homestay->total_sales == 0)
                          <span class="badge bg-danger">Never Sold</span>
                        @elseif($homestay->total_sales < $resourceMetrics['homestays']['avg_sales'])
                          <span class="badge bg-warning">Low Performance</span>
                        @else
                          <span class="badge bg-success">Good Performance</span>
                        @endif
                      </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">No homestay data available</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- CULINARY TAB -->
            <div class="tab-pane fade" id="culinary" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Culinary Place</th>
                      <th>Package Name</th>
                      <th class="text-center">Capacity</th>
                      <th class="text-end">Price</th>
                      <th class="text-center">Sales Count</th>
                      <th class="text-center">Participants</th>
                      <th class="text-end">Culinaries Revenue</th>
                      <th class="text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($culinarySales as $culinary)
                    <tr class="{{ $culinary->total_sales == 0 ? 'table-danger' : '' }}">
                      <td><span class="badge bg-warning">{{ $culinary->id_culinary }}</span> {{ $culinary->nama }}</td>
                      <td class="fw-semibold">{{ $culinary->nama_paket }}</td>
                      <td class="text-center">{{ $culinary->kapasitas }}</td>
                      <td class="text-end"> {{ format_ringgit($culinary->harga) }}</td>
                      <td class="text-center">
                        <span class="badge {{ $culinary->total_sales > 0 ? 'bg-success' : 'bg-danger' }}">
                          {{ $culinary->total_sales }}
                        </span>
                      </td>
                      <td class="text-center">{{ $culinary->total_participants }}</td>
                      <td class="text-end fw-bold"> {{ format_ringgit($culinary->total_revenue) }}</td>
                      <td class="text-center">
                        @if($culinary->total_sales == 0)
                          <span class="badge bg-danger">Never Sold</span>
                        @elseif($culinary->total_sales < $resourceMetrics['culinary']['avg_sales'])
                          <span class="badge bg-warning">Low Performance</span>
                        @else
                          <span class="badge bg-success">Good Performance</span>
                        @endif
                      </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">No culinary data available</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- KIOSKS TAB -->
            <div class="tab-pane fade" id="kiosks" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>ID</th>
                      <th>Kiosk Name</th>
                      <th class="text-center">Capacity</th>
                      <th class="text-end">Price/Package</th>
                      <th class="text-center">Sales Count</th>
                      <th class="text-center">Participants</th>
                      <th class="text-end">Kiosks Revenue</th>
                      <th class="text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($kioskSales as $kiosk)
                    <tr class="{{ $kiosk->total_sales == 0 ? 'table-danger' : '' }}">
                      <td><span class="badge bg-info">{{ $kiosk->id_kiosk }}</span></td>
                      <td class="fw-semibold">{{ $kiosk->nama }}</td>
                      <td class="text-center">{{ $kiosk->kapasitas }}</td>
                      <td class="text-end"> {{ format_ringgit($kiosk->harga_per_paket) }}</td>
                      <td class="text-center">
                        <span class="badge {{ $kiosk->total_sales > 0 ? 'bg-success' : 'bg-danger' }}">
                          {{ $kiosk->total_sales }}
                        </span>
                      </td>
                      <td class="text-center">{{ $kiosk->total_participants }}</td>
                      <td class="text-end fw-bold"> {{ format_ringgit($kiosk->total_revenue) }}</td>
                      <td class="text-center">
                        @if($kiosk->total_sales == 0)
                          <span class="badge bg-danger">Never Sold</span>
                        @elseif($kiosk->total_sales < $resourceMetrics['kiosks']['avg_sales'])
                          <span class="badge bg-warning">Low Performance</span>
                        @else
                          <span class="badge bg-success">Good Performance</span>
                        @endif
                      </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">No kiosk data available</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--  Row 4: Recommendations for Unsold Resources -->
  @if($unsoldBoats->count() > 0 || $unsoldHomestays->count() > 0 || $unsoldCulinary->count() > 0 || $unsoldKiosks->count() > 0)
  <div class="row mt-4">
    <div class="col-12">
      <div class="card border-danger">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <i class="ti ti-alert-triangle text-danger fs-7 me-2"></i>
            <h5 class="card-title fw-semibold mb-0 text-danger">⚠️ Unsold Resources - Action Required!</h5>
          </div>
          <p class="text-muted mb-4">The following resources have never been sold. Consider including them in new tour packages to improve resource distribution.</p>
          
          <div class="row">
            @if($unsoldBoats->count() > 0)
            <div class="col-lg-6 mb-3">
              <div class="alert alert-danger">
                <h6 class="alert-heading fw-semibold"><i class="ti ti-anchor me-1"></i> Unsold Boats ({{ $unsoldBoats->count() }})</h6>
                <ul class="mb-0">
                  @foreach($unsoldBoats as $boat)
                  <li><strong>{{ $boat->id_boat }}</strong> - {{ $boat->nama }} (Capacity: {{ $boat->kapasitas }}, Price:  {{ format_ringgit($boat->harga_sewa) }})</li>
                  @endforeach
                </ul>
              </div>
            </div>
            @endif
            @if($unsoldPackages->count() > 0)

            <div class="col-lg-6 mb-3">
              <div class="alert alert-danger">
                <h6 class="alert-heading fw-semibold"><i class="ti ti-package me-1"></i> Unsold Tour Packages ({{ $unsoldPackages->count() }})</h6>
                <ul class="mb-0">
                  @foreach($unsoldPackages as $package)
                  <li><strong>{{ $package->id_paket }}</strong> - {{ $package->nama_paket }} ({{ $package->durasi_hari }} days, Price: {{ format_ringgit($package->harga_final) }})</li>
                  @endforeach
                </ul>
              </div>
            </div>
            @endif
            
            @if($unsoldHomestays->count() > 0)
            <div class="col-lg-6 mb-3">
              <div class="alert alert-danger">
                <h6 class="alert-heading fw-semibold"><i class="ti ti-home me-1"></i> Unsold Homestays ({{ $unsoldHomestays->count() }})</h6>
                <ul class="mb-0">
                  @foreach($unsoldHomestays as $homestay)
                  <li><strong>{{ $homestay->id_homestay }}</strong> - {{ $homestay->nama }} (Capacity: {{ $homestay->kapasitas }}, Price:  {{ format_ringgit($homestay->harga_per_malam) }}/night)</li>
                  @endforeach
                </ul>
              </div>
            </div>
            @endif
            
            @if($unsoldCulinary->count() > 0)
            <div class="col-lg-6 mb-3">
              <div class="alert alert-danger">
                <h6 class="alert-heading fw-semibold"><i class="ti ti-tools-kitchen-2 me-1"></i> Unsold Culinary Packages ({{ $unsoldCulinary->count() }})</h6>
                <ul class="mb-0">
                  @foreach($unsoldCulinary as $culinary)
                  <li><strong>{{ $culinary->nama }}</strong> - {{ $culinary->nama_paket }} (Price:  {{ format_ringgit($culinary->harga) }})</li>
                  @endforeach
                </ul>
              </div>
            </div>
            @endif
            
            @if($unsoldKiosks->count() > 0)
            <div class="col-lg-6 mb-3">
              <div class="alert alert-danger">
                <h6 class="alert-heading fw-semibold"><i class="ti ti-building-store me-1"></i> Unsold Kiosks ({{ $unsoldKiosks->count() }})</h6>
                <ul class="mb-0">
                  @foreach($unsoldKiosks as $kiosk)
                  <li><strong>{{ $kiosk->id_kiosk }}</strong> - {{ $kiosk->nama }} (Price:  {{ format_ringgit($kiosk->harga_per_paket) }})</li>
                  @endforeach
                </ul>
              </div>
            </div>
            @endif
          </div>
          
          <div class="alert alert-info mt-3">
            <h6 class="fw-semibold"><i class="ti ti-bulb me-1"></i> Recommendations:</h6>
            <ul class="mb-0">
              <li>Create promotional packages featuring these resources</li>
              <li>Offer discounts for packages including unsold items</li>
              <li>Bundle with popular resources to increase visibility</li>
              <li>Review pricing strategy for underperforming items</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!--  Row 5: Charts & Performance -->
  <div class="row mt-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
            <div class="mb-3 mb-sm-0">
              <h5 class="card-title fw-semibold">Revenue Trend (Last 12 Months)</h5>
            </div>
          </div>
          <div id="revenueChart"></div>
        </div>
      </div>
    </div>
    
    <!-- Order Status Distribution -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title fw-semibold mb-4">Order Status Distribution</h5>
          <div id="distributionChart"></div>
          <div class="mt-4">
            @foreach($ordersByStatus as $status)
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-capitalize">{{ $status->status }}</span>
              <span class="badge bg-light-{{ $status->status == 'paid' ? 'success' : ($status->status == 'pending' ? 'warning' : 'info') }} text-{{ $status->status == 'paid' ? 'success' : ($status->status == 'pending' ? 'warning' : 'info') }}">
                {{ $status->count }}
              </span>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

  @endsection

  @push('scripts')
  <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Revenue Trend Chart
    var revenueOptions = {
      series: [{
        name: "Revenue",
        data: @json($revenueTrend->pluck('revenue'))
      }],
      chart: {
        type: 'area',
        height: 350,
        fontFamily: 'inherit',
        foreColor: '#adb0bb',
        toolbar: { show: false }
      },
      colors: ['#5D87FF'],
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          opacityTo: 0.1,
        }
      },
      xaxis: {
        categories: @json($revenueTrend->pluck('month')),
        labels: {
          formatter: function(val) {
            return val;
          }
        }
      },
      yaxis: {
        labels: {
          formatter: function(val) {
            return 'RM ' + val.toFixed(0);
          }
        }
      },
      tooltip: {
        theme: 'dark',
        y: {
          formatter: function(val) {
            return 'RM ' + val.toFixed(2);
          }
        }
      }
    };
    
    var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
    revenueChart.render();
    
    // Order Status Distribution Chart
    // Mapping warna berdasarkan status
  const statusColors = {
      'paid': '#13DEB9',    // Hijau
      'pending': '#FFAE1F', // Kuning/Orange
      'cancelled': '#FA896B' // Merah
  };

  // Ambil data label dari Laravel
  var labels = @json($ordersByStatus->pluck('status'));

  // Buat array warna yang urutannya sesuai dengan label yang muncul
  var chartColors = labels.map(status => statusColors[status.toLowerCase()] || '#adb0bb');

  var distributionOptions = {
      series: @json($ordersByStatus->pluck('count')),
      chart: {
          type: 'donut',
          height: 250,
          fontFamily: 'inherit',
          foreColor: '#adb0bb'
      },
      labels: labels.map(s => s.charAt(0).toUpperCase() + s.slice(1)), // Capitalize
      colors: chartColors, // Menggunakan array warna yang sudah dipetakan
      plotOptions: {
          pie: {
              donut: {
                  size: '70%',
                  labels: {
                      show: true,
                      name: { show: true },
                      value: { show: true }
                  }
              }
          }
      },
      legend: { show: false }
  };

  var distributionChart = new ApexCharts(document.querySelector("#distributionChart"), distributionOptions);
  distributionChart.render();
  });
  </script>
  @endpush

  @section('styles')
  <style>
  .round-40 {
    width: 40px;
    height: 40px;
  }

  .timeline-badge {
    width: 10px;
    height: 10px;
    border-radius: 50%;
  }

  .timeline-item:not(:last-child) .timeline-badge-wrap::after {
    content: '';
    position: absolute;
    width: 2px;
    height: 100%;
    background: #e9ecef;
    left: 4px;
    top: 10px;
  }

  .nav-pills .nav-link {
    border-radius: 0.25rem;
    margin-right: 0.5rem;
  }

  .nav-pills .nav-link.active {
    background-color: #5D87FF;
  }

  .table-danger {
    background-color: rgba(250, 137, 107, 0.1);
  }

  /* Custom Pagination Styling */
  .pagination {
    margin-bottom: 0;
  }

  .page-link {
    color: #5D87FF;
    border-color: #dee2e6;
  }

  .page-link:hover {
    color: #4556d6;
    background-color: #e9ecef;
    border-color: #dee2e6;
  }

  .page-item.active .page-link {
    background-color: #5D87FF;
    border-color: #5D87FF;
  }

  .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
  }
  </style>
  @endsection