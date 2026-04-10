<style>
.checkout-shell {
    background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
}

.checkout-header h2 {
    font-weight: 700;
    color: #0f172a;
}

.checkout-kicker {
    display: inline-block;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #2563eb;
    font-weight: 700;
}

.checkout-account {
    padding: 0.65rem 0.9rem;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #e5e7eb;
}

.checkout-panel {
    border-radius: 22px;
}

.checkout-section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
}

.checkout-section-step {
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
    color: #2563eb;
    font-size: 0.88rem;
    font-weight: 700;
    flex-shrink: 0;
}

.checkout-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 1.5rem 0;
}

.payment-method-card {
    border: 1px solid #dbe2ea;
    border-radius: 16px;
    padding: 18px;
    cursor: pointer;
    transition: all 0.25s ease;
    background: #fff;
}

.payment-method-card .form-check-input {
    margin-top: 0.4rem;
}

.payment-method-card .form-check-label {
    display: block;
    padding-left: 0.5rem;
}

.payment-method-card:hover {
    border-color: #94a3b8;
    background: #fbfdff;
}

.payment-method-card:has(input[type="radio"]:checked) {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
    background: #f8fbff;
}

.payment-method-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.payment-method-icon--stripe {
    background: #eff6ff;
    color: #2563eb;
}

.payment-method-icon--xendit {
    background: #ecfdf5;
    color: #16a34a;
}

.payment-method-tag {
    padding: 0.4rem 0.65rem;
    border-radius: 999px;
    background: #f8fafc;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.checkout-muted-box {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 1rem;
    background: #fafcff;
}

.checkout-submit-button {
    min-height: 56px;
    border-radius: 14px;
}

.checkout-summary-badge {
    padding: 0.45rem 0.7rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 0.78rem;
    font-weight: 700;
}

.checkout-summary-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.checkout-summary-meta span {
    font-size: 0.88rem;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.checkout-original-price {
    color: #94a3b8;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: line-through;
}

.checkout-total-box {
    padding: 1rem;
    border-radius: 18px;
    background: #f8fafc;
}

.checkout-summary-note {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

@media (max-width: 767.98px) {
    .payment-method-card .form-check-label {
        padding-left: 0;
    }

    .payment-method-tag {
        display: none;
    }

    .checkout-summary-meta {
        flex-direction: column;
        gap: 0.45rem;
    }
}
</style>
