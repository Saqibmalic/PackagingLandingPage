{{-- Gallery lightbox. Populated by app.js from the tiles in #gallery. --}}
<dialog class="lb" id="lightbox" aria-label="Box photo viewer">
  <button type="button" class="lb__x" data-lb-close aria-label="Close photo">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" fill="none"/></svg>
  </button>
  <button type="button" class="lb__nav lb__nav--prev" data-lb-prev aria-label="Previous photo">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
  </button>
  <button type="button" class="lb__nav lb__nav--next" data-lb-next aria-label="Next photo">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
  </button>
  <figure class="lb__fig">
    <img id="lb-img" src="" alt="">
    <figcaption id="lb-cap"></figcaption>
  </figure>
  <div class="lb__actions">
    <button type="button" class="btn btn--ghostlight lb__toggle" id="lb-toggle" data-track="gallery-open-inside" hidden>
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9l8-5 8 5-8 5z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 9v7l8 5 8-5V9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
      <span id="lb-toggle-txt">See it open</span>
    </button>
    <a class="btn btn--accent lb__cta" href="#quote" data-track="cta-lightbox">Quote a box like this</a>
  </div>
</dialog>
