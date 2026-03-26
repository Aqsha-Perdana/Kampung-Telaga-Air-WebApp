  <!--  Row 5: Unsold Resources - Compact Accordion -->
  @php
    $totalUnsold = ($unsoldPackages->count() ?? 0) + ($unsoldBoats->count() ?? 0) + ($unsoldHomestays->count() ?? 0) + ($unsoldCulinary->count() ?? 0) + ($unsoldKiosks->count() ?? 0);
  @endphp
  @if($totalUnsold > 0)
  <div class="row mt-4">
    <div class="col-12">
      <div class="card-modern animate-fade-in-up" style="animation-delay: 0.65s;">
        <div class="card-body p-4">
          {{-- Header with summary badges --}}
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
              <i class="ti ti-alert-triangle text-danger me-2" style="font-size: 1.4rem;"></i>
              <h5 class="fw-bold mb-0 text-danger">Unsold Resources</h5>
              <span class="badge bg-danger ms-2">{{ $totalUnsold }} items</span>
            </div>
            <small class="text-muted"><i class="ti ti-bulb me-1"></i>Bundle unsold items with popular packages to improve distribution</small>
          </div>

          {{-- Summary Badge Row --}}
          <div class="d-flex flex-wrap gap-2 mb-3">
            @if($unsoldPackages->count() > 0)
              <span class="badge bg-primary-subtle text-primary px-3 py-2"><i class="ti ti-package me-1"></i>{{ $unsoldPackages->count() }} Packages</span>
            @endif
            @if($unsoldBoats->count() > 0)
              <span class="badge bg-info-subtle text-info px-3 py-2"><i class="ti ti-anchor me-1"></i>{{ $unsoldBoats->count() }} Boats</span>
            @endif
            @if($unsoldHomestays->count() > 0)
              <span class="badge bg-warning-subtle text-warning px-3 py-2"><i class="ti ti-home me-1"></i>{{ $unsoldHomestays->count() }} Homestays</span>
            @endif
            @if($unsoldCulinary->count() > 0)
              <span class="badge bg-success-subtle text-success px-3 py-2"><i class="ti ti-tools-kitchen-2 me-1"></i>{{ $unsoldCulinary->count() }} Culinary</span>
            @endif
            @if($unsoldKiosks->count() > 0)
              <span class="badge bg-danger-subtle text-danger px-3 py-2"><i class="ti ti-building-store me-1"></i>{{ $unsoldKiosks->count() }} Kiosks</span>
            @endif
          </div>

          {{-- Accordion --}}
          <div class="accordion accordion-flush unsold-accordion" id="unsoldAccordion">

            {{-- Unsold Packages --}}
            @if($unsoldPackages->count() > 0)
            <div class="accordion-item border rounded mb-2">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#unsoldPkgs">
                  <i class="ti ti-package me-2 text-primary"></i>
                  <strong>Tour Packages</strong>
                  <span class="badge bg-danger ms-2">{{ $unsoldPackages->count() }}</span>
                </button>
              </h2>
              <div id="unsoldPkgs" class="accordion-collapse collapse" data-bs-parent="#unsoldAccordion">
                <div class="accordion-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr><th>ID</th><th>Name</th><th>Duration</th><th class="text-end">Price</th><th class="text-center">Action</th></tr>
                      </thead>
                      <tbody>
                        @foreach($unsoldPackages as $pkg)
                        <tr>
                          <td><span class="badge bg-primary">{{ $pkg->id_paket }}</span></td>
                          <td class="fw-medium">{{ $pkg->nama_paket }}</td>
                          <td>{{ $pkg->durasi_hari }} days</td>
                          <td class="text-end">{{ format_ringgit($pkg->harga_final) }}</td>
                          <td class="text-center">
                            <a href="{{ url('/admin/paket-wisata/' . $pkg->id_paket . '/edit') }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                              <i class="ti ti-edit" style="font-size: .8rem;"></i> Edit
                            </a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endif

            {{-- Unsold Boats --}}
            @if($unsoldBoats->count() > 0)
            <div class="accordion-item border rounded mb-2">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#unsoldBoats">
                  <i class="ti ti-anchor me-2 text-info"></i>
                  <strong>Boats</strong>
                  <span class="badge bg-danger ms-2">{{ $unsoldBoats->count() }}</span>
                </button>
              </h2>
              <div id="unsoldBoats" class="accordion-collapse collapse" data-bs-parent="#unsoldAccordion">
                <div class="accordion-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr><th>ID</th><th>Name</th><th class="text-center">Capacity</th><th class="text-end">Price</th><th class="text-center">Action</th></tr>
                      </thead>
                      <tbody>
                        @foreach($unsoldBoats as $boat)
                        <tr>
                          <td><span class="badge bg-info">{{ $boat->id_boat }}</span></td>
                          <td class="fw-medium">{{ $boat->nama }}</td>
                          <td class="text-center">{{ $boat->kapasitas }}</td>
                          <td class="text-end">{{ format_ringgit($boat->harga_sewa) }}</td>
                          <td class="text-center">
                            <a href="{{ url('/admin/boats/' . $boat->id_boat . '/edit') }}" class="btn btn-sm btn-outline-info py-0 px-2">
                              <i class="ti ti-edit" style="font-size: .8rem;"></i> Edit
                            </a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endif

            {{-- Unsold Homestays --}}
            @if($unsoldHomestays->count() > 0)
            <div class="accordion-item border rounded mb-2">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#unsoldHomestays">
                  <i class="ti ti-home me-2 text-warning"></i>
                  <strong>Homestays</strong>
                  <span class="badge bg-danger ms-2">{{ $unsoldHomestays->count() }}</span>
                </button>
              </h2>
              <div id="unsoldHomestays" class="accordion-collapse collapse" data-bs-parent="#unsoldAccordion">
                <div class="accordion-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr><th>ID</th><th>Name</th><th class="text-center">Capacity</th><th class="text-end">Price/Night</th><th class="text-center">Action</th></tr>
                      </thead>
                      <tbody>
                        @foreach($unsoldHomestays as $homestay)
                        <tr>
                          <td><span class="badge bg-warning">{{ $homestay->id_homestay }}</span></td>
                          <td class="fw-medium">{{ $homestay->nama }}</td>
                          <td class="text-center">{{ $homestay->kapasitas }}</td>
                          <td class="text-end">{{ format_ringgit($homestay->harga_per_malam) }}</td>
                          <td class="text-center">
                            <a href="{{ url('/admin/homestays/' . $homestay->id_homestay . '/edit') }}" class="btn btn-sm btn-outline-warning py-0 px-2">
                              <i class="ti ti-edit" style="font-size: .8rem;"></i> Edit
                            </a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endif

            {{-- Unsold Culinary --}}
            @if($unsoldCulinary->count() > 0)
            <div class="accordion-item border rounded mb-2">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#unsoldCulinary">
                  <i class="ti ti-tools-kitchen-2 me-2 text-success"></i>
                  <strong>Culinary Packages</strong>
                  <span class="badge bg-danger ms-2">{{ $unsoldCulinary->count() }}</span>
                </button>
              </h2>
              <div id="unsoldCulinary" class="accordion-collapse collapse" data-bs-parent="#unsoldAccordion">
                <div class="accordion-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr><th>Place</th><th>Package</th><th class="text-end">Price</th><th class="text-center">Action</th></tr>
                      </thead>
                      <tbody>
                        @foreach($unsoldCulinary as $cul)
                        <tr>
                          <td class="fw-medium">{{ $cul->nama }}</td>
                          <td>{{ $cul->nama_paket }}</td>
                          <td class="text-end">{{ format_ringgit($cul->harga) }}</td>
                          <td class="text-center">
                            <a href="{{ url('/admin/culinaries/' . $cul->id_culinary . '/edit') }}" class="btn btn-sm btn-outline-success py-0 px-2">
                              <i class="ti ti-edit" style="font-size: .8rem;"></i> Edit
                            </a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endif

            {{-- Unsold Kiosks --}}
            @if($unsoldKiosks->count() > 0)
            <div class="accordion-item border rounded mb-2">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#unsoldKiosks">
                  <i class="ti ti-building-store me-2 text-danger"></i>
                  <strong>Kiosks</strong>
                  <span class="badge bg-danger ms-2">{{ $unsoldKiosks->count() }}</span>
                </button>
              </h2>
              <div id="unsoldKiosks" class="accordion-collapse collapse" data-bs-parent="#unsoldAccordion">
                <div class="accordion-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr><th>ID</th><th>Name</th><th class="text-end">Price</th><th class="text-center">Action</th></tr>
                      </thead>
                      <tbody>
                        @foreach($unsoldKiosks as $kiosk)
                        <tr>
                          <td><span class="badge bg-danger">{{ $kiosk->id_kiosk }}</span></td>
                          <td class="fw-medium">{{ $kiosk->nama }}</td>
                          <td class="text-end">{{ format_ringgit($kiosk->harga_per_paket) }}</td>
                          <td class="text-center">
                            <a href="{{ url('/admin/kiosks/' . $kiosk->id_kiosk . '/edit') }}" class="btn btn-sm btn-outline-danger py-0 px-2">
                              <i class="ti ti-edit" style="font-size: .8rem;"></i> Edit
                            </a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endif

          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
