<script>
let itineraryCount = 1;

// Add Recommended Item Function
function addRecommendedItem(type, id) {
    const tabMap = {
        boat: 'boat',
        homestay: 'homestay',
        destination: 'destinasi',
        culinary: 'kuliner',
        kiosk: 'kiosk',
    };

    const checkboxMap = {
        boat: `boat${id}`,
        homestay: `home${id}`,
        destination: `dest${id}`,
        culinary: `cul${id}`,
        kiosk: `kiosk${id}`,
    };

    const tabId = `pills-${tabMap[type] || type}-tab`;
    const tabElement = document.getElementById(tabId);

    if (!tabElement) {
        console.error(`Tab not found: ${tabId}`);
        return;
    }

    tabElement.click();

    setTimeout(() => {
        const checkboxId = checkboxMap[type];
        if (!checkboxId) {
            console.error(`Checkbox mapping not found for type: ${type}`);
            return;
        }

        const checkbox = document.getElementById(checkboxId);
        if (!checkbox) {
            console.error(`Checkbox not found: ${checkboxId}`);
            return;
        }

        if (type === 'culinary') {
            const accordionItem = checkbox.closest('.accordion-item');
            if (accordionItem) {
                const button = accordionItem.querySelector('.accordion-button');
                if (button && button.classList.contains('collapsed')) {
                    button.click();
                }
            }
        }

        if (!checkbox.checked) {
            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change'));
        }

        const targetCard = checkbox.closest('.card');
        if (targetCard) {
            targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 200);
}

// Apply Suggested Combo
function applySuggestedCombo() {
    const suggestions = @json($recommendations['suggested_combo']['items'] ?? []);
    
    for (const [type, item] of Object.entries(suggestions)) {
        addRecommendedItem(type, item.id);
    }
}

// Update duration info display
function updateDurationInfo() {
    const duration = parseInt(document.getElementById('durasi_hari').value) || 1;
    document.getElementById('package-duration-display').textContent = duration;
}

// Validate day input doesn't exceed package duration
function validateDayInput(input) {
    const duration = parseInt(document.getElementById('durasi_hari').value) || 999;
    const day = parseInt(input.value);
    
    if (day > duration) {
        alert(`Day ${day} exceeds package duration (${duration} days). Adjusted to day ${duration}.`);
        input.value = duration;
    }
    
    if (day < 1) {
        input.value = 1;
    }
}

function updateCounter(type) {
    const checked = document.querySelectorAll(`.item-card[data-type="${type}"] .item-checkbox:checked`).length;
    const badge = document.getElementById(`count-${type}`);
    if (badge) {
        badge.textContent = checked;
        badge.style.display = checked > 0 ? 'inline-block' : 'none';
    }
}

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
                        <input type="number" class="form-control day-input" name="itinerary_hari[]" value="${itineraryCount}" min="1" onchange="validateDayInput(this)">
                    </div>
                    <div class="col-md-10">
                        <label class="form-label fw-bold">Title:</label>
                        <input type="text" class="form-control mb-2" name="itinerary_judul[]">
                        <label class="form-label fw-bold">Description:</label>
                        <textarea class="form-control" name="itinerary_deskripsi[]" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newItinerary);
}

function removeItinerary(btn) {
    btn.closest('.itinerary-item').remove();
}

function toggleDiscount() {
    const tipeDiskon = document.getElementById('tipe_diskon').value;
    const nominalGroup = document.getElementById('discount_nominal_group');
    const persenGroup = document.getElementById('discount_persen_group');
    
    nominalGroup.style.display = tipeDiskon === 'nominal' ? 'block' : 'none';
    persenGroup.style.display = tipeDiskon === 'persen' ? 'block' : 'none';
    
    if (tipeDiskon !== 'nominal') document.getElementById('diskon_nominal').value = 0;
    if (tipeDiskon !== 'persen') document.getElementById('diskon_persen').value = 0;
    
    calculatePricing();
}

