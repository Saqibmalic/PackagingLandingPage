# Rigid Boxes Landing Page — Custom Boxes Experts

A single-purpose, ads-optimized landing page for **custom rigid boxes only**, built to match the
customboxesexperts.com theme (navy `#1A3163`, amber `#F2A65A`, mint `#D0F3EC`, cream `#FEF6E9`).

Static HTML/CSS/JS — no build step, no framework, no dependencies. Drop it on any host.

```
index.html            The landing page + two-step quote modal
thank-you.html        Post-submit page (noindex) where the conversion fires
privacy-policy.html   Required by Google Ads — must be reachable
terms.html            Trust/transparency signal for landing page quality
submit-lead.php       Optional PHP lead handler (2 stages, uploads, CSV backup)
robots.txt
assets/css/styles.css All styling. Brand tokens live in :root at the top.
assets/js/main.js     Two-step flow, validation, tracking, gclid/UTM capture
uploads/              Created on first artwork upload (see Security below)
```

## The two-step quote flow

Every "get a quote" button opens a modal. The hero form is step 1 inline; both paths land in
the same place.

```
Step 1 — name, email, phone, quantity
   │     ↓ posts on its own, gets a lead_id back
   │     ↓ LEAD CONVERSION FIRES HERE
   │     ↓ you receive "New Rigid Box Lead — call this person now"
   ▼
Step 2 — L×W×D + units, style, board, wrap, insert, finishing,
         compare quantity, in-hands date, artwork upload, notes
         ↓ posts as a follow-up against the same lead_id
         ↓ you receive "Box Specs Added [LEAD ID]"
         └─ or they hit "Skip — I'll send specs later" and you
            still have a fully contactable lead
```

**Why step 1 posts by itself:** a long spec form that only submits at the end throws away every
buyer who quits halfway. Here the contact record is banked the moment it is complete, so the
detailed questions can be as thorough as you like without costing you leads. The Google Ads
conversion fires at step 1 for the same reason — that is the moment you actually got something
of value.

If JavaScript is unavailable the hero form posts normally as a complete stage-1 lead and
redirects to the thank-you page. Nothing is lost.

---

## 1. Before you go live — 8 required edits

| # | What | Where |
|---|------|-------|
| 1 | Replace `AW-XXXXXXXXXX` with your Google Ads conversion ID | `index.html`, `thank-you.html` (head) |
| 2 | Replace `G-XXXXXXXXXX` with your GA4 measurement ID | `index.html`, `thank-you.html` (head) |
| 3 | Replace `REPLACE_LEAD_LABEL` / `REPLACE_CALL_LABEL` conversion labels | `assets/js/main.js` (top), `thank-you.html` |
| 4 | Replace the three `REPLACE —` testimonials with **real, attributable** quotes | `index.html` → `#reviews` |
| 5 | Point the form at your handler (PHP file, CRM, or Zapier webhook) | `index.html` → `<form action="…">` |
| 6 | Set the recipient email in the PHP handler | `submit-lead.php` → `$TO`, `$FROM` |
| 7 | Update `<link rel="canonical">` and the OG URLs to the real URL | `index.html` (head) |
| 8 | Confirm turnaround, MOQ and price ranges match what sales can actually deliver | `index.html` throughout |

**Do not skip #4.** Google Ads prohibits fabricated testimonials, and a disapproval on a
lead-gen page is hard to reverse.

### Optional
- Your homepage promises a quote **in 15 minutes**. This page says **1 hour**. Pick one and make it
  consistent across the site — mismatched claims hurt both trust and ad review. Search
  `within one hour` / `1 hr` / `1 business hour` in `index.html` to change it.
- The local `privacy-policy.html` and `terms.html` are complete and ad-compliant. If you'd rather use
  the live site's versions, swap the footer/consent links for those URLs — just make sure they load.

---

## 2. Deployment

Recommended URL: `https://www.customboxesexperts.com/custom-rigid-boxes/`
(a real subfolder on the main domain — inherits domain trust, keeps the ad destination on-brand).

