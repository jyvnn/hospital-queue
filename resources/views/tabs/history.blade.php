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
                    function submitDebounced(){
                        if(timeout) clearTimeout(timeout);
                        timeout = setTimeout(function(){ form.submit(); }, 350);
                    }
                    var search = document.getElementById('historySearch');
                    if(search){
                        search.addEventListener('input', submitDebounced);
                    }
                    var selects = form.querySelectorAll('select');
                    selects.forEach(function(s){ s.addEventListener('change', function(){ form.submit(); }); });
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
            
            <!-- History Statistics -->
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
        </div>
    </div>
</div>
