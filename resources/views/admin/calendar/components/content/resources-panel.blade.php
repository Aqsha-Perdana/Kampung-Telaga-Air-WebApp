<div class="resources-panel">
    <div class="sidebar-section-title resources-panel-header">
        <span><i class="ti ti-lifebuoy text-primary"></i> Resources</span>
        <input type="date" id="availabilityDate" class="form-control form-control-sm" style="width:120px;font-size:0.7rem;">
    </div>

    <div class="resources-panel-body">
        <div class="sidebar-resource boats">
            <div class="res-header">
                <div class="res-title"><i class="ti ti-sailboat text-info"></i> Boats</div>
                <span class="utilization-badge low" id="boatUtilBadge">0%</span>
            </div>
            <div class="res-stats-row">
                <div>
                    <div class="res-stat-val available" id="boatsAvailable">-</div>
                    <div class="res-stat-lbl">Avail</div>
                </div>
                <div>
                    <div class="res-stat-val booked" id="boatsBooked">-</div>
                    <div class="res-stat-lbl">Booked</div>
                </div>
                <div>
                    <div class="res-stat-val total" id="boatsTotal">-</div>
                    <div class="res-stat-lbl">Total</div>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill low" id="boatProgress" style="width:0%;"></div>
            </div>
            <div class="capacity-info">
                <span><i class="ti ti-users"></i> <span id="boatCapAvail">-</span>/<span id="boatCapTotal">-</span> pax</span>
                <span id="boatUtilPercent">0%</span>
            </div>
            <div class="resource-meta" id="boatsMeta">Memuat data ketersediaan...</div>
        </div>

        <div class="sidebar-resource homestays">
            <div class="res-header">
                <div class="res-title"><i class="ti ti-home-2 text-success"></i> Homestays</div>
                <span class="utilization-badge low" id="homestayUtilBadge">0%</span>
            </div>
            <div class="res-stats-row">
                <div>
                    <div class="res-stat-val available" id="homestaysAvailable">-</div>
                    <div class="res-stat-lbl">Avail</div>
                </div>
                <div>
                    <div class="res-stat-val booked" id="homestaysBooked">-</div>
                    <div class="res-stat-lbl">Booked</div>
                </div>
                <div>
                    <div class="res-stat-val total" id="homestaysTotal">-</div>
                    <div class="res-stat-lbl">Total</div>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill low" id="homestayProgress" style="width:0%;"></div>
            </div>
            <div class="capacity-info">
                <span><i class="ti ti-users"></i> <span id="homestayCapAvail">-</span>/<span id="homestayCapTotal">-</span> pax</span>
                <span id="homestayUtilPercent">0%</span>
            </div>
            <div class="resource-meta" id="homestaysMeta">Memuat data ketersediaan...</div>
        </div>

        <div class="sidebar-resource culinaries">
            <div class="res-header">
                <div class="res-title"><i class="ti ti-tools-kitchen-2 text-warning"></i> Culinaries</div>
                <span class="utilization-badge low" id="culinaryUtilBadge">0%</span>
            </div>
            <div class="res-stats-row">
                <div>
                    <div class="res-stat-val available" id="culinariesAvailable">-</div>
                    <div class="res-stat-lbl">Avail</div>
                </div>
                <div>
                    <div class="res-stat-val booked" id="culinariesBooked">-</div>
                    <div class="res-stat-lbl">Booked</div>
                </div>
                <div>
                    <div class="res-stat-val total" id="culinariesTotal">-</div>
                    <div class="res-stat-lbl">Total</div>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill low" id="culinaryProgress" style="width:0%;"></div>
            </div>
            <div class="capacity-info">
                <span><i class="ti ti-users"></i> <span id="culinaryCapAvail">-</span>/<span id="culinaryCapTotal">-</span> pax</span>
                <span id="culinaryUtilPercent">0%</span>
            </div>
            <div class="resource-meta" id="culinariesMeta">Memuat data ketersediaan...</div>
        </div>

        <div class="sidebar-resource kiosks">
            <div class="res-header">
                <div class="res-title"><i class="ti ti-building-store text-danger"></i> Kiosks</div>
                <span class="utilization-badge low" id="kioskUtilBadge">0%</span>
            </div>
            <div class="res-stats-row">
                <div>
                    <div class="res-stat-val available" id="kiosksAvailable">-</div>
                    <div class="res-stat-lbl">Avail</div>
                </div>
                <div>
                    <div class="res-stat-val booked" id="kiosksBooked">-</div>
                    <div class="res-stat-lbl">Booked</div>
                </div>
                <div>
                    <div class="res-stat-val total" id="kiosksTotal">-</div>
                    <div class="res-stat-lbl">Total</div>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill low" id="kioskProgress" style="width:0%;"></div>
            </div>
            <div class="capacity-info">
                <span><i class="ti ti-users"></i> <span id="kioskCapAvail">-</span>/<span id="kioskCapTotal">-</span> pax</span>
                <span id="kioskUtilPercent">0%</span>
            </div>
            <div class="resource-meta" id="kiosksMeta">Memuat data ketersediaan...</div>
        </div>
    </div>
</div>