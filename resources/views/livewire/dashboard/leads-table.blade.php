<div class="wrap">

  {{-- ── Headline numbers ─────────────────────────────────────── --}}
  <div class="cards">
    <div class="card"><span class="card__n">{{ number_format($stats['total']) }}</span><span class="card__l">Leads all time</span></div>
    <div class="card"><span class="card__n">{{ number_format($stats['today']) }}</span><span class="card__l">Today</span></div>
    <div class="card"><span class="card__n">{{ number_format($stats['week']) }}</span><span class="card__l">Last 7 days</span></div>
    <div class="card"><span class="card__n">{{ number_format($stats['with_gclid']) }}</span><span class="card__l">From Google Ads</span></div>
    <div class="card"><span class="card__n">{{ number_format($stats['with_specs']) }}</span><span class="card__l">With box specs</span></div>
    <div class="card card--win">
      <span class="card__n">{{ number_format($stats['won']) }}</span>
      <span class="card__l">Won &middot; ${{ number_format($stats['won_value'], 2) }}</span>
    </div>
  </div>

  {{-- ── Exports ──────────────────────────────────────────────── --}}
  <section class="panel">
    <h2 class="panel__h">Send conversions back to Google Ads</h2>
    <p class="panel__p">Mark the leads that turned into real business as <strong>won</strong> and put the
    order value against them, then upload one of these files. That is what teaches Smart Bidding which
    clicks are worth paying for &mdash; without it, Google optimises for form fills rather than revenue.</p>

    <div class="export__btns">
      <a class="btn btn--primary" href="{{ route('dashboard.export', $this->exportParams('gclid')) }}">
        Download GCLID conversions
      </a>
      <a class="btn btn--dark" href="{{ route('dashboard.export', $this->exportParams('enhanced')) }}">
        Download enhanced conversions
      </a>
      <a class="btn btn--ghost" href="{{ route('dashboard.export', $this->exportParams('all')) }}">
        Download everything (CSV)
      </a>
    </div>

    <p class="export__note">
      Each file contains exactly the leads matching the filters below, newest first.
      <strong>GCLID conversions</strong> &rarr; Tools &amp; Settings &rsaquo; Conversions &rsaquo; Uploads. It
      only includes leads that carried a click id, and declares the
      <code>{{ config('leads.ads_timezone') }}</code> time zone &mdash; that must match your Google Ads account.
      <strong>Enhanced conversions</strong> is the fallback for leads with no click id: email and phone are
      SHA-256 hashed here, so no personal data leaves your server in readable form.
      Both use the conversion action name <code>{{ config('leads.conversion_name') }}</code>.
    </p>
  </section>

  {{-- ── Filters ──────────────────────────────────────────────── --}}
  <div class="filters">
    <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search name, email, phone, reference, notes…" aria-label="Search leads">

    <select wire:model.live="status" aria-label="Filter by status">
      <option value="">Any status</option>
      @foreach (config('leads.statuses') as $option)
        <option value="{{ $option }}">{{ ucfirst($option) }}</option>
      @endforeach
    </select>

    <label class="filters__date">From <input type="date" wire:model.live="from"></label>
    <label class="filters__date">To <input type="date" wire:model.live="to"></label>

    <label class="filters__check"><input type="checkbox" wire:model.live="gclidOnly"> Google Ads only</label>
    <label class="filters__check"><input type="checkbox" wire:model.live="hasSpecs"> Has specs</label>

    <button class="btn btn--ghost btn--sm" type="button" wire:click="clearFilters">Clear</button>
  </div>

  <p class="count">
    {{ number_format($leads->total()) }} {{ Str::plural('lead', $leads->total()) }} matching
    <span wire:loading class="count__busy">&middot; updating…</span>
  </p>

  {{-- ── The leads ────────────────────────────────────────────── --}}
  @if ($leads->isEmpty())
    <div class="empty">
      <h3>No leads to show</h3>
      <p>Either nothing has come in yet, or no lead matches these filters. The quote form on the
      landing page writes here the moment someone submits it.</p>
    </div>
  @else
    <div class="tablewrap">
      <table class="leads">
        <thead>
          <tr>
            <th>Received</th><th>Contact</th><th>Quantity</th><th>Source</th>
            <th>Status</th><th>Value</th><th><span class="vh">Actions</span></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($leads as $lead)
            <tr class="lead" wire:key="lead-{{ $lead->id }}">
              <td data-th="Received">
                <span class="when">{{ $lead->created_at->format('j M Y, H:i') }}</span>
                <span class="lid">{{ $lead->reference }}</span>
              </td>
              <td data-th="Contact">
                <span class="nm">{{ $lead->name }}</span>
                <a class="lnk" href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                <a class="lnk" href="tel:{{ preg_replace('/\D/', '', $lead->phone) }}">{{ $lead->phone }}</a>
              </td>
              <td data-th="Quantity">
                {{ $lead->quantity }}
                @if ($lead->hasSpecs())
                  <span class="tag tag--spec">specs added</span>
                @endif
              </td>
              <td data-th="Source">
                @if ($lead->gclid)
                  <span class="tag tag--ads">Google Ads</span>
                @elseif ($lead->utm_source)
                  <span class="tag">{{ $lead->utm_source }}</span>
                @else
                  <span class="tag tag--dim">direct</span>
                @endif
                @if ($lead->utm_campaign)
                  <span class="sub">{{ $lead->utm_campaign }}</span>
                @endif
              </td>
              <td data-th="Status">
                <select class="status status--{{ $lead->status }}"
                        wire:change="setStatus({{ $lead->id }}, $event.target.value)"
                        aria-label="Status for {{ $lead->reference }}">
                  @foreach (config('leads.statuses') as $option)
                    <option value="{{ $option }}" @selected($lead->status === $option)>{{ ucfirst($option) }}</option>
                  @endforeach
                </select>
              </td>
              <td data-th="Value">
                <input class="val" type="number" step="0.01" min="0" value="{{ $lead->value }}"
                       wire:change="setValue({{ $lead->id }}, $event.target.value)"
                       aria-label="Order value for {{ $lead->reference }}">
              </td>
              <td class="row-actions">
                <button class="btn btn--ghost btn--sm" type="button" wire:click="toggle({{ $lead->id }})">
                  {{ $expanded === $lead->id ? 'Hide' : 'Details' }}
                </button>
              </td>
            </tr>

            @if ($expanded === $lead->id)
              <tr class="detail" wire:key="detail-{{ $lead->id }}">
                <td colspan="7">
                  <div class="detail__grid">

                    <div>
                      <h4>The box</h4>
                      @if ($lead->hasSpecs())
                        <dl>
                          @if ($lead->dimensions()) <dt>Size</dt><dd>{{ $lead->dimensions() }}</dd> @endif
                          @if ($lead->style) <dt>Style</dt><dd>{{ $lead->style }}</dd> @endif
                          @if ($lead->board) <dt>Board</dt><dd>{{ $lead->board }}</dd> @endif
                          @if ($lead->wrap) <dt>Wrap</dt><dd>{{ $lead->wrap }}</dd> @endif
                          @if ($lead->insert) <dt>Insert</dt><dd>{{ $lead->insert }}</dd> @endif
                          @if ($lead->finish) <dt>Finishing</dt><dd>{{ implode(', ', $lead->finish) }}</dd> @endif
                          @if ($lead->second_quantity) <dt>Compare</dt><dd>{{ $lead->second_quantity }}</dd> @endif
                          @if ($lead->need_by) <dt>Needed by</dt><dd>{{ $lead->need_by->format('j M Y') }}</dd> @endif
                        </dl>
                      @else
                        <p class="quote">No specs yet &mdash; this lead stopped after the contact step.
                        Call them; do not wait for the spec form.</p>
                      @endif

                      @if ($lead->notes)
                        <h4>What they told us</h4>
                        <p class="quote">{{ $lead->notes }}</p>
                      @endif

                      @if ($lead->files)
                        <h4>Artwork</h4>
                        <ul class="files">
                          @foreach ($lead->files as $file)
                            <li>
                              <a href="{{ route('dashboard.artwork', [$lead, $file['stored']]) }}">{{ $file['original'] }}</a>
                              <span class="sub">{{ number_format($file['size'] / 1024, 0) }} KB</span>
                            </li>
                          @endforeach
                        </ul>
                      @endif
                    </div>

                    <div>
                      <h4>Where it came from</h4>
                      <dl>
                        <dt>Received</dt><dd>{{ $lead->created_at->format('j M Y, H:i:s') }}</dd>
                        <dt>GCLID</dt><dd class="mono brk">{{ $lead->gclid ?: '—' }}</dd>
                        @if ($lead->gbraid) <dt>GBRAID</dt><dd class="mono brk">{{ $lead->gbraid }}</dd> @endif
                        @if ($lead->wbraid) <dt>WBRAID</dt><dd class="mono brk">{{ $lead->wbraid }}</dd> @endif
                        <dt>Source</dt><dd>{{ $lead->utm_source ?: '—' }} / {{ $lead->utm_medium ?: '—' }}</dd>
                        <dt>Campaign</dt><dd>{{ $lead->utm_campaign ?: '—' }}</dd>
                        <dt>Keyword</dt><dd>{{ $lead->utm_term ?: '—' }}</dd>
                        <dt>Ad</dt><dd>{{ $lead->utm_content ?: '—' }}</dd>
                        <dt>Landing page</dt><dd class="brk">{{ $lead->page_url ?: '—' }}</dd>
                        <dt>IP</dt><dd class="mono">{{ $lead->ip ?: '—' }}</dd>
                        <dt>Exported</dt><dd>{{ $lead->exported_at?->format('j M Y, H:i') ?: 'not yet' }}</dd>
                      </dl>
                    </div>

                    <div>
                      <h4>Your notes</h4>
                      <textarea class="notes" rows="5" wire:model="noteDrafts.{{ $lead->id }}"
                                placeholder="Quoted $2.80/unit at 1,000. Calling back Thursday."></textarea>
                      <div class="detail__btns">
                        <button class="btn btn--primary btn--sm" type="button" wire:click="saveNotes({{ $lead->id }})">Save notes</button>
                        <button class="btn btn--danger btn--sm" type="button"
                                wire:click="delete({{ $lead->id }})"
                                wire:confirm="Delete this lead permanently? This cannot be undone.">Delete lead</button>
                      </div>
                      @if ($justSaved === $lead->id)
                        <p class="saved">Saved</p>
                      @endif
                    </div>

                  </div>
                </td>
              </tr>
            @endif
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="pager">{{ $leads->links('vendor.pagination.dashboard') }}</div>
  @endif
</div>
