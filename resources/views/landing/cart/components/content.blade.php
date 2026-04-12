<section class="py-5 cart-shell" style="margin-top: 80px;">
    <div class="container">
        @php
            $originalTotal = $cartItems->sum(function ($item) {
                return (float) ($item->paket->harga_jual ?? $item->harga_satuan ?? 0);
            });
            $discountTotal = max(0, $originalTotal - (float) $total);
            $hasDiscountedItems = $discountTotal > 0.00001;
        @endphp
        <div class="cart-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <p class="cart-kicker mb-2">Review your booking</p>
                <h2 class="mb-2">Shopping Cart</h2>
                <p class="text-muted mb-0">Check your selected packages, update any details if needed, then continue to checkout.</p>
            </div>

            <div class="cart-account d-flex align-items-center gap-3">
                @auth
                    <div class="text-muted small">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </div>
                @endauth

                @if(!$cartItems->isEmpty())
                    <span class="cart-count-badge">{{ $cartItems->count() }} item{{ $cartItems->count() > 1 ? 's' : '' }}</span>
                @endif
            </div>
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
            <div class="card border-0 shadow-sm cart-empty-card">
                <div class="card-body text-center py-5">
                    <div class="cart-empty-icon mb-3">
                        <i class="bi bi-cart-x"></i>
                    </div>
                    <h4 class="mt-2">Your cart is empty</h4>
                    <p class="text-muted mb-4">You have not added any travel packages yet.</p>
                    <a href="{{ route('landing.paket-wisata') }}" class="btn btn-primary px-4">
                        <i class="bi bi-compass"></i> Browse Travel Packages
                    </a>
                </div>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="cart-list d-grid gap-3">
                        @foreach($cartItems as $item)
                        @php
                            $packagePhoto = $item->paket->foto_thumbnail ?? null;
                            $destinationPhoto = optional(optional($item->paket->destinasis->first())->fotos->first())->foto;
                            $imageUrl = $packagePhoto
                                ? Storage::url($packagePhoto)
                                : ($destinationPhoto
                                    ? Storage::url($destinationPhoto)
                                    : 'https://via.placeholder.com/200x150?text=' . urlencode($item->paket->nama_paket));
                        @endphp
                        <div class="card border-0 shadow-sm hover-lift cart-item-card">
                            <div class="card-body p-4">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-3">
                                        <div class="cart-item-image">
                                            <img src="{{ $imageUrl }}"
                                                 class="img-fluid rounded-4"
                                                 alt="{{ $item->paket->nama_paket }}"
                                                 style="object-fit: cover; height: 140px; width: 100%;"
                                                 loading="lazy">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                            <span class="cart-item-badge">
                                                <i class="bi bi-calendar3"></i> {{ $item->paket->durasi_hari }} Days
                                            </span>
                                            <span class="text-muted small">{{ format_ringgit($item->harga_satuan) }} per package</span>
                                        </div>

                                        <h5 class="mb-2">{{ $item->paket->nama_paket }}</h5>

                                        <div class="cart-item-meta">
                                            <span><i class="bi bi-people-fill"></i> {{ $item->jumlah_peserta }} participants</span>
                                            <span><i class="bi bi-shield-check"></i> {{ $item->paket->participant_range_label }}</span>
                                            <span><i class="bi bi-calendar-event"></i> {{ $item->tanggal_keberangkatan->format('d M Y') }}</span>
                                        </div>

                                        @if($item->catatan)
                                            <div class="cart-item-note mt-3">
                                                <small class="text-muted d-block mb-1">Notes</small>
                                                <p class="mb-0 small text-muted">{{ Str::limit($item->catatan, 90) }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-3">
                                        <div class="cart-item-side text-md-end">
                                            @php
                                                $originalPrice = (float) ($item->paket->harga_jual ?? $item->harga_satuan ?? 0);
                                                $finalPrice = (float) ($item->subtotal ?? 0);
                                                $itemHasDiscount = $originalPrice > $finalPrice;
                                            @endphp
                                            @if($itemHasDiscount)
                                                <div class="cart-original-price mb-1">{{ format_ringgit($originalPrice) }}</div>
                                            @endif
                                            <h5 class="text-primary mb-3">{{ format_ringgit($finalPrice) }}</h5>

                                            <div class="d-grid gap-2">
                                                <button class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal{{ $item->id }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>

                                                <form action="{{ route('cart.remove', $item->id) }}"
                                                      method="POST"
                                                      class="cart-delete-form"
                                                      data-item-label="{{ $item->paket->nama_paket }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger w-100"
                                                            onclick="return confirm('Remove this item from the cart?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-pencil-square"></i> Edit Cart Item
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
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top cart-summary-card" style="top: 100px;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <h5 class="mb-1">Order Summary</h5>
                                    <p class="text-muted small mb-0">{{ $cartItems->count() }} package booking{{ $cartItems->count() > 1 ? 's' : '' }}</p>
                                </div>
                                <span class="cart-count-badge">{{ $cartItems->count() }}</span>
                            </div>

                            <div class="cart-summary-list mb-3">
                                @foreach($cartItems as $item)
                                <div class="cart-summary-line">
                                    <div>
                                        <div class="fw-semibold">{{ Str::limit($item->paket->nama_paket, 28) }}</div>
                                        <small class="text-muted">{{ $item->jumlah_peserta }} participants • {{ $item->tanggal_keberangkatan->format('d M Y') }}</small>
                                    </div>
                                    <div class="text-end">
                                        @if((float) ($item->paket->harga_jual ?? $item->harga_satuan ?? 0) > (float) ($item->subtotal ?? 0))
                                            <div class="cart-original-price">{{ format_ringgit($item->paket->harga_jual) }}</div>
                                        @endif
                                        <strong class="text-primary">{{ format_ringgit($item->subtotal) }}</strong>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="cart-total-box mb-4">
                                @if($hasDiscountedItems)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Original Price</span>
                                        <span class="cart-original-price">{{ format_ringgit($originalTotal) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Discount</span>
                                        <strong class="text-success">-{{ format_ringgit($discountTotal) }}</strong>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <strong>{{ format_ringgit($total) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <h5 class="mb-0">Total</h5>
                                    <h4 class="text-primary mb-0">{{ format_ringgit($total) }}</h4>
                                </div>
                            </div>

                            @auth
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 btn-lg mb-2 cart-primary-action">
                                    <i class="bi bi-credit-card"></i> Proceed to Checkout
                                </a>
                            @else
                                <a href="{{ route('wisatawan.login') }}" class="btn btn-primary w-100 btn-lg mb-2 cart-primary-action">
                                    <i class="bi bi-lock-fill"></i> Login to Checkout
                                </a>
                            @endauth

                            <a href="{{ route('landing.paket-wisata') }}" class="btn btn-outline-secondary w-100 mb-3">
                                <i class="bi bi-arrow-left"></i> Continue Shopping
                            </a>

                            <form action="{{ route('cart.clear') }}"
                                  method="POST"
                                  class="cart-clear-form"
                                  data-item-count="{{ $cartItems->count() }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger w-100"
                                        onclick="return confirm('Remove all items from your cart?')">
                                    <i class="bi bi-trash"></i> Empty Cart
                                </button>
                            </form>

                            <div class="cart-summary-note mt-4 pt-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-info-circle"></i> You can still review your booking details before payment.
                                </small>
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-shield-check text-success"></i> Payment is completed on the next step.
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-headset text-primary"></i> Need help? You can update or remove items anytime before checkout.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
