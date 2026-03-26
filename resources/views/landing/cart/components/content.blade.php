<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <!-- Header with User Info -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-cart3"></i> Shopping Cart</h2>
                @auth
                    <small class="text-muted">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </small>
                @endauth
            </div>
            @if(!$cartItems->isEmpty())
                <div class="text-end">
                    <span class="badge bg-primary fs-6">{{ $cartItems->count() }} Item(s)</span>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($cartItems->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <h4 class="mt-3">Your cart is empty</h4>
                <p class="text-muted">No tour packages have been added yet.</p>
                <a href="{{ route('landing.paket-wisata') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-compass"></i> Browse Travel Packages
                </a>
            </div>
        @else
            <div class="row">
                <div class="col-lg-8">
                    @foreach($cartItems as $item)
                    <div class="card mb-3 shadow-sm hover-lift">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    @if($item->paket->destinasis->first() && $item->paket->destinasis->first()->fotos->count() > 0)
                                        <img src="{{ asset('storage/'.$item->paket->destinasis->first()->fotos->first()->foto) }}"
                                             class="img-fluid rounded"
                                             alt="{{ $item->paket->nama_paket }}"
                                             style="object-fit: cover; height: 120px; width: 100%;"
                                             loading="lazy">
                                    @else
                                        <img src="https://via.placeholder.com/200x150?text={{ urlencode($item->paket->nama_paket) }}"
                                             class="img-fluid rounded"
                                             alt="{{ $item->paket->nama_paket }}"
                                             loading="lazy">
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-2">{{ $item->paket->nama_paket }}</h5>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <span class="badge bg-info">
                                            <i class="bi bi-calendar3"></i> {{ $item->paket->durasi_hari }} Days
                                        </span>
                                        <span class="text-muted small">
                                            {{ format_ringgit($item->harga_satuan) }} per package
                                        </span>
                                    </div>
                                    <p class="mb-1">
                                        <i class="bi bi-people-fill text-primary"></i>
                                        <strong>{{ $item->jumlah_peserta }}</strong> participants
                                    </p>
                                    <p class="mb-1 small text-muted">
                                        <i class="bi bi-shield-check"></i>
                                        {{ $item->paket->participant_range_label }}
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-calendar-event text-primary"></i>
                                        <strong>{{ $item->tanggal_keberangkatan->format('d M Y') }}</strong>
                                    </p>
                                    @if($item->catatan)
                                        <p class="mb-0 small text-muted">
                                            <i class="bi bi-chat-left-text"></i>
                                            {{ Str::limit($item->catatan, 50) }}
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-3 text-end">
                                    <h5 class="text-primary mb-3">{{ format_ringgit($item->subtotal) }}</h5>
                                    <div class="btn-group-vertical w-100" role="group">
                                        <button class="btn btn-sm btn-outline-primary mb-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal{{ $item->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="btn-group-vertical w-100">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger w-100 "
                                                    onclick="return confirm('Remove this item from the cart?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="bi bi-pencil-square"></i> Edit Item Cart
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('cart.update', $item->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <strong>{{ $item->paket->nama_paket }}</strong>
                                            <br><small>{{ format_ringgit($item->harga_satuan) }} per package</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-people-fill"></i> Number of Participants
                                            </label>
                                            <input type="number"
                                                   class="form-control"
                                                   name="jumlah_peserta"
                                                   value="{{ $item->jumlah_peserta }}"
                                                   min="{{ max((int) ($item->paket->minimum_participants ?? 1), 1) }}"
                                                   @if($item->paket->maximum_participants) max="{{ $item->paket->maximum_participants }}" @endif
                                                   required>
                                            <small class="text-muted">{{ $item->paket->participant_range_label }}. Participant count does not change the package price.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-calendar-event"></i> Departure Date
                                            </label>
                                            <input type="date"
                                                   class="form-control"
                                                   name="tanggal_keberangkatan"
                                                   value="{{ $item->tanggal_keberangkatan->format('Y-m-d') }}"
                                                   min="{{ date('Y-m-d', strtotime('+3 days')) }}"
                                                   required>
                                            <small class="text-muted">At least 3 days from today</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-chat-left-text"></i> Notes (Optional)
                                            </label>
                                            <textarea class="form-control"
                                                      name="catatan"
                                                      rows="3"
                                                      maxlength="500"
                                                      placeholder="Special requests, food allergies, etc...">{{ $item->catatan }}</textarea>
                                            <small class="text-muted">Maximum 500 characters</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-body">
                            <h5 class="mb-4">
                                <i class="bi bi-receipt"></i> Order Summary
                            </h5>

                            <div class="mb-3">
                                @foreach($cartItems as $item)
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">
                                        {{ Str::limit($item->paket->nama_paket, 25) }}
                                        <br>
                                        <small class="text-primary">{{ $item->jumlah_peserta }} participants - {{ format_ringgit($item->harga_satuan) }} per package</small>
                                    </span>
                                    <strong>{{ format_ringgit($item->subtotal) }}</strong>
                                </div>
                                @endforeach
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal ({{ $cartItems->count() }} package booking{{ $cartItems->count() > 1 ? 's' : '' }})</span>
                                <strong>{{ format_ringgit($total) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span><i class="bi bi-tag-fill"></i> Discount</span>
                                <strong>RM 0</strong>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between mb-4">
                                <h5 class="mb-0">Total</h5>
                                <h4 class="text-primary mb-0">{{ format_ringgit($total) }}</h4>
                            </div>

                            @auth
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 btn-lg mb-2">
                                    <i class="bi bi-credit-card"></i> Proceed to Checkout
                                </a>
                            @else
                                <a href="{{ route('wisatawan.login') }}" class="btn btn-primary w-100 btn-lg mb-2">
                                    <i class="bi bi-lock-fill"></i> Login to Checkout
                                </a>
                            @endauth

                            <a href="{{ route('landing.paket-wisata') }}" class="btn btn-outline-secondary w-100 mb-3">
                                <i class="bi bi-arrow-left"></i> Continue Shopping
                            </a>

                            <form action="{{ route('cart.clear') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger w-100"
                                        onclick="return confirm('Remove all items from your cart?')">
                                    <i class="bi bi-trash"></i> Empty Cart
                                </button>
                            </form>

                            <!-- Trust Badges -->
                            <div class="border-top mt-4 pt-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-shield-check text-success"></i> Secure Payment
                                </small>
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-arrow-clockwise text-info"></i> Flexible Cancellation
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-headset text-primary"></i> 24/7 Customer Support
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
