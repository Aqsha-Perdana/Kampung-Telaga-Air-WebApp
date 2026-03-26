<div class="alerts-section" id="alertsSection">
    <div class="alerts-header">
        <div class="alerts-title">
            <i class="ti ti-alert-triangle text-danger"></i> Alerts
        </div>
        <div class="d-flex align-items-center gap-1">
            <div class="alert-counts" id="alertCounts">
                <span class="alert-badge critical">
                    <i class="ti ti-exclamation-circle"></i>
                    <span id="criticalCount">0</span>
                </span>
                <span class="alert-badge warning">
                    <i class="ti ti-alert-triangle"></i>
                    <span id="warningCount">0</span>
                </span>
                <span class="alert-badge info">
                    <i class="ti ti-info-circle"></i>
                    <span id="capacityIssueCount">0</span>
                </span>
            </div>
            <button class="btn btn-sm btn-outline-primary" style="padding:2px 6px;font-size:0.65rem;" onclick="refreshAlerts()">
                <i class="ti ti-refresh"></i>
            </button>
        </div>
    </div>
    <div id="alertsContainer" style="max-height:300px;overflow-y:auto;">
        <div id="criticalAlertsContainer"></div>
        <div id="warningAlertsContainer"></div>
        <div id="capacityIssuesContainer"></div>
        <div id="upcomingSummaryContainer"></div>
        <div class="empty-alerts" id="emptyAlerts">
            <div class="empty-alerts-icon">?</div>
            <div class="empty-alerts-text">All Clear!</div>
            <div class="empty-alerts-subtext">No conflicts detected</div>
        </div>
    </div>
</div>