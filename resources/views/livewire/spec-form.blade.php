<div>
  @if ($done)
    <div class="ty__card ty__card--stacked ty__card--center">
      <span class="ty__tick"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7"/></svg></span>
      <h2>{{ $lead->hasSpecs() ? 'Specs received — thank you' : 'No problem — we have what we need' }}</h2>
      <p class="lede lede--center">
        {{ $lead->hasSpecs()
            ? 'Your box details are attached to your request. We’ll factor them into the quote and reply within one business hour.'
            : 'A specialist has your contact details and will be in touch within one business hour. We can take the box specs over the phone.' }}
      </p>
      <div class="ty__cta ty__cta--spaced">
        <a class="btn btn--primary" href="tel:{{ config('site.phone_e164') }}" data-track="phone-thankyou">Call {{ config('site.phone') }}</a>
        <a class="btn btn--quiet btn--sm" href="{{ route('home') }}">Back to rigid boxes</a>
      </div>
    </div>
  @else
    <div class="ty__card ty__card--stacked ty__card--form">
      <h2 class="center">Make your quote exact <span class="opt">(optional)</span></h2>
      <p class="ty__sub">Your request is already in and a specialist is on it. Add box details here and
      we&rsquo;ll skip a round of questions &mdash; or just wait for our call.</p>

      <form class="form" wire:submit="submit" novalidate>
        <p class="modal__saved">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Your details are saved as reference <strong>{{ $lead->reference }}</strong> &mdash; we can reach you
          either way. These specs just make the quote exact.
        </p>

        <fieldset class="field">
          <legend>Box size</legend>
          <div class="dims">
            <label class="dim"><span>Length</span>
              <input wire:model.blur="length" type="number" min="0" step="0.125" inputmode="decimal" placeholder="8"></label>
            <label class="dim"><span>Width</span>
              <input wire:model.blur="width" type="number" min="0" step="0.125" inputmode="decimal" placeholder="6"></label>
            <label class="dim"><span>Depth</span>
              <input wire:model.blur="depth" type="number" min="0" step="0.125" inputmode="decimal" placeholder="3"></label>
            <label class="dim"><span>Units</span>
              <select wire:model="units">
                <option value="in">Inches</option><option value="cm">Centimetres</option><option value="mm">Millimetres</option>
              </select>
            </label>
          </div>
          @error('length') <p class="err">{{ $message }}</p> @enderror
          @error('width') <p class="err">{{ $message }}</p> @enderror
          @error('depth') <p class="err">{{ $message }}</p> @enderror
          <p class="hint">Length = left to right, Width = front to back, Depth = top to bottom.
          Not sure? Leave it blank and we&rsquo;ll size it around your product.</p>
        </fieldset>

        <div class="field-row">
          <div class="field">
            <label for="s-style">Box style</label>
            <select id="s-style" wire:model="style">
              <option value="">Not sure &mdash; recommend one</option>
              @foreach ($this->styles() as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
            </select>
          </div>
          <div class="field">
            <label for="s-board">Board thickness</label>
            <select id="s-board" wire:model="board">
              <option value="">Recommend for my product</option>
              @foreach ($this->boards() as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
            </select>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="s-wrap">Wrap stock</label>
            <select id="s-wrap" wire:model="wrap">
              <option value="">Not sure yet</option>
              @foreach ($this->wraps() as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
            </select>
          </div>
          <div class="field">
            <label for="s-insert">Insert</label>
            <select id="s-insert" wire:model="insert">
              <option value="">No insert / not sure</option>
              @foreach ($this->inserts() as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
            </select>
          </div>
        </div>

        <fieldset class="field">
          <legend>Finishing</legend>
          <div class="chips">
            @foreach ($this->finishes() as $option)
              <label class="chip">
                <input type="checkbox" wire:model="finish" value="{{ $option }}"><span>{{ $option }}</span>
              </label>
            @endforeach
          </div>
        </fieldset>

        <div class="field-row">
          <div class="field">
            <label for="s-qty2">Compare a second quantity <span class="opt">(optional)</span></label>
            <input id="s-qty2" wire:model.blur="second_quantity" type="text" inputmode="numeric" placeholder="e.g. 1,000">
          </div>
          <div class="field @error('need_by') is-bad @enderror">
            <label for="s-date">Need it in hand by</label>
            <input id="s-date" wire:model.blur="need_by" type="date" min="{{ now()->toDateString() }}">
            @error('need_by') <p class="err">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="field @error('files.*') is-bad @enderror @error('files') is-bad @enderror">
          <label for="s-files">Upload artwork or a reference photo <span class="opt">(optional)</span></label>
          <div class="upload">
            <input id="s-files" wire:model="files" type="file" multiple
                   accept=".jpg,.jpeg,.png,.pdf,.ai,.eps,.zip">
            <p class="upload__hint">JPG, PNG, PDF, AI, EPS or ZIP &mdash; up to {{ config('leads.max_files') }} files,
            20MB each. A photo of a box you like works just as well as a dieline.</p>
            <p class="upload__status" wire:loading wire:target="files">Uploading&hellip;</p>
            @if ($files)
              <ul class="upload__list">
                @foreach ($files as $file)
                  <li>{{ $file->getClientOriginalName() }}</li>
                @endforeach
              </ul>
            @endif
          </div>
          @error('files') <p class="err">{{ $message }}</p> @enderror
          @foreach ($errors->get('files.*') as $messages)
            @foreach ($messages as $message) <p class="err">{{ $message }}</p> @endforeach
          @endforeach
        </div>

        <div class="field @error('notes') is-bad @enderror">
          <label for="s-notes">Anything else we should know?</label>
          <textarea id="s-notes" wire:model.blur="notes" rows="3"
                    placeholder="Product going inside, launch deadline, a competitor's box you want matched, Pantone colors…"></textarea>
          @error('notes') <p class="err">{{ $message }}</p> @enderror
        </div>

        <button class="btn btn--primary btn--block" type="submit" data-track="spec-submit" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="submit">Send My Specs &amp; Get the Quote</span>
          <span wire:loading wire:target="submit">Sending your specs&hellip;</span>
        </button>
        <button type="button" class="modal__skip" wire:click="skip">No thanks &mdash; I&rsquo;ll send them later</button>
      </form>
    </div>
  @endif
</div>
