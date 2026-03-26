<style>
.item-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.item-card.checked,
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

.badge {
    display: none;
}

.badge:not(:empty) {
    display: inline-block;
}
</style>
