{{-- Every "get a quote" CTA opens this instead of scrolling the page back to
     the hero. It holds a second instance of the same Livewire component, so
     there is exactly one implementation of the form to maintain. --}}
<dialog class="modal" id="quote-modal" aria-labelledby="modal-title">
  <div class="modal__box">
    <header class="modal__head">
      <div>
        <p class="modal__step">Free 3D mockup &amp; exact quote in 1 business hour</p>
        <h2 id="modal-title">Get your free 3D mockup &amp; quote</h2>
      </div>
      <button type="button" class="modal__x" data-modal-close aria-label="Close quote form">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" fill="none"/></svg>
      </button>
    </header>

    <div class="modal__body">
      <livewire:quote-form :modal="true" />
    </div>
  </div>
</dialog>
