@php
    $adsId = config('tracking.ads_id');
    $ga4Id = config('tracking.ga4_id');
@endphp

@if ($adsId || $ga4Id)
    {{-- One gtag.js load covers both properties. Until the IDs are set in .env
         nothing is rendered at all, which keeps local runs and tests clean. --}}
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());</script>
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $adsId ?: $ga4Id }}"></script>
    <script>
        @if ($adsId) gtag('config', @json($adsId, JSON_UNESCAPED_SLASHES)); @endif
        @if ($ga4Id) gtag('config', @json($ga4Id, JSON_UNESCAPED_SLASHES)); @endif
        window.CBE_CALL_LABEL = @json(config('tracking.call_label'), JSON_UNESCAPED_SLASHES);
    </script>
@endif
