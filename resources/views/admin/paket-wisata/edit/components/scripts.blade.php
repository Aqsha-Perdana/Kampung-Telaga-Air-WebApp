<script>
let itineraryCount = {{ max($paketWisata->itineraries->count(), 1) }};

function previewFoto(input) {
    const preview = document.getElementById('foto-preview');
    const img = document.getElementById('foto-preview-img');

    if (!preview || !img) {
        return;
    }

    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

const PACKAGE_BUFFER_PERCENTAGE = {{ package_fee_buffer_percentage() }};
let _lastRecommendedPrice = 0;
let _recommendedPriceRequestToken = 0;

async function calculateRecommendedPrice() {
    const costPrice = parseFloat(document.getElementById('display_harga_modal')?.value?.replace(/,/g, '')) || 0;
    const targetProfit = parseFloat(document.getElementById('target_profit')?.value) || 0;
    const card = document.getElementById('recommendation-card');

    if (!card) {
        return;
    }

    if (targetProfit <= 0 && costPrice <= 0) {
        card.style.display = 'none';
        return;
    }

    const requestToken = ++_recommendedPriceRequestToken;

    try {
        const response = await fetch('{{ route('paket-wisata.calculate-price') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                cost_price: costPrice,
                target_profit: targetProfit,
            }),
        });

        const data = await response.json();

        if (requestToken !== _recommendedPriceRequestToken) {
            return;
        }

        if (!response.ok) {
            throw new Error(data?.message || 'Failed to calculate recommended price.');
        }

        const sellingPrice = parseFloat(data.selling_price) || 0;
        const estimatedFee = parseFloat(data.estimated_fee) || 0;
        const netProfit = parseFloat(data.net_profit) || 0;

        _lastRecommendedPrice = sellingPrice;

        document.getElementById('rec_selling_price').textContent = 'RM ' + formatRinggit(sellingPrice);
        document.getElementById('rec_estimated_fee').textContent = 'RM ' + formatRinggit(estimatedFee);
        document.getElementById('rec_net_profit').textContent = 'RM ' + formatRinggit(netProfit);
        card.style.display = 'block';
    } catch (error) {
        const estimatedFee = costPrice * PACKAGE_BUFFER_PERCENTAGE;
        const rawPrice = costPrice + targetProfit + estimatedFee;
        const sellingPrice = Math.ceil(rawPrice);
        const netProfit = sellingPrice - estimatedFee - costPrice;

        _lastRecommendedPrice = sellingPrice;

        document.getElementById('rec_selling_price').textContent = 'RM ' + formatRinggit(sellingPrice);
        document.getElementById('rec_estimated_fee').textContent = 'RM ' + formatRinggit(estimatedFee);
        document.getElementById('rec_net_profit').textContent = 'RM ' + formatRinggit(netProfit);
        card.style.display = 'block';
    }
}

function applyRecommendedPrice() {
    if (_lastRecommendedPrice > 0) {
        document.getElementById('harga_jual').value = _lastRecommendedPrice;
        calculatePricing();
    }
}

