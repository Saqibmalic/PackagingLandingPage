@extends('layouts.site')

@section('title', 'Custom Rigid Boxes | Free 3D Mockup & Quote in 1 Hour | Custom Boxes Experts')
@section('description', 'Custom rigid boxes made to spec — magnetic closure, drawer, shoulder neck & book style. 2mm–3mm greyboard, foil, emboss, soft-touch. Free 3D mockup, quote in 1 hour, free US shipping, 100 unit minimum.')
@section('canonical', config('site.canonical'))

@section('meta')
<meta property="og:type" content="website">
<meta property="og:title" content="Custom Rigid Boxes — Free 3D Mockup &amp; Quote in 1 Hour">
<meta property="og:description" content="Luxury rigid setup boxes for beauty, spirits, tech and jewelry brands. 100 unit minimum, free design support, free US shipping.">
<meta property="og:url" content="{{ config('site.canonical') }}">
<meta property="og:site_name" content="{{ config('site.company') }}">
@endsection

@section('head')
@include('partials.schema')
@endsection

@section('body')

@include('partials.header')

<a class="skip" href="#quote">Skip to quote form</a>

<main>

{{-- ── Hero + lead form ──────────────────────────────────────── --}}
<section class="hero">
  <div class="wrap hero__grid">

    <div class="hero__copy">
      <p class="eyebrow"><span class="dot"></span> Rigid &amp; setup boxes only &mdash; this page, this specialty</p>
      <h1>Custom Rigid Boxes That Feel Like the Brand You&rsquo;re Building</h1>
      <p class="lede">
        Magnetic closure, drawer, shoulder-neck and book-style rigid boxes on 2mm&ndash;3mm greyboard,
        wrapped in the stock you choose, finished with foil, emboss or soft-touch.
        Send your specs and get a <strong>free 3D mockup and an exact quote within one hour</strong>.
      </p>
    </div>

    <div class="hero__support">
      <ul class="ticks">
        <li>Free 3D mockup &amp; dieline &mdash; before you spend a dollar</li>
        <li>No die, plate or design charges &mdash; ever</li>
        <li>You approve a physical sample before the run starts</li>
        <li>Free shipping across the USA &amp; Canada</li>
      </ul>

      <div class="hero__proof">
        <div class="proof"><strong>100</strong><span>unit minimum<br>per size &amp; style</span></div>
        <div class="proof"><strong>12&ndash;15</strong><span>business days<br>standard turnaround</span></div>
        <div class="proof"><strong>1 hr</strong><span>quote response<br>in business hours</span></div>
      </div>

      <p class="hero__cred">We don&rsquo;t broker your job out. <strong>Structure, print, foil, wrap and
      hand-assembly happen on our own floor</strong> &mdash; one project manager, one quality standard,
      one person to call when the date matters.</p>
    </div>

    <livewire:quote-form />

  </div>
</section>

{{-- ── Industry strip ────────────────────────────────────────── --}}
<section class="strip">
  <div class="wrap strip__in">
    <p class="strip__label">Rigid packaging built for</p>
    <ul class="strip__list">
      <li>Beauty &amp; skincare</li><li>Spirits &amp; wine</li><li>Jewelry</li>
      <li>Consumer electronics</li><li>Cannabis</li><li>Subscription &amp; PR kits</li><li>Confectionery</li>
    </ul>
  </div>
</section>

{{-- ════════════════════════════════════════════════════════════
     FILM WALL — self-hosted, silent, looping.
     Clips are cut from real production footage and stripped of audio,
     so they behave like moving photographs. Nothing downloads until a
     tile scrolls into view: the <video> carries no src until the
     observer in app.js assigns one, so the LCP and the initial payload
     are untouched, and off-screen tiles pause themselves.
     ════════════════════════════════════════════════════════════ --}}
