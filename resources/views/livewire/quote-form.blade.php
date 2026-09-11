<div class="formcard @if ($modal) formcard--modal @endif" @if (! $modal) id="quote" @endif>
  @unless ($modal)
    <div class="formcard__head">
      <h2>Get your free 3D mockup &amp; quote</h2>
      <p>Four fields to start. A rigid box specialist replies within one business hour.</p>
    </div>
  @endunless

  <form class="form" wire:submit="submit" novalidate>
    {{-- Bots fill every field they can find. This one is off-screen and
         tabbable only by a machine, so anything in it fails the submission. --}}
    <div class="hp" aria-hidden="true">
      <label>Company website<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
    </div>

    @if ($modal)
      <p class="modal__lede">Four fields to start. A rigid box specialist replies within one business hour.</p>
    @endif

    <div class="field @error('name') is-bad @enderror">
      <label for="{{ $this->getId() }}-name">Full name <span class="req">*</span></label>
      <input id="{{ $this->getId() }}-name" wire:model.blur="name" type="text" autocomplete="name"
             placeholder="Jordan Reyes" @error('name') aria-invalid="true" @enderror>
      @error('name') <p class="err">{{ $message }}</p> @enderror
    </div>

    <div class="field-row">
      <div class="field @error('email') is-bad @enderror">
        <label for="{{ $this->getId() }}-email">Work email <span class="req">*</span></label>
        <input id="{{ $this->getId() }}-email" wire:model.blur="email" type="email" autocomplete="email"
               inputmode="email" placeholder="you@brand.com" @error('email') aria-invalid="true" @enderror>
        @error('email') <p class="err">{{ $message }}</p> @enderror
      </div>
      <div class="field @error('phone') is-bad @enderror">
        <label for="{{ $this->getId() }}-phone">Phone <span class="req">*</span></label>
        <input id="{{ $this->getId() }}-phone" wire:model.blur="phone" type="tel" autocomplete="tel"
               inputmode="tel" data-phone placeholder="(555) 010-2233" @error('phone') aria-invalid="true" @enderror>
        @error('phone') <p class="err">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="field @error('quantity') is-bad @enderror">
      <label for="{{ $this->getId() }}-qty">Quantity needed <span class="req">*</span></label>
      <select id="{{ $this->getId() }}-qty" wire:model.blur="quantity" @error('quantity') aria-invalid="true" @enderror>
        <option value="">Select quantity</option>
        @foreach ($this->quantities() as $option)
          <option value="{{ $option }}">{{ $option }}</option>
        @endforeach
      </select>
      @error('quantity') <p class="err">{{ $message }}</p> @enderror
    </div>

    @unless ($modal)
      <p class="formcard__note">After you submit, you can add box size &amp; finishing to make the quote exact &mdash; optional, about 40 seconds.</p>
    @endunless

    <button class="btn btn--primary btn--block" type="submit" data-track="form-submit" wire:loading.attr="disabled">
      <span wire:loading.remove wire:target="submit">Get My Free Quote &amp; 3D Mockup</span>
      <span wire:loading wire:target="submit">Saving your details&hellip;</span>
    </button>

    <p class="consent">
      By submitting, you agree that {{ config('site.company') }} may contact you by phone, email or SMS about your
      packaging inquiry. Message and data rates may apply. Consent is not a condition of purchase.
      See our <a href="{{ route('privacy') }}">Privacy&nbsp;Policy</a>.
    </p>

    @unless ($modal)
      <p class="formcard__alt">
        Rather talk it through? Call <a href="tel:{{ config('site.phone_e164') }}" data-track="phone-form">{{ config('site.phone') }}</a>
        or email <a href="mailto:{{ config('site.email') }}" data-track="email-form">{{ config('site.email') }}</a>
      </p>
    @endunless
  </form>
</div>
