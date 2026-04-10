<section class="py-5 checkout-shell" style="margin-top: 80px;">
    <div class="container">
        @php
            $originalTotal = $cartItems->sum(function ($item) {
                return (float) ($item->paket->harga_jual ?? $item->harga_satuan ?? 0);
            });
            $discountTotal = max(0, $originalTotal - (float) $total);
            $hasDiscountedItems = $discountTotal > 0.00001;
        @endphp
        <div class="checkout-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <p class="checkout-kicker mb-2">Secure booking</p>
                <h2 class="mb-2">Checkout</h2>
                <p class="text-muted mb-0">Review your booking details, choose a payment method, and complete your order in MYR.</p>
            </div>
            <div class="checkout-account text-muted small">
                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 checkout-panel mb-4">
                    <div class="card-body p-4">
                        <form id="payment-form">
                            @csrf
                            <div id="checkout-feedback" class="alert alert-danger d-none mb-4" role="alert"></div>

                            <div class="checkout-section mb-4">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <h5 class="checkout-section-title mb-1">Contact details</h5>
                                        <p class="text-muted small mb-0">We will use this information for confirmation and follow-up.</p>
                                    </div>
                                    <span class="checkout-section-step">1</span>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text"
                                               class="form-control"
                                               name="customer_name"
                                               id="customer_name"
                                               value="{{ $user->name }}"
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email"
                                               class="form-control"
                                               name="customer_email"
                                               id="customer_email"
                                               value="{{ $user->email }}"
                                               required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel"
                                               class="form-control"
                                               name="customer_phone"
                                               id="customer_phone"
                                               value="{{ $user->phone ?? '' }}"
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Display Currency</label>
                                        <select class="form-select" name="display_currency" id="display_currency">
                                            <option value="MYR" selected>MYR - Ringgit Malaysia</option>
                                            <option value="USD">USD - US Dollar</option>
                                            <option value="IDR">IDR - Indonesian Rupiah</option>
                                            <option value="SGD">SGD - Singapore Dollar</option>
                                            <option value="EUR">EUR - Euro</option>
                                            <option value="GBP">GBP - British Pound</option>
                                            <option value="AUD">AUD - Australian Dollar</option>
                                            <option value="JPY">JPY - Japanese Yen</option>
                                            <option value="CNY">CNY - Chinese Yuan</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">
                                            Payment will still be charged in <strong>MYR</strong>.
                                            <span id="exchange-rate-info" class="d-none">
                                                <br>Approx: <span id="rate-display"></span>
                                            </span>
                                        </small>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control"
                                              name="customer_address"
                                              id="customer_address"
                                              rows="3">{{ $user->address ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="checkout-divider"></div>

                            <div class="checkout-section">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <h5 class="checkout-section-title mb-1">Payment method</h5>
                                        <p class="text-muted small mb-0">Choose the payment option that feels most comfortable for you.</p>
                                    </div>
                                    <span class="checkout-section-step">2</span>
                                </div>

                                <div class="payment-methods mb-4">
                                    <div class="form-check payment-method-card mb-3">
                                        <input class="form-check-input payment-method-input" type="radio" name="payment_method" id="stripe" value="stripe" {{ config('payment.default', 'stripe') === 'stripe' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="stripe">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div class="d-flex gap-3">
                                                    <div class="payment-method-icon payment-method-icon--stripe">
                                                        <i class="bi bi-credit-card"></i>
                                                    </div>
                                                    <div>
                                                        <strong class="d-block">{{ payment_method_label('stripe') }}</strong>
                                                        <p class="mb-1 small text-muted">Pay directly with your debit or credit card.</p>
                                                        <small class="text-muted">Visa, Mastercard, Amex</small>
                                                    </div>
                                                </div>
                                                <span class="payment-method-tag">Card payment</span>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="form-check payment-method-card mb-0">
                                        <input class="form-check-input payment-method-input" type="radio" name="payment_method" id="xendit" value="xendit" {{ config('payment.default', 'stripe') === 'xendit' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="xendit">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div class="d-flex gap-3">
                                                    <div class="payment-method-icon payment-method-icon--xendit">
                                                        <i class="bi bi-wallet2"></i>
                                                    </div>
                                                    <div>
                                                        <strong class="d-block">{{ payment_method_label('xendit') }}</strong>
                                                        <p class="mb-1 small text-muted">Continue to Xendit to choose your payment channel.</p>
                                                        <small class="text-muted">GrabPay, FPX, DuitNow, eWallet and more</small>
                                                    </div>
                                                </div>
                                                <span class="payment-method-tag">Hosted checkout</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div id="stripe-section" class="payment-section">
                                    <div class="checkout-muted-box">
                                        <h6 class="mb-3">Card Information</h6>
                                        <div id="card-element" class="form-control mb-2" style="height: 40px; padding-top: 10px;"></div>
                                        <div id="card-errors" class="text-danger mt-2"></div>
                                    </div>
                                </div>

                                <div id="xendit-section" class="payment-section d-none">
                                    <div class="checkout-muted-box">
                                        <h6 class="mb-2">Xendit checkout</h6>
                                        <p class="text-muted mb-2">You will be redirected securely to Xendit to complete your payment.</p>
                                        <small class="text-muted d-block">Available channels may include GrabPay, FPX, DuitNow, direct debit, and other supported methods in your sandbox account.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" id="submit-button" class="btn btn-success btn-lg checkout-submit-button">
                                    <span id="button-text">
                                        <i class="bi bi-lock-fill"></i> Pay
                                        <span id="pay-amount-myr" class="fw-bold">{{ format_ringgit($total) }}</span>
                                        <span id="pay-amount-display" class="text-white-50 small d-none"></span>
                                    </span>
                                    <span id="spinner" class="spinner-border spinner-border-sm d-none"></span>
                                </button>
                            </div>

                            <div class="checkout-trust-note mt-3">
                                <small class="text-muted d-flex align-items-start gap-2 mb-0">
                                    <i class="bi bi-shield-check text-success mt-1"></i>
                                    <span>Your payment is encrypted and processed securely. Final charges will be in MYR.</span>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 checkout-panel checkout-summary sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <h5 class="mb-1">Order Summary</h5>
                                <p class="text-muted small mb-0">{{ $cartItems->count() }} booking{{ $cartItems->count() > 1 ? 's' : '' }} ready for payment</p>
                            </div>
                            <span class="checkout-summary-badge">MYR</span>
                        </div>

                        @foreach($cartItems as $item)
                        <div class="checkout-summary-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $item->paket->nama_paket }}</h6>
                                    @if((float) ($item->paket->harga_jual ?? $item->harga_satuan ?? 0) > (float) ($item->paket->harga_final ?? $item->harga_satuan ?? 0))
                                        <small class="checkout-original-price d-block">{{ format_ringgit($item->paket->harga_jual) }}</small>
                                    @endif
                                    <small class="text-primary d-block">{{ format_ringgit($item->paket->harga_final) }} per package</small>
                                </div>
                                <div class="text-end">
                                    @if((float) ($item->paket->harga_jual ?? $item->harga_satuan ?? 0) > (float) ($item->subtotal ?? 0))
                                        <div class="checkout-original-price">{{ format_ringgit($item->paket->harga_jual) }}</div>
                                    @endif
                                    <strong class="d-block item-price-myr" data-myr="{{ $item->subtotal }}">
                                        {{ format_ringgit($item->subtotal) }}
                                    </strong>
                                    <small class="text-muted item-price-display d-none"></small>
                                </div>
                            </div>
                            <div class="checkout-summary-meta">
                                <span><i class="bi bi-people"></i> {{ $item->jumlah_peserta }} participants</span>
                                <span><i class="bi bi-calendar"></i> {{ $item->tanggal_keberangkatan->format('d M Y') }}</span>
                                <span><i class="bi bi-clock"></i> {{ $item->paket->durasi_hari }} days</span>
                            </div>
                        </div>
                        @endforeach

                        <div class="checkout-total-box mb-3">
                            @if($hasDiscountedItems)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Original Price</span>
                                    <span class="checkout-original-price">{{ format_ringgit($originalTotal) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Discount</span>
                                    <strong class="text-success">-{{ format_ringgit($discountTotal) }}</strong>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal ({{ $cartItems->count() }} package booking{{ $cartItems->count() > 1 ? 's' : '' }})</span>
                                <strong id="subtotal-myr">{{ format_ringgit($total) }}</strong>
                            </div>
                            <div id="subtotal-display" class="d-flex justify-content-between text-muted small d-none">
                                <span>Display Amount</span>
                                <span id="subtotal-display-amount"></span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Total Payment (MYR)</h5>
                                <h4 class="text-primary mb-0" id="total-myr">{{ format_ringgit($total) }}</h4>
                            </div>
                            <div id="total-display" class="text-end text-muted small mt-1 d-none">
                                Approx <span id="total-display-amount"></span>
                            </div>
                        </div>

                        <div class="checkout-summary-note">
                            <small class="text-muted d-block">
                                <i class="bi bi-info-circle"></i>
                                You will receive confirmation after payment is completed successfully.
                            </small>
                        </div>

                        <input type="hidden" id="base-total" value="{{ $total }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
