{{-- $nav (bool) shows the section links; the thank-you page hides them so the
     only ways out are the phone number and the quote already submitted. --}}
@php($showNav = $nav ?? true)
<header class="head" id="top">
  <div class="head__top">
    <div class="wrap head__top-in">
      @include('partials.logo')
      <p class="head__claim">Rigid &amp; luxury setup boxes &mdash; made in the USA, shipped free</p>
      @if ($showNav)
        <a class="btn btn--primary btn--sm head__topcta" href="#quote" data-track="cta-header">Get a Quote</a>
      @else
        <a class="btn btn--primary btn--sm head__topcta" href="tel:{{ config('site.phone_e164') }}" data-track="phone-header">{{ config('site.phone') }}</a>
      @endif
    </div>
  </div>

  @if ($showNav)
    <div class="head__bar">
      <div class="wrap head__bar-in">
        <nav class="head__nav" aria-label="Page sections">
          <a href="#work">Our Work</a>
          <a href="#styles">Box Styles</a>
          <a href="#specs">Materials &amp; Finishes</a>
          <a href="#process">How It Works</a>
          <a href="#pricing">Pricing</a>
          <a href="#faq">FAQ</a>
        </nav>
        <div class="head__contact">
          <a href="mailto:{{ config('site.email') }}" data-track="email-header">
            <svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v14H3z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M3 6l9 7 9-7" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
            <span class="lbl">Email</span> <b>{{ config('site.email') }}</b>
          </a>
          <a href="tel:{{ config('site.phone_e164') }}" data-track="phone-header">
            <svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15 15 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.4 11.4 0 00.57 3.6 1 1 0 01-.25 1z" fill="currentColor"/></svg>
            <span class="lbl">Hotline</span> <b>{{ config('site.phone') }}</b>
          </a>
        </div>
      </div>
    </div>
  @endif
</header>
