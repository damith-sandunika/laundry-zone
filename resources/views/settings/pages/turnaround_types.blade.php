@extends('settings.pages.layout_component', ['title' => 'Turnarounds', 'description' => 'Manage service turnarounds'])
@section('content')
    @include('settings.tabs.turnaround_types')
@endsection