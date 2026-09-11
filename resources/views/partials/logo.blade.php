{{-- $href is optional; callers that omit it link back to the landing page. --}}
<a class="logo" href="{{ $href ?? route('home') }}" aria-label="{{ config('site.company') }}">
  <svg class="logo__mark" viewBox="0 0 40 44" aria-hidden="true">
    <path d="M20 2l16 9.2v18.4L20 42 4 29.6V11.2z" fill="none" stroke="#1A3163" stroke-width="3"/>
    <rect x="13" y="15" width="14" height="14" rx="3" fill="#F2A65A"/>
  </svg>
  <span class="logo__txt"><strong>CUSTOM</strong><em>Boxes Experts</em></span>
</a>
