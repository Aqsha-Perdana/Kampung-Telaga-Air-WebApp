<style>
.package-breadcrumb {
    background: #f8fafc;
}

.package-detail-shell {
    background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
}

.package-kicker {
    display: inline-block;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #2563eb;
    font-weight: 700;
}

.package-title {
    font-size: clamp(2rem, 3vw, 2.8rem);
    font-weight: 700;
    color: #0f172a;
}

.package-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.package-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #64748b;
    font-size: 0.94rem;
}

.package-hero-card,
.package-section-card,
.package-booking-card,
.package-note-card {
    border-radius: 24px;
}

.package-price-card {
    padding: 1rem 1.15rem;
    border-radius: 18px;
    background: #f8fafc;
    min-width: 210px;
}

.package-price {
    font-size: 1.8rem;
    font-weight: 800;
    color: #2563eb;
    line-height: 1.1;
}

.package-original-price {
    color: #94a3b8;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: line-through;
}

.package-stat-card {
    padding: 1rem;
    border-radius: 18px;
    background: #f8fafc;
    height: 100%;
}

.package-section-header h4,
.package-section-header h5 {
    color: #0f172a;
    font-weight: 700;
}

.package-timeline-badge {
    width: 48px;
    height: 48px;
    border-radius: 999px;
    background: #2563eb;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}

.package-muted-panel {
    background: #f8fafc;
    border-radius: 16px;
    padding: 1rem;
}

.package-resource-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
}

.package-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.package-booking-badge {
    padding: 0.45rem 0.7rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 0.78rem;
    font-weight: 700;
}

.package-total-box {
    padding: 1rem;
    border-radius: 18px;
    background: #f8fafc;
}

.package-primary-action {
    min-height: 54px;
    border-radius: 14px;
}

.package-trust-note {
    border-top: 1px solid #e5e7eb;
}

.package-checklist li {
    display: flex;
    gap: 0.5rem;
    align-items: flex-start;
}

.package-checklist i {
    margin-top: 0.15rem;
}

.card.border-warning {
    border-width: 2px;
    background: linear-gradient(to bottom, #fffbf0 0%, #ffffff 100%);
}

input:disabled,
textarea:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-primary[href*="login"],
.btn-warning {
    position: relative;
    overflow: hidden;
}

.btn-primary[href*="login"]::before,
.btn-warning::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-primary[href*="login"]:hover::before,
.btn-warning:hover::before {
    width: 300px;
    height: 300px;
}

@keyframes pulse-warning {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
}

.card.border-warning {
    animation: pulse-warning 2s infinite;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.btn-primary[href*="login"]:hover i {
    animation: shake 0.5s ease;
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

@media (max-width: 991.98px) {
    .package-price-card {
        width: 100%;
        text-align: left !important;
    }
}

@media (max-width: 767.98px) {
    .package-meta {
        flex-direction: column;
        gap: 0.45rem;
    }

    .package-timeline-item {
        align-items: flex-start;
    }
}
</style>
