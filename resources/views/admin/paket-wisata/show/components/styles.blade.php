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

.sticky-bottom {
    position: sticky;
    bottom: 20px;
    z-index: 10;
}

@media (max-width: 768px) {
    .sticky-bottom {
        position: relative;
        bottom: 0;
    }
}
</style>
