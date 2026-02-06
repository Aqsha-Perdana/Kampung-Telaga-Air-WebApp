@extends('layout.sidebar')

@section('title', 'Package Sales Calendar')

@section('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    .calendar-page-header {
        background: linear-gradient(135deg, #5D87FF 0%, #5D87FF 100%);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }

    .calendar-page-header h2 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .calendar-page-header p {
        margin: 8px 0 0 0;
        opacity: 0.9;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
    }

    .stat-card.bookings .stat-icon {
        background: linear-gradient(135deg, #5D87FF 0%,#5D87FF 100%);
    }

    .stat-card.participants .stat-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card.revenue .stat-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-card.days .stat-icon {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
    }

    .legend-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 24px;
        border: 1px solid #f0f0f0;
    }

    .legend-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .legend {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .legend-color {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .calendar-wrapper {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
    }

    /* FullCalendar Enhanced Styling */
    .fc {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .fc-toolbar-title {
        font-size: 1.75rem !important;
        font-weight: 700;
        color: #2c3e50;
    }

    .fc-button {
        background: linear-gradient(135deg, #5D87FF 0%, #5D87FF 100%) !important;
        border: none !important;
        text-transform: capitalize;
        padding: 10px 20px !important;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
        transition: all 0.3s ease !important;
    }

    .fc-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
    }

    .fc-daygrid-day-number {
        font-weight: 600;
        padding: 10px;
        font-size: 0.95rem;
    }

    .fc-col-header-cell {
        background: #f8f9fa;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        color: #6c757d;
        padding: 12px 0;
    }

    .fc-event {
        border-radius: 8px !important;
        padding: 6px 10px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
    }

    .fc-day-today {
        background-color: rgba(102, 126, 234, 0.08) !important;
    }

    /* Modal Styling */
    .modal-header {
        background: linear-gradient(135deg, #5D87FF 0%, #5D87FF 100%);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 24px;
    }

    .modal-content {
        border-radius: 16px;
        border: none;
    }

    .summary-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(0,0,0,0.08);
    }

    .order-card {
        border: 2px solid #f0f0f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        background: white;
    }

    .badge-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #667eea;
        margin: 24px 0 16px 0;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="calendar-page-header">
        <h2 class="text-white">
            <i class="text-white ti ti-calendar-event"></i>
        Calendar
        </h2>
        <p>Visualizing departure schedules for all sold tour packages</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card bookings">
            <div class="stat-icon">📊</div>
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value" id="totalBookings">-</div>
        </div>
        <div class="stat-card participants">
            <div class="stat-icon">👥</div>
            <div class="stat-label">Total Participants</div>
            <div class="stat-value" id="totalPeserta">-</div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value" id="totalRevenue">-</div>
        </div>
        <div class="stat-card days">
            <div class="stat-icon">📅</div>
            <div class="stat-label">Active Days</div>
            <div class="stat-value" id="uniqueDates">-</div>
        </div>
    </div>

    <div class="legend-card">
        <div class="legend-title">Booking Density Levels</div>
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #28a745;"></div>
                <span>1-2 Packages</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffc107;"></div>
                <span>3-4 Packages</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #fd7e14;"></div>
                <span>5-9 Packages</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #dc3545;"></div>
                <span>10+ Packages</span>
            </div>
        </div>
    </div>

    <div class="calendar-wrapper">
        <div id="calendar"></div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="ti ti-calendar-stats"></i>
                    Sales Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="loading-spinner d-flex justify-content-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    if (!calendarEl) return;

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

    function showDetailModal(date) {
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        const modalBody = document.getElementById('modalBody');
        
        modalBody.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;
        modal.show();

        fetch(`/admin/calendar/date-detail?date=${date}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalTitle').innerHTML = `
                    <i class="ti ti-calendar-stats"></i>
                    Sales for ${data.date}
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

    updateMonthlyStats(new Date());
});
</script>
@endsection