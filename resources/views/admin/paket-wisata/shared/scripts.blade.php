<script>
let itineraryCount = {{ max(count($itineraryItems ?? []), 1) }};
const PACKAGE_BUFFER_PERCENTAGE = {{ package_fee_buffer_percentage() }};
let _lastRecommendedPrice = 0;
let _recommendedPriceRequestToken = 0;

function getItineraryItems() {
    return Array.from(document.querySelectorAll('#itinerary-container .itinerary-item'));
}

function renderItineraryEmptyState() {
    return `
        <div class="empty-builder-state" id="itinerary-empty-state">
            <div class="mb-3">
                <i class="bi bi-journal-text" style="font-size: 2rem; color: var(--package-accent);"></i>
            </div>
            <h6 class="fw-bold mb-2">No itinerary days yet</h6>
            <p class="mb-3">Add a day manually or ask AI to draft the day-by-day flow from the resources you selected.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateContent('itinerary', this)">
                    <i class="bi bi-stars"></i> AI Generate Itinerary
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="addItinerary()">
                    <i class="bi bi-plus-circle"></i> Add First Day
                </button>
            </div>
        </div>
    `;
}

function syncItineraryEmptyState() {
    const container = document.getElementById('itinerary-container');
    if (!container) {
        return;
    }

    const items = getItineraryItems();
    const emptyState = document.getElementById('itinerary-empty-state');

    if (items.length === 0 && !emptyState) {
        container.innerHTML = renderItineraryEmptyState();
    }

    if (items.length > 0 && emptyState) {
        emptyState.remove();
    }
}

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

async function calculateRecommendedPrice() {
    const costPrice = parseFloat((document.getElementById('display_harga_modal')?.value || '0').replace(/,/g, '')) || 0;
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
                Accept: 'application/json',
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

    card.querySelectorAll('input[type="number"]:not([type="checkbox"])').forEach((input) => {
        input.disabled = !checkbox.checked;
        if (checkbox.checked && (!input.value || parseInt(input.value, 10) < 1)) {
            input.value = 1;
        }
    });

    card.classList.toggle('checked', checkbox.checked);
    updateCounter(card.dataset.type);
    calculatePricing();
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
    document.getElementById(tabId)?.click();

    setTimeout(() => {
        const checkbox = document.getElementById(checkboxMap[type]);
        if (!checkbox) {
            return;
        }

        if (type === 'culinary') {
            const accordionItem = checkbox.closest('.accordion-item');
            const button = accordionItem?.querySelector('.accordion-button.collapsed');
            button?.click();
        }

        if (!checkbox.checked) {
            checkbox.checked = true;
            toggleItemInputs(checkbox);
        }

        const itemCard = checkbox.closest('.item-card');
        if (itemCard) {
            itemCard.classList.add('checked');
            itemCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => itemCard.classList.remove('recommendation-pulse'), 900);
            requestAnimationFrame(() => itemCard.classList.add('recommendation-pulse'));
        }
    }, 180);
}

function applySuggestedCombo() {
    const suggestions = @json($recommendations['suggested_combo']['items'] ?? []);
    Object.entries(suggestions).forEach(([type, item]) => addRecommendedItem(type, item.id));
}

function updateDurationInfo() {
    const duration = parseInt(document.getElementById('durasi_hari')?.value, 10) || 1;
    const display = document.getElementById('package-duration-display');
    const summary = document.getElementById('summary-duration');

    if (display) {
        display.textContent = duration;
    }

    if (summary) {
        summary.textContent = duration + ' day' + (duration > 1 ? 's' : '');
    }

    document.querySelectorAll('.day-input').forEach((input) => validateDayInput(input));
    refreshItineraryLabels();
}

