<style>
.cart-shell {
    background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
}

.cart-kicker {
    display: inline-block;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #2563eb;
    font-weight: 700;
}

.cart-header h2 {
    font-weight: 700;
    color: #0f172a;
}

.cart-account {
    flex-wrap: wrap;
}

.cart-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0 0.9rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 0.85rem;
    font-weight: 700;
}

.cart-empty-card,
.cart-item-card,
.cart-summary-card {
    border-radius: 22px;
}

.cart-empty-icon {
    width: 4.75rem;
    height: 4.75rem;
    border-radius: 18px;
    background: #eff6ff;
    color: #2563eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08) !important;
}

.cart-item-image img {
    display: block;
}

.cart-item-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.7rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 0.8rem;
    font-weight: 700;
}

.cart-item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.cart-item-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #64748b;
    font-size: 0.9rem;
}

.cart-item-note {
    padding: 0.85rem 0.95rem;
    border-radius: 14px;
    background: #f8fafc;
}

.cart-item-side {
    padding-left: 0.5rem;
}

.cart-original-price {
    color: #94a3b8;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: line-through;
}

.cart-summary-list {
    display: grid;
    gap: 0.9rem;
}

.cart-summary-line {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.9rem 1rem;
    border-radius: 16px;
    background: #f8fafc;
}

.cart-total-box {
    padding: 1rem;
    border-radius: 18px;
    background: #f8fafc;
}

.cart-primary-action {
    min-height: 54px;
    border-radius: 14px;
}

.cart-summary-note {
    border-top: 1px solid #e5e7eb;
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

@keyframes cartBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.cart-badge-animate {
    animation: cartBounce 0.5s ease;
}

@media (max-width: 767.98px) {
    .cart-item-meta,
    .cart-summary-line {
        flex-direction: column;
    }

    .cart-item-side {
        padding-left: 0;
        text-align: left !important;
    }
}
</style>
