@php($active = $activeTab ?? 'queue')
<div id="reportsTab" class="tab-content{{ $active !== 'reports' ? ' hidden' : '' }}">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="card stat-card">
            <div class="stat-number">{{ $stats['totalPatientsToday'] ?? 0 }}</div>
            <div class="stat-label">Total Patients Today</div>
            <p style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">
                <span>{{ $stats['completed'] ?? 0 }}</span> completed
            </p>
        </div>
        
        <div class="card stat-card">
            <div class="stat-number">{{ $stats['averageWait'] ?? 0 }} <span style="font-size:0.8rem">min</span></div>
            <div class="stat-label">Average Wait Time</div>
            <p style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">
                Based on today's consultations
            </p>
        </div>
        
        <div class="card stat-card">
            <div class="stat-number">{{ $stats['doctorUtilization'] ?? 0 }}%</div>
            <div class="stat-label">Doctor Utilization</div>
            <p style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">
                Average across all doctors
            </p>
        </div>
    </div>

    <!-- Charts -->
    <div id="chartsContainer">
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <div class="card-title">Patients over time</div>
                <div class="card-description">Trend of check-ins over the selected range</div>
            </div>
            <div class="card-body" style="height:420px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                    <div>
                        <button id="preset7" class="btn btn-sm">Last 7 days</button>
                        <button id="preset30" class="btn btn-sm">Last 30 days</button>
                        <button id="preset90" class="btn btn-sm">Last 90 days</button>
                    </div>
                    <div style="font-size:0.85rem; color:#6b7280">
                        <label for="from">From</label>
                        <input id="from" type="date" style="margin-left:6px; margin-right:10px;" />
                        <label for="to">To</label>
                        <input id="to" type="date" style="margin-left:6px;" />
                        <button id="applyRange" class="btn btn-sm" style="margin-left:8px;">Apply</button>
                    </div>
                </div>

                <div style="height: calc(100% - 48px);">
                    <canvas id="patientsOverTimeChart" style="width:100%; height:100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <div class="card-title">System Recommendations</div>
            <div class="card-description">Based on current data and utilization patterns</div>
        </div>
        <div class="card-body" id="recommendationsContainer">
            <!-- Dynamic recommendations will be loaded here -->
        </div>
    </div>
</div>

<!-- Chart code is bundled via Vite (imported from resources/js/reports.js). -->