<section class="sec sec--cream" id="film">
  <div class="wrap">
    <header class="sechead">
      <h2>This is what lands on your customer&rsquo;s doorstep</h2>
      <p>Rigid boxes we produced, filmed as they actually open &mdash; not renders. Both of these
      started as a phone call about specs, exactly like the one you&rsquo;re about to have.</p>
    </header>

    <div class="films">
      @foreach ([
        ['file' => 'rigid-book', 'title' => 'Book-style rigid box', 'sub' => 'Hinged lid, printed tissue, inner tray', 'alt' => 'A navy book-style rigid box opening to printed tissue and an inner tray', 'track' => 'cta-film-book'],
        ['file' => 'rigid-lidbase', 'title' => 'Two-piece lid & base', 'sub' => 'Snug lift-off lid, fitted candle well', 'alt' => 'A red two-piece rigid box, lid lifted off and a candle taken out', 'track' => 'cta-film-lidbase'],
      ] as $film)
      <figure class="film">
        <div class="film__frame">
          <video class="film__vid" data-src="{{ asset('assets/video/'.$film['file'].'.mp4') }}"
                 poster="{{ asset('assets/video/'.$film['file'].'.jpg') }}"
                 width="540" height="960"
                 muted loop playsinline preload="none"
                 aria-label="{{ $film['alt'] }}"></video>
          <button class="film__toggle" type="button" aria-label="Pause video">
            <svg class="film__ic film__ic--pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5h3v14H8zM13 5h3v14h-3z" fill="currentColor"/></svg>
            <svg class="film__ic film__ic--play"  viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>
          </button>
        </div>
        <figcaption>
          <strong>{{ $film['title'] }}</strong>
          <span>{{ $film['sub'] }}</span>
        </figcaption>
        <a class="film__cta" href="#quote" data-track="{{ $film['track'] }}">Quote a box like this</a>
      </figure>
      @endforeach
    </div>

    <p class="films__note">Sound is off by default &mdash; these are silent loops, so nothing
    will start talking at you.</p>
  </div>
</section>

{{-- ── Work gallery ──────────────────────────────────────────── --}}
<section class="sec" id="work">
  <div class="wrap">
    <header class="sechead">
      <h2>Rigid boxes we&rsquo;ve built</h2>
      <p>Magnetic closure, drawer and printed lid-and-base builds from recent production runs. Tap any box to see it larger &mdash; the caption gives the exact build, so you can point at one and say &ldquo;like that.&rdquo;</p>
    </header>

    <div class="gallery" id="gallery">
      @foreach ([
        ['src' => 'box-1', 'w' => 1194, 'h' => 896,
         'alt' => 'Botanica rigid box: navy base with a full-colour botanical printed lid, closed',
         'caption' => 'Two-piece lid & base · full-colour botanical print across the lid wrap · uncoated navy base · white inner tray · lid lifts clean off',
         'title' => 'Two-piece lid & base', 'sub' => 'Printed lid wrap, uncoated navy base'],
        ['src' => 'box-2', 'w' => 1000, 'h' => 1000,
         'alt' => 'Teal rigid drawer box with silver foil logo and an orange inner drawer with a ribbon pull',
         'caption' => 'Drawer / slide-out rigid · teal soft-touch sleeve · silver foil stamped logo · contrasting orange drawer with a woven ribbon pull',
         'title' => 'Drawer / slide-out', 'sub' => 'Soft-touch teal, silver foil, ribbon pull'],
        ['src' => 'box-3', 'w' => 1000, 'h' => 1000,
         'alt' => 'Rigid two-piece lid and base box with a full-colour sunflower wrap',
         'caption' => 'Two-piece lid & base rigid · full-colour CMYK sunflower wrap printed edge to edge across the lid and base',
         'title' => 'Two-piece lid & base', 'sub' => 'Full-colour sunflower wrap'],
      ] as $shot)
      <figure class="shot">
        <img src="{{ asset('assets/img/boxes/'.$shot['src'].'.jpg') }}"
             alt="{{ $shot['alt'] }}"
             width="{{ $shot['w'] }}" height="{{ $shot['h'] }}" loading="lazy" decoding="async"
             data-open="{{ asset('assets/img/boxes/'.$shot['src'].'.1.jpg') }}"
             data-caption="{{ $shot['caption'] }}">
        <figcaption><strong>{{ $shot['title'] }}</strong><span>{{ $shot['sub'] }}</span></figcaption>
      </figure>
      @endforeach
    </div>

    <p class="center gallery__cta">
      <a class="btn btn--primary" href="#quote" data-track="cta-gallery">Get a Box Like These Quoted</a>
      <span class="gallery__hint">Point us at any box above and we&rsquo;ll price that exact build for your product.</span>
    </p>

    {{-- Corrugated work, kept clearly separate from the rigid line-up so the
         page stays on-message for rigid box searches. --}}
    <aside class="also">
      <div class="also__thumbs">
        @foreach ([
          ['carton-flower', 'A floral printed folding carton opened to lift out a candle'],
          ['carton-soap', 'A pink folding carton being folded up around a bar of soap'],
          ['carton-tart', 'White printed folding cartons being assembled by hand'],
          ['carton-otriea', 'A printed skincare folding carton turned to show each panel'],
        ] as [$file, $alt])
        <video class="film__vid" data-src="{{ asset('assets/video/'.$file.'.mp4') }}"
               poster="{{ asset('assets/video/'.$file.'.jpg') }}" width="360" height="640"
               muted loop playsinline preload="none"
               aria-label="{{ $alt }}"></video>
        @endforeach
      </div>
      <p><strong>We also print corrugated mailers and folding cartons.</strong>
      Same press, same finishing team &mdash; useful if your rigid box needs an outer shipper, or if
      you want a lighter option for e-commerce. Mention it when you request your quote and we&rsquo;ll
      price both.</p>
    </aside>
  </div>
