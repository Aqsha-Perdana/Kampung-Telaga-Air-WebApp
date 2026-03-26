<script>
let itineraryCount = {{ $paketWisata->itineraries->count() > 0 ? $paketWisata->itineraries->count() : 1 }};

// Update counter untuk badge
function updateCounter(type) {
    const checked = document.querySelectorAll(`.item-card[data-type="${type}"] .item-checkbox:checked`).length;
    const badge = document.getElementById(`count-${type}`);
    badge.textContent = checked;
    badge.style.display = checked > 0 ? 'inline-block' : 'none';
}

// Toggle discount fields
function toggleDiscount() {
    const tipeDiskon = document.getElementById('tipe_diskon').value;
    const nominalGroup = document.getElementById('discount_nominal_group');
    const persenGroup = document.getElementById('discount_persen_group');
    
    nominalGroup.style.display = 'none';
    persenGroup.style.display = 'none';
    
    if (tipeDiskon === 'nominal') {
        nominalGroup.style.display = 'block';
    } else if (tipeDiskon === 'persen') {
        persenGroup.style.display = 'block';
    }
    
    calculatePricing();
}

// Calculate pricing
function calculatePricing() {
    let totalModal = 0;
    
    // Calculate homestay prices
    document.querySelectorAll('.homestay-checkbox:checked').forEach(function(checkbox) {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.getAttribute('data-price')) || 0;
        const nights = parseInt(card.querySelector('.homestay-malam').value) || 1;
        totalModal += price * nights;
    });
    
    // Calculate culinary prices
    document.querySelectorAll('.culinary-checkbox:checked').forEach(function(checkbox) {
        const price = parseFloat(checkbox.getAttribute('data-price')) || 0;
        totalModal += price;
    });
    
    // Calculate boat prices
    document.querySelectorAll('.boat-checkbox:checked').forEach(function(checkbox) {
        const price = parseFloat(checkbox.getAttribute('data-price')) || 0;
        totalModal += price;
    });
    
    // Calculate kiosk prices
    document.querySelectorAll('.kiosk-checkbox:checked').forEach(function(checkbox) {
        const price = parseFloat(checkbox.getAttribute('data-price')) || 0;
        totalModal += price;
    });
    
    // Update modal price
    document.getElementById('harga_modal').value = totalModal.toFixed(2);
    document.getElementById('display_harga_modal').value = formatRinggit(totalModal);
    
    // Calculate final price
    const hargaJual = parseFloat(document.getElementById('harga_jual').value) || 0;
    const tipeDiskon = document.getElementById('tipe_diskon').value;
    let diskon = 0;
    
    if (tipeDiskon === 'nominal') {
        diskon = parseFloat(document.getElementById('diskon_nominal').value) || 0;
    } else if (tipeDiskon === 'persen') {
        const persenDiskon = parseFloat(document.getElementById('diskon_persen').value) || 0;
        diskon = (hargaJual * persenDiskon) / 100;
    }
    
    const hargaFinal = hargaJual - diskon;
    document.getElementById('display_harga_final').textContent = 'RM ' + formatRinggit(hargaFinal);
    
    // Calculate profit
    const profit = hargaFinal - totalModal;
    const profitPersen = totalModal > 0 ? (profit / totalModal) * 100 : 0;
    
    document.getElementById('display_profit').textContent = formatRinggit(profit);
    document.getElementById('display_profit_persen').textContent = profitPersen.toFixed(2) + '%';
}

// Format number to Ringgit
function formatRinggit(amount) {
    return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Add itinerary
function addItinerary() {
    itineraryCount++;
    const container = document.getElementById('itinerary-container');
    const newItinerary = `
        <div class="itinerary-item card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Day ${itineraryCount}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary(this)">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Day:</label>
                        <input type="number" class="form-control" name="itinerary_hari[]" value="${itineraryCount}" min="1">
                    </div>
                    <div class="col-md-10">
                        <label class="form-label fw-bold">Title:</label>
                        <input type="text" class="form-control mb-2" name="itinerary_judul[]" placeholder="Example: Arrival & Check-in">
                        <label class="form-label fw-bold">Description:</label>
                        <textarea class="form-control" name="itinerary_deskripsi[]" rows="2" placeholder="Describe activities for this day..."></textarea>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newItinerary);
}

// Remove itinerary
function removeItinerary(btn) {
    btn.closest('.itinerary-item').remove();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize counters
    ['destinasi', 'homestay', 'kuliner', 'boat', 'kiosk'].forEach(type => {
        updateCounter(type);
    });
    
    // Initial pricing calculation
    calculatePricing();
});
</script>