Upload the files to that folder. The PHP handler needs PHP 7.4+ and a working `mail()` or SMTP
setup; if your host blocks `mail()`, use Formspree, a CRM webhook or Zapier and set the form
`action` to that endpoint instead.

### Security — read this before enabling uploads

**Move `leads.csv` outside the web root** if your host allows it — it is a plain-text backup of
every lead. `robots.txt` blocks crawlers from it, but that is not access control.

The artwork upload accepts `jpg, jpeg, png, pdf, ai, eps, zip` only, caps files at 5 × 20MB,
discards the original filename in favour of a random one, and drops an `.htaccess` into
`uploads/` that disables the PHP engine and denies direct access. That combination is what stops
an upload form from becoming a way to run code on your server. If your host runs nginx (where
`.htaccess` does nothing), move `$UPLOAD_DIR` outside the web root or add an nginx rule denying
execution in that directory — otherwise disable the upload field.

Add these to `.htaccess` for speed (Core Web Vitals feed into landing page experience):

```apache
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript image/svg+xml
</IfModule>
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
</IfModule>
```

---

## 3. Google Ads setup

### Conversion actions
Create two, both **Primary**:

| Action | Type | Counting | Value |
|---|---|---|---|
| Rigid Quote Form | Website → `generate_lead` | One | Set a value (e.g. $40 = margin × close rate) |
| Rigid Phone Call | Website → phone click | One | Same |

Values matter more than most advertisers think: with values set you can move from Maximize
Conversions to **Maximize Conversion Value** or **tROAS** once you have ~30 conversions/month.

Both fire automatically — the form conversion fires on submit *and* on `thank-you.html`
(Google de-duplicates), and every `tel:` link on the page fires the call conversion.

### Ad group structure — one page, tight themes

The page ranks well for Quality Score because every one of these has matching on-page copy.
Run each as a **separate ad group** with its own ad copy pointing at the relevant anchor.

| Ad group | Core keywords | Landing URL |
|---|---|---|
| Rigid boxes (core) | custom rigid boxes, rigid boxes wholesale, custom rigid packaging | `/custom-rigid-boxes/` |
| Magnetic closure | custom magnetic closure boxes, magnetic gift box wholesale | `…/#styles` |
| Drawer / sliding | rigid drawer boxes, sliding rigid box custom | `…/#styles` |
| Shoulder neck | shoulder neck boxes, neck box packaging | `…/#styles` |
| Luxury / gift | luxury rigid boxes, luxury packaging boxes custom, premium gift boxes wholesale | `/custom-rigid-boxes/` |
| Setup boxes | custom setup boxes, setup box manufacturer usa | `/custom-rigid-boxes/` |
| Industry | rigid boxes for cosmetics / perfume / jewelry / cannabis | `…/#styles` |

Use **Phrase** and **Exact** match to start. Broad match on this niche burns budget on
"how to make a rigid box at home" traffic.

### Negative keyword list — add before your first click

```
free, diy, how to make, template, tutorial, wholesale supplier china, alibaba,
jobs, salary, hiring, near me cheap, second hand, used, recycling, machine,
rigid box making machine, manufacturer in india, manufacturer in pakistan,
amazon, etsy, ebay, download, pdf, images, clipart, definition, meaning
```

Add competitor names as negatives *unless* you deliberately want conquesting ad groups:
`oxo packaging, refine packaging, plus printers, custom box usa, usa box maker,
icustomboxes, custom boxes only, premium custom boxes, weprintboxes`.

### Ad copy angles that match this page
Every headline below has a matching on-page proof point — that alignment is what lifts the
landing page experience component of Quality Score.

- `Custom Rigid Boxes — Free 3D Mockup`
- `Quote in 1 Hour · 100 Box Minimum`
- `No Die or Plate Charges — Ever`
- `Free Shipping USA & Canada`
- `Sample Approved Before We Print`
- `Magnetic, Drawer & Shoulder Neck`

