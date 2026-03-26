<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-credit-card"></i> Checkout</h2>
            <div class="text-muted small">
                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-4">
                            <i class="bi bi-person-fill"></i> Order Information
                        </h5>

                        <form id="payment-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-person"></i> Full Name
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           name="customer_name"
                                           id="customer_name"
                                           value="{{ $user->name }}"
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-envelope"></i> Email
                                    </label>
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
                                    <label class="form-label">
                                        <i class="bi bi-phone"></i> Number Phone
                                    </label>
                                    <input type="tel"
                                           class="form-control"
                                           name="customer_phone"
                                           id="customer_phone"
                                           value="{{ $user->phone ?? '' }}"
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-eye"></i> Display Currency
                                    </label>
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
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Payment will be charged in MYR</strong>
                                        <span id="exchange-rate-info" class="d-none">
                                            <br>Approx: <span id="rate-display"></span>
                                        </span>
                                    </small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt"></i> Address
                                </label>
                                <textarea class="form-control"
                                          name="customer_address"
                                          id="customer_address"
                                          rows="3">{{ $user->address ?? '' }}</textarea>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">
                                <i class="bi bi-wallet2"></i> Payment Methods
                            </h5>

                            <div class="payment-methods mb-4">
                                <div class="form-check payment-method-card mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe" checked>
                                    <label class="form-check-label w-100" for="stripe">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="bi bi-credit-card text-primary"></i>
                                                <strong>Credit/Debit Card</strong>
                                                <p class="mb-0 small text-muted">Visa, Mastercard, Amex</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="stripe-section" class="payment-section">
                                <h6 class="mb-3">Card Information</h6>
                                <div id="card-element" class="form-control mb-2" style="height: 40px; padding-top: 10px;"></div>
                                <div id="card-errors" class="text-danger mt-2"></div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" id="submit-button" class="btn btn-success btn-lg">
                                    <span id="button-text">
                                        <i class="bi bi-lock-fill"></i> Pay
                                        <span id="pay-amount-myr" class="fw-bold">{{ format_ringgit($total) }}</span>
                                        <span id="pay-amount-display" class="text-white-50 small d-none"></span>
                                    </span>
                                    <span id="spinner" class="spinner-border spinner-border-sm d-none"></span>
                                </button>
                            </div>

                            <div class="alert alert-warning mt-3 mb-0">
                                <small>
                                    <i class="bi bi-shield-check"></i>
                                    Your payment is secure and encrypted. All charges will be in MYR.
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <h5 class="mb-4">
                            <i class="bi bi-receipt"></i> Order Summary
                        </h5>

                        @foreach($cartItems as $item)
                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="mb-1">{{ $item->paket->nama_paket }}</h6>
                            <small class="text-primary d-block mb-1">{{ format_ringgit($item->paket->harga_final) }} per package</small>
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-people"></i> {{ $item->jumlah_peserta }} participants | {{ $item->paket->participant_range_label }}
                                <br><i class="bi bi-calendar"></i> {{ $item->tanggal_keberangkatan->format('d M Y') }}
                            </small>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ $item->paket->durasi_hari }} Days - Flat package price</span>
                                <div class="text-end">
                                    <strong class="d-block item-price-myr" data-myr="{{ $item->subtotal }}">
                                        {{ format_ringgit($item->subtotal) }}
                                    </strong>
                                    <small class="text-muted item-price-display d-none"></small>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="bg-light p-3 rounded mb-3">
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

                        <input type="hidden" id="base-total" value="{{ $total }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