function calculatePricing() {
    let totalModal = 0;
    let breakdownHTML = '';
    let itemCount = 0;
    
    console.log('=== Starting Price Calculation ===');
    
    // Calculate Homestay (price x nights)
    const homestayCheckboxes = document.querySelectorAll('.homestay-checkbox:checked');
    homestayCheckboxes.forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const malamInput = card.querySelector('.homestay-malam');
        const malam = parseInt(malamInput?.value) || 1;
        const name = card.dataset.name || 'Homestay';
        
        const subtotal = price * malam;
        totalModal += subtotal;
        itemCount++;
        
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 60%;">
                    <i class="bi bi-house"></i> ${name}
                    <br><span class="text-muted">${formatRinggit(price)} &times; ${malam} night${malam > 1 ? 's' : ''}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
        
        console.log(`Homestay: ${price} x ${malam} nights = ${subtotal}`);
    });
    
    // Calculate Culinary (price x days)
    const culinaryCheckboxes = document.querySelectorAll('.culinary-checkbox:checked');
    culinaryCheckboxes.forEach(checkbox => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="culinary_hari[]"]');
        const hari = parseInt(hariInput?.value) || 1;
        const name = card.dataset.name || 'Culinary';
        
        const subtotal = price * hari;
        totalModal += subtotal;
        itemCount++;
        
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 60%;">
                    <i class="bi bi-cup-hot"></i> ${name}
                    <br><span class="text-muted">${formatRinggit(price)} &times; ${hari} day${hari > 1 ? 's' : ''}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
        
        console.log(`Culinary: ${price} x ${hari} days = ${subtotal}`);
    });
    
    // Calculate Boat (price x days)
    const boatCheckboxes = document.querySelectorAll('.boat-checkbox:checked');
    boatCheckboxes.forEach(checkbox => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="boat_hari[]"]');
        const hari = parseInt(hariInput?.value) || 1;
        const name = card.dataset.name || 'Boat';
        
        const subtotal = price * hari;
        totalModal += subtotal;
        itemCount++;
        
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 60%;">
                    <i class="bi bi-water"></i> ${name}
                    <br><span class="text-muted">${formatRinggit(price)} &times; ${hari} day${hari > 1 ? 's' : ''}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
        
        console.log(`Boat: ${price} x ${hari} days = ${subtotal}`);
    });
    
    // Calculate Kiosk (price x days)
    const kioskCheckboxes = document.querySelectorAll('.kiosk-checkbox:checked');
    kioskCheckboxes.forEach(checkbox => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="kiosk_hari[]"]');
        const hari = parseInt(hariInput?.value) || 1;
        const name = card.dataset.name || 'Kiosk';
        
        const subtotal = price * hari;
        totalModal += subtotal;
        itemCount++;
        
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 60%;">
                    <i class="bi bi-shop"></i> ${name}
                    <br><span class="text-muted">${formatRinggit(price)} &times; ${hari} day${hari > 1 ? 's' : ''}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
        
        console.log(`Kiosk: ${price} x ${hari} days = ${subtotal}`);
    });
    
    console.log(`Total Modal: ${totalModal}`);
    
    // Update display
    document.getElementById('breakdown-total').textContent = 'RM ' + formatRinggit(totalModal);
    document.getElementById('display_harga_modal').value = formatRinggit(totalModal);
    document.getElementById('harga_modal').value = totalModal;
    
    if (itemCount === 0) {
        document.getElementById('cost-breakdown').innerHTML = '<small class="text-muted">No items selected yet</small>';
    } else {
        document.getElementById('cost-breakdown').innerHTML = breakdownHTML;
    }
    
    // Calculate Final Price and Profit
    calculateProfit(totalModal);
}

