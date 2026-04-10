<style>
:root {
    --package-accent: #1f6feb;
    --package-accent-soft: #edf4ff;
    --package-ink: #133b63;
    --package-muted: #6c7c93;
    --package-surface: #f7f9fc;
    --package-border: #d9e4f1;
    --package-success: #1f9d72;
    --package-warning: #c47b16;
}

.package-hero {
    background:
        radial-gradient(circle at top right, rgba(31, 111, 235, 0.16), transparent 34%),
        linear-gradient(135deg, #ffffff 0%, #f4f8fd 100%);
    border: 1px solid var(--package-border);
    border-radius: 24px;
    padding: 1.5rem 1.75rem;
    box-shadow: 0 16px 40px rgba(31, 59, 99, 0.08);
}

.package-hero-title {
    color: var(--package-ink);
    font-size: clamp(1.6rem, 2vw, 2.2rem);
}

.package-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: var(--package-accent-soft);
    color: var(--package-accent);
    border-radius: 999px;
    padding: 0.45rem 0.85rem;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.package-hero-stat {
    border: 1px solid var(--package-border);
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.85);
    padding: 0.95rem 1rem;
    min-width: 160px;
}

.package-hero-stat strong {
    color: var(--package-ink);
    font-size: 1.15rem;
}

.package-sidebar {
    position: sticky;
    top: 88px;
}

.package-panel {
    border: 1px solid var(--package-border);
    border-radius: 22px;
    box-shadow: 0 10px 26px rgba(18, 56, 95, 0.06);
    overflow: hidden;
}

.package-panel + .package-panel {
    margin-top: 1rem;
}

.package-panel-header {
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #fcfdff 0%, #eff5fb 100%);
    border-bottom: 1px solid var(--package-border);
}

.package-panel-body {
    padding: 1.25rem;
    background: #fff;
}

.package-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
}

.package-mini-stat {
    border: 1px solid var(--package-border);
    border-radius: 16px;
    background: var(--package-surface);
    padding: 0.85rem 1rem;
}

.package-mini-stat .label {
    display: block;
    color: var(--package-muted);
    font-size: 0.78rem;
    margin-bottom: 0.2rem;
}

.package-mini-stat .value {
    color: var(--package-ink);
    font-weight: 700;
}

.package-helper {
    border-radius: 16px;
    background: #f5fbff;
    border: 1px solid #cfe8ff;
    color: #205072;
    padding: 0.95rem 1rem;
    font-size: 0.9rem;
}

.package-workspace {
    border: 1px solid var(--package-border);
    border-radius: 24px;
    box-shadow: 0 12px 32px rgba(18, 56, 95, 0.06);
    overflow: hidden;
}

.package-workspace-top {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.selection-overview {
    margin-bottom: 1rem;
}

.selection-overview .overview-card {
    border: 1px solid var(--package-border);
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    padding: 1rem 1rem 0.9rem;
    height: 100%;
}

.selection-overview .overview-card .label {
    display: block;
    color: var(--package-muted);
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.35rem;
}

.selection-overview .overview-card .value {
    color: var(--package-ink);
    font-weight: 800;
    font-size: 1.15rem;
}

.resource-toolbar {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.resource-search {
    max-width: 360px;
}

.resource-tabs-shell {
    position: sticky;
    top: 76px;
    z-index: 10;
    background: #fff;
    padding: 0.75rem 0 0.9rem;
    border-bottom: 1px solid var(--package-border);
    margin-bottom: 1.25rem;
}

.resource-tabs-shell .nav-pills {
    gap: 0.65rem;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding-bottom: 0.25rem;
}

.resource-tabs-shell .nav-link {
    color: var(--package-muted);
    border-radius: 999px;
    border: 1px solid transparent;
    background: #f5f8fc;
    white-space: nowrap;
    font-weight: 600;
    padding: 0.7rem 1rem;
}

.resource-tabs-shell .nav-link.active {
    background: linear-gradient(135deg, #1f6feb 0%, #5e96ff 100%);
    color: #fff;
    box-shadow: 0 10px 20px rgba(31, 111, 235, 0.24);
}

.resource-tabs-shell .nav-link .badge {
    background: rgba(255, 255, 255, 0.2) !important;
    color: inherit;
}

.resource-pane-intro {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}

.resource-pane-intro p {
    margin: 0;
    color: var(--package-muted);
}

.item-card,
.itinerary-item {
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background-color 0.25s ease;
    border: 1px solid var(--package-border);
    border-radius: 18px;
    background: #fff;
}

.item-card:hover,
.itinerary-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(18, 56, 95, 0.08);
}

.item-card.checked,
.item-card:has(.item-checkbox:checked) {
    border-color: #89b5ff;
    background: #f7fbff;
    box-shadow: 0 12px 24px rgba(31, 111, 235, 0.12);
}

.item-card.recommendation-pulse {
    animation: recommendationPulse 0.9s ease;
}

.item-card .card-body {
    padding: 1rem;
}

.item-card-visual {
    width: 100%;
    height: 126px;
    object-fit: cover;
    border-radius: 14px;
    margin: 0.75rem 0;
}

.item-meta-badges {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
    margin-bottom: 0.75rem;
}

.item-meta-badges .badge {
    border-radius: 999px;
    padding: 0.45rem 0.65rem;
}

.recommendation-panel {
    border: 1px solid var(--package-border);
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 10px 28px rgba(18, 56, 95, 0.06);
}

.recommendation-summary-card {
    border-radius: 18px;
    padding: 1rem;
    text-align: center;
    border: 1px solid transparent;
}

.recommendation-summary-card strong {
    display: block;
    font-size: 1.4rem;
}

.itinerary-header-card {
    border: 1px solid var(--package-border);
    border-radius: 18px;
    background: linear-gradient(135deg, #f7fbff 0%, #fff9ef 100%);
    padding: 1rem 1.1rem;
    margin-bottom: 1rem;
}

.itinerary-item .card-body {
    padding: 1rem 1.1rem;
}

.itinerary-day-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: var(--package-accent-soft);
    color: var(--package-accent);
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.45rem 0.75rem;
}

.empty-builder-state {
    border: 1px dashed var(--package-border);
    border-radius: 18px;
    background: var(--package-surface);
    padding: 2rem 1.25rem;
    text-align: center;
    color: var(--package-muted);
}

#cost-breakdown small {
    display: block;
    line-height: 1.65;
}

#profit-alert,
#profit-status-badge {
    transition: all 0.25s ease;
}

@keyframes recommendationPulse {
    0% {
        transform: translateY(0);
        box-shadow: 0 0 0 0 rgba(31, 111, 235, 0.22);
    }

    40% {
        transform: translateY(-4px);
        box-shadow: 0 0 0 12px rgba(31, 111, 235, 0);
    }

    100% {
        transform: translateY(0);
        box-shadow: 0 0 0 0 rgba(31, 111, 235, 0);
    }
}

@media (max-width: 991.98px) {
    .package-sidebar,
    .resource-tabs-shell {
        position: static;
        top: auto;
    }

    .package-meta-grid {
        grid-template-columns: 1fr;
    }
}
</style>
