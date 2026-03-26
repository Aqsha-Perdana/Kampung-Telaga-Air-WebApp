  <!-- NEW: Row 2 - Enhanced Features (Upcoming Bookings + Revenue by Category + Attention Panel) -->
  <div class="row mt-4">
    <!-- Upcoming Bookings Widget -->
    <div class="col-lg-4">
      <div class="card-modern animate-fade-in-up h-100" style="animation-delay: 0.35s;">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">
              <i class="ti ti-calendar-event me-2 text-primary"></i>Upcoming Bookings
            </h6>
            <span class="badge bg-primary-subtle text-primary">Next 7 Days</span>
          </div>
          @if($upcomingBookings->count() > 0)
            <div class="list-group list-group-flush">
              @foreach($upcomingBookings->take(5) as $booking)
              <div class="list-group-item px-0 py-2 border-0 bg-transparent">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <p class="mb-0 fw-semibold text-dark">{{ $booking->customer_name }}</p>
                    <small class="text-muted">
                      {{ \Carbon\Carbon::parse($booking->tanggal_keberangkatan)->format('D, d M') }} 
                      • {{ $booking->jumlah_peserta }} pax
                    </small>
                  </div>
                  <span class="badge bg-info-subtle text-info">{{ Str::limit($booking->nama_paket, 15) }}</span>
                </div>
              </div>
              @endforeach
            </div>
            @if($upcomingBookings->count() > 5)
            <div class="text-center mt-2">
              <small class="text-muted">+{{ $upcomingBookings->count() - 5 }} more bookings</small>
            </div>
            @endif
          @else
            <div class="text-center py-4">
              <i class="ti ti-calendar-off text-muted" style="font-size: 2.5rem;"></i>
              <p class="text-muted mb-0 mt-2">No upcoming bookings</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Revenue by Category Donut -->
    <div class="col-lg-4">
      <div class="card-modern animate-fade-in-up h-100" style="animation-delay: 0.4s;">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">
              <i class="ti ti-chart-pie me-2 text-success"></i>Revenue by Category
            </h6>
          </div>
          <div id="revenueByCategoryChart"></div>
          <div class="row mt-3 g-2">
            @php
              $categoryLabels = ['Boats' => 'info', 'Homestays' => 'warning', 'Culinary' => 'success', 'Kiosk' => 'danger'];
              $categoryData = [
                'Boats' => $revenueByCategory['boats'] ?? 0,
                'Homestays' => $revenueByCategory['homestays'] ?? 0,
                'Culinary' => $revenueByCategory['culinary'] ?? 0,
                'Kiosk' => $revenueByCategory['kiosk'] ?? 0,
              ];
              $totalCatRevenue = array_sum($categoryData);
            @endphp
            @foreach($categoryLabels as $label => $color)
              <div class="col-6">
                <div class="d-flex align-items-center">
                  <span class="bg-{{ $color }} rounded-circle me-2" style="width: 10px; height: 10px;"></span>
                  <small class="text-muted">{{ $label }}</small>
                  <small class="ms-auto fw-semibold">{{ $totalCatRevenue > 0 ? number_format(($categoryData[$label] / $totalCatRevenue) * 100, 0) : 0 }}%</small>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <!-- Attention Panel -->
    <div class="col-lg-4">
      <div class="card-modern animate-fade-in-up h-100" style="animation-delay: 0.45s;">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">
              <i class="ti ti-alert-triangle me-2 text-warning"></i>Needs Attention
            </h6>
          </div>
          
          <!-- Stale Pending Orders -->
          @if($stalePendingCount > 0)
          <div class="alert alert-warning py-2 px-3 mb-2 d-flex align-items-center">
            <i class="ti ti-clock-hour-4 me-2"></i>
            <div>
              <strong>{{ $stalePendingCount }}</strong> pending order(s) > 3 days old
            </div>
          </div>
          @endif

          <!-- Never Sold Resources Summary -->
          <div class="list-group list-group-flush">
            @if($neverSoldPackagesCount > 0)
            <div class="list-group-item px-0 py-2 border-0 bg-transparent d-flex align-items-center">
              <span class="badge bg-danger me-2">{{ $neverSoldPackagesCount }}</span>
              <span class="text-muted">Packages never sold</span>
            </div>
            @endif
            @if($neverSoldBoatsCount > 0)
            <div class="list-group-item px-0 py-2 border-0 bg-transparent d-flex align-items-center">
              <span class="badge bg-danger me-2">{{ $neverSoldBoatsCount }}</span>
              <span class="text-muted">Boats never booked</span>
            </div>
            @endif
            @if($neverSoldHomestaysCount > 0)
            <div class="list-group-item px-0 py-2 border-0 bg-transparent d-flex align-items-center">
              <span class="badge bg-danger me-2">{{ $neverSoldHomestaysCount }}</span>
              <span class="text-muted">Homestays never booked</span>
            </div>
            @endif
          </div>

          @if($stalePendingCount == 0 && $neverSoldPackagesCount == 0 && $neverSoldBoatsCount == 0 && $neverSoldHomestaysCount == 0)
          <div class="text-center py-4">
            <i class="ti ti-circle-check text-success" style="font-size: 2.5rem;"></i>
            <p class="text-success mb-0 mt-2 fw-semibold">All Clear!</p>
            <small class="text-muted">No items need attention</small>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

