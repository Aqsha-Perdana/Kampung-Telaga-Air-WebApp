@extends('landing.layout')

@section('content')
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
                                             style="object-fit: cover; height: 120px; width: 100%;">
                                    @else
                                        <img src="https://via.placeholder.com/200x150?text={{ urlencode($item->paket->nama_paket) }}" 
                                             class="img-fluid rounded" 
                                             alt="{{ $item->paket->nama_paket }}">
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-2">{{ $item->paket->nama_paket }}</h5>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <span class="badge bg-info">
                                            <i class="bi bi-calendar3"></i> {{ $item->paket->durasi_hari }} Hari
                                        </span>
                                        <span class="text-muted small">
                                            {{ format_ringgit($item->harga_satuan) }} 
                                        </span>
                                    </div>
                                    <p class="mb-1">
                                        <i class="bi bi-people-fill text-primary"></i> 
                                        <strong>{{ $item->jumlah_peserta }}</strong> people
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
                                            <br><small>{{ format_ringgit($item->harga_satuan) }} per orang</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-people-fill"></i> Number of Participants
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="jumlah_peserta" 
                                                   value="{{ $item->jumlah_peserta }}" 
                                                   min="1" 
                                                   max="50"
                                                   required>
                                            <small class="text-muted">Maksimal 50 peserta per booking</small>
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
                                            <small class="text-muted">Minimal 3 hari dari sekarang</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-chat-left-text"></i> Notes (Optional)
                                            </label>
                                            <textarea class="form-control" 
                                                      name="catatan" 
                                                      rows="3"
                                                      maxlength="500"
                                                      placeholder="Permintaan khusus, alergi makanan, dll...">{{ $item->catatan }}</textarea>
                                            <small class="text-muted">Maksimal 500 karakter</small>
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
                                        <small class="text-primary">{{ $item->jumlah_peserta }} × {{ format_ringgit($item->harga_satuan) }}</small>
                                    </span>
                                    <strong>{{ format_ringgit($item->subtotal) }}</strong>
                                </div>
                                @endforeach
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal ({{ $cartItems->count() }} item)</span>
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
                                        onclick="return confirm('Hapus semua item dari keranjang?')">
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

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
}

.badge {
    font-weight: 500;
}

.modal-content {
    border-radius: 15px;
    border: none;
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    z-index: 9999;
    min-width: 350px;
    max-width: 400px;
    animation: slideInRight 0.4s ease-out;
    border-left: 4px solid #10b981;
}

.toast-notification.error {
    border-left-color: #ef4444;
}

.toast-notification.warning {
    border-left-color: #f59e0b;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.toast-notification.hiding {
    animation: slideOutRight 0.3s ease-in forwards;
}

.toast-header-custom {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.toast-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.toast-icon.success {
    background: #d1fae5;
    color: #10b981;
}

.toast-icon.error {
    background: #fee2e2;
    color: #ef4444;
}

.toast-icon.warning {
    background: #fef3c7;
    color: #f59e0b;
}

.toast-body-custom {
    margin-left: 52px;
}

.toast-title {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
    color: #1f2937;
}

.toast-message {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
}

.toast-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 20px;
    color: #9ca3af;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.toast-close:hover {
    background: #f3f4f6;
    color: #4b5563;
}

.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: #10b981;
    border-radius: 0 0 0 12px;
    animation: progressBar 4s linear forwards;
}

.toast-progress.error {
    background: #ef4444;
}

.toast-progress.warning {
    background: #f59e0b;
}

@keyframes progressBar {
    from { width: 100%; }
    to { width: 0%; }
}

.toast-actions {
    margin-top: 12px;
    margin-left: 52px;
    display: flex;
    gap: 8px;
}

