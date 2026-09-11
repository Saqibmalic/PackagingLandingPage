@extends('layouts.site')

@section('title', 'Thanks — your rigid box quote is on its way | Custom Boxes Experts')
@section('description', 'Your rigid box quote request has been received. A packaging specialist will reply within one business hour.')
@section('robots', 'noindex,nofollow')
@section('body-attributes', 'data-page="thank-you"')

@section('head')
@if ($fireConversion && config('tracking.lead_label'))
  {{-- The single "Quote Form Submit" conversion, fired once per lead.
       $fireConversion comes from a flashed session key, so a refresh or a
       bookmark cannot count the same lead twice. The email is passed for
       enhanced conversions and is hashed by Google's tag before it leaves
       the browser. --}}
  <script>
    if (typeof gtag === 'function') {
      gtag('set', 'user_data', { email: @json($lead->email) });
      gtag('event', 'conversion', { send_to: @json(config('tracking.lead_label'), JSON_UNESCAPED_SLASHES) });
      gtag('event', 'generate_lead', { value: 1, currency: @json(config('leads.currency')) });
    }
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: 'lead_submitted',
      lead_id: @json($lead->reference),
      enhanced_conversion: { email: @json($lead->email) }
    });
  </script>
@endif
@endsection

@section('body')

@include('partials.header', ['nav' => false])

<main class="ty">
  <div class="wrap">
    <div class="ty__card">
      <span class="ty__tick"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7"/></svg></span>
      <h1>Got it &mdash; your quote is being built now</h1>
      <p class="lede lede--center">A rigid box specialist has your details and will reply
      <strong>within one business hour</strong> with pricing and a free 3D mockup.</p>

      <div class="ty__next">
        <h2>What happens next</h2>
        <ul>
          <li>We review your dimensions and quantity, and flag anything that would raise your cost unnecessarily.</li>
          <li>You get itemized pricing across two quantity tiers, plus a dieline and a rendered 3D mockup of your box.</li>
          <li>If the specs need a conversation, we&rsquo;ll call the number you gave us &mdash; nothing gets quoted blind.</li>
        </ul>
      </div>

      <p class="ty__ref">Your reference is <strong>{{ $lead->reference }}</strong> &mdash; quote it if you call.</p>

      <p class="ty__aside">Need it faster, or want to talk it through right now?</p>
      <div class="ty__cta">
        <a class="btn btn--primary" href="tel:{{ config('site.phone_e164') }}" data-track="phone-thankyou">Call {{ config('site.phone') }}</a>
        <a class="btn btn--quiet btn--sm" href="{{ route('home') }}">Back to rigid boxes</a>
      </div>
    </div>

    <livewire:spec-form :lead="$lead" />
  </div>
</main>

<footer class="foot">
  <div class="wrap foot__legal foot__legal--only">
    <p>&copy; {{ date('Y') }} {{ config('site.company') }}. All rights reserved.</p>
    <p><a href="{{ route('privacy') }}">Privacy Policy</a> &middot; <a href="{{ route('terms') }}">Terms &amp; Conditions</a></p>
  </div>
</footer>

@endsection
