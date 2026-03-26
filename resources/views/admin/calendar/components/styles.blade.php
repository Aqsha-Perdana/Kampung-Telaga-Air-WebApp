<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    /* ─── Compact Header ─── */
    .calendar-page-header {
        background: linear-gradient(135deg, #5D87FF 0%, #4a6fd8 100%);
        border-radius: 12px;
        padding: 14px 20px;
        margin-bottom: 16px;
        color: white;
        box-shadow: 0 3px 12px rgba(93,135,255,0.25);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .calendar-page-header h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .calendar-page-header p { display: none; }

    /* ─── Inline Stat Chips (inside header) ─── */
    .header-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .stat-chip {
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(4px);
        border-radius: 8px;
        padding: 6px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        color: white;
        white-space: nowrap;
    }
    .stat-chip-value {
        font-weight: 800;
        font-size: 0.95rem;
    }
    .stat-chip-label {
        opacity: 0.85;
        font-weight: 500;
    }

    /* ─── Sidebar Resource Cards ─── */
    .sidebar-resource {
        background: white;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
    }
    .sidebar-resource .res-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .sidebar-resource .res-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .sidebar-resource .res-title i { font-size: 1rem; }

    .utilization-badge {
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.65rem;
        font-weight: 700;
    }
    .utilization-badge.low { background: #d4edda; color: #155724; }
    .utilization-badge.medium { background: #fff3cd; color: #856404; }
    .utilization-badge.high { background: #f8d7da; color: #721c24; }

    .res-stats-row {
        display: flex;
        justify-content: space-around;
        text-align: center;
        margin-bottom: 6px;
    }
    .res-stat-val {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .res-stat-val.available { color: #28a745; }
    .res-stat-val.booked { color: #dc3545; }
    .res-stat-val.total { color: #6c757d; }
    .res-stat-lbl {
        font-size: 0.6rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    .progress-bar-container {
        height: 5px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 4px;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    .progress-bar-fill.low { background: linear-gradient(90deg, #28a745, #5cb85c); }
    .progress-bar-fill.medium { background: linear-gradient(90deg, #ffc107, #ffca2c); }
    .progress-bar-fill.high { background: linear-gradient(90deg, #dc3545, #e4606d); }

    .capacity-info {
        font-size: 0.68rem;
        color: #6c757d;
        display: flex;
        justify-content: space-between;
    }

    .sidebar-resource.boats { border-left: 3px solid #4facfe; }
    .sidebar-resource.homestays { border-left: 3px solid #43e97b; }
    .sidebar-resource.culinaries { border-left: 3px solid #f093fb; }
    .sidebar-resource.kiosks { border-left: 3px solid #ffa500; }

    .sidebar-section-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }

    /* ─── Inline Legend ─── */
    .legend-bar {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: center;
        padding: 8px 16px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 12px;
        border: 1px solid #e9ecef;
    }
    .legend-bar .lbl {
        font-size: 0.7rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .legend-bar .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
    }
    .legend-bar .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }

    /* ─── Calendar Wrapper ─── */
    .calendar-wrapper {
        background: white;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
    }

    /* ─── FullCalendar Overrides ─── */
    .fc { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 700;
        color: #2c3e50;
    }
    .fc-button {
        background: #5D87FF !important;
        border: none !important;
        text-transform: capitalize;
        padding: 6px 14px !important;
        font-weight: 600;
        font-size: 0.8rem;
        border-radius: 6px !important;
        box-shadow: 0 2px 6px rgba(93,135,255,0.25) !important;
        transition: all 0.2s ease !important;
    }
    .fc-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(93,135,255,0.35) !important;
    }
    .fc-daygrid-day-number { font-weight: 600; padding: 6px; font-size: 0.85rem; }
    .fc-col-header-cell {
        background: #f8f9fa;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        color: #6c757d;
        padding: 8px 0;
    }
    .fc-event {
        border-radius: 6px !important;
        padding: 4px 8px !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        box-shadow: 0 1px 4px rgba(0,0,0,0.12) !important;
    }
    .fc-day-today { background-color: rgba(93,135,255,0.06) !important; }

    /* ─── Modal ─── */
    .modal-header {
        background: linear-gradient(135deg, #5D87FF 0%, #4a6fd8 100%);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 20px;
    }
    .modal-content { border-radius: 16px; border: none; }
    .summary-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .order-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        background: white;
    }
    .badge-status {
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .section-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #5D87FF;
        margin: 16px 0 10px 0;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Modal UX overrides: clearer focus and softer contrast */
    #detailModal .modal-dialog {
        max-width: 940px;
    }
    #detailModal .modal-content {
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        overflow: hidden;
        background: #ffffff;
    }
    #detailModal .modal-header {
        border: 0;
        padding: 14px 18px;
    }
    #detailModal .modal-title {
        font-size: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    #detailModal .modal-body {
        background: #f8fbff;
        padding: 18px;
    }
    #detailModal .summary-box {
        background: #ffffff;
        border: 1px solid #e6edf8;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }
    #detailModal .order-card {
        border: 1px solid #e6edf8;
        background: #ffffff;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
    }
    #detailModal .modal-header .btn-close {
        opacity: 0.9;
    }
    #detailModal .modal-header .btn-close:hover {
        opacity: 1;
    }
    /* Safety net: prevent accidental "leaving" state from freezing calendar interactions */
    #main-wrapper.is-leaving .body-wrapper,
    #main-wrapper.is-leaving .container-fluid {
        opacity: 1 !important;
        transform: none !important;
        pointer-events: auto !important;
    }

    /* ─── Alerts Section (sidebar) ─── */
    .alerts-section {
        background: white;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
    }
    .alerts-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }
    .alerts-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .alert-counts { display: flex; gap: 4px; flex-wrap: wrap; }
    .alert-badge {
        padding: 2px 7px;
        border-radius: 10px;
        font-size: 0.6rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 3px;
    }
    .alert-badge.critical { background: #fee; color: #c00; }
    .alert-badge.warning { background: #fff3cd; color: #856404; }
    .alert-badge.info { background: #d1ecf1; color: #0c5460; }

    .alerts-container { display: flex; flex-direction: column; gap: 6px; }

    .alert-section-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 4px;
        margin-top: 8px;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .alert-section-title.critical { color: #dc3545; }
    .alert-section-title.warning { color: #ffc107; }
    .alert-section-title.info { color: #17a2b8; }

    .alert-card {
        border-radius: 6px;
        padding: 8px 10px;
        border-left: 3px solid;
        background: white;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    .alert-card:hover { box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
    .alert-card.critical { border-left-color: #dc3545; background: #fff5f5; }
    .alert-card.warning { border-left-color: #ffc107; background: #fffbf0; }

    .alert-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .alert-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .alert-date { font-size: 0.65rem; color: #6c757d; font-weight: 600; }
    .alert-body { margin-bottom: 6px; }
    .alert-resource-info {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 4px;
        font-size: 0.7rem;
        color: #495057;
    }
    .alert-resource-name { font-weight: 600; color: #2c3e50; }

    .conflict-bookings {
        margin-top: 4px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .conflict-booking-item {
        padding: 5px 8px;
        background: white;
        border-radius: 4px;
        border: 1px solid #e9ecef;
        font-size: 0.65rem;
    }
    .booking-order-id { font-weight: 700; color: #5D87FF; font-size: 0.65rem; }
    .booking-details { margin-top: 2px; color: #6c757d; font-size: 0.6rem; }

    .alert-actions { display: flex; gap: 4px; flex-wrap: wrap; }
    .alert-action-btn {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .alert-action-btn.primary { background: #5D87FF; color: white; }
    .alert-action-btn.primary:hover { background: #4a6fd8; }
    .alert-action-btn.secondary { background: #e9ecef; color: #495057; }
    .alert-action-btn.secondary:hover { background: #dee2e6; }
    .alert-action-btn.danger { background: #dc3545; color: white; }
    .alert-action-btn.danger:hover { background: #c82333; }

    .capacity-summary {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 8px;
        margin-top: 6px;
    }
    .capacity-summary-title {
        font-size: 0.65rem;
        font-weight: 700;
        color: #495057;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    .capacity-summary-items {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .capacity-summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 6px;
        background: white;
        border-radius: 4px;
        font-size: 0.65rem;
    }
    .capacity-summary-date { font-weight: 600; color: #2c3e50; }
    .capacity-summary-resources { display: flex; gap: 4px; flex-wrap: wrap; }
    .capacity-pill {
        padding: 1px 6px;
        border-radius: 8px;
        font-size: 0.55rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .capacity-pill.high { background: #fee; color: #c00; }
    .capacity-pill.medium { background: #fff3cd; color: #856404; }

    .empty-alerts { text-align: center; padding: 16px; color: #6c757d; }
    .empty-alerts-icon { font-size: 1.8rem; margin-bottom: 6px; opacity: 0.5; }
    .empty-alerts-text { font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; }
    .empty-alerts-subtext { font-size: 0.72rem; opacity: 0.8; }

    /* ─── Alert Table ─── */
    .alert-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.7rem;
        margin-bottom: 8px;
    }
    .alert-table thead { background: #f8f9fa; }
    .alert-table th {
        padding: 4px 6px;
        text-align: left;
        font-weight: 700;
        font-size: 0.6rem;
        color: #6c757d;
        text-transform: uppercase;
        border-bottom: 2px solid #dee2e6;
    }
    .alert-table td {
        padding: 5px 6px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    .alert-row.critical { background: #fff5f5; }
    .alert-row.critical:hover { background: #ffebeb; }
    .alert-row.warning { background: #fffbf0; }
    .alert-row.warning:hover { background: #fff8e1; }
    .alert-row.info { background: #f0f9ff; }
    .alert-row.info:hover { background: #e6f5ff; }

    .alert-date-col {
        font-weight: 600;
        color: #2c3e50;
        white-space: nowrap;
        width: 80px;
        font-size: 0.65rem;
    }
    .conflict-details { font-size: 0.65rem; color: #495057; line-height: 1.3; }

    .conflict-count {
        font-weight: 700;
        color: #dc3545;
        display: inline-block;
        margin-right: 6px;
    }

    .over-badge {
        background: #dc3545;
        color: white;
        padding: 2px 6px;
        border-radius: 8px;
        font-size: 0.65rem;
        font-weight: 700;
        margin-left: 4px;
    }

    .actions-col {
        width: 80px;
        text-align: right;
        white-space: nowrap;
    }

    .btn-mini {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 4px;
        transition: all 0.2s ease;
    }

    .btn-mini.primary {
        background: #5D87FF;
        color: white;
    }

    .btn-mini.primary:hover {
        background: #4a6fd8;
    }

    .btn-mini.secondary {
        background: #e9ecef;
        color: #495057;
    }

    .btn-mini.secondary:hover {
        background: #dee2e6;
    }

    .forecast-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 8px;
    }

    .forecast-item {
        background: white;
        padding: 8px 10px;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .forecast-date {
        font-weight: 700;
        font-size: 0.75rem;
        color: #2c3e50;
        margin-bottom: 2px;
    }

    .refresh-alerts-btn {
        background: #5D87FF;
        color: white;
        border: none;
        padding: 6px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .refresh-alerts-btn:hover {
        background: #4a6fd8;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(93, 135, 255, 0.4);
    }

    .over-capacity-box {
        padding: 8px 10px;
        background: #fff3cd;
        border-radius: 6px;
        border-left: 3px solid #ffc107;
        margin-top: 6px;
        font-size: 0.75rem;
    }

    .over-capacity-box strong {
        color: #856404;
    }

    .high-util-box {
        padding: 6px 10px;
        background: #fff3cd;
        border-radius: 6px;
        margin-bottom: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
    }

    /* Calendar visual alignment with other admin pages */
    .calendar-page-header {
        background: #ffffff;
        border: 1px solid #e8edf6;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    .calendar-page-header h2 {
        color: #2a3547;
        font-size: 1.1rem;
    }
    .calendar-page-header h2 i {
        color: #5d87ff;
    }
    .stat-chip {
        background: #f4f8ff;
        border: 1px solid #dfe8ff;
        color: #2a3547;
        backdrop-filter: none;
    }
    .stat-chip-value {
        color: #2a3547;
    }
    .stat-chip-label {
        color: #5a6a85;
        opacity: 1;
    }
    .legend-bar,
    .calendar-wrapper {
        border: 1px solid #e8edf6;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }

    .resources-panel {
        background: #ffffff;
        border: 1px solid #e8edf6;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        padding: 12px;
        margin-bottom: 12px;
    }
    .resources-panel-header {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eef2f8;
    }
    .resources-panel-header input.form-control {
        height: 30px;
        border-radius: 8px;
        border-color: #dbe5f3;
        font-weight: 600;
        color: #334155;
    }
    .resources-panel-body {
        display: grid;
        gap: 12px;
    }

    .sidebar-section-title {
        margin-bottom: 0;
        font-size: 0.76rem;
        color: #5a6a85;
    }
    .sidebar-resource {
        border-radius: 12px;
        border: 1px solid #ebf1f6;
        box-shadow: none;
        margin-bottom: 0;
        padding: 12px 12px 10px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .sidebar-resource:hover {
        border-color: #d9e6ff;
        box-shadow: 0 4px 10px rgba(93, 135, 255, 0.08);
    }
    .sidebar-resource .res-header {
        margin-bottom: 10px;
    }
    .sidebar-resource .res-title {
        font-size: 0.92rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: #1e293b;
        gap: 7px;
    }
    .utilization-badge {
        min-width: 52px;
        text-align: center;
        padding: 3px 8px;
        font-size: 0.68rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        border-radius: 999px;
    }
    .res-stats-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0;
        margin-bottom: 8px;
        border: 1px solid #edf2f9;
        border-radius: 10px;
        overflow: hidden;
        background: #fcfdff;
    }
    .res-stats-row > div {
        text-align: center;
        padding: 8px 6px 7px;
    }
    .res-stats-row > div:not(:last-child) {
        border-right: 1px solid #edf2f9;
    }
    .res-stat-val {
        font-size: 1.24rem;
        line-height: 1.05;
        font-weight: 800;
        letter-spacing: -0.02em;
        font-variant-numeric: tabular-nums;
    }
    .res-stat-lbl {
        margin-top: 2px;
        font-size: 0.6rem;
        font-weight: 700;
        letter-spacing: 0.08em;
    }
    .progress-bar-container {
        height: 6px;
        margin: 7px 0 6px;
        border-radius: 999px;
    }
    .capacity-info {
        font-size: 0.7rem;
        align-items: center;
        margin-bottom: 4px;
        font-variant-numeric: tabular-nums;
    }
    .capacity-info span {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .resource-meta {
        font-size: 0.69rem;
        line-height: 1.35;
        color: #64748b;
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px dashed #e6edf8;
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7em;
        word-break: break-word;
    }

</style>