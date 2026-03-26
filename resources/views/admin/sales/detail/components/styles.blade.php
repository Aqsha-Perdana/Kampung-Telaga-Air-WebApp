@push('styles')
<style>
@media print {
    .btn, .card-header, nav, .no-print {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}
.breakdown-section .card {
    transition: transform 0.2s;
}
.breakdown-section .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.refund-control-card .card-header {
    background: linear-gradient(135deg, #0f766e, #16a34a);
    color: #fff;
}
.refund-note {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f8fafc;
}
.refund-breakdown {
    padding: 0.75rem;
    border-radius: 10px;
    border: 1px dashed #cbd5e1;
    background: #fcfcfd;
}
.admin-order-timeline {
    position: relative;
}
.admin-order-timeline::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 12px;
    bottom: 12px;
    width: 2px;
    background: #d7deea;
}
.admin-order-timeline .timeline-item {
    position: relative;
    display: flex;
    gap: 1rem;
    padding-bottom: 1.5rem;
}
.admin-order-timeline .timeline-item:last-child {
    padding-bottom: 0;
}
.admin-order-timeline .timeline-marker {
    position: relative;
    z-index: 1;
    width: 40px;
    height: 40px;
    border-radius: 999px;
    background: #eef2ff;
    color: #64748b;
    border: 2px solid #d7deea;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.admin-order-timeline .timeline-item.is-completed .timeline-marker {
    background: #dcfce7;
    color: #15803d;
    border-color: #86efac;
}
.admin-order-timeline .timeline-item.is-active .timeline-marker {
    background: #dbeafe;
    color: #1d4ed8;
    border-color: #93c5fd;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}
.admin-order-timeline .timeline-content {
    flex: 1;
    min-width: 0;
    padding-top: 0.15rem;
}
@media (max-width: 767.98px) {
    .admin-order-timeline::before {
        left: 17px;
    }
    .admin-order-timeline .timeline-item {
        gap: 0.75rem;
    }
    .admin-order-timeline .timeline-marker {
        width: 36px;
        height: 36px;
    }
}
</style>
@endpush

