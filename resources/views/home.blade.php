@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="container-fluid" style="background:#f3fbfb; min-height:80vh; padding:50px 0;">
    <div class="container">
        <div class="bg-white rounded-4 shadow-sm p-4 animate-in" style="border:1px solid #eef7f7;">
            {{-- Central header and tabs are provided by layouts.app; include only the queue tab content here. --}}
            @include('tabs.queue', ['activeTab' => 'queue'])
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/event-handlers.js') }}"></script>
    <script src="{{ asset('assets/js/queue-management.js') }}"></script>
    <script src="{{ asset('assets/js/doctor-management.js') }}"></script>
    <script src="{{ asset('assets/js/patient-history.js') }}"></script>
    <script src="{{ asset('assets/js/reports-analytics.js') }}"></script>
@endpush