function calculateProfit(modal) {
    const hargaJual = parseFloat(document.getElementById('harga_jual').value) || 0;
    const tipeDiskon = document.getElementById('tipe_diskon').value;
    let diskon = 0;
    
    if (tipeDiskon === 'nominal') {
        diskon = parseFloat(document.getElementById('diskon_nominal').value) || 0;
    } else if (tipeDiskon === 'persen') {
        const persen = parseFloat(document.getElementById('diskon_persen').value) || 0;
        diskon = (hargaJual * persen) / 100;
    }
    
    const hargaFinal = Math.max(0, hargaJual - diskon);
    const profit = hargaFinal - modal;
    const profitPersen = modal > 0 ? (profit / modal) * 100 : 0;
    
    // Display updates
    document.getElementById('display_harga_final').textContent = 'RM ' + formatRinggit(hargaFinal);
    
    // Profit Card Updates
    document.getElementById('display_final_price_profit').textContent = 'RM ' + formatRinggit(hargaFinal);
    document.getElementById('display_cost_price_profit').textContent = 'RM ' + formatRinggit(modal);
    document.getElementById('display_profit').textContent = 'RM ' + formatRinggit(profit);
    document.getElementById('display_profit_persen').textContent = profitPersen.toFixed(2) + '%';
    
    const statusBadge = document.getElementById('profit-status-badge');
    const profitAlertCard = document.getElementById('profit-alert').closest('.card');
    
    if (hargaJual > 0) {
        if (profit < 0) {
            statusBadge.className = 'badge bg-danger py-2 px-3';
            statusBadge.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Loss! Price too low';
            // change card border
            if(profitAlertCard) {
                profitAlertCard.classList.remove('border-primary', 'border-success', 'border-info', 'border-warning');
                profitAlertCard.classList.add('border-danger');
            }
        } else if (profit === 0) {
            statusBadge.className = 'badge bg-warning text-dark py-2 px-3';
            statusBadge.innerHTML = '<i class="bi bi-dash-circle"></i> Break Even (No Profit)';
            if(profitAlertCard) {
                profitAlertCard.classList.remove('border-danger', 'border-success', 'border-info');
                profitAlertCard.classList.add('border-warning');
            }
        } else if (profitPersen < 20) {
            statusBadge.innerHTML = '<i class="bi bi-graph-up"></i> Low Margin - Consider increasing price';
            statusBadge.className = 'badge bg-info py-2 px-3';
            profitAlertCard.classList.remove('border-danger', 'border-success', 'border-warning');
            profitAlertCard.classList.add('border-info');
        } else if (profitPersen >= 20 && profitPersen < 50) {
            statusBadge.innerHTML = '<i class="bi bi-check-circle"></i> Good Profit Margin';
            statusBadge.className = 'badge bg-success py-2 px-3';
            profitAlertCard.classList.remove('border-danger', 'border-info', 'border-warning');
            profitAlertCard.classList.add('border-success');
        } else {
            statusBadge.innerHTML = '<i class="bi bi-star-fill"></i> Excellent Profit Margin!';
            statusBadge.className = 'badge bg-primary py-2 px-3';
            profitAlertCard.classList.remove('border-danger', 'border-success', 'border-info', 'border-warning');
            profitAlertCard.classList.add('border-primary');
        }
    } else {
        statusBadge.className = 'badge bg-secondary py-2 px-3';
        statusBadge.innerHTML = '<i class="bi bi-info-circle"></i> Set selling price to see profit';
        if(profitAlertCard) {
            profitAlertCard.classList.remove('border-success', 'border-danger', 'border-info', 'border-warning');
            profitAlertCard.classList.add('border-primary');
        }
    }
}

// Add event listener for selling price and discount inputs
document.getElementById('harga_jual').addEventListener('input', () => calculatePricing());
document.getElementById('diskon_nominal').addEventListener('input', () => calculatePricing());
document.getElementById('diskon_persen').addEventListener('input', () => calculatePricing());

