@php($address = config('site.address'))
<footer class="foot">
  <div class="wrap foot__in">
    <div class="foot__brand">
      @include('partials.logo')
      <p>Custom rigid and luxury setup boxes for US and Canadian brands. Structural design, printing,
      foiling, finishing and delivery run under one project manager &mdash; and nothing goes to press
      until you have approved a sample you can hold.</p>
    </div>
    <div class="foot__col">
      <h3>Contact</h3>
      <p><strong>Phone:</strong> <a href="tel:{{ config('site.phone_e164') }}" data-track="phone-footer">{{ config('site.phone') }}</a></p>
      <p><strong>Email:</strong> <a href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a></p>
      <p><strong>Address:</strong> {{ $address['street'] }},<br>{{ $address['city'] }}, {{ $address['region'] }} {{ $address['postal_code'] }}, USA</p>
    </div>
    <div class="foot__col">
      <h3>Hours</h3>
      <p>{!! implode('<br>', array_map('e', config('site.hours'))) !!}</p>
    </div>
    <div class="foot__col">
      <h3>Company</h3>
      <p><a href="{{ route('privacy') }}">Privacy Policy</a></p>
      <p><a href="{{ route('terms') }}">Terms &amp; Conditions</a></p>
      <p><a href="{{ config('site.main_site') }}">Main website</a></p>
    </div>
  </div>
  <div class="wrap foot__legal">
    <p>&copy; {{ date('Y') }} {{ config('site.company') }}. All rights reserved.</p>
    <p>Prices shown are indicative ranges, not offers. Final pricing is confirmed in a written quote.</p>
  </div>
</footer>
