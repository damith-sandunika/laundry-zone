@extends('settings.pages.layout_component', ['title' => 'Payment Methods', 'description' => 'Manage accepted payment methods'])
@section('content')
    @include('settings.tabs.payment_methods')
@endsection