@extends('layouts.patient')

@section('title', 'Patient Portal - Queue & History')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="bg-white rounded-4 shadow-sm p-4 card animate-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Patient Portal</h3>
                        <div class="card-description">View the current queue and your visit history. This is a public demo UI (no login required).</div>
                    </div>
                    <div>
                        <small class="text-muted">Status: <span id="refreshStatus">Live (demo)</span></small>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="d-flex gap-2 mb-3">
                        <button id="tabQueue" class="btn btn-primary">Queue</button>
                        <button id="tabHistory" class="btn">History</button>
                    </div>

                    <div id="panelQueue">
                        <h5>Current Queue</h5>
                        <p class="card-description">People currently waiting to be seen. Positions update in real-time in a production setup; this demo uses static sample data.</p>

                        <div class="table-responsive">
                            <table class="table" id="queueTable" aria-describedby="queueDesc">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Assigned Doctor</th>
                                        <th>Check-in</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- populated by JS -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button id="refreshBtn" class="btn">Refresh</button>
                        </div>
                    </div>

                    <div id="panelHistory" style="display:none;">
                        <h5>Your Visit History</h5>
                        <p class="card-description">A short list of previous visits for this demo user (static sample data).</p>

                        <div class="table-responsive">
                            <table class="table" id="historyTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doctor</th>
                                        <th>Notes</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- populated by JS -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button id="downloadHistory" class="btn">Download</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Demo static data (no DB/auth). Replace with AJAX calls to a real API when available.
    const sampleQueue = [
        { name: 'Maria Lopez', doctor: 'Dr. A. Santos', check_in: '2025-11-04 08:12', status: 'Waiting' },
        { name: 'John Dela Cruz', doctor: 'Dr. B. Reyes', check_in: '2025-11-04 08:18', status: 'Waiting' },
        { name: 'Ana Torres', doctor: 'Dr. A. Santos', check_in: '2025-11-04 08:24', status: 'In Progress' },
        { name: 'Carlos Mendoza', doctor: 'Unassigned', check_in: '2025-11-04 08:30', status: 'Waiting' }
    ];

    const sampleHistory = [
        { date: '2025-10-28', doctor: 'Dr. A. Santos', notes: 'Routine check-up', status: 'Completed' },
        { date: '2025-09-12', doctor: 'Dr. C. Lim', notes: 'Follow-up - blood work', status: 'Completed' }
    ];

    function renderQueue() {
        const tbody = document.querySelector('#queueTable tbody');
        tbody.innerHTML = '';
        sampleQueue.forEach((p, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${i+1}</td>
                <td>${escapeHtml(p.name)}</td>
                <td>${escapeHtml(p.doctor)}</td>
                <td>${escapeHtml(p.check_in)}</td>
                <td>${escapeHtml(p.status)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderHistory() {
        const tbody = document.querySelector('#historyTable tbody');
        tbody.innerHTML = '';
        sampleHistory.forEach(h => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(h.date)}</td>
                <td>${escapeHtml(h.doctor)}</td>
                <td>${escapeHtml(h.notes)}</td>
                <td>${escapeHtml(h.status)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function escapeHtml(s){
        if (!s) return '';
        return s.replace(/&/g, '&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    document.addEventListener('DOMContentLoaded', function(){
        const tabQueue = document.getElementById('tabQueue');
        const tabHistory = document.getElementById('tabHistory');
        const panelQueue = document.getElementById('panelQueue');
        const panelHistory = document.getElementById('panelHistory');

        tabQueue.addEventListener('click', function(){
            tabQueue.classList.add('btn-primary');
            tabQueue.classList.remove('btn');
            tabHistory.classList.remove('btn-primary');
            tabHistory.classList.add('btn');
            panelQueue.style.display = '';
            panelHistory.style.display = 'none';
        });
        tabHistory.addEventListener('click', function(){
            tabHistory.classList.add('btn-primary');
            tabHistory.classList.remove('btn');
            tabQueue.classList.remove('btn-primary');
            tabQueue.classList.add('btn');
            panelHistory.style.display = '';
            panelQueue.style.display = 'none';
        });

        document.getElementById('refreshBtn').addEventListener('click', function(){
            // For demo, simply re-render. In real app you'd fetch updated queue from server.
            renderQueue();
            document.getElementById('refreshStatus').textContent = 'Refreshed (demo)';
            setTimeout(()=> document.getElementById('refreshStatus').textContent = 'Live (demo)', 1200);
        });

        document.getElementById('downloadHistory').addEventListener('click', function(){
            const csv = sampleHistory.map(h => `${h.date},"${h.doctor}","${h.notes}",${h.status}`).join('\n');
            const blob = new Blob(["Date,Doctor,Notes,Status\n" + csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'visit-history.csv'; document.body.appendChild(a); a.click(); a.remove();
            URL.revokeObjectURL(url);
        });

        // initial render
        renderQueue();
        renderHistory();
    });
</script>
@endpush

@endsection
