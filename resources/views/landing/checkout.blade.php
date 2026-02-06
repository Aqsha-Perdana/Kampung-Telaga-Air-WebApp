@extends('landing.layout')

@section('content')
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
                                        <option value="MYR" selected>🇲🇾 MYR - Ringgit Malaysia</option>
                                        <option value="USD">🇺🇸 USD - US Dollar</option>
                                        <option value="IDR">🇮🇩 IDR - Indonesian Rupiah</option>
                                        <option value="SGD">🇸🇬 SGD - Singapore Dollar</option>
                                        <option value="EUR">🇪🇺 EUR - Euro</option>
                                        <option value="GBP">🇬🇧 GBP - British Pound</option>
                                        <option value="AUD">🇦🇺 AUD - Australian Dollar</option>
                                        <option value="JPY">🇯🇵 JPY - Japanese Yen</option>
                                        <option value="CNY">🇨🇳 CNY - Chinese Yuan</option>
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

                            <!-- Payment Method Selection -->
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

                            <!-- Stripe Card Section -->
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
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-people"></i> {{ $item->jumlah_peserta }} peserta • 
                                <i class="bi bi-calendar"></i> {{ $item->tanggal_keberangkatan->format('d M Y') }}
                            </small>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ $item->paket->durasi_hari }} Days</span>
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
                                <span>Subtotal ({{ $cartItems->count() }} items)</span>
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
                                ≈ <span id="total-display-amount"></span>
                            </div>
                        </div>

                        <input type="hidden" id="base-total" value="{{ $total }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.payment-method-card {
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method-card:hover {
    border-color: #667eea;
    background: #f8f9ff;
}

.payment-method-card input[type="radio"]:checked ~ label {
    border-color: #667eea;
    background: #f8f9ff;
}
</style>

@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
console.log('=== CHECKOUT SCRIPT LOADED (Display Currency Only) ===');

// Configuration
const BASE_TOTAL_MYR = parseFloat(document.getElementById('base-total').value);
console.log('Base Total (MYR):', BASE_TOTAL_MYR);

// Initialize Stripe
const stripe = Stripe('{{ config("services.stripe.key") }}');
const elements = stripe.elements();
const cardElement = elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            '::placeholder': { color: '#aab7c4' }
        },
        invalid: { color: '#dc3545', iconColor: '#dc3545' }
    }
});
cardElement.mount('#card-element');

cardElement.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

// ============================================
// DISPLAY CURRENCY CONVERSION (UI ONLY)
// ============================================

let currentDisplayCurrency = 'MYR';
let currentExchangeRate = 1;

async function fetchExchangeRate(targetCurrency) {
    if (targetCurrency === 'MYR') {
        return 1;
    }

    try {
        const url = `https://api.exchangerate-api.com/v4/latest/MYR`;
        const response = await fetch(url);
        const data = await response.json();
        
        return data.rates[targetCurrency] || 1;
    } catch (error) {
        console.error('Exchange rate fetch error:', error);
        return 1;
    }
}

