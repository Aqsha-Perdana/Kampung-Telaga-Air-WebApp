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
        'EUR': { symbol: '\u20AC', decimals: 2 },
        'GBP': { symbol: '\u00A3', decimals: 2 },
        'AUD': { symbol: 'A$', decimals: 2 },
        'JPY': { symbol: '\u00A5', decimals: 0 },
        'CNY': { symbol: '\u00A5', decimals: 2 }
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
            `1 MYR ~ ${formatCurrency(currentExchangeRate, targetCurrency)}`;
    } else {
        rateInfo.classList.add('d-none');
    }

    // Update item prices (display only)
    document.querySelectorAll('.item-price-myr').forEach(el => {
        const myrPrice = parseFloat(el.dataset.myr);
        const displayPrice = myrPrice * currentExchangeRate;

        const displayEl = el.parentElement.querySelector('.item-price-display');
        if (targetCurrency !== 'MYR') {
            displayEl.textContent = `~ ${formatCurrency(displayPrice, targetCurrency)}`;
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

        payAmountDisplay.textContent = ` (~ ${formatCurrency(displayTotal, targetCurrency)})`;
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
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.8); z-index: 9999;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                        <h5 class="mb-3">Processing Your Payment...</h5>
                        <p class="text-muted mb-4">Please wait, generating your tickets...</p>

                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="hideProcessingModal()">
                            <i class="bi bi-x"></i> Hide / Run in Background
                        </button>
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

async function waitForWebhookProcessing(orderId, maxAttempts = 6) {
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

                if (data.status === 'paid') {
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
        }, 1000);
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