function validateDayInput(input) {
    const duration = parseInt(document.getElementById('durasi_hari')?.value, 10) || 999;
    const day = parseInt(input.value, 10);

    if (day > duration) {
        input.value = duration;
    }

    if (day < 1 || Number.isNaN(day)) {
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

function refreshItineraryLabels() {
    syncItineraryEmptyState();

    const items = getItineraryItems();
    const countTarget = document.getElementById('summary-itinerary-count');

    items.forEach((item, index) => {
        const heading = item.querySelector('[data-itinerary-title]');
        const dayInput = item.querySelector('input[name="itinerary_hari[]"]');
        const currentDay = parseInt(dayInput?.value, 10) || index + 1;

        if (heading) {
            heading.textContent = 'Day ' + currentDay;
        }
    });

    if (countTarget) {
        countTarget.textContent = items.length;
    }
}

function addItinerary(prefill = null) {
    itineraryCount++;

    const itineraryDay = prefill?.day || itineraryCount;
    const itineraryTitle = prefill?.title || '';
    const itineraryDescription = prefill?.description || '';

    const container = document.getElementById('itinerary-container');
    document.getElementById('itinerary-empty-state')?.remove();
    const markup = `
        <div class="itinerary-item card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="itinerary-day-chip"><i class="bi bi-calendar-event"></i> <span data-itinerary-title>Day ${itineraryDay}</span></span>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItinerary(this)">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Day</label>
                        <input type="number" class="form-control day-input" name="itinerary_hari[]" value="${itineraryDay}" min="1" onchange="validateDayInput(this); refreshItineraryLabels();">
                    </div>
                    <div class="col-md-10">
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" class="form-control mb-2" name="itinerary_judul[]" value="${itineraryTitle}" placeholder="Example: Arrival & sightseeing">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="itinerary_deskripsi[]" rows="3" placeholder="Describe the flow for this day...">${itineraryDescription}</textarea>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', markup);
    refreshItineraryLabels();
}

function removeItinerary(button) {
    button.closest('.itinerary-item')?.remove();
    refreshItineraryLabels();
}

function toggleDiscount() {
    const discountType = document.getElementById('tipe_diskon')?.value;
    const nominalGroup = document.getElementById('discount_nominal_group');
    const persenGroup = document.getElementById('discount_persen_group');

    if (nominalGroup) {
        nominalGroup.style.display = discountType === 'nominal' ? 'block' : 'none';
    }

    if (persenGroup) {
        persenGroup.style.display = discountType === 'persen' ? 'block' : 'none';
    }

    if (discountType !== 'nominal' && document.getElementById('diskon_nominal')) {
        document.getElementById('diskon_nominal').value = 0;
    }

    if (discountType !== 'persen' && document.getElementById('diskon_persen')) {
        document.getElementById('diskon_persen').value = 0;
    }

    calculatePricing();
}

function updateSelectionSummary(totalModal = 0) {
    const selectedItems = document.querySelectorAll('.item-card .item-checkbox:checked').length;
    const selectedTarget = document.getElementById('summary-selected-count');
    const costTarget = document.getElementById('summary-cost-preview');

    if (selectedTarget) {
        selectedTarget.textContent = selectedItems;
    }

    if (costTarget) {
        costTarget.textContent = 'RM ' + formatRinggit(totalModal);
    }

    const itineraryTarget = document.getElementById('summary-itinerary-count');
    if (itineraryTarget) {
        itineraryTarget.textContent = getItineraryItems().length;
    }
}

function calculatePricing() {
    let totalModal = 0;
    let breakdownHTML = '';
    let itemCount = 0;

    const addBreakdownLine = (icon, name, description, subtotal) => {
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 62%;">
                    <i class="bi ${icon}"></i> ${name}
                    <br><span class="text-muted">${description}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
    };

    document.querySelectorAll('.homestay-checkbox:checked').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const nights = parseInt(card.querySelector('.homestay-malam')?.value, 10) || 1;
        const subtotal = price * nights;

        totalModal += subtotal;
        itemCount++;
        addBreakdownLine('bi-house', card.dataset.name || 'Homestay', `${formatRinggit(price)} x ${nights} night${nights > 1 ? 's' : ''}`, subtotal);
    });

    document.querySelectorAll('.culinary-checkbox:checked').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const day = parseInt(card.querySelector('input[name="culinary_hari[]"]')?.value, 10) || 1;
        const subtotal = price * day;

        totalModal += subtotal;
        itemCount++;
        addBreakdownLine('bi-cup-hot', card.dataset.name || 'Culinary', `${formatRinggit(price)} x ${day} day${day > 1 ? 's' : ''}`, subtotal);
    });

    document.querySelectorAll('.boat-checkbox:checked').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const day = parseInt(card.querySelector('input[name="boat_hari[]"]')?.value, 10) || 1;
        const subtotal = price * day;

        totalModal += subtotal;
        itemCount++;
        addBreakdownLine('bi-water', card.dataset.name || 'Boat', `${formatRinggit(price)} x ${day} day${day > 1 ? 's' : ''}`, subtotal);
    });

    document.querySelectorAll('.kiosk-checkbox:checked').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const day = parseInt(card.querySelector('input[name="kiosk_hari[]"]')?.value, 10) || 1;
        const subtotal = price * day;

        totalModal += subtotal;
        itemCount++;
        addBreakdownLine('bi-shop', card.dataset.name || 'Kiosk', `${formatRinggit(price)} x ${day} day${day > 1 ? 's' : ''}`, subtotal);
    });

    const breakdownTotal = document.getElementById('breakdown-total');
    const modalInput = document.getElementById('display_harga_modal');
    const breakdownContainer = document.getElementById('cost-breakdown');

    if (breakdownTotal) {
        breakdownTotal.textContent = 'RM ' + formatRinggit(totalModal);
    }

    if (modalInput) {
        modalInput.value = formatRinggit(totalModal);
    }

    if (breakdownContainer) {
        breakdownContainer.innerHTML = itemCount === 0
            ? '<small class="text-muted">No billable resources selected yet.</small>'
            : breakdownHTML;
    }

    updateSelectionSummary(totalModal);
    calculateProfit(totalModal);
    calculateRecommendedPrice();
}

function calculateProfit(modal) {
    const sellingPrice = parseFloat(document.getElementById('harga_jual')?.value) || 0;
    const discountType = document.getElementById('tipe_diskon')?.value;
    let discount = 0;

    if (discountType === 'nominal') {
        discount = parseFloat(document.getElementById('diskon_nominal')?.value) || 0;
    } else if (discountType === 'persen') {
        const percent = parseFloat(document.getElementById('diskon_persen')?.value) || 0;
        discount = (sellingPrice * percent) / 100;
    }

    const finalPrice = Math.max(0, sellingPrice - discount);
    const grossProfit = finalPrice - modal;
    const profitMargin = modal > 0 ? (grossProfit / modal) * 100 : 0;
    const pricingBuffer = modal > 0 ? modal * PACKAGE_BUFFER_PERCENTAGE : 0;
    const netProfitAfterFee = grossProfit - pricingBuffer;

    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    };

    setText('display_harga_final', 'RM ' + formatRinggit(finalPrice));
    setText('display_final_price_profit', 'RM ' + formatRinggit(finalPrice));
    setText('display_cost_price_profit', 'RM ' + formatRinggit(modal));
    setText('display_pricing_buffer', 'RM ' + formatRinggit(pricingBuffer));
    setText('display_profit', 'RM ' + formatRinggit(grossProfit));
    setText('display_net_profit_after_fee', 'RM ' + formatRinggit(netProfitAfterFee));
    setText('display_profit_persen', profitMargin.toFixed(2) + '%');

    const statusBadge = document.getElementById('profit-status-badge');
    const profitAlertCard = document.getElementById('profit-analysis-card');

    if (!statusBadge || !profitAlertCard) {
        return;
    }

    if (sellingPrice <= 0) {
        statusBadge.className = 'badge bg-secondary py-2 px-3 w-100 text-wrap lh-base';
        statusBadge.innerHTML = '<i class="bi bi-info-circle"></i> Set a selling price to preview the margin.';
        profitAlertCard.classList.remove('border-success', 'border-danger', 'border-info', 'border-warning');
        profitAlertCard.classList.add('border-primary');
        return;
    }

    if (grossProfit < 0) {
        statusBadge.className = 'badge bg-danger py-2 px-3 w-100 text-wrap lh-base';
        statusBadge.innerHTML = '<i class="bi bi-exclamation-triangle"></i> The package is currently selling below cost.';
        profitAlertCard.classList.remove('border-primary', 'border-success', 'border-info', 'border-warning');
        profitAlertCard.classList.add('border-danger');
    } else if (grossProfit === 0) {
        statusBadge.className = 'badge bg-warning text-dark py-2 px-3 w-100 text-wrap lh-base';
        statusBadge.innerHTML = '<i class="bi bi-dash-circle"></i> Break-even price. There is no gross profit yet.';
        profitAlertCard.classList.remove('border-primary', 'border-danger', 'border-success', 'border-info');
        profitAlertCard.classList.add('border-warning');
    } else if (profitMargin < 20) {
        statusBadge.className = 'badge bg-info py-2 px-3 w-100 text-wrap lh-base';
        statusBadge.innerHTML = '<i class="bi bi-graph-up"></i> Margin is positive, but still on the lean side.';
        profitAlertCard.classList.remove('border-primary', 'border-danger', 'border-success', 'border-warning');
        profitAlertCard.classList.add('border-info');
    } else {
        statusBadge.className = 'badge bg-success py-2 px-3 w-100 text-wrap lh-base';
        statusBadge.innerHTML = '<i class="bi bi-check-circle"></i> Margin looks healthy for this package.';
        profitAlertCard.classList.remove('border-primary', 'border-danger', 'border-info', 'border-warning');
        profitAlertCard.classList.add('border-success');
    }
}

function formatRinggit(number) {
    return parseFloat(number || 0).toLocaleString('en-MY', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function generateContent(target = 'all', triggerButton = null) {
    const button = triggerButton || document.activeElement;
    const payload = {
        durasi_hari: document.getElementById('durasi_hari')?.value,
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

                data.itinerary.forEach((day) => addItinerary(day));
                refreshItineraryLabels();
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

function filterResourceItems() {
    const query = (document.getElementById('resource-search-input')?.value || '').trim().toLowerCase();
    const activePane = document.querySelector('#pills-tabContent .tab-pane.show.active');

    if (!activePane) {
        return;
    }

    activePane.querySelectorAll('.resource-searchable').forEach((item) => {
        const matches = query === '' || (item.dataset.search || '').toLowerCase().includes(query);
        item.style.display = matches ? '' : 'none';
    });

    activePane.querySelectorAll('.accordion-item').forEach((accordionItem) => {
        const searchableChildren = accordionItem.querySelectorAll('.resource-searchable');
        if (!searchableChildren.length) {
            return;
        }

        const hasVisibleChild = Array.from(searchableChildren).some((item) => item.style.display !== 'none');
        accordionItem.style.display = hasVisibleChild ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    ['destinasi', 'homestay', 'kuliner', 'boat', 'kiosk'].forEach((type) => updateCounter(type));

    document.querySelectorAll('.item-checkbox').forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        if (!card) {
            return;
        }

        card.querySelectorAll('input[type="number"]').forEach((input) => {
            input.disabled = !checkbox.checked;
        });

        card.classList.toggle('checked', checkbox.checked);
    });

    updateDurationInfo();
    refreshItineraryLabels();
    toggleDiscount();

    document.getElementById('resource-search-input')?.addEventListener('input', filterResourceItems);
    document.querySelectorAll('[data-bs-toggle="pill"]').forEach((tab) => {
        tab.addEventListener('shown.bs.tab', () => {
            document.getElementById('resource-search-input').value = '';
            filterResourceItems();
        });
    });

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
            event.target.id === 'harga_jual' ||
            event.target.id === 'diskon_nominal' ||
            event.target.id === 'diskon_persen' ||
            event.target.name === 'culinary_hari[]' ||
            event.target.name === 'boat_hari[]' ||
            event.target.name === 'kiosk_hari[]'
        ) {
            calculatePricing();
        }
    });

    calculatePricing();
    syncItineraryEmptyState();
    updateSelectionSummary(parseFloat((document.getElementById('display_harga_modal')?.value || '0').replace(/,/g, '')) || 0);
    filterResourceItems();
});
</script>
