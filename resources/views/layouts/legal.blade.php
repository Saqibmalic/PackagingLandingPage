@extends('layouts.site')

@section('robots', 'index,follow')

@section('body')

<header class="head" id="top">
  <div class="head__top">
    <div class="wrap head__top-in">
      @include('partials.logo')
      <p class="head__claim">Rigid &amp; luxury setup boxes &mdash; made in the USA</p>
      <a class="btn btn--primary btn--sm head__topcta" href="{{ route('home') }}#quote">Get a Quote</a>
    </div>
  </div>
</header>

<main class="legal">
  <div class="wrap wrap--narrow">
    <h1>@yield('heading')</h1>
    <p class="updated">@yield('updated')</p>
    @yield('legal')
  </div>
</main>

<footer class="foot">
  <div class="wrap foot__legal foot__legal--only">
    <p>&copy; {{ date('Y') }} {{ config('site.company') }}. All rights reserved.</p>
    <p>@yield('footer-links')</p>
  </div>
</footer>

@endsection
