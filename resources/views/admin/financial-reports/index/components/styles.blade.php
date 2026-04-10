<style>
.opening-cash-card {
    border: 1px solid #e9eef5;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
}

.opening-cash-shell {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
    gap: 1rem;
    align-items: start;
}

.opening-cash-summary,
.opening-cash-form-wrap {
    border: 1px solid #edf2f7;
    border-radius: 14px;
    background: #ffffff;
}

.opening-cash-summary {
    padding: 1rem 1.1rem;
}

.opening-cash-form-wrap {
    padding: 0.95rem;
}

.opening-cash-meta-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.opening-cash-eyebrow {
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    font-weight: 700;
}

.opening-cash-amount {
    color: #0f172a;
    font-weight: 700;
}

.opening-cash-caption,
.opening-cash-note,
.opening-cash-helper {
    font-size: 0.9rem;
    color: #64748b;
}

.opening-cash-note,
.opening-cash-helper {
    margin-top: 0.65rem;
}

.opening-cash-state {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.35rem 0.7rem;
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
}

.opening-cash-state.is-manual {
    background: #e0f2fe;
    color: #0369a1;
}

.opening-cash-state.is-fallback {
    background: #fef3c7;
    color: #92400e;
}

.opening-cash-history {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-top: 0.85rem;
}

.opening-cash-history-item {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.35rem 0.65rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
}

.opening-cash-form textarea {
    resize: vertical;
}

@media (max-width: 991.98px) {
    .opening-cash-shell {
        grid-template-columns: 1fr;
    }
}

@media print {
    .sidebar, .navbar, .btn, .nav-pills { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