.toast-btn {
    padding: 6px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.toast-btn-primary {
    background: #2563eb;
    color: white;
}

.toast-btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.toast-btn-secondary {
    background: #f3f4f6;
    color: #4b5563;
}

.toast-btn-secondary:hover {
    background: #e5e7eb;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9998;
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Confetti Animation */
@keyframes confetti {
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}

.confetti-piece {
    position: fixed;
    width: 10px;
    height: 10px;
    background: #2563eb;
    top: -10px;
    z-index: 10000;
    animation: confetti 3s ease-out forwards;
}

/* Cart Badge Animation */
@keyframes cartBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.cart-badge-animate {
    animation: cartBounce 0.5s ease;
}
</style>

<script>
    // Auto dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Update total when editing
    document.querySelectorAll('input[name="jumlah_peserta"]').forEach(input => {
        input.addEventListener('input', function() {
            // You can add real-time total calculation here if needed
        });
    });

    function showToast(options) {
    const {
        type = 'success',
        title = 'Notifikasi',
        message = '',
        duration = 4000,
        showActions = false,
        actions = []
    } = options;

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    const iconMap = {
        success: '✓',
        error: '✕',
        warning: '⚠'
    };

    toast.innerHTML = `
        <button class="toast-close" onclick="closeToast(this)">×</button>
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
    const badges = document.querySelectorAll('.badge, .position-absolute.badge');
    badges.forEach(badge => {
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
    return loadingOverlay;
}

function hideLoading() {
    const loadingOverlay = document.getElementById('cartLoadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

// ===== FORM SUBMISSION HANDLER =====

const addToCartForm = document.getElementById('addToCartForm');
if (addToCartForm) {
    addToCartForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        @guest
            showToast({
                type: 'warning',
                title: 'Login Diperlukan',
                message: 'Silakan login terlebih dahulu untuk menambahkan paket ke keranjang!',
                showActions: true,
                actions: [
                    {
                        text: 'Login Sekarang',
                        class: 'toast-btn-primary',
                        icon: 'box-arrow-in-right',
                        onclick: "window.location.href='{{ route('wisatawan.login') }}'"
                    }
                ],
                duration: 5000
            });
            return false;
        @else
            // Validasi form
            const jumlahPeserta = this.querySelector('input[name="jumlah_peserta"]').value;
            const tanggalKeberangkatan = this.querySelector('input[name="tanggal_keberangkatan"]').value;
            
            if (!jumlahPeserta || jumlahPeserta < 1) {
                showToast({
                    type: 'error',
                    title: 'Validasi Gagal',
                    message: 'Jumlah peserta minimal 1 orang.',
                    duration: 3000
                });
                return false;
            }
            
            if (!tanggalKeberangkatan) {
                showToast({
                    type: 'error',
                    title: 'Validasi Gagal',
                    message: 'Silakan pilih tanggal keberangkatan.',
                    duration: 3000
                });
                return false;
            }
            
            const selectedDate = new Date(tanggalKeberangkatan);
            const minDate = new Date();
            minDate.setDate(minDate.getDate() + 3);
            
            if (selectedDate < minDate) {
                showToast({
                    type: 'error',
                    title: 'Validasi Gagal',
                    message: 'Tanggal keberangkatan minimal 3 hari dari sekarang.',
                    duration: 3000
                });
                return false;
            }

            // Show loading
            showLoading();

            // Submit form
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    createConfetti();
                    animateCartBadge();
                    
                    const paketName = data.paket_nama || '{{ $paket->nama_paket ?? "Paket Wisata" }}';
                    
                    showToast({
                        type: 'success',
                        title: 'Berhasil Ditambahkan!',
                        message: `Paket "${paketName}" telah ditambahkan ke keranjang Anda.`,
                        showActions: true,
                        actions: [
                            {
                                text: 'Lihat Keranjang',
                                class: 'toast-btn-primary',
                                icon: 'cart3',
                                onclick: "window.location.href='{{ route('cart.index') }}'"
                            },
                            {
                                text: 'Lanjut Belanja',
                                class: 'toast-btn-secondary',
                                onclick: 'closeToast(this)'
                            }
                        ],
                        duration: 5000
                    });
                    
                    // Reset form
                    setTimeout(() => {
                        const jumlahInput = this.querySelector('input[name="jumlah_peserta"]');
                        const tanggalInput = this.querySelector('input[name="tanggal_keberangkatan"]');
                        const catatanInput = this.querySelector('textarea[name="catatan"]');
                        
                        if (jumlahInput) jumlahInput.value = 1;
                        if (tanggalInput) tanggalInput.value = '';
                        if (catatanInput) catatanInput.value = '';
                    }, 1000);
                } else {
                    showToast({
                        type: 'error',
                        title: 'Gagal Ditambahkan',
                        message: data.message || 'Terjadi kesalahan saat menambahkan paket ke keranjang.',
                        duration: 4000
                    });
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                
                showToast({
                    type: 'error',
                    title: 'Terjadi Kesalahan',
                    message: 'Gagal menambahkan paket ke keranjang. Silakan coba lagi.',
                    duration: 4000
                });
            });
        @endguest
    });
}

// ===== DISABLED INPUT HANDLER =====

document.querySelectorAll('input:disabled, textarea:disabled').forEach(function(element) {
    element.addEventListener('click', function() {
        @guest
            showToast({
                type: 'warning',
                title: 'Login Diperlukan',
                message: 'Silakan login untuk mengakses form pemesanan',
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

// ===== PRICE UPDATE ON CHANGE =====

const pesertaInput = document.querySelector('input[name="jumlah_peserta"]');
if (pesertaInput) {
    pesertaInput.addEventListener('input', function() {
        @if(isset($paket))
        const basePrice = {{ $paket->harga_final }};
        const totalPrice = basePrice * this.value;
        
        // Update total price display
        const priceElements = document.querySelectorAll('.h5.text-primary.mb-0');
        priceElements.forEach(el => {
            const currentText = el.textContent;
            if (currentText.includes('RM')) {
                el.textContent = 'RM ' + totalPrice.toFixed(2);
            }
        });
        
        console.log('Total: RM ' + totalPrice.toFixed(2));
        @endif
    });
}

// ===== FLASH MESSAGES =====

@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast({
            type: 'success',
            title: 'Berhasil!',
            message: '{{ session('success') }}',
            duration: 4000
        });
    });
@endif

@if(session('error'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast({
            type: 'error',
            title: 'Gagal!',
            message: '{{ session('error') }}',
            duration: 4000
        });
    });
@endif
</script>
@endsection