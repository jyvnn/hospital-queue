@php($active = $activeTab ?? 'history')
@include('tabs.history', ['activeTab' => $active])