</section>

{{-- ── Why us ────────────────────────────────────────────────── --}}
<section class="sec" id="why">
  <div class="wrap">
    <header class="sechead">
      <h2>Why brands move their rigid box program to us</h2>
      <p>Rigid boxes are hand-assembled, unforgiving work. Almost everything that goes wrong goes wrong <em>before</em> production starts &mdash; so that&rsquo;s where we put the effort.</p>
    </header>

    <div class="grid grid--3 cards">
      <article class="card">
        <span class="card__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l8.5 4.8v9.4L12 21.5 3.5 16.7V7.3z"/><path d="M3.5 7.3L12 12l8.5-4.7M12 12v9.5"/></svg></span>
        <h3>You see it in 3D before you commit</h3>
        <p>Every quote comes with a dimensioned dieline and a rendered 3D mockup of your box &mdash; your artwork, your stock, your finishes. Free, and yours whether you order or not.</p>
      </article>
      <article class="card">
        <span class="card__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 6.5V12l3.8 2.2"/></svg></span>
        <h3>Quotes in an hour, not a week</h3>
        <p>One specialist owns your project end to end. No ticket queues, no re-explaining your specs to a new rep three emails in.</p>
      </article>
      <article class="card">
        <span class="card__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8.5l8-4.5 8 4.5v7L12 20l-8-4.5z"/><path d="M8.5 11.8l2.6 2.6 4.6-4.8"/></svg></span>
        <h3>Nothing runs without your sample</h3>
        <p>We build a physical pre-production sample of the exact box &mdash; board, wrap, foil, insert &mdash; and hold the line until you approve it in hand.</p>
      </article>
      <article class="card">
        <span class="card__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.5 6.5h17v12h-17z"/><path d="M3.5 10.5h17M8.5 6.5v12"/></svg></span>
        <h3>Structural engineering included</h3>
        <p>Board thickness, wrap allowance, insert cavities and drop-test tolerance are engineered around your product &mdash; not copied off a template.</p>
      </article>
      <article class="card">
        <span class="card__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 3.5h8l5 5v12h-13z"/><path d="M14.5 3.5v5h5M9.5 13h6M9.5 16.5h4"/></svg></span>
        <h3>One price, no surprise line items</h3>
        <p>Dies, plates, structural design, mockups and ground freight sit inside the number we quote. What you approve is what you pay.</p>
      </article>
      <article class="card">
        <span class="card__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-4.7-7-10.2A7 7 0 1119 10.8C19 16.3 12 21 12 21z"/><circle cx="12" cy="10.6" r="2.6"/></svg></span>
        <h3>US-based project management</h3>
        <p>Your account manager works US hours from our {{ config('site.address.city') }}, {{ config('site.address.region') }} office and answers the phone. Freight and delivery windows are handled before they become your problem.</p>
      </article>
    </div>
  </div>