function toggleItemInputs(checkbox) {
    const card = checkbox.closest('.item-card');

    if (!card) {
        return;
    }

    const inputs = card.querySelectorAll('input[type="number"]:not([type="checkbox"])');
    inputs.forEach((input) => {
        input.disabled = !checkbox.checked;
        if (checkbox.checked && (!input.value || parseInt(input.value, 10) < 1)) {
            input.value = 1;
        }
    });

    card.classList.toggle('checked', checkbox.checked);
    card.classList.toggle('border-primary', checkbox.checked);
    card.classList.toggle('shadow-sm', checkbox.checked);
}

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
        return;
    }

    tabElement.click();

    setTimeout(() => {
        const checkbox = document.getElementById(checkboxMap[type]);

        if (!checkbox) {
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

        checkbox.closest('.card')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 200);
}

function applySuggestedCombo() {
    const suggestions = @json($recommendations['suggested_combo']['items'] ?? []);

    for (const [type, item] of Object.entries(suggestions)) {
        addRecommendedItem(type, item.id);
    }
}

function updateDurationInfo() {
    const durationDisplay = document.getElementById('package-duration-display');

    if (!durationDisplay) {
        return;
    }

    const duration = parseInt(document.getElementById('durasi_hari')?.value, 10) || 1;
    durationDisplay.textContent = duration;
}

function validateDayInput(input) {
    const duration = parseInt(document.getElementById('durasi_hari')?.value, 10) || 999;
    const day = parseInt(input.value, 10);

    if (day > duration) {
        alert(`Day ${day} exceeds package duration (${duration} days). Adjusted to day ${duration}.`);
        input.value = duration;
    }

    if (day < 1) {
        input.value = 1;
    }
}

function updateCounter(type) {
    const badge = document.getElementById(`count-${type}`);

    if (!badge) {
        return;
    }

    const checked = document.querySelectorAll(`.item-card[data-type="${type}"] .item-checkbox:checked`).length;
    badge.textContent = checked;
    badge.style.display = checked > 0 ? 'inline-block' : 'none';
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

function removeItinerary(button) {
    button.closest('.itinerary-item')?.remove();
}

function toggleDiscount() {
    const tipeDiskon = document.getElementById('tipe_diskon').value;
    const nominalGroup = document.getElementById('discount_nominal_group');
    const persenGroup = document.getElementById('discount_persen_group');

    nominalGroup.style.display = tipeDiskon === 'nominal' ? 'block' : 'none';
    persenGroup.style.display = tipeDiskon === 'persen' ? 'block' : 'none';

    if (tipeDiskon !== 'nominal') {
        document.getElementById('diskon_nominal').value = 0;
    }

    if (tipeDiskon !== 'persen') {
        document.getElementById('diskon_persen').value = 0;
    }

    calculatePricing();
}

function calculatePricing() {
    let totalModal = 0;
    let breakdownHTML = '';
    let itemCount = 0;

    document.querySelectorAll('.homestay-checkbox:checked').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const malamInput = card.querySelector('.homestay-malam');
        const malam = parseInt(malamInput?.value, 10) || 1;
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
    });

    document.querySelectorAll('.culinary-checkbox:checked').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="culinary_hari[]"]');
        const hari = parseInt(hariInput?.value, 10) || 1;
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
    });

    document.querySelectorAll('.boat-checkbox:checked').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="boat_hari[]"]');
        const hari = parseInt(hariInput?.value, 10) || 1;
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
    });

    document.querySelectorAll('.kiosk-checkbox:checked').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="kiosk_hari[]"]');
        const hari = parseInt(hariInput?.value, 10) || 1;
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
    });

    document.getElementById('breakdown-total').textContent = 'RM ' + formatRinggit(totalModal);
    document.getElementById('display_harga_modal').value = formatRinggit(totalModal);

    if (itemCount === 0) {
        document.getElementById('cost-breakdown').innerHTML = '<small class="text-muted">No items selected yet</small>';
    } else {
        document.getElementById('cost-breakdown').innerHTML = breakdownHTML;
    }

    calculateProfit(totalModal);
    calculateRecommendedPrice();
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
    const pricingBuffer = modal > 0 ? modal * PACKAGE_BUFFER_PERCENTAGE : 0;
    const netProfitAfterFee = profit - pricingBuffer;

    document.getElementById('display_harga_final').textContent = 'RM ' + formatRinggit(hargaFinal);
    document.getElementById('display_final_price_profit').textContent = 'RM ' + formatRinggit(hargaFinal);
    document.getElementById('display_cost_price_profit').textContent = 'RM ' + formatRinggit(modal);
    document.getElementById('display_pricing_buffer').textContent = 'RM ' + formatRinggit(pricingBuffer);
    document.getElementById('display_profit').textContent = 'RM ' + formatRinggit(profit);
    document.getElementById('display_net_profit_after_fee').textContent = 'RM ' + formatRinggit(netProfitAfterFee);
    document.getElementById('display_profit_persen').textContent = profitPersen.toFixed(2) + '%';

    const statusBadge = document.getElementById('profit-status-badge');
    const profitAlertCard = document.getElementById('profit-alert')?.closest('.card');

    if (!statusBadge || !profitAlertCard) {
        return;
    }

    if (hargaJual > 0) {
        if (profit < 0) {
            statusBadge.className = 'badge bg-danger py-2 px-3 w-100 text-wrap lh-base';
            statusBadge.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Loss! Price too low';
            profitAlertCard.classList.remove('border-primary', 'border-success', 'border-info', 'border-warning');
            profitAlertCard.classList.add('border-danger');
        } else if (profit === 0) {
            statusBadge.className = 'badge bg-warning text-dark py-2 px-3 w-100 text-wrap lh-base';
            statusBadge.innerHTML = '<i class="bi bi-dash-circle"></i> Break Even (No Profit)';
            profitAlertCard.classList.remove('border-danger', 'border-success', 'border-info');
            profitAlertCard.classList.add('border-warning');
        } else if (profitPersen < 20) {
            statusBadge.className = 'badge bg-info py-2 px-3 w-100 text-wrap lh-base';
            statusBadge.innerHTML = '<i class="bi bi-graph-up"></i> Low Margin - Consider increasing price';
            profitAlertCard.classList.remove('border-danger', 'border-success', 'border-warning');
            profitAlertCard.classList.add('border-info');
        } else if (profitPersen < 50) {
            statusBadge.className = 'badge bg-success py-2 px-3 w-100 text-wrap lh-base';
            statusBadge.innerHTML = '<i class="bi bi-check-circle"></i> Good Profit Margin';
            profitAlertCard.classList.remove('border-danger', 'border-info', 'border-warning');
            profitAlertCard.classList.add('border-success');
        } else {
            statusBadge.className = 'badge bg-primary py-2 px-3 w-100 text-wrap lh-base';
            statusBadge.innerHTML = '<i class="bi bi-star-fill"></i> Excellent Profit Margin!';
            profitAlertCard.classList.remove('border-danger', 'border-success', 'border-info', 'border-warning');
            profitAlertCard.classList.add('border-primary');
        }
    } else {
        statusBadge.className = 'badge bg-secondary py-2 px-3 w-100 text-wrap lh-base';
        statusBadge.innerHTML = '<i class="bi bi-info-circle"></i> Set selling price to see profit';
        profitAlertCard.classList.remove('border-success', 'border-danger', 'border-info', 'border-warning');
        profitAlertCard.classList.add('border-primary');
    }
}

