@php($active = $activeTab ?? 'queue')
<div id="queueTab" class="tab-content{{ $active !== 'queue' ? ' hidden' : '' }}">
    <!-- Waiting Queue -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <div class="card-title">Current Queue</div>
            <div class="card-description" id="waitingCount">{{ isset($waitingPatients) ? count($waitingPatients) . ' patients waiting' : '0 patients waiting' }}</div>
        </div>
        <div class="card-body">
            <div id="waitingQueue">
                @if(isset($waitingPatients) && $waitingPatients->count())
                    @foreach($waitingPatients as $p)
                        <div class="card mb-4">
                            <div class="card-body d-flex justify-content-between align-items-start">
                                <div style="flex:1">
                                    <h5 class="mb-1">{{ $p['name'] }} <small class="text-muted">({{ $p['age'] }}, {{ $p['gender'] }})</small></h5>
                                    <p class="small text-muted mb-2">{{ $p['symptoms'] }}</p>
                                    <div class="small text-muted">Priority: {{ $p['priority'] ?? 'Regular' }} • Service: {{ $p['service_type'] }}</div>
                                    <div class="small text-muted">Check-in: {{ \Carbon\Carbon::parse($p['check_in_time'])->format('g:i:s A') }}</div>

                                    {{-- Recommended doctors removed per UX request --}}
                                </div>

                                <div style="min-width:220px; margin-left:20px;">
                                    <form action="{{ url('/patients/assign-doctor') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="patient_id" value="{{ $p['id'] }}">
                                        <div class="form-group">
                                            <select name="doctor_id" class="form-select">
                                                <option value="">Assign doctor</option>
                                                @foreach($doctors ?? [] as $d)
                                                    <option value="{{ $d->id }}">{{ $d->full_name }} - {{ max(0, ($d->max_patients_per_day ?? 0) - ($d->current_patients ?? 0)) }} slots left</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mt-2 text-end">
                                            <button type="submit" class="btn btn-primary">Assign</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div>No patients currently waiting in queue</div>
                @endif
            </div>
        </div>
    </div>

    <!-- In Progress Consultations -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <div class="card-title">In Progress Consultations</div>
            <div class="card-description" id="inProgressCount">{{ isset($inProgressPatients) ? count($inProgressPatients) . ' ongoing consultations' : '0 ongoing consultations' }}</div>
        </div>
        <div class="card-body">
            <div id="inProgressQueue">
                @if(isset($inProgressPatients) && $inProgressPatients->count())
                    @foreach($inProgressPatients as $p)
                        <div class="card mb-4">
                            <div class="card-body d-flex justify-content-between align-items-start">
                                <div style="flex:1">
                                    <h5 class="mb-1">{{ $p['name'] }} <small class="text-muted">({{ $p['age'] }})</small></h5>
                                    <div class="small text-muted">With Dr. {{ optional($doctors->firstWhere('id', $p['assigned_doctor_id']))->full_name ?? '—' }}</div>
                                    <div class="small text-muted">Service: {{ $p['service_type'] }}</div>
                                </div>
                                <div style="min-width:220px; margin-left:20px; text-align:right;">
                                    <form action="{{ url('/patients/complete') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="patient_id" value="{{ $p['id'] }}">
                                        <button type="submit" class="btn btn-primary">Complete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div>No ongoing consultations</div>
                @endif
            </div>
        </div>
    </div>
</div>