Sitelinks: Box Styles (`#styles`), Materials & Finishes (`#specs`), How It Works (`#process`),
Pricing (`#pricing`).
Callouts: Free 3D Mockup · No Setup Fees · Pre-Production Sample · US-Based Support · FSC® Stocks.
Structured snippet (Types): Magnetic Closure, Drawer, Shoulder Neck, Book Style, Telescoping, Rigid Mailer.
Add a **call extension** with (888) 716-1078 and a **lead form asset** as a backup capture path.

---

## 4. Why the page is built this way

Landing page experience is one of the three Quality Score components, and it is the one most
packaging competitors get wrong. Specific choices here:

- **Form above the fold, 4 required fields.** Every extra field costs completions; box specs are in
  an optional `<details>` block, so motivated buyers self-qualify without blocking anyone else.
- **No exit paths.** The nav is anchor links only — no top-level menu leaking paid clicks to your
  main site. The only outbound links are the privacy policy, terms and one footer link home.
- **Real specification content.** Board thicknesses, gsm ranges, stock names, finish options and
  ten genuine FAQs. This is what makes the page *relevant* rather than a generic squeeze page, and
  it is what Google's landing page raters and the FAQ rich result both reward.
- **Honest price ranges.** Competitors hide pricing entirely. Publishing a range filters out
  tire-kickers before they cost you a sales call, and reads as transparent to both users and Google.
- **Inline SVG illustrations, no stock photos.** Eight box constructions drawn as vectors: zero image
  bytes, instant LCP, no generic AI-looking product renders. Swap in real product photography of
  your own boxes when you have it — that will convert better than any illustration.
- **Sticky call/quote bar on mobile.** Most packaging leads on paid mobile traffic call rather than
  type. The bar is always reachable and fires a call conversion.
- **TCPA consent line under the button** naming the company and the contact methods, plus a linked
  privacy policy — required for lead-gen ads collecting phone numbers, and it reduces form anxiety.
- **`gclid` and UTM pass-through.** Hidden fields carry the ad click ID into your inbox/CRM, so you
  can tie a closed deal back to the exact keyword — and later upload offline conversions to teach
  Smart Bidding which leads were actually worth money.

## 5. On adding Packlane-style instant pricing

Recommended: **no, not for rigid boxes, and not while this is a lead-gen page.** Three reasons.

**The math doesn't close.** Packlane prices a small, constrained catalogue — a few corrugated
styles, a fixed material list, a fixed print option set. That is a small enough grid to solve
algorithmically. Rigid boxes are hand-assembled with variable wraps, magnets, foil, ribbon,
inserts and specialty stocks; the price surface is large and non-linear. A calculator over that
either quotes below cost on the awkward combinations or pads every cell to stay safe — which
makes you look expensive against competitors who quote by hand.

**It fights your own funnel.** Packlane is e-commerce: instant price, self-serve checkout, no
salesperson. This page is lead-gen: the price *is* the reason to hand over a phone number. Give
the number away for free and the form loses its job. You would be paying $8–25 a click to show
people a price and then hoping they come back.

**It creates disputes.** A displayed price that a human later corrects upward is the fastest way
to lose a deal you had already won.

**The version worth building instead:** an *estimate* widget that takes size, quantity and
finishing and returns a **range** ("boxes like this typically run $2.40–$3.60 each"), then asks
for contact details to "lock the exact price and get a free 3D mockup." You get the engagement
and the transparency signal, you keep the lead capture, and you are never wrong because you never
quoted a number. If you want this, I need your real cost matrix — board and wrap cost per square
inch, per-process finishing costs, assembly labour per style, and your quantity break curve.
Without those it would just be invented numbers.

## 6. What to test first

1. **Headline** — brand-emotional (current) vs. spec-direct (`Custom Rigid Boxes From 100 Units — Quote in 1 Hour`).
2. **Offer** — free 3D mockup (current) vs. free physical sample. The sample offer lifts lead quality and cuts volume.
3. **Form length** — the current 4 fields vs. a two-step form (quantity first, contact second).
4. **Price block on/off** — it filters hard; measure cost per *qualified* lead, not cost per lead.

Give each test two weeks or 100 conversions, whichever comes later.
