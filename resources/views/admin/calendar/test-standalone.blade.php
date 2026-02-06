<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Kalender - Standalone</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    
    <style>
        body {
            padding: 20px;
            background-color: #f5f7fa;
            font-family: 'Inter', Arial, sans-serif;
        }
        
        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .test-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .calendar-wrapper {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            min-height: 600px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 5px 0;
        }
        
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .fc-button {
            background-color: #667eea !important;
            border: none !important;
        }
        
        .fc-event {
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Test Banner -->
        <div class="test-banner">
            <h3>🧪 Test Kalender - Standalone Version</h3>
            <p class="mb-0">Ini adalah versi test tanpa layout untuk memastikan kalender berfungsi</p>
        </div>
        
        <!-- Statistics -->
        <div class="stats-card">
            <div class="row" id="monthlyStats">
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-label">Total Booking</div>
                        <div class="stat-value" id="totalBookings">-</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-label">Total Peserta</div>
                        <div class="stat-value" id="totalPeserta">-</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-label">Total Pendapatan</div>
                        <div class="stat-value" id="totalRevenue">-</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-label">Hari Aktif</div>
                        <div class="stat-value" id="uniqueDates">-</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Legend -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #28a745; border-radius: 4px;"></div>
                        <span>1-2 Paket</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #ffc107; border-radius: 4px;"></div>
                        <span>3-4 Paket</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #fd7e14; border-radius: 4px;"></div>
                        <span>5-9 Paket</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #dc3545; border-radius: 4px;"></div>
                        <span>10+ Paket</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Calendar -->
        <div class="calendar-wrapper">
            <div id="calendar"></div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== STANDALONE CALENDAR TEST ===');
        console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
        console.log('FullCalendar loaded:', typeof FullCalendar !== 'undefined');
        
        const calendarEl = document.getElementById('calendar');
        
        if (!calendarEl) {
            console.error('Calendar element not found!');
            return;
        }
        
        if (typeof FullCalendar === 'undefined') {
            console.error('FullCalendar not loaded!');
            calendarEl.innerHTML = '<div class="alert alert-danger">FullCalendar library gagal dimuat</div>';
            return;
        }
        
        try {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    week: 'Minggu'
                },
                locale: 'id',
                firstDay: 1,
                height: 'auto',
                events: function(info, successCallback, failureCallback) {
                    const url = `/admin/calendar/events?start=${info.startStr}&end=${info.endStr}`;
                    console.log('Fetching events from:', url);
                    
                    fetch(url)
                        .then(response => {
                            console.log('Response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Events loaded:', data.length, 'events');
                            console.log('Events data:', data);
                            successCallback(data);
                        })
                        .catch(error => {
                            console.error('Error loading events:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    alert('Event: ' + info.event.title + '\nDate: ' + info.event.startStr);
                },
                datesSet: function(info) {
                    console.log('Date range changed');
                    updateMonthlyStats(info.view.currentStart);
                }
            });
            
            calendar.render();
            console.log('✅ Calendar rendered successfully!');
            
        } catch (error) {
            console.error('Error initializing calendar:', error);
            calendarEl.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
        }
        
        // Update statistics
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
                })
                .catch(error => {
                    console.error('Error loading statistics:', error);
                });
        }
        
        function numberFormat(num) {
            return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
        
        // Initial stats load
        updateMonthlyStats(new Date());
    });
    </script>
</body>
</html>