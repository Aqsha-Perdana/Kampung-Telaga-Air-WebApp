<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const detailModalEl = document.getElementById('detailModal');
    const mainWrapperEl = document.getElementById('main-wrapper');
    const unlockLeavingState = () => {
        mainWrapperEl?.classList.remove('is-leaving');
        document.querySelectorAll('.sidebar-item.is-pending').forEach(item => item.classList.remove('is-pending'));
    };

    // Ensure modal is attached directly to body so parent container effects don't block clicks.
    if (detailModalEl && detailModalEl.parentElement !== document.body) {
        document.body.appendChild(detailModalEl);
    }

    const cleanupDetailModalState = () => {
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open', 'calendar-detail-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        unlockLeavingState();
    };
    const detailModalInstance = detailModalEl
        ? bootstrap.Modal.getOrCreateInstance(detailModalEl, {
            backdrop: true,
            keyboard: true
        })
        : null;
    
    unlockLeavingState();

    if (!calendarEl) return;

    if (detailModalEl) {
        detailModalEl.addEventListener('hidden.bs.modal', () => {
            cleanupDetailModalState();
        });
    }

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week'
        },
        locale: 'en', // Set to English
        firstDay: 1,
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            fetch(`/admin/calendar/events?start=${info.startStr}&end=${info.endStr}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            showDetailModal(info.event.startStr);
        },
        datesSet: function(info) {
            updateMonthlyStats(info.view.currentStart);
        },
        eventDidMount: function(info) {
            info.el.title = `Click to view details`;
        }
    });

    calendar.render();

    // Conflict Alerts Functions
    function refreshAlerts() {
        const startDate = getLocalDateIso();
        const endDateObj = new Date();
        endDateObj.setDate(endDateObj.getDate() + 7);
        const offset = endDateObj.getTimezoneOffset() * 60000;
        const endDate = new Date(endDateObj.getTime() - offset).toISOString().split('T')[0];
        
        fetch(`/admin/calendar/conflict-alerts?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                renderAlerts(data);
            })
            .catch(error => console.error('Error fetching alerts:', error));
    }
    window.refreshAlerts = refreshAlerts;

    function renderAlerts(data) {
        // Update counts
        document.getElementById('criticalCount').textContent = data.total_critical;
        document.getElementById('warningCount').textContent = data.total_warnings;
        document.getElementById('capacityIssueCount').textContent = data.total_capacity_issues;
        
        const totalAlerts = data.total_critical + data.total_warnings + data.total_capacity_issues;
        
        if (totalAlerts === 0) {
            // Show empty state
            document.getElementById('emptyAlerts').style.display = 'block';
            document.getElementById('criticalAlertsContainer').innerHTML = '';
            document.getElementById('warningAlertsContainer').innerHTML = '';
            document.getElementById('capacityIssuesContainer').innerHTML = '';
            document.getElementById('upcomingSummaryContainer').innerHTML = '';
        } else {
            // Hide empty state
            document.getElementById('emptyAlerts').style.display = 'none';
            
            // Render critical alerts (Overbooking)
            renderCriticalAlerts(data.alerts.critical);
            
            // Render warning alerts (High Capacity)
            renderWarningAlerts(data.alerts.warnings);
            
            // Render capacity issues
            renderCapacityIssues(data.alerts.capacity_issues);
            
            // Render upcoming summary
            renderUpcomingSummary(data.upcoming_summary);
        }
    }

    function renderCriticalAlerts(alerts) {
        const container = document.getElementById('criticalAlertsContainer');
        
        if (alerts.length === 0) {
            container.innerHTML = '';
            return;
        }
        
        let html = `
            <div class="alert-section-title critical">
                <i class="ti ti-exclamation-circle"></i> OVERBOOKING (${alerts.length})
            </div>
            <table class="alert-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Resource</th>
                        <th>Conflicts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        alerts.forEach(alert => {
            const bookingsList = alert.bookings.map(b => 
                `#${b.order_id} (${b.pax}p) - ${b.customer_name}`
            ).join(' • ');
            
            html += `
                <tr class="alert-row critical">
                    <td class="alert-date-col">${alert.formatted_date}</td>
                    <td>
                        <i class="ti ti-${getResourceIcon(alert.resource_type)}"></i>
                        <strong>${alert.resource_name}</strong>
                    </td>
                    <td class="conflict-details">
                        <span class="conflict-count">${alert.bookings.length} orders:</span>
                        ${bookingsList}
                    </td>
                    <td class="actions-col">
                        <a href="/admin/sales/${alert.bookings[0].order_id}" target="_blank" class="btn-mini primary">
                            <i class="ti ti-eye"></i>
                        </a>
                        <button class="btn-mini secondary" onclick="alert('Reschedule')">
                            <i class="ti ti-calendar"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                </tbody>
            </table>
        `;
        
        container.innerHTML = html;
    }

    function renderWarningAlerts(alerts) {
        const container = document.getElementById('warningAlertsContainer');
        
        if (alerts.length === 0) {
            container.innerHTML = '';
            return;
        }
        
        let html = `
            <div class="alert-section-title warning">
                <i class="ti ti-alert-triangle"></i> OVER CAPACITY (${alerts.length})
            </div>
            <table class="alert-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Resource</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        alerts.forEach(alert => {
            html += `
                <tr class="alert-row warning">
                    <td class="alert-date-col">${alert.formatted_date}</td>
                    <td>
                        <i class="ti ti-${getResourceIcon(alert.resource_type)}"></i>
                        <strong>${alert.resource_name}</strong>
                    </td>
                    <td>
                        Cap: ${alert.resource_capacity} • Booked: ${alert.booked_pax} 
                        <span class="over-badge">(+${alert.over_capacity})</span>
                        <br><small>Order #${alert.order_id}</small>
                    </td>
                    <td class="actions-col">
                        <a href="/admin/sales/${alert.order_id}" target="_blank" class="btn-mini primary">
                            <i class="ti ti-eye"></i>
                        </a>
                    </td>
                </tr>
            `;
        });
        
        html += `
                </tbody>
            </table>
        `;
        
        container.innerHTML = html;
    }

    function renderCapacityIssues(issues) {
        const container = document.getElementById('capacityIssuesContainer');
        
        if (issues.length === 0) {
            container.innerHTML = '';
            return;
        }
        
        let html = `
            <div class="alert-section-title info">
                <i class="ti ti-chart-line"></i> HIGH UTILIZATION (${issues.length})
            </div>
            <table class="alert-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th colspan="3">Resources at 90%+ Capacity</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        issues.forEach(issue => {
            const resourcesList = issue.resources.map(r => 
                `<span class="capacity-pill high">${capitalizeFirst(r.type)}: ${r.utilization}%</span>`
            ).join(' ');
            
            html += `
                <tr class="alert-row info">
                    <td class="alert-date-col">${issue.formatted_date}</td>
                    <td colspan="3">
                        ${resourcesList}
                    </td>
                </tr>
            `;
        });
        
        html += `
                </tbody>
            </table>
        `;
        
        container.innerHTML = html;
    }

    function renderUpcomingSummary(summary) {
        const container = document.getElementById('upcomingSummaryContainer');
        
        if (summary.length === 0) {
            container.innerHTML = '';
            return;
        }
        
        let html = `
            <div class="capacity-summary">
                <div class="capacity-summary-title">⚡ 7-DAY FORECAST</div>
                <div class="forecast-grid">
        `;
        
        summary.forEach(item => {
            html += `
                <div class="forecast-item">
                    <div class="forecast-date">${item.formatted_date}</div>
                    ${item.boats >= 70 ? `<span class="capacity-pill ${item.boats >= 90 ? 'high' : 'medium'}">B:${item.boats}%</span>` : ''}
                    ${item.homestays >= 70 ? `<span class="capacity-pill ${item.homestays >= 90 ? 'high' : 'medium'}">H:${item.homestays}%</span>` : ''}
                    ${item.culinaries >= 70 ? `<span class="capacity-pill ${item.culinaries >= 90 ? 'high' : 'medium'}">C:${item.culinaries}%</span>` : ''}
                    ${item.kiosks >= 70 ? `<span class="capacity-pill ${item.kiosks >= 90 ? 'high' : 'medium'}">K:${item.kiosks}%</span>` : ''}
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    }

    function getResourceIcon(resourceType) {
        const icons = {
            'boat': 'sailboat',
            'homestay': 'home-2',
            'culinary': 'tools-kitchen-2',
            'kiosk': 'building-store'
        };
        return icons[resourceType] || 'alert-circle';
    }

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function showDetailModal(date) {
        const modalBody = document.getElementById('modalBody');
        
        modalBody.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-white"></div></div>`;
        unlockLeavingState();
        cleanupDetailModalState();
        detailModalInstance?.show();

        fetch(`/admin/calendar/date-detail?date=${date}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalTitle').innerHTML = `
                    <i class="text-white ti ti-calendar-stats"></i>
                    <span class="text-white">Sales for ${data.date}</span>
                `;
                
                let html = `
                    <div class="summary-box">
                        <div class="summary-item">
                            <span>📦 Total Packages Sold:</span>
                            <strong>${data.totalPaket} Packages</strong>
                        </div>
                        <div class="summary-item">
                            <span>👥 Total Participants:</span>
                            <strong>${data.totalPeserta} Pax</strong>
                        </div>
                        <div class="summary-item">
                            <span>💰 Total Revenue:</span>
                            <strong>RM ${numberFormat(data.totalRevenue)}</strong>
                        </div>
                    </div>
                    <div class="section-title">Order Details</div>
                `;

                if (data.items.length === 0) {
                    html += `<div class="text-center p-4 text-muted"><p>No orders for this date</p></div>`;
                } else {
                    data.items.forEach(item => {
                        html += `
                            <div class="order-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-1" style="font-weight: 700;">${item.nama_paket}</h6>
                                        <small class="text-muted">Order ID: ${item.id_order}</small>
                                    </div>
                                    <span class="badge-status bg-light text-dark border">${item.status_label}</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Customer</small>
                                        <strong>${item.customer_name}</strong>
                                        <div class="small">${item.customer_phone}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Package Details</small>
                                        <div class="small">Duration: ${item.durasi_hari} Days</div>
                                        <div class="small">Pax: ${item.jumlah_peserta} Person</div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top d-flex justify-content-between">
                                    <small class="text-muted">Ordered on: ${item.order_date}</small>
                                    <a href="/admin/sales/${item.id_order}" class="btn btn-sm btn-outline-primary" target="_blank">View Details</a>
                                </div>
                            </div>
                        `;
                    });
                }
                modalBody.innerHTML = html;
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load data.</div>`;
            });
    }

    function updateMonthlyStats(date) {
        const month = date.getMonth() + 1;
        const year = date.getFullYear();

        fetch(`/admin/calendar/statistics?month=${month}&year=${year}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalBookings').textContent = data.totalBookings;
                document.getElementById('totalPeserta').textContent = data.totalPeserta;
                document.getElementById('totalRevenue').textContent = 'RM ' + numberFormat(data.totalRevenue);
                document.getElementById('uniqueDates').textContent = data.uniqueDates;
            });
    }

    function numberFormat(num) {
        return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Resource Availability Functions
    const availabilityDateInput = document.getElementById('availabilityDate');

    function getLocalDateIso() {
        const now = new Date();
        const offset = now.getTimezoneOffset() * 60000;
        return new Date(now.getTime() - offset).toISOString().split('T')[0];
    }

    availabilityDateInput.value = getLocalDateIso();

    function updateResourceAvailability(date) {
        fetch(`/admin/calendar/resource-availability?date=${date}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Update Boats
                document.getElementById('boatsAvailable').textContent = data.boats.available;
                document.getElementById('boatsBooked').textContent = data.boats.booked;
                document.getElementById('boatsTotal').textContent = data.boats.total;
                document.getElementById('boatCapAvail').textContent = data.boats.available_capacity;
                document.getElementById('boatCapTotal').textContent = data.boats.total_capacity;
                document.getElementById('boatUtilPercent').textContent = data.boats.utilization_percent + '%';
                
                const boatProgress = document.getElementById('boatProgress');
                boatProgress.style.width = data.boats.utilization_percent + '%';
                updateUtilizationStyle('boat', data.boats.utilization_percent);
                updateResourceMeta('boatsMeta', data.boats.available_list, 'Tidak ada boat tersedia');

                // Update Homestays
                document.getElementById('homestaysAvailable').textContent = data.homestays.available;
                document.getElementById('homestaysBooked').textContent = data.homestays.booked;
                document.getElementById('homestaysTotal').textContent = data.homestays.total;
                document.getElementById('homestayCapAvail').textContent = data.homestays.available_capacity;
                document.getElementById('homestayCapTotal').textContent = data.homestays.total_capacity;
                document.getElementById('homestayUtilPercent').textContent = data.homestays.utilization_percent + '%';
                
                const homestayProgress = document.getElementById('homestayProgress');
                homestayProgress.style.width = data.homestays.utilization_percent + '%';
                updateUtilizationStyle('homestay', data.homestays.utilization_percent);
                updateResourceMeta('homestaysMeta', data.homestays.available_list, 'Tidak ada homestay tersedia');

                // Update Culinaries
                document.getElementById('culinariesAvailable').textContent = data.culinaries.available;
                document.getElementById('culinariesBooked').textContent = data.culinaries.booked;
                document.getElementById('culinariesTotal').textContent = data.culinaries.total;
                document.getElementById('culinaryCapAvail').textContent = data.culinaries.available_capacity;
                document.getElementById('culinaryCapTotal').textContent = data.culinaries.total_capacity;
                document.getElementById('culinaryUtilPercent').textContent = data.culinaries.utilization_percent + '%';
                
                const culinaryProgress = document.getElementById('culinaryProgress');
                culinaryProgress.style.width = data.culinaries.utilization_percent + '%';
                updateUtilizationStyle('culinary', data.culinaries.utilization_percent);
                updateResourceMeta('culinariesMeta', data.culinaries.available_list, 'Tidak ada culinary tersedia');

                // Update Kiosks
                document.getElementById('kiosksAvailable').textContent = data.kiosks.available;
                document.getElementById('kiosksBooked').textContent = data.kiosks.booked;
                document.getElementById('kiosksTotal').textContent = data.kiosks.total;
                document.getElementById('kioskCapAvail').textContent = data.kiosks.available_capacity;
                document.getElementById('kioskCapTotal').textContent = data.kiosks.total_capacity;
                document.getElementById('kioskUtilPercent').textContent = data.kiosks.utilization_percent + '%';
                
                const kioskProgress = document.getElementById('kioskProgress');
                kioskProgress.style.width = data.kiosks.utilization_percent + '%';
                updateUtilizationStyle('kiosk', data.kiosks.utilization_percent);
                updateResourceMeta('kiosksMeta', data.kiosks.available_list, 'Tidak ada kiosk tersedia');
            })
            .catch(error => {
                setResourceFallbackState();
                console.error('Error fetching resource availability:', error);
            });
    }

    function updateUtilizationStyle(type, percent) {
        const badge = document.getElementById(type + 'UtilBadge');
        const progress = document.getElementById(type + 'Progress');

        if (!badge || !progress) {
            return;
        }
        
        badge.classList.remove('low', 'medium', 'high');
        progress.classList.remove('low', 'medium', 'high');
        
        let level = 'low';
        if (percent >= 70 && percent < 90) level = 'medium';
        else if (percent >= 90) level = 'high';
        
        badge.classList.add(level);
        progress.classList.add(level);
        badge.textContent = percent + '% Used';
    }

    function updateResourceMeta(elementId, availableList, fallbackText) {
        const element = document.getElementById(elementId);
        if (!element) return;

        if (!Array.isArray(availableList) || availableList.length === 0) {
            element.textContent = fallbackText;
            return;
        }

        const names = availableList
            .map(item => item?.nama)
            .filter(name => typeof name === 'string' && name.trim().length > 0);

        if (names.length === 0) {
            element.textContent = fallbackText;
            return;
        }

        const preview = names.slice(0, 2).join(', ');
        const remaining = names.length - 2;
        element.textContent = remaining > 0 ? `${preview} +${remaining} lainnya` : preview;
    }

    function setResourceFallbackState() {
        const mappings = [
            { key: 'boats', singular: 'boat', emptyText: 'Gagal memuat data boat' },
            { key: 'homestays', singular: 'homestay', emptyText: 'Gagal memuat data homestay' },
            { key: 'culinaries', singular: 'culinary', emptyText: 'Gagal memuat data culinary' },
            { key: 'kiosks', singular: 'kiosk', emptyText: 'Gagal memuat data kiosk' },
        ];

        mappings.forEach(({ key, singular, emptyText }) => {
            document.getElementById(`${key}Available`).textContent = '-';
            document.getElementById(`${key}Booked`).textContent = '-';
            document.getElementById(`${key}Total`).textContent = '-';
            document.getElementById(`${singular}CapAvail`).textContent = '-';
            document.getElementById(`${singular}CapTotal`).textContent = '-';
            document.getElementById(`${singular}UtilPercent`).textContent = '0%';
            document.getElementById(`${key}Meta`).textContent = emptyText;
            const progress = document.getElementById(`${singular}Progress`);
            if (progress) progress.style.width = '0%';
            updateUtilizationStyle(singular, 0);
        });
    }

    availabilityDateInput.addEventListener('change', function() {
        updateResourceAvailability(this.value);
    });

    // Update when clicking on calendar date
    const originalEventClick = calendar.options.eventClick;
    
    updateMonthlyStats(new Date());
    updateResourceAvailability(availabilityDateInput.value);
    refreshAlerts(); // Load conflict alerts on page load
});
</script>