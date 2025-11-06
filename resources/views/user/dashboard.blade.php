@extends('layouts.userapp')

@section('title', 'Patient Portal - Queue & History')

@section('content')
<div class="container-fluid" style="min-height:80vh; padding:70px 0;">
    <div class="row">
        <div class="col-12">
            <div class="bg-white rounded-4 shadow-sm p-4 card animate-in" style="border:1px solid #eef7f7;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <div>
                            <strong>Patient Portal</strong>
                        </div>
                        <div class="text-muted small">Last updated: <span id="lastUpdated">Loading…</span></div>
                    </div>
                    @php($activeTab = request()->get('tab','queue'))

                    {{-- Include user tab blades (server-side) to match admin pattern --}}
                    @include('user.tabs.queue', ['activeTab' => $activeTab, 'waitingPatients' => $waitingPatients ?? null, 'inProgressPatients' => $inProgressPatients ?? null, 'doctors' => $doctors ?? null])
                    @include('user.tabs.history', ['activeTab' => $activeTab, 'historyPatients' => $historyPatients ?? collect()])

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
        // Client-side AJAX refresh: fetch /patients/list and update the waiting & in-progress cards
        document.addEventListener('DOMContentLoaded', function(){
        // Optional manual refresh button handler removed (no #refreshBtn in markup)

                // build a small map of doctors for client-side rendering
                const doctorsMap = @json(isset($doctors) ? $doctors->mapWithKeys(function($d){ return [$d->id => $d->full_name]; }) : (object)[]);

                function priorityClass(priority){
                        if (!priority) return 'priority-regular';
                        if (priority.toLowerCase() === 'urgent') return 'priority-urgent';
                        if (priority.toLowerCase() === 'high') return 'priority-high';
                        return 'priority-regular';
                }

                function escapeHtml(s){ if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

                function formatTimestamp(ts){
                    try { const d = new Date(ts); return d.toLocaleString(); } catch(e){ return new Date().toLocaleString(); }
                }

                function setLastUpdated(ts){
                    const el = document.getElementById('lastUpdated');
                    if (!el) return;
                    el.textContent = formatTimestamp(ts);
                }

                // Initialize the last-updated immediately so the UI doesn't show the placeholder long
                try { setLastUpdated(Date.now()); } catch (e) { /* ignore */ }

                function buildWaitingHtml(list){
                        if (!Array.isArray(list) || list.length === 0) return '<div>No patients currently waiting in queue</div>';
                        return list.map(function(p, idx){
                                const assigned = p.assigned_doctor_name || doctorsMap[p.assigned_doctor_id] || 'Unassigned';
                                const priority = p.priority || 'Regular';
                                const badgeClass = priorityClass(priority);
                                const checkin = p.check_in_time || p.check_in || '';
                                return `
                                        <div class="card mb-4">
                                            <div class="card-body d-flex justify-content-between align-items-start">
                                                <div style="flex:1">
                                                    <h5 class="mb-1">${idx+1}. ${escapeHtml(p.name)} <small class="text-muted">(${escapeHtml(p.age || '')}, ${escapeHtml(p.gender || '')})</small></h5>
                                                    <p class="small text-muted mb-2">${escapeHtml(p.symptoms || '')}</p>
                                                    <div class="small text-muted">Priority: <span class="priority-badge ${badgeClass}">${escapeHtml(priority)}</span> • Service: ${escapeHtml(p.service_type || '')}</div>
                                                    <div class="small text-muted">Check-in: ${escapeHtml(checkin)}</div>
                                                </div>
                                                <div style="min-width:220px; margin-left:20px; text-align:right;">
                                                    <div class="small text-muted">Assigned: ${escapeHtml(assigned)}</div>
                                                </div>
                                            </div>
                                        </div>`;
                        }).join('');
                }

                function buildInProgressHtml(list){
                        if (!Array.isArray(list) || list.length === 0) return '<div>No ongoing consultations</div>';
                        return list.map(function(p, idx){
                                const assigned = p.assigned_doctor_name || doctorsMap[p.assigned_doctor_id] || '—';
                                const priority = p.priority || 'Regular';
                                const badgeClass = priorityClass(priority);
                                const checkin = p.check_in_time || p.check_in || '';
                                return `
                                        <div class="card mb-4">
                                            <div class="card-body d-flex justify-content-between align-items-start">
                                                <div style="flex:1">
                                                    <h5 class="mb-1">${idx+1}. ${escapeHtml(p.name)} <small class="text-muted">(${escapeHtml(p.age || '')})</small></h5>
                                                    <div class="small text-muted">With Dr. ${escapeHtml(assigned)}</div>
                                                    <div class="small text-muted">Service: ${escapeHtml(p.service_type || '')} • <span class="priority-badge ${badgeClass}">${escapeHtml(priority)}</span></div>
                                                </div>
                                                <div style="min-width:220px; margin-left:20px; text-align:right;">
                                                    <div class="badge bg-secondary" style="padding:8px 10px; border-radius:6px; color:#fff;">In Progress</div>
                                                </div>
                                            </div>
                                        </div>`;
                        }).join('');
                }

                let fetchInProgress = false;
                const pollIntervalMs = 10000; // 10 seconds
                let pollTimer = null;

                // Exponential backoff state for long-poll failures
                let lpErrorCount = 0;
                const lpMaxDelay = 30000; // 30s

        function fetchQueueAjax(){
                        if (fetchInProgress) return Promise.resolve();
                        fetchInProgress = true;
                        return fetch("{{ url('/patients/list') }}", { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }})
                                .then(function(r){ return r.json(); })
                                .then(function(js){
                                        if (!js || !js.success) return;
                                        const data = js.data || [];
                                        // split into waiting and in-progress according to status
                                        const waiting = data.filter(p => (p.status || '').toLowerCase() === 'waiting');
                                        const inprog = data.filter(p => (p.status || '').toLowerCase() === 'in progress' || (p.status || '').toLowerCase() === 'in-progress');

                                        const waitingContainer = document.getElementById('waitingQueue');
                                        const inprogContainer = document.getElementById('inProgressQueue');
                                        const waitingCount = document.getElementById('waitingCount');
                                        const inprogCount = document.getElementById('inProgressCount');

                                        if (waitingContainer) waitingContainer.innerHTML = buildWaitingHtml(waiting);
                                        if (inprogContainer) inprogContainer.innerHTML = buildInProgressHtml(inprog);
                                        if (waitingCount) waitingCount.textContent = waiting.length + ' patients waiting';
                                        if (inprogCount) inprogCount.textContent = inprog.length + ' ongoing consultations';
                    // Update last-updated timestamp
                    try { setLastUpdated(Date.now()); } catch(e){}
                                }).catch(function(){ /* noop */ }).finally(function(){ fetchInProgress = false; });
                }

                function startPolling(){
                    if (pollTimer) return;
                    pollTimer = setInterval(function(){
                        // only poll when the page is visible
                        if (document.visibilityState && document.visibilityState !== 'visible') return;
                        fetchQueueAjax();
                    }, pollIntervalMs);
                }

                function stopPolling(){
                    if (!pollTimer) return;
                    clearInterval(pollTimer);
                    pollTimer = null;
                }

                document.addEventListener('visibilitychange', function(){
                    if (document.visibilityState === 'visible') startPolling(); else stopPolling();
                });

                // initial load via AJAX so users get the freshest queue without reload
                fetchQueueAjax().finally(startPolling);

                // Long-polling version listener for near-instant updates when admin changes queue
                // If Echo is available and broadcasting is configured, we also listen to 'patients' channel
                // Attach to a single application namespace instead of creating a loose global
                window.App = window.App || {};
                window.App.fetchQueueAjax = fetchQueueAjax;

                let lastVersion = 0;
                function listenVersion(){
                    // only listen when visible to avoid holding connections while hidden
                    if (document.visibilityState && document.visibilityState !== 'visible') {
                        setTimeout(listenVersion, 3000);
                        return;
                    }
                    fetch("{{ url('/patients/version') }}?since=" + lastVersion, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }})
                        .then(r => r.json())
                        .then(js => {
                            if (js && js.success && js.version && js.version > lastVersion) {
                                lastVersion = js.version;
                                fetchQueueAjax();
                                lpErrorCount = 0; // reset error count on success
                            }
                        })
                        .catch(()=>{ lpErrorCount++; })
                        .finally(()=>{
                            // Exponential backoff on consecutive failures
                            const delay = Math.min(100 + Math.pow(2, lpErrorCount) * 250, lpMaxDelay);
                            setTimeout(listenVersion, delay);
                        });
                }

                // start listening both via Echo (if configured) and long-poll fallback
                listenVersion();
        });
</script>
@endpush

@endsection
