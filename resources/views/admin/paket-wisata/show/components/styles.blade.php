<style>
/* Custom Styles */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.info-box {
    transition: all 0.3s ease;
}

.info-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.package-item {
    transition: all 0.3s ease;
}

.package-item:hover {
    transform: translateX(5px);
    background-color: #fff !important;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.timeline-badge {
    font-size: 1.2rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 25px;
    top: 60px;
    bottom: -40px;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
}

.card {
    transition: all 0.3s ease;
}

.admin-show-actions {
    margin-top: 1.5rem;
    border-radius: 1.25rem;
    overflow: hidden;
    border: 1px solid rgba(13, 110, 253, 0.08);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(250, 252, 255, 1) 100%);
}

.admin-show-actions-shell {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.admin-show-delete-form {
    display: inline-flex;
}

.admin-show-action-btn {
    min-width: 190px;
    border-radius: 0.95rem;
    padding-left: 1.35rem;
    padding-right: 1.35rem;
}

@media (max-width: 768px) {
    .admin-show-actions-shell {
        flex-direction: column;
        align-items: stretch;
    }

    .admin-show-action-btn,
    .admin-show-delete-form {
        width: 100%;
        min-width: 0;
    }

    .admin-show-actions .card-body {
        padding: 1.25rem;
    }
}
</style>
