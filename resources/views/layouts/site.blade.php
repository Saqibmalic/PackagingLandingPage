<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
{{-- Google asks for the tag "immediately after the <head> element", and the
     reason is real: the sooner gtag.js is requested, the fewer visitors who
     bounce early go uncounted. It sits just below charset rather than above
     it because the HTML spec wants the encoding declared in the first 1024
     bytes, and a script tag ahead of it eats into that budget. Two meta tags
     cost ~100 bytes and the tag is still the first thing that loads. --}}
@include('partials.tracking')
<title>@yield('title', 'Custom Rigid Boxes | Custom Boxes Experts')</title>
<meta name="description" content="@yield('description')">
@hasSection('canonical')
    <link rel="canonical" href="@yield('canonical')">
@endif
<meta name="robots" content="@yield('robots', 'index,follow,max-image-preview:large')">
@yield('meta')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
{{-- Fonts load without blocking first paint: text renders instantly in the
     system fallback, then swaps to Plus Jakarta Sans / Source Sans 3. --}}
@php($fonts = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Source+Sans+3:wght@400;600;700&display=swap')
<link rel="preload" as="style" href="{{ $fonts }}">
<link rel="stylesheet" href="{{ $fonts }}" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="{{ $fonts }}"></noscript>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%231A3163'/><path d='M16 6l8 4.6v9.2L16 24.4 8 19.8v-9.2z' fill='none' stroke='%23F2A65A' stroke-width='2'/><rect x='12.5' y='12.5' width='7' height='7' rx='1.5' fill='%23F2A65A'/></svg>">

@yield('head')
</head>
<body @yield('body-attributes')>

@yield('body')

@livewireScripts
<!-- Start of Zendesk Widget script (Deferred for Performance) -->
<script>
    // Defer Zendesk widget until after page load or user interaction
    function loadZendesk() {
        if (window.zendeskLoaded) return;
        window.zendeskLoaded = true;

        var script = document.createElement('script');
        script.id = 'ze-snippet';
        script.src = 'https://static.zdassets.com/ekr/snippet.js?key=33aefcc6-f282-4500-92b1-189b2fa9efcb';
        script.async = true;
        document.body.appendChild(script);
    }

    // Load on interaction or after 5 seconds (whichever comes first)
    var events = ['mousedown', 'touchstart', 'scroll', 'keydown'];
    var timeout = setTimeout(loadZendesk, 5000);

    events.forEach(function(event) {
        window.addEventListener(event, function() {
            clearTimeout(timeout);
            loadZendesk();
        }, { once: true, passive: true });
    });
</script>
<!-- End of Zendesk Widget script -->
</body>
</html>
