@extends('layouts.app')

@section('title', 'Patient History')

@section('content')
<div class="container my-5">
    <div class="bg-white rounded-4 shadow-sm p-4 animate-in" style="border:1px solid #eef7f7;">
        @include('tabs.history', ['activeTab' => 'history'])
    </div>
</div>
@endsection