</section>

{{-- ── Box styles ────────────────────────────────────────────── --}}
<section class="sec sec--cream" id="styles">
  <div class="wrap">
    <header class="sechead">
      <h2>Every rigid box construction we manufacture</h2>
      <p>Not sure which fits your product? Tell us what goes inside &mdash; we&rsquo;ll recommend a structure and price two options so you can compare.</p>
    </header>

    <div class="grid grid--4 styles">
      @foreach ([
        ['magnetic-closure', 'Magnetic Closure', 'Hidden neodymium magnets, satin-lined flap. The default for premium unboxing and PR kits.', 'Green rigid box with a hinged magnetic-flap lid open, revealing a satin-lined cream interior'],
        ['two-piece', 'Two-Piece Lid & Base', 'The classic setup box. Lid drops over the base — simple, sturdy, lowest cost per unit at volume.', 'Green and navy two-piece setup box with the lid lifted fully clear of the gold-lined base'],
        ['drawer', 'Drawer / Slide-Out', 'Inner tray slides from an outer sleeve, with a ribbon or die-cut pull. Reads expensive at a mid-tier price.', 'Copper drawer-style rigid box with the inner tray sliding out on a ribbon pull'],
        ['shoulder-neck', 'Shoulder Neck', 'An inner neck lifts the lid clear of the base. The signature look for fragrance and fine spirits.', 'Navy rigid box with the lid lifted, showing a contrasting cream inner neck that raises the lid clear of the base'],
        ['book-style', 'Book Style', 'Hinged cover with a magnet or ribbon catch. The shelf presence of a hardback — ideal for kits and sets.', 'Burgundy book-style rigid box with a hinged cover opening like a hardback, satin-lined cavity inside'],
        ['telescoping', 'Telescoping', 'A deep lid covering most of the base. Adds structural strength for heavier or fragile products.', 'Floral-print rigid box with a deep telescoping lid lifted over the navy-lined base'],
        ['collapsible-rigid', 'Collapsible Rigid', 'Ships flat, snaps up with magnets. Cuts inbound freight and warehouse space by up to 80%.', 'Purple collapsible rigid box shown made up beside a second one folded flat with its magnetic side flaps open'],
        ['rigid-mailer', 'Rigid Mailer & Inserts', 'E-commerce-ready rigid mailers with EVA foam, molded pulp or satin-lined cavities cut to your product.', 'Navy and copper rigid box open flat with a satin foam insert tray die-cut into compartments'],
      ] as [$file, $name, $copy, $alt])
      <article class="style">
        <div class="style__art style__art--photo"><img src="{{ asset('assets/img/styles/'.$file.'.jpg') }}" alt="{{ $alt }}" width="1200" height="976" loading="lazy" decoding="async"></div>
        <h3>{{ $name }}</h3>
        <p>{{ $copy }}</p>
      </article>
      @endforeach
    </div>

    <p class="center"><a class="btn btn--primary" href="#quote" data-track="cta-styles">Price My Box Style</a></p>
  </div>
</section>