function formatRinggit(number) {
    return parseFloat(number).toLocaleString('en-MY', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function generateContent(target = 'all', triggerButton = null) {
    const button = triggerButton || document.activeElement;
    const payload = {
        durasi_hari: document.getElementById('durasi_hari').value,
        destinasi_ids: Array.from(document.querySelectorAll('input[name="destinasi_ids[]"]:checked')).map((el) => el.value),
        homestay_ids: Array.from(document.querySelectorAll('input[name="homestay_ids[]"]:checked')).map((el) => el.value),
        boat_ids: Array.from(document.querySelectorAll('input[name="boat_ids[]"]:checked')).map((el) => el.value),
        culinary_paket_ids: Array.from(document.querySelectorAll('input[name="culinary_paket_ids[]"]:checked')).map((el) => el.value),
        kiosk_ids: Array.from(document.querySelectorAll('input[name="kiosk_ids[]"]:checked')).map((el) => el.value),
        destinasi_hari: Array.from(document.querySelectorAll('input[name="destinasi_hari[]"]')).map((el) => el.value),
        homestay_malam: Array.from(document.querySelectorAll('input[name="homestay_malam[]"]')).map((el) => el.value),
        boat_hari: Array.from(document.querySelectorAll('input[name="boat_hari[]"]')).map((el) => el.value),
        culinary_hari: Array.from(document.querySelectorAll('input[name="culinary_hari[]"]')).map((el) => el.value),
        kiosk_hari: Array.from(document.querySelectorAll('input[name="kiosk_hari[]"]')).map((el) => el.value),
        _token: '{{ csrf_token() }}',
    };

    const originalText = button?.innerHTML;

    if (button) {
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
        button.disabled = true;
    }

    fetch('{{ route("paket-wisata.generate-content") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(payload),
    })
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to generate content.');
            }

            if (target === 'all' || target === 'description') {
                document.getElementById('deskripsi').value = data.description;
            }

            if (target === 'all' || target === 'itinerary') {
                const container = document.getElementById('itinerary-container');
                container.innerHTML = '';
                itineraryCount = 0;

                data.itinerary.forEach((day) => {
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
        })
        .catch((error) => {
            console.error(error);
            alert(error.message || 'An error occurred while generating content.');
        })
        .finally(() => {
            if (button) {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
}

document.addEventListener('DOMContentLoaded', () => {
    ['destinasi', 'homestay', 'kuliner', 'boat', 'kiosk'].forEach((type) => {
        updateCounter(type);
    });

    document.querySelectorAll('.item-checkbox').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        if (!card) {
            return;
        }

        card.querySelectorAll('input[type="number"]').forEach((input) => {
            input.disabled = !checkbox.checked;
        });

        card.classList.toggle('checked', checkbox.checked);
        card.classList.toggle('border-primary', checkbox.checked);
        card.classList.toggle('shadow-sm', checkbox.checked);
    });

    updateDurationInfo();

    document.getElementById('harga_jual')?.addEventListener('input', calculatePricing);
    document.getElementById('diskon_nominal')?.addEventListener('input', calculatePricing);
    document.getElementById('diskon_persen')?.addEventListener('input', calculatePricing);

    document.addEventListener('change', (event) => {
        if (
            event.target.classList.contains('homestay-malam') ||
            event.target.name === 'culinary_hari[]' ||
            event.target.name === 'boat_hari[]' ||
            event.target.name === 'kiosk_hari[]'
        ) {
            calculatePricing();
        }
    });

    document.addEventListener('input', (event) => {
        if (
            event.target.classList.contains('homestay-malam') ||
            event.target.name === 'culinary_hari[]' ||
            event.target.name === 'boat_hari[]' ||
            event.target.name === 'kiosk_hari[]'
        ) {
            calculatePricing();
        }
    });

    calculatePricing();
});
</script>
