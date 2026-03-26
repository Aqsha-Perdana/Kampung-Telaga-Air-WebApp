  <div class="row mb-4 align-items-stretch">
    <!-- Welcome Header -->
    <div class="col-lg-8 mb-3 mb-lg-0">
      <div class="card shadow-sm border-0 overflow-hidden h-100" style="background: linear-gradient(135deg, #5D87FF 0%, #5D87FF 100%); border-radius: 16px;">
        <div class="card-body p-4 position-relative">
          <div class="d-flex align-items-center justify-content-between position-relative" style="z-index: 2;">
            <div>
              <h4 class="mb-1 fw-bold text-white">
                Welcome, Admin! 👋
              </h4>
              <p class="mb-0 text-white small opacity-75">
                Dashboard Monitoring Kampung Telaga Air.
              </p>
            </div>
            <div class="d-none d-md-flex align-items-center bg-white bg-opacity-25 rounded-pill px-3 py-2 border border-white border-opacity-25">
                <i class="ti ti-calendar me-2 text-white"></i>
                <span class="text-white fw-bold small">{{ \Carbon\Carbon::now()->isoFormat('D MMM YYYY') }}</span>
            </div>
          </div>

          <div class="mt-4 pt-3 border-top border-white border-opacity-25 position-relative" style="z-index: 2;">
            <div class="row g-2">
              <div class="col-4">
                <div class="rounded-3 p-2 bg-white bg-opacity-10 border border-white border-opacity-10">
                  <div class="text-white-50 small" style="font-size: 0.72rem;">Total Orders</div>
                  <div class="text-white fw-bold">{{ number_format($totalOrders ?? 0) }}</div>
                </div>
              </div>
              <div class="col-4">
                <div class="rounded-3 p-2 bg-white bg-opacity-10 border border-white border-opacity-10">
                  <div class="text-white-50 small" style="font-size: 0.72rem;">Pending</div>
                  <div class="text-white fw-bold">{{ number_format($pendingPayments ?? 0) }}</div>
                </div>
              </div>
              <div class="col-4">
                <div class="rounded-3 p-2 bg-white bg-opacity-10 border border-white border-opacity-10">
                  <div class="text-white-50 small" style="font-size: 0.72rem;">Revenue Month</div>
                  <div class="text-white fw-bold">{{ format_ringgit($revenueThisMonth ?? 0) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Decorative abstract shape -->
          <div class="position-absolute end-0 top-0 h-100 d-none d-md-block" style="width: 150px; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1)); clip-path: polygon(20% 0%, 100% 0, 100% 100%, 0% 100%);"></div>
        </div>
      </div>
    </div>

    <!-- Compact Recent Orders -->
    <div class="col-lg-4">
      <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-history me-2 text-primary"></i>Recent Orders</h6>
            <a href="{{ route('sales.index') }}" class="text-decoration-none small fw-bold">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($recentOrders->take(3) as $order)
                <div class="list-group-item px-3 py-2 border-0 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.65rem;">#{{ $order->id_order }}</span>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($order->created_at)->diffForHumans(null, true, true) }} ago</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            @php
                                $statusColor = match($order->status) {
                                    'paid', 'completed' => 'success',
                                    'pending', 'refund_requested' => 'warning',
                                    'cancelled', 'failed' => 'danger',
                                    'refunded' => 'primary',
                                    'confirmed' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="bg-{{ $statusColor }} rounded-circle me-2" style="width: 8px; height: 8px;"></span>
                            <div>
                                <p class="mb-0 fw-semibold text-dark small text-truncate" style="max-width: 100px;">{{ $order->customer_name }}</p>
                            </div>
                        </div>
                        <span class="fw-bold text-dark small">{{ format_ringgit($order->total_amount) }}</span>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted small">No recent orders</div>
                @endforelse
            </div>
        </div>
      </div>
    </div>
  </div>