function formatCurrency(amount, currency) {
    const formats = {
        'MYR': { symbol: 'RM', decimals: 2 },
        'USD': { symbol: '$', decimals: 2 },
        'IDR': { symbol: 'Rp', decimals: 0 },
        'SGD': { symbol: 'S$', decimals: 2 },
        'EUR': { symbol: '€', decimals: 2 },
        'GBP': { symbol: '£', decimals: 2 },
        'AUD': { symbol: 'A$', decimals: 2 },
        'JPY': { symbol: '¥', decimals: 0 },
        'CNY': { symbol: '¥', decimals: 2 }
    };

    const format = formats[currency] || { symbol: currency, decimals: 2 };
    const formatted = format.decimals === 0 
        ? Math.round(amount).toLocaleString('en-US')
        : amount.toFixed(format.decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    return `${format.symbol} ${formatted}`;
}

async function updateDisplayPrices(targetCurrency) {
    console.log('Updating display to:', targetCurrency);
    
    currentDisplayCurrency = targetCurrency;
    currentExchangeRate = await fetchExchangeRate(targetCurrency);

    const displayTotal = BASE_TOTAL_MYR * currentExchangeRate;
    
    // Update exchange rate info
    const rateInfo = document.getElementById('exchange-rate-info');
    if (targetCurrency !== 'MYR') {
        rateInfo.classList.remove('d-none');
        document.getElementById('rate-display').textContent = 
            `1 MYR ≈ ${formatCurrency(currentExchangeRate, targetCurrency)}`;
    } else {
        rateInfo.classList.add('d-none');
    }

    // Update item prices (display only)
    document.querySelectorAll('.item-price-myr').forEach(el => {
        const myrPrice = parseFloat(el.dataset.myr);
        const displayPrice = myrPrice * currentExchangeRate;
        
        const displayEl = el.parentElement.querySelector('.item-price-display');
        if (targetCurrency !== 'MYR') {
            displayEl.textContent = `≈ ${formatCurrency(displayPrice, targetCurrency)}`;
            displayEl.classList.remove('d-none');
        } else {
            displayEl.classList.add('d-none');
        }
    });

    // Update summary (display only)
    const subtotalDisplay = document.getElementById('subtotal-display');
    const totalDisplay = document.getElementById('total-display');
    const payAmountDisplay = document.getElementById('pay-amount-display');
    
    if (targetCurrency !== 'MYR') {
        document.getElementById('subtotal-display-amount').textContent = 
            formatCurrency(displayTotal, targetCurrency);
        subtotalDisplay.classList.remove('d-none');

        document.getElementById('total-display-amount').textContent = 
            formatCurrency(displayTotal, targetCurrency);
        totalDisplay.classList.remove('d-none');

        payAmountDisplay.textContent = ` (≈ ${formatCurrency(displayTotal, targetCurrency)})`;
        payAmountDisplay.classList.remove('d-none');
    } else {
        subtotalDisplay.classList.add('d-none');
        totalDisplay.classList.add('d-none');
        payAmountDisplay.classList.add('d-none');
    }
}

// Currency change event
document.getElementById('display_currency').addEventListener('change', function() {
    updateDisplayPrices(this.value);
});

// ============================================
// WEBHOOK WAITING FUNCTIONS
// ============================================

function showProcessingModal() {
    const modal = document.createElement('div');
    modal.id = 'processing-modal';
    modal.innerHTML = `
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.8);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                        <h5 class="mb-3">Processing Your Payment...</h5>
                        <p class="text-muted">Please wait, generating your tickets...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function hideProcessingModal() {
    const modal = document.getElementById('processing-modal');
    if (modal) modal.remove();
}

async function waitForWebhookProcessing(orderId, maxAttempts = 30) {
    console.log(`Waiting for webhook: ${orderId}`);
    let attempts = 0;

    return new Promise((resolve) => {
        const intervalId = setInterval(async () => {
            attempts++;
            console.log(`Polling ${attempts}/${maxAttempts}`);

            try {
                const response = await fetch(`/api/order-status/${orderId}`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                console.log('Status:', data.status);

                if (data.status === 'paid' && data.redeem_code) {
                    clearInterval(intervalId);
                    hideProcessingModal();
                    window.location.href = `{{ route("checkout.success") }}?order_id=${orderId}`;
                    resolve();
                } else if (attempts >= maxAttempts) {
                    clearInterval(intervalId);
                    hideProcessingModal();
                    window.location.href = `{{ route("checkout.success") }}?order_id=${orderId}`;
                    resolve();
                }
            } catch (error) {
                if (attempts >= maxAttempts) {
                    clearInterval(intervalId);
                    hideProcessingModal();
                    window.location.href = `{{ route("checkout.success") }}?order_id=${orderId}`;
                    resolve();
                }
            }
        }, 2000);
    });
}

// ============================================
// FORM SUBMISSION
// ============================================

document.getElementById('payment-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    setLoading(true);

    const formData = {
        customer_name: document.getElementById('customer_name').value,
        customer_email: document.getElementById('customer_email').value,
        customer_phone: document.getElementById('customer_phone').value,
        customer_address: document.getElementById('customer_address').value,
        display_currency: currentDisplayCurrency,
        payment_method: 'stripe'
    };

    console.log('Submitting payment (MYR):', formData);

    try {
        // Step 1: Create order
        const response = await fetch('{{ route("checkout.process") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        console.log('Order created:', result.order_id);

        // Step 2: Confirm payment with Stripe
        const {error, paymentIntent} = await stripe.confirmCardPayment(result.client_secret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: formData.customer_name,
                    email: formData.customer_email
                }
            }
        });

        if (error) throw new Error(error.message);

        // Step 3: Wait for webhook
        if (paymentIntent.status === 'succeeded') {
            console.log('Payment succeeded, waiting for webhook...');
            showProcessingModal();
            setLoading(false);
            await waitForWebhookProcessing(result.order_id);
        }

    } catch (error) {
        console.error('Payment error:', error);
        showError(error.message);
        setLoading(false);
    }
});

function setLoading(isLoading) {
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    const submitButton = document.getElementById('submit-button');
    
    submitButton.disabled = isLoading;
    buttonText.classList.toggle('d-none', isLoading);
    spinner.classList.toggle('d-none', !isLoading);
}

function showError(message) {
    const errorElement = document.getElementById('card-errors');
    errorElement.textContent = message;
    setTimeout(() => errorElement.textContent = '', 5000);
}

console.log('=== SCRIPT READY ===');
</script>
@endpush