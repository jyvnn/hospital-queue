@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
<div class="container">
    <h2 class="mb-4">Appointments</h2>

    @if($appointments->isEmpty())
        <div class="alert alert-info">No appointments found.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Scheduled At</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appt)
                        <tr>
                            <td>{{ $appt->id }}</td>
                            <td>
                                @if($appt->patient_id)
                                    {{ optional(App\Models\Patient::find($appt->patient_id))->full_name ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($appt->doctor_id)
                                    {{ optional(App\Models\Doctor::find($appt->doctor_id))->full_name ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $appt->scheduled_at ? $appt->scheduled_at : '—' }}</td>
                            <td>{{ $appt->status ?? '—' }}</td>
                            <td>{{ 
                              \Illuminate\Support\Str::limit($appt->notes ?? '', 80) 
                            }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
