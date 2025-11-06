@php($active = $activeTab ?? 'queue')
<div id="historyTab" class="tab-content{{ $active !== 'history' ? ' hidden' : '' }}">
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <div class="card-title">Patient History</div>
            <div class="card-description">Complete record of all patients from the database</div>
        </div>
        <div class="card-body">
            <!-- Search and Filter Controls -->
            <form id="historyFiltersForm" action="{{ route('history') }}" method="GET">
                <div class="grid grid-cols-3" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label class="form-label" for="historySearch">Search Patients</label>
                        <input type="text" id="historySearch" name="search" value="{{ request('search') }}" class="form-input" placeholder="Search by name, contact, or symptoms...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="historyStatus">Filter by Status</label>
                        <select id="historyStatus" name="status" class="form-select">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="Waiting" {{ request('status') == 'Waiting' ? 'selected' : '' }}>Waiting</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="historyDate">Filter by Date</label>
                        <select id="historyDate" name="date" class="form-select">
                            <option value="" {{ request('date') == '' ? 'selected' : '' }}>All Time</option>
                            <option value="today" {{ request('date') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('date') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('date') == 'month' ? 'selected' : '' }}>This Month</option>
                        </select>
                    </div>
                </div>
            </form>

            <script>
                (function(){
                    var form = document.getElementById('historyFiltersForm');
                    if (!form) return;
                    var timeout = null;
                    function triggerSubmit(){
                        // Dispatch submit event and if not prevented, perform native submit
                        try {
                            var ev = new Event('submit', { cancelable: true });
                        } catch (e) {
                            // older browsers
                            var ev = document.createEvent('Event'); ev.initEvent('submit', true, true);
                        }
                        var notPrevented = form.dispatchEvent(ev);
                        if (notPrevented) form.submit();
                    }
                    function submitDebounced(){
                        if(timeout) clearTimeout(timeout);
                        timeout = setTimeout(function(){ triggerSubmit(); }, 350);
                    }
                    var search = document.getElementById('historySearch');
                    if(search){
                        search.addEventListener('input', submitDebounced);
                    }
                    var selects = form.querySelectorAll('select');
                    selects.forEach(function(s){ s.addEventListener('change', function(){ triggerSubmit(); }); });
                })();
            </script>
            </form>
            
                    <!-- History Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Patient Name</th>
                                    <th>Age</th>
                                    <th>Contact</th>
                                    <th>Symptoms</th>
                                    <th>Status</th>
                                    <th>Check-in Time</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                @if(isset($historyPatients) && $historyPatients->count())
                                    @foreach($historyPatients as $p)
                                    <tr>
                                        <td>{{ $p['id'] }}</td>
                                        <td>{{ $p['name'] }}</td>
                                        <td>{{ $p['age'] }}</td>
                                        <td>{{ $p['contact'] }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($p['symptoms'], 80) }}</td>
                                        <td>{{ $p['status'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($p['check_in_time'])->format('Y-m-d H:i') }}</td>
                                        <td>{{ $p['priority'] ?? 'Regular' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8">No patient history available.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
            
            <!-- Loading indicator for history -->
            <div id="historyLoading" class="loading hidden" style="margin: 20px 0;">
                <div class="spinner"></div>
                <p>Loading patient history...</p>
            </div>
            
            <!-- History Statistics (visible to staff/admin only) -->
            @if(auth()->check() && auth()->user()->is_admin)
            <div class="stats-grid" style="margin-top: 30px;">
                <div class="card stat-card">
                    <div class="stat-number" id="totalHistoryPatients">{{ $stats['totalPatientsToday'] ?? 0 }}</div>
                    <div class="stat-label">Total Patients</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-number" id="completedHistoryPatients">{{ $stats['completed'] ?? 0 }}</div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-number" id="todayHistoryPatients">{{ $stats['totalPatientsToday'] ?? 0 }}</div>
                    <div class="stat-label">Today's Patients</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(auth()->check() && !auth()->user()->is_admin)
<script>
    (function(){
        // For non-admin users, override the history table with only their records
        var tableBody = document.getElementById('historyTableBody');
        var totalEl = document.getElementById('totalHistoryPatients');
        var completedEl = document.getElementById('completedHistoryPatients');
        var todayEl = document.getElementById('todayHistoryPatients');
        var loading = document.getElementById('historyLoading');

        function formatRow(p){
            var symptoms = (p.symptoms || '').substring(0,80);
            var checkin = p.check_in_time ? new Date(p.check_in_time).toLocaleString() : '';
            return '<tr>'+
                '<td>'+ (p.id || '') +'</td>'+
                '<td>'+ (p.name || '') +'</td>'+
                '<td>'+ (p.age || '') +'</td>'+
                '<td>'+ (p.contact || '') +'</td>'+
                '<td>'+ (escapeHtml(symptoms) || '') +'</td>'+
                '<td>'+ (p.status || '') +'</td>'+
                '<td>'+ (checkin) +'</td>'+
                '<td>'+ (p.priority || 'Regular') +'</td>'+
                '</tr>';
        }

        function escapeHtml(s){ if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        function fetchMyHistory(){
            if (!tableBody) return;
            if (loading) loading.classList.remove('hidden');
            var params = [];
            params.push('email={{ urlencode(auth()->user()->email) }}');
            if (typeof searchInput !== 'undefined' && searchInput && searchInput.value) params.push('search=' + encodeURIComponent(searchInput.value));
            if (typeof statusSelect !== 'undefined' && statusSelect && statusSelect.value) params.push('status=' + encodeURIComponent(statusSelect.value));
            if (typeof dateSelect !== 'undefined' && dateSelect && dateSelect.value) params.push('date=' + encodeURIComponent(dateSelect.value));
            var url = '/patient/history?' + params.join('&');

            fetch(url, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }})
                .then(function(r){ return r.json(); })
                .then(function(js){
                    if (!js || !js.success) return;
                    var rows = (js.data || []).map(function(p){ return formatRow(p); }).join('');
                    tableBody.innerHTML = rows || '<tr><td colspan="8">No patient history available.</td></tr>';
                    if (totalEl) totalEl.textContent = js.stats ? js.stats.total : (js.data||[]).length;
                    if (completedEl) completedEl.textContent = js.stats ? js.stats.completed : 0;
                    if (todayEl) todayEl.textContent = js.stats ? js.stats.total : (js.data||[]).length;
                })
                .catch(function(){ /* noop */ })
                .finally(function(){ if (loading) loading.classList.add('hidden'); });
        }

        // wire filter form for patients: use AJAX instead of full-page submit
        var form = document.getElementById('historyFiltersForm');
        var searchInput = document.getElementById('historySearch');
        var statusSelect = document.getElementById('historyStatus');
        var dateSelect = document.getElementById('historyDate');
        var filterTimeout = null;

        function applyFiltersDebounced(){
            if (filterTimeout) clearTimeout(filterTimeout);
            filterTimeout = setTimeout(fetchMyHistory, 350);
        }

        if (form) {
            // prevent full-page submit; we handle filters via AJAX for patients
            form.addEventListener('submit', function(e){ e.preventDefault(); fetchMyHistory(); });
        }
        if (searchInput) searchInput.addEventListener('input', applyFiltersDebounced);
        if (statusSelect) statusSelect.addEventListener('change', fetchMyHistory);
        if (dateSelect) dateSelect.addEventListener('change', fetchMyHistory);

        // initial fetch
        fetchMyHistory();
    })();
</script>
@endif
