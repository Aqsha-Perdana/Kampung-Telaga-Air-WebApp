<style>
.item-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.item-card:has(.item-checkbox:checked) {
    border-color: #0d6efd;
    background-color: #f8f9ff;
}

.nav-pills .nav-link {
    color: #6c757d;
    border-radius: 10px;
}

.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.nav-pills .nav-link:hover:not(.active) {
    background-color: #f8f9fa;
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

#cost-breakdown small {
    display: block;
    line-height: 1.8;
}

/* Profit Analysis Card Animation */
#profit-alert {
    transition: all 0.3s ease;
}

#profit-status-badge {
    font-size: 0.85rem;
    transition: all 0.3s ease;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Highlight positive profit */
.text-success {
    font-weight: 700;
}

/* Company Profit Card Enhancement */
.card.border-primary {
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.15);
}

.card.border-success {
    box-shadow: 0 4px 10px rgba(25, 135, 84, 0.15);
}

.card.border-danger {
    box-shadow: 0 4px 10px rgba(220, 53, 69, 0.15);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 4px 10px rgba(220, 53, 69, 0.15);
    }
    50% {
        box-shadow: 0 4px 20px rgba(220, 53, 69, 0.3);
    }
}
</style>
