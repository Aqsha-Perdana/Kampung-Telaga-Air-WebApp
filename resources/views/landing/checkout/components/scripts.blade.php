@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const BASE_TOTAL_MYR = parseFloat(document.getElementById('base-total').value);
const stripeKey = '{{ config("services.stripe.key") }}';
const checkoutProcessUrl = '{{ route("checkout.process") }}';
const checkoutSuccessUrl = '{{ route("checkout.success") }}';
const exchangeRateUrlTemplate = '{{ route("checkout.exchange-rate", ["currency" => "__CURRENCY__"]) }}';

let currentPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || '{{ config("payment.default", "stripe") }}';
let currentDisplayCurrency = 'MYR';
let currentExchangeRate = 1;
const exchangeRateCache = { MYR: 1 };

const stripe = stripeKey ? Stripe(stripeKey) : null;
let cardElement = null;

if (stripe) {
    const elements = stripe.elements();
    cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#1e293b',
                fontFamily: '"Plus Jakarta Sans", "Segoe UI", sans-serif',
                '::placeholder': { color: '#94a3b8' }
            },
            invalid: {
                color: '#dc2626',
                iconColor: '#dc2626'
            }
        }
    });

    cardElement.mount('#card-element');
    cardElement.on('change', function (event) {
        const displayError = document.getElementById('card-errors');
        displayError.textContent = event.error ? event.error.message : '';
    });
}

async function fetchExchangeRate(targetCurrency) {
    if (targetCurrency === 'MYR') {
        return 1;
    }

    if (exchangeRateCache[targetCurrency]) {
        return exchangeRateCache[targetCurrency];
    }

    try {
        const response = await fetch(exchangeRateUrlTemplate.replace('__CURRENCY__', targetCurrency), {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to fetch exchange rate.');
        }

        const data = await response.json();
        const rate = parseFloat(data.rate || 1);
        exchangeRateCache[targetCurrency] = rate;

        return rate;
    } catch (error) {
        return 1;
    }
}

function formatCurrency(amount, currency) {
    const formats = {
        MYR: { symbol: 'RM', decimals: 2 },
        USD: { symbol: '$', decimals: 2 },
        IDR: { symbol: 'Rp', decimals: 0 },
        SGD: { symbol: 'S$', decimals: 2 },
        EUR: { symbol: '\u20AC', decimals: 2 },
        GBP: { symbol: '\u00A3', decimals: 2 },
        AUD: { symbol: 'A$', decimals: 2 },
        JPY: { symbol: '\u00A5', decimals: 0 },
        CNY: { symbol: '\u00A5', decimals: 2 }
    };

    const format = formats[currency] || { symbol: currency, decimals: 2 };
    const formatted = format.decimals === 0
        ? Math.round(amount).toLocaleString('en-US')
        : amount.toFixed(format.decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    return `${format.symbol} ${formatted}`;
}

async function updateDisplayPrices(targetCurrency) {
    currentDisplayCurrency = targetCurrency;
    currentExchangeRate = await fetchExchangeRate(targetCurrency);

    const displayTotal = BASE_TOTAL_MYR * currentExchangeRate;
    const rateInfo = document.getElementById('exchange-rate-info');

    if (targetCurrency !== 'MYR') {
        rateInfo.classList.remove('d-none');
        document.getElementById('rate-display').textContent = `1 MYR ~ ${formatCurrency(currentExchangeRate, targetCurrency)}`;
    } else {
        rateInfo.classList.add('d-none');
    }

    document.querySelectorAll('.item-price-myr').forEach((el) => {
        const myrPrice = parseFloat(el.dataset.myr);
        const displayPrice = myrPrice * currentExchangeRate;
        const displayEl = el.parentElement.querySelector('.item-price-display');

        if (!displayEl) {
            return;
        }

        if (targetCurrency !== 'MYR') {
            displayEl.textContent = `~ ${formatCurrency(displayPrice, targetCurrency)}`;
            displayEl.classList.remove('d-none');
        } else {
            displayEl.classList.add('d-none');
        }
    });

    const subtotalDisplay = document.getElementById('subtotal-display');
    const totalDisplay = document.getElementById('total-display');
    const payAmountDisplay = document.getElementById('pay-amount-display');

    if (targetCurrency !== 'MYR') {
        document.getElementById('subtotal-display-amount').textContent = formatCurrency(displayTotal, targetCurrency);
        document.getElementById('total-display-amount').textContent = formatCurrency(displayTotal, targetCurrency);
        payAmountDisplay.textContent = ` (~ ${formatCurrency(displayTotal, targetCurrency)})`;

        subtotalDisplay.classList.remove('d-none');
        totalDisplay.classList.remove('d-none');
        payAmountDisplay.classList.remove('d-none');
    } else {
        subtotalDisplay.classList.add('d-none');
        totalDisplay.classList.add('d-none');
        payAmountDisplay.classList.add('d-none');
    }
}

function togglePaymentSections(paymentMethod) {
    currentPaymentMethod = paymentMethod;

    document.getElementById('stripe-section').classList.toggle('d-none', paymentMethod !== 'stripe');
    document.getElementById('xendit-section').classList.toggle('d-none', paymentMethod !== 'xendit');

    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');

    if (paymentMethod === 'xendit') {
        buttonText.innerHTML = `<i class="bi bi-box-arrow-up-right"></i> Continue to Xendit <span id="pay-amount-myr" class="fw-bold">${formatCurrency(BASE_TOTAL_MYR, 'MYR')}</span><span id="pay-amount-display" class="text-white-50 small ${currentDisplayCurrency === 'MYR' ? 'd-none' : ''}"></span>`;
    } else {
        buttonText.innerHTML = `<i class="bi bi-lock-fill"></i> Pay <span id="pay-amount-myr" class="fw-bold">${formatCurrency(BASE_TOTAL_MYR, 'MYR')}</span><span id="pay-amount-display" class="text-white-50 small ${currentDisplayCurrency === 'MYR' ? 'd-none' : ''}"></span>`;
    }

    if (submitButton.disabled) {
        submitButton.disabled = false;
    }

    updateDisplayPrices(currentDisplayCurrency);

    if (paymentMethod !== 'stripe') {
        document.getElementById('card-errors').textContent = '';
    }
}

function setLoading(isLoading, message = '') {
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    const submitButton = document.getElementById('submit-button');

    submitButton.disabled = isLoading;
    buttonText.classList.toggle('d-none', isLoading);
    spinner.classList.toggle('d-none', !isLoading);

    if (isLoading && message) {
        spinner.setAttribute('title', message);
    } else {
        spinner.removeAttribute('title');
    }
}

function showError(message) {
    const feedback = document.getElementById('checkout-feedback');
    const cardErrors = document.getElementById('card-errors');

    feedback.textContent = message;
    feedback.classList.remove('d-none');
    cardErrors.textContent = message;

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function clearError() {
    const feedback = document.getElementById('checkout-feedback');
    feedback.textContent = '';
    feedback.classList.add('d-none');
    document.getElementById('card-errors').textContent = '';
}

function showProcessingModal(title, subtitle) {
    hideProcessingModal();

    const modal = document.createElement('div');
    modal.id = 'processing-modal';
    modal.innerHTML = `
        <div class="modal fade show" style="display: block; background: rgba(15, 23, 42, 0.55); z-index: 9999;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-5 px-4">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                        <h5 class="mb-2">${title}</h5>
                        <p class="text-muted mb-0">${subtitle}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
}

function hideProcessingModal() {
    const modal = document.getElementById('processing-modal');
    if (modal) {
        modal.remove();
    }
}

async function submitCheckoutForm() {
    const formData = {
        customer_name: document.getElementById('customer_name').value,
        customer_email: document.getElementById('customer_email').value,
        customer_phone: document.getElementById('customer_phone').value,
        customer_address: document.getElementById('customer_address').value,
        display_currency: currentDisplayCurrency,
        payment_method: currentPaymentMethod
    };

    const response = await fetch(checkoutProcessUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Unable to create the order.');
    }

    return { formData, result };
}

document.getElementById('display_currency').addEventListener('change', function () {
    updateDisplayPrices(this.value);
});

document.querySelectorAll('.payment-method-input').forEach((input) => {
    input.addEventListener('change', function () {
        if (this.checked) {
            clearError();
            togglePaymentSections(this.value);
        }
    });
});

document.getElementById('payment-form').addEventListener('submit', async function (event) {
    event.preventDefault();
    clearError();
    setLoading(true, 'Preparing your payment...');

    try {
        const { formData, result } = await submitCheckoutForm();

        if (formData.payment_method === 'stripe') {
            if (!stripe || !cardElement) {
                throw new Error('Stripe is not configured for this environment yet.');
            }

            const confirmation = await stripe.confirmCardPayment(result.client_secret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: formData.customer_name,
                        email: formData.customer_email
                    }
                }
            });

            if (confirmation.error) {
                throw new Error(confirmation.error.message);
            }

            showProcessingModal(
                'Finishing your payment...',
                'We are confirming the transaction and preparing your booking summary.'
            );

            window.location.href = `${checkoutSuccessUrl}?order_id=${result.order_id}`;
            return;
        }

        if (formData.payment_method === 'xendit') {
            if (!result.redirect_url) {
                throw new Error('Xendit payment link was not generated.');
            }

            showProcessingModal(
                'Opening Xendit checkout...',
                'You will be redirected to Xendit to choose your payment channel.'
            );

            window.location.href = result.redirect_url;
            return;
        }

        throw new Error('Unsupported payment method.');
    } catch (error) {
        hideProcessingModal();
        showError(error.message || 'Something went wrong during checkout.');
        setLoading(false);
    }
});

togglePaymentSections(currentPaymentMethod);
</script>
@endpush
