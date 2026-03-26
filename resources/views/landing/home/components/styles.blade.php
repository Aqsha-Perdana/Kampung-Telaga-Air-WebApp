<style>
.badge-360 {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(59, 130, 246, 0.95);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.destination-card {
    position: relative;
    overflow: hidden;
}

.destination-card img {
    transition: transform 0.3s ease;
}

.destination-link:hover .destination-card img {
    transform: scale(1.05);
}
</style>