{{-- ── Specs ─────────────────────────────────────────────────── --}}
<section class="sec" id="specs">
  <div class="wrap">
    <header class="sechead">
      <h2>The spec sheet, in plain English</h2>
      <p>Everything here mixes and matches. Have a reference box you like? Send a photo &mdash; we&rsquo;ll match it.</p>
    </header>

    <div class="specs">
      @foreach ([
        ['Board & construction', [
          'Rigid board' => '1.5mm, 2mm, 2.5mm, 3mm greyboard',
          'Retail standard' => '2mm greyboard, wrapped inside and out',
          'Assembly' => 'Hand-wrapped corners, machine-glued edges',
          'Closures' => 'Magnets, ribbon pulls, elastic bands, tuck flaps',
        ]],
        ['Wrap stocks', [
          'Printed art paper' => '128–157gsm, full CMYK + Pantone',
          'Specialty' => 'Linen, felt, leatherette, uncoated colorplan',
          'Natural' => 'Kraft, recycled and FSC®-certified stocks',
          'Interior' => 'Contrast wrap, flock, satin or velvet lining',
        ]],
        ['Printing & finishing', [
          'Print' => 'Offset CMYK, Pantone spot, white ink on dark stock',
          'Foil' => 'Gold, silver, rose gold, copper, holographic',
          'Texture' => 'Emboss, deboss, spot UV',
          'Lamination' => 'Matte, gloss, soft-touch, anti-scuff',
        ]],
        ['Inserts & protection', [
          'Foam' => 'EVA and PU, die-cut or CNC-routed cavities',
          'Paperboard' => 'Platforms, dividers, wrapped trays',
          'Sustainable' => 'Molded pulp, honeycomb, corrugated inserts',
          'Premium' => 'Satin, velvet, thermoformed PET',
        ]],
      ] as [$heading, $rows])
      <div class="spec">
        <h3>{{ $heading }}</h3>
        <dl>
          @foreach ($rows as $term => $definition)
            <dt>{{ $term }}</dt><dd>{{ $definition }}</dd>
          @endforeach
        </dl>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── Process ───────────────────────────────────────────────── --}}
<section class="sec sec--navy" id="process">
  <div class="wrap">
    <header class="sechead sechead--light">
      <h2>From your specs to your dock in four steps</h2>
      <p>You approve at every gate. Nothing moves forward until you say so.</p>
    </header>

    <ol class="steps">
      @foreach ([
        ['Send your specs', 'Dimensions, quantity, what goes inside. Rough notes or a photo of a box you like both work fine.', '1 minute'],
        ['Quote & 3D mockup', 'Itemized pricing across two quantity tiers, plus a dieline and a rendered mockup of your exact box.', 'Within 1 business hour'],
        ['Pre-production sample', 'We build one real box — printed, foiled, assembled — and ship it to you for hands-on approval.', '4–6 business days'],
        ['Production & delivery', 'Full run manufactured, QC-checked against your approved sample, shipped free to your door.', '12–15 business days'],
      ] as $i => [$title, $copy, $timing])
      <li class="step">
        <span class="step__n">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
        <h3>{{ $title }}</h3>
        <p>{{ $copy }}</p>
        <span class="step__t">{{ $timing }}</span>
      </li>
      @endforeach
    </ol>
  </div>
</section>

{{-- ── Pricing ───────────────────────────────────────────────── --}}
<section class="sec" id="pricing">
  <div class="wrap">
    <header class="sechead">
      <h2>What rigid boxes actually cost</h2>
      <p>No packaging company can post a real price list &mdash; size, board, wrap and finishing move the number too much. Here is the honest range, so you can budget before you talk to anyone.</p>
    </header>

    <div class="grid grid--3 tiers">
      <article class="tier">
        <p class="tier__q">100 &ndash; 500 units</p>
        <p class="tier__p">$3.20<span class="dash">&ndash;</span>$6.00 <span class="per">/ unit</span></p>
        <p class="tier__d">Launch runs, PR kits and market tests. Full customization, no volume commitment.</p>
      </article>
      <article class="tier tier--hi">
        <p class="tier__badge">Most quoted</p>
        <p class="tier__q">500 &ndash; 2,500 units</p>
        <p class="tier__p">$1.90<span class="dash">&ndash;</span>$3.40 <span class="per">/ unit</span></p>
        <p class="tier__d">The sweet spot for DTC and retail rollouts. Setup cost spreads thin from here.</p>
      </article>
      <article class="tier">
        <p class="tier__q">2,500+ units</p>
        <p class="tier__p">$1.20<span class="dash">&ndash;</span>$2.20 <span class="per">/ unit</span></p>
        <p class="tier__d">Program pricing with scheduled releases and warehousing options available.</p>
      </article>
    </div>

    <p class="note">Indicative ranges for a 2mm board box around 8&Prime;&times;6&Prime;&times;3&Prime; with a printed wrap and one finishing process &mdash; including dies, plates, design and ground freight. Your exact number comes back within the hour.</p>
    <p class="center"><a class="btn btn--primary" href="#quote" data-track="cta-pricing">Get My Exact Price</a></p>
  </div>
</section>

{{-- ── Testimonials ──────────────────────────────────────────
     REPLACE with real, attributable customer quotes before running ads.
     Google Ads policy prohibits fabricated testimonials. Each card that
     still holds placeholder copy hides itself, and the section goes with
     them if none are real yet.
     ────────────────────────────────────────────────────────── --}}
<section class="sec sec--cream" id="reviews">
  <div class="wrap">
    <header class="sechead"><h2>What buyers say after the first run</h2></header>

    <div class="grid grid--3 quotes">
      @foreach ([
        'REPLACE — real customer quote about mockup speed or structural help.',
        'REPLACE — real customer quote about foil/print quality or the sample process.',
        'REPLACE — real customer quote about hitting a deadline or a reorder.',
      ] as $testimonial)
      <figure class="quote">
        <div class="stars" aria-label="5 out of 5">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <blockquote>{{ $testimonial }}</blockquote>
        <figcaption><strong>Name</strong><span>Title, Brand</span></figcaption>
      </figure>
      @endforeach
    </div>
  </div>
</section>

{{-- ── FAQ ───────────────────────────────────────────────────── --}}
<section class="sec" id="faq">
  <div class="wrap wrap--narrow">
    <header class="sechead"><h2>Questions buyers ask before they order</h2></header>

    <div class="faq">
      @foreach (config('faq') as $item)
        <details>
          <summary>{{ $item['q'] }}</summary>
          <div><p>{{ $item['a'] }}</p></div>
        </details>
      @endforeach
    </div>
  </div>
</section>

{{-- ════════════════════════════════════════════════════════════
     YOUTUBE SHORTS — click-to-load facades.
     Tiles are built by app.js from the list in config/site.php, so
     adding a video is a one-line .env edit rather than an HTML change.
     Thumbnails come straight from YouTube and nothing loads from
     youtube.com until a visitor actually clicks — a cold YouTube iframe
     costs ~700KB and would wreck the LCP on a paid landing page. With
     no links configured the whole section stays hidden.
     ════════════════════════════════════════════════════════════ --}}
<section class="sec sec--cream" id="video" @if (! config('site.shorts')) hidden @endif>
  <div class="wrap">
    <header class="sechead">
      <h2>Sixty seconds on our factory floor</h2>
      <p>No stock footage, no showreel. Presses running, wraps going on by hand, finished boxes
      being packed &mdash; filmed where your order will actually be made.</p>
    </header>

    <div class="videos" id="shorts"></div>
    <script type="application/json" id="shorts-data">@json(config('site.shorts'))</script>

    @if (config('site.shorts_channel'))
      <p class="videos__social">
        More on
        <a href="{{ config('site.shorts_channel') }}" target="_blank" rel="noopener" data-track="social-youtube">YouTube&nbsp;Shorts</a>
      </p>
    @endif
  </div>
</section>

{{-- ── Final CTA ─────────────────────────────────────────────── --}}
<section class="final">
  <div class="wrap final__in">
    <div>
      <h2>Send your specs. Get a 3D mockup and a real number back within the hour.</h2>
      <p>Free mockup, free dieline, no die or plate charges &mdash; and nothing runs until you&rsquo;ve held the sample.</p>
    </div>
    <div class="final__cta">
      <a class="btn btn--accent btn--lg" href="#quote" data-track="cta-final">Get My Free Quote</a>
      <a class="btn btn--ghostlight btn--lg" href="tel:{{ config('site.phone_e164') }}" data-track="phone-final">Call {{ config('site.phone') }}</a>
    </div>
  </div>
</section>

</main>

@include('partials.footer')

{{-- ── Sticky mobile action bar ──────────────────────────────── --}}
<div class="sticky" role="region" aria-label="Quick actions">
  <a class="sticky__call" href="tel:{{ config('site.phone_e164') }}" data-track="phone-sticky">
    <svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15 15 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.4 11.4 0 00.57 3.6 1 1 0 01-.25 1z" fill="currentColor"/></svg>
    Call now
  </a>
  <a class="sticky__quote" href="#quote" data-track="cta-sticky">Get Free Quote</a>
</div>

@include('partials.lightbox')
@include('partials.quote-modal')

@endsection
