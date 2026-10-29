@extends('layouts.site')

@section('content')
    <!-- Swiper CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    @include('website.partials.hero')
    @include('website.partials.services')
    @include('website.partials.how-it-works')
    @include('website.partials.why-choose-us')
    @include('website.partials.google-reviews')

    <!-- Footer -->
@endsection



