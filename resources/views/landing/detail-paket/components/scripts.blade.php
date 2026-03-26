<script>
function showToast(options) {
    const {
        type = 'success',
        title = 'Notification',
        message = '',
        duration = 4000,
        showActions = false,
        actions = []
    } = options;

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;

    const iconMap = {
        success: '&#10003;',
        error: '&#10005;',
        warning: '&#9888;'
    };

    toast.innerHTML = `
        <button class="toast-close" onclick="closeToast(this)">&times;</button>
        <div class="toast-header-custom">
            <div class="toast-icon ${type}">
                ${iconMap[type]}
            </div>
            <div class="toast-title">${title}</div>
        </div>
        <div class="toast-body-custom">
            <div class="toast-message">${message}</div>
            ${showActions ? `
                <div class="toast-actions">
                    ${actions.map(action => `
                        <button class="toast-btn ${action.class}" onclick="${action.onclick}">
                            ${action.icon ? `<i class="bi bi-${action.icon}"></i> ` : ''}${action.text}
                        </button>
                    `).join('')}
                </div>
            ` : ''}
        </div>
        <div class="toast-progress ${type}"></div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        closeToast(toast.querySelector('.toast-close'));
    }, duration);

    return toast;
}

function closeToast(button) {
    const toast = button.closest('.toast-notification');
    if (toast) {
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
    }
}

function createConfetti() {
    const colors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
    for (let i = 0; i < 30; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti-piece';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 0.5 + 's';
        confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
        document.body.appendChild(confetti);

        setTimeout(() => confetti.remove(), 3000);
    }
}

function animateCartBadge() {
    document.querySelectorAll('.badge, .position-absolute.badge').forEach(badge => {
        badge.classList.add('cart-badge-animate');
        setTimeout(() => badge.classList.remove('cart-badge-animate'), 500);
    });
}

function showLoading() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.id = 'cartLoadingOverlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingOverlay);
}

function hideLoading() {
    document.getElementById('cartLoadingOverlay')?.remove();
}

const addToCartForm = document.getElementById('addToCartForm');

document.querySelectorAll('input:disabled, textarea:disabled').forEach(function(element) {
    element.addEventListener('click', function() {
        @guest
            showToast({
                type: 'warning',
                title: 'Login Required',
                message: 'Please log in to access the booking form.',
                showActions: true,
                actions: [
                    {
                        text: 'Login',
                        class: 'toast-btn-primary',
                        icon: 'box-arrow-in-right',
                        onclick: "window.location.href='{{ route('wisatawan.login') }}'"
                    }
                ],
                duration: 4000
            });
        @endguest
    });
});

if (addToCartForm) {
    addToCartForm.addEventListener('submit', function(e) {
        e.preventDefault();

        @guest
            showToast({
                type: 'warning',
                title: 'Login Required',
                message: 'Please log in first to add this package to your cart.',
                showActions: true,
                actions: [
                    {
                        text: 'Log In Now',
                        class: 'toast-btn-primary',
                        icon: 'box-arrow-in-right',
                        onclick: "window.location.href='{{ route('wisatawan.login') }}'"
                    }
                ],
                duration: 5000
            });
            return false;
        @else
            const participantsInput = this.querySelector('input[name="jumlah_peserta"]');
            const dateInput = this.querySelector('input[name="tanggal_keberangkatan"]');
            const noteInput = this.querySelector('textarea[name="catatan"]');
            const participants = parseInt(participantsInput?.value || '0', 10);
            const minParticipants = parseInt(participantsInput?.dataset.minParticipants || participantsInput?.min || '1', 10);
            const maxParticipantsRaw = participantsInput?.dataset.maxParticipants || participantsInput?.max || '';
            const maxParticipants = maxParticipantsRaw ? parseInt(maxParticipantsRaw, 10) : null;
            const departureDate = dateInput?.value || '';

            if (!participants || participants < minParticipants) {
                showToast({
                    type: 'error',
                    title: 'Validation Failed',
                    message: `This package requires at least ${minParticipants} participant${minParticipants > 1 ? 's' : ''}.`,
                    duration: 3000
                });
                return false;
            }

            if (maxParticipants !== null && participants > maxParticipants) {
                showToast({
                    type: 'error',
                    title: 'Validation Failed',
                    message: `This package allows up to ${maxParticipants} participants.`,
                    duration: 3000
                });
                return false;
            }

            if (!departureDate) {
                showToast({
                    type: 'error',
                    title: 'Validation Failed',
                    message: 'Please select a departure date.',
                    duration: 3000
                });
                return false;
            }

            const selectedDate = new Date(departureDate);
            const minDate = new Date();
            minDate.setHours(0, 0, 0, 0);
            minDate.setDate(minDate.getDate() + 3);

            if (selectedDate < minDate) {
                showToast({
                    type: 'error',
                    title: 'Validation Failed',
                    message: 'Departure must be at least 3 days from today.',
                    duration: 3000
                });
                return false;
            }

            showLoading();

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw payload;
                }
                return payload;
            })
            .then(data => {
                hideLoading();
                createConfetti();
                animateCartBadge();

                const paketName = data.paket_nama || '{{ $paket->nama_paket ?? "Package" }}';

                showToast({
                    type: 'success',
                    title: 'Added Successfully!',
                    message: `Package "${paketName}" has been added to your cart.`,
                    showActions: true,
                    actions: [
                        {
                            text: 'View Cart',
                            class: 'toast-btn-primary',
                            icon: 'cart3',
                            onclick: "window.location.href='{{ route('cart.index') }}'"
                        },
                        {
                            text: 'Continue Shopping',
                            class: 'toast-btn-secondary',
                            onclick: 'closeToast(this)'
                        }
                    ],
                    duration: 5000
                });

                if (participantsInput) {
                    participantsInput.value = participantsInput.dataset.minParticipants || participantsInput.min || 1;
                }
                if (dateInput) {
                    dateInput.value = '';
                }
                if (noteInput) {
                    noteInput.value = '';
                }
            })
            .catch(error => {
                hideLoading();
                const message = error?.errors?.jumlah_peserta?.[0]
                    || error?.message
                    || 'Failed to add the package to your cart. Please try again.';

                showToast({
                    type: 'error',
                    title: 'Unable to Add Package',
                    message,
                    duration: 4000
                });
            });
        @endguest
    });
}

@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    showToast({
        type: 'success',
        title: 'Success!',
        message: '{{ session('success') }}',
        duration: 4000
    });
});
@endif

@if(session('error'))
document.addEventListener('DOMContentLoaded', function() {
    showToast({
        type: 'error',
        title: 'Failed!',
        message: '{{ session('error') }}',
        duration: 4000
    });
});
@endif
</script>