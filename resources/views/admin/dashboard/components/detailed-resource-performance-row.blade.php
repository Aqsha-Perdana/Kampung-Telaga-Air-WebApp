

  <!--  Row 3: Detailed Resource Sales Tables with Tabs -->
  <div class="row mt-4">
    <div class="col-12">
        <div class="card-modern animate-fade-in-up" style="animation-delay: 0.5s;">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-4 text-dark">Detailed Resource Sales Performance</h5>
          <p class="text-muted small mb-3">
            Revenue excludes refunded orders. Refunded transactions are shown as additional info per row.
          </p>
          
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
                  <table class="table table-modern align-middle">
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
                      <td class="text-end fw-bold">
                        {{ format_ringgit($package->total_revenue) }}
                        @if(($package->refunded_orders ?? 0) > 0)
                          <div class="small text-danger mt-1">
                            Refunded {{ (int) $package->refunded_orders }} order(s):
                            -{{ format_ringgit((float) ($package->refunded_revenue ?? 0)) }}
                          </div>
                        @endif
                      </td>
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
                <table class="table table-modern align-middle">
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
                      <td class="text-end fw-bold">
                        {{ format_ringgit($boat->total_revenue) }}
                        @if(($boat->refunded_orders ?? 0) > 0)
                          <div class="small text-danger mt-1">
                            Refunded {{ (int) $boat->refunded_orders }} order(s):
                            -{{ format_ringgit((float) ($boat->refunded_revenue ?? 0)) }}
                          </div>
                        @endif
                      </td>
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
                <table class="table table-modern align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>ID</th>
                      <th>Homestay Name</th>
                      <th class="text-center">Capacity</th>
                      <th class="text-end">Price/Night</th>
                      <th class="text-center">Sales Count</th>
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
                      <td class="text-end fw-bold">
                        {{ format_ringgit($homestay->total_revenue) }}
                        @if(($homestay->refunded_orders ?? 0) > 0)
                          <div class="small text-danger mt-1">
                            Refunded {{ (int) $homestay->refunded_orders }} order(s):
                            -{{ format_ringgit((float) ($homestay->refunded_revenue ?? 0)) }}
                          </div>
                        @endif
                      </td>
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
                      <td class="text-end fw-bold">
                        {{ format_ringgit($culinary->total_revenue) }}
                        @if(($culinary->refunded_orders ?? 0) > 0)
                          <div class="small text-danger mt-1">
                            Refunded {{ (int) $culinary->refunded_orders }} order(s):
                            -{{ format_ringgit((float) ($culinary->refunded_revenue ?? 0)) }}
                          </div>
                        @endif
                      </td>
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
                      <td class="text-end fw-bold">
                        {{ format_ringgit($kiosk->total_revenue) }}
                        @if(($kiosk->refunded_orders ?? 0) > 0)
                          <div class="small text-danger mt-1">
                            Refunded {{ (int) $kiosk->refunded_orders }} order(s):
                            -{{ format_ringgit((float) ($kiosk->refunded_revenue ?? 0)) }}
                          </div>
                        @endif
                      </td>
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