// Helper format ringgit
function formatRinggit(number) {
    return parseFloat(number).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// AI Content Generation
function generateContent(target = 'all') {
    // Collect all selected IDs
    const formData = new FormData(document.querySelector('form'));
    const data = Object.fromEntries(formData.entries());
    
    // Add array data correctly using getAll
    const arrayFields = ['destinasi_ids[]', 'homestay_ids[]', 'boat_ids[]', 'culinary_paket_ids[]', 'kiosk_ids[]', 
                        'destinasi_hari[]', 'homestay_malam[]', 'boat_hari[]', 'culinary_hari[]', 'kiosk_hari[]'];
    
    // Convert to simple object for JSON
    const payload = {
        durasi_hari: document.getElementById('durasi_hari').value,
        destinasi_ids: formData.getAll('destinasi_ids[]'),
        homestay_ids: formData.getAll('homestay_ids[]'),
        boat_ids: formData.getAll('boat_ids[]'),
        culinary_paket_ids: formData.getAll('culinary_paket_ids[]'),
        kiosk_ids: formData.getAll('kiosk_ids[]'),
        destinasi_hari: formData.getAll('destinasi_hari[]'),
        boat_hari: formData.getAll('boat_hari[]'),
        culinary_hari: formData.getAll('culinary_hari[]'),
        kiosk_hari: formData.getAll('kiosk_hari[]'),
        _token: '{{ csrf_token() }}'
    };

    // Show loading state
    const originalText = event.target.innerHTML;
    event.target.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
    event.target.disabled = true;

    fetch('{{ route("paket-wisata.generate-content") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update Description
            if (target === 'all' || target === 'description') {
                const descArea = document.getElementById('deskripsi');
                // Append if not empty, or replace? Let's append if there's text, or replace if generated
                 descArea.value = data.description;
            }

            // Update Itinerary
            if (target === 'all' || target === 'itinerary') {
                const container = document.getElementById('itinerary-container');
                container.innerHTML = ''; // Clear existing
                itineraryCount = 0; // Reset count
                
                data.itinerary.forEach(day => {
                    itineraryCount++;
                    const newItinerary = `
                        <div class="itinerary-item card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6 class="mb-0">Day ${day.day}</h6>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary(this)">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Day:</label>
                                        <input type="number" class="form-control day-input" name="itinerary_hari[]" value="${day.day}" min="1" onchange="validateDayInput(this)">
                                    </div>
                                    <div class="col-md-10">
                                        <label class="form-label fw-bold">Title:</label>
                                        <input type="text" class="form-control mb-2" name="itinerary_judul[]" value="${day.title}">
                                        <label class="form-label fw-bold">Description:</label>
                                        <textarea class="form-control" name="itinerary_deskripsi[]" rows="2">${day.description}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', newItinerary);
                });
            }
            
            // Show toast/alert
           // alert('Content generated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating content.');
    })
    .finally(() => {
        // Restore button state
        event.target.innerHTML = originalText;
        event.target.disabled = false;
    });
}

// Event listeners for real-time calculation
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing...');
    
    // Initialize counters
    ['destinasi', 'homestay', 'kuliner', 'boat', 'kiosk'].forEach(type => {
        updateCounter(type);
    });
    
    // Initialize duration display
    updateDurationInfo();
    
    // Add event listeners for pricing inputs
    const hargaJualInput = document.getElementById('harga_jual');
    const diskonNominalInput = document.getElementById('diskon_nominal');
    const diskonPersenInput = document.getElementById('diskon_persen');
    
    if (hargaJualInput) hargaJualInput.addEventListener('input', calculatePricing);
    if (diskonNominalInput) diskonNominalInput.addEventListener('input', calculatePricing);
    if (diskonPersenInput) diskonPersenInput.addEventListener('input', calculatePricing);
    
    // Add event listeners for quantity changes (days/nights)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('homestay-malam') ||
            e.target.name === 'culinary_hari[]' ||
            e.target.name === 'boat_hari[]' ||
            e.target.name === 'kiosk_hari[]') {
            calculatePricing();
        }
    });
    
    // Also trigger on input for immediate feedback
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('homestay-malam') ||
            e.target.name === 'culinary_hari[]' ||
            e.target.name === 'boat_hari[]' ||
            e.target.name === 'kiosk_hari[]') {
            calculatePricing();
        }
    });
    
    // Initial calculation
    calculatePricing();
    
    console.log('Initialization complete');
});
</script>


