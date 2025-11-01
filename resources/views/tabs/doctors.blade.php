@php($active = $activeTab ?? 'queue')
<div id="doctorsTab" class="tab-content{{ $active !== 'doctors' ? ' hidden' : '' }}">
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <div class="card-title">Medical Staff</div>
            <div class="card-description">Doctors and their current availability</div>
        </div>
        <div class="card-body">
            <div id="doctorsGrid" class="grid grid-cols-4">
                @if(!empty($doctors) && $doctors->count())
                    @foreach($doctors as $doc)
                        <div class="card doctor-card" style="margin:8px; padding:12px;">
                            <div class="doctor-name" style="font-weight:600;">{{ $doc->full_name }}</div>
                            <div class="doctor-specialty" style="color:#6b7280; font-size:0.9rem;">{{ $doc->specialty }}</div>
                            <div style="margin-top:8px;">Availability: {{ $doc->availability ?? '—' }}</div>
                            <div>Patients: {{ $doc->current_patients ?? 0 }} / {{ $doc->max_patients_per_day ?? '—' }}</div>
                            <div style="margin-top:6px; font-weight:600; color:#0f172a;">Slots left: {{ max(0, ($doc->max_patients_per_day ?? 0) - ($doc->current_patients ?? 0)) }}</div>
                            @if(!empty($doc->expertise))
                                <div style="margin-top:6px; font-size:0.85rem; color:#374151;">{{ \Illuminate\Support\Str::limit($doc->expertise, 80) }}</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div>No doctors found.</div>
                @endif
            </div>
        </div>
    </div>
</div>
