{{-- Google Tag Manager --}}
{{-- GTM_ID is set in .env. Leave blank to disable GTM entirely. --}}
@php $gtmId = config('services.gtm_id'); @endphp

@if($gtmId)
    {{-- ── HEAD SCRIPT (paste as high in <head> as possible) ── --}}
    {{-- Usage: @include('layouts.partials.gtm') in <head> --}}
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
@endif
