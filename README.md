# Rigid Boxes Landing Page — Custom Boxes Experts

A single-purpose, ads-optimized landing page for **custom rigid boxes only**, built to match the
customboxesexperts.com theme (navy `#1A3163`, amber `#F2A65A`, mint `#D0F3EC`, cream `#FEF6E9`).

Static HTML/CSS/JS — no build step, no framework, no dependencies. Drop it on any host.

```
index.html            The landing page + two-step quote modal
thank-you.html        Post-submit page (noindex) where the conversion fires
privacy-policy.html   Required by Google Ads — must be reachable
terms.html            Trust/transparency signal for landing page quality
submit-lead.php       PHP lead handler (2 stages, uploads, CSV backup)
google-apps-script.gs Google Sheets backend — no server needed (see section 2)
robots.txt
assets/css/styles.css All styling. Brand tokens live in :root at the top.
assets/js/main.js     Two-step flow, validation, tracking, gallery, video facades
assets/img/           Gallery photos + video posters (placeholders ship with it)
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
| 4b | Add your box photos (section 5) | `assets/img/boxes/` |
| 4c | Paste your Shorts links into `SHORTS` and the Trustpilot figures into `TRUSTPILOT` (sections 5–6) | `assets/js/main.js` (top) |
| 5 | Choose your backend and set `BACKEND` (see section 2) | `assets/js/main.js` (top) |
| 6 | Set the recipient email — `NOTIFY_EMAIL` (Sheets) or `$TO`/`$FROM` (PHP) | `google-apps-script.gs` / `submit-lead.php` |
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

## 2. Where the leads go — pick one

Set `BACKEND` at the top of `assets/js/main.js`. Both options receive the identical JSON
payload, so you can switch later without touching anything else.

### Option A — Google Sheet (recommended if your agents work from a sheet)

One row per lead. Stage 1 creates the row with a **"New — call now"** status; stage 2 fills the
spec columns in the *same row* and flips the status to **"Specs received"**. Artwork is saved to
a Drive folder with the links in the row. You also get an email alert per stage.

Setup is in the header comment of `google-apps-script.gs` — about 5 minutes:
create a sheet → Extensions → Apps Script → paste the file → Deploy as Web app
(**Execute as: Me**, **Who has access: Anyone**) → copy the `/exec` URL → paste it into
`BACKEND` as `{ mode: 'sheets', url: '…/exec' }`.

Sheet columns: `Timestamp · Lead ID · Status · Name · Email · Phone · Quantity · Compare Qty ·
Length · Width · Depth · Units · Box Style · Board · Wrap Stock · Insert · Finishing ·
Needed By · Notes · Artwork · GCLID · Source · Medium · Campaign · Keyword · Content · Page URL`

`Status` is a plain text cell — have your agents overwrite it with Contacted / Quoted / Won /
Lost. The `GCLID` column is what you will need later for offline conversion uploads.

This option needs **no server at all**, which means the page can live on free static hosting.

### Option B — `submit-lead.php` on your own hosting

Emails each stage to `$TO` and appends to `leads.csv`, saving artwork under `uploads/`.
Needs PHP 7.4+ and a working `mail()`. This is the default in the shipped config.

You can also point `BACKEND.url` at a Zapier or Make webhook, or a CRM endpoint — anything that
accepts a JSON POST. The payload keys are the field `name` attributes plus `stage`, `lead_id`,
`gclid`, the `utm_*` set, `page_url`, and `files[]` as `{name, type, data}` with base64 `data`.

## 3. Testing it on a throwaway domain

The page is static, so with **Option A** the whole thing — including the form writing to your
sheet — works on free hosting in a couple of minutes:

| Host | How | Notes |
|---|---|---|
| **GitHub Pages** | Repo → Settings → Pages → Source: this branch, folder `/` | Fastest, the code is already pushed. URL: `https://<user>.github.io/PackagingLandingPage/` |
| **Netlify Drop** | Drag the project folder onto [app.netlify.com/drop](https://app.netlify.com/drop) | No account needed to start, instant HTTPS URL |
| **Cloudflare Pages** | Connect the repo, framework preset "None" | Free custom domains |

All three are static-only, so `submit-lead.php` will **not** run on them — use Option A for the
test. Add `?gclid=TEST123&utm_campaign=test` to the URL when you try it, then confirm those
values land in the sheet.

Before pointing real ad spend at a test domain, note that the canonical tag and structured data
still reference `customboxesexperts.com`. That is correct for production but means a test host is
telling Google the real page lives elsewhere — fine for testing, wrong for a live campaign.

## 4. Deployment

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

## 5. Adding your photos and videos

### Photos — drop-in replacement, no code

The gallery holds **six rigid boxes, each with two shots**: a closed hero shot in the grid, and an
open shot revealed by a "See it open" button in the lightbox. That second shot is the one that
sells — nobody buys a rigid box for the outside.

Drop your files into `assets/img/boxes/` using these names. The `.1` files follow your own
naming convention, so most of your photos only need the prefix added:

| File | Export at | Aspect | Shown as | Which box |
|---|---|---|---|---|
| `box-1.jpg` | **1600 × 1200** | 4:3 landscape | wide tile | **AFC** — navy magnetic closure, collapsible |
| `box-1.1.jpg` | **1600 × 1200** | 4:3 landscape | lightbox "open" | same box, open |
| `box-2.jpg` | **1200 × 1200** | 1:1 square | square tile | **PBS North Carolina** — black drawer, silver foil |
| `box-2.1.jpg` | **1600 × 1200** | 4:3 landscape | lightbox "open" | same box, drawer out |
| `box-3.jpg` | **1200 × 1200** | 1:1 square | square tile | **FOX** — sunflower two-piece lid & base |
| `box-3.1.jpg` | **1600 × 1200** | 4:3 landscape | lightbox "open" | same box, lid off |
| `box-4.jpg` | **1200 × 1200** | 1:1 square | square tile | your next rigid box |
| `box-4.1.jpg` | **1600 × 1200** | 4:3 landscape | lightbox "open" | same box, open |
| `box-5.jpg` | **1600 × 1200** | 4:3 landscape | wide tile | your next rigid box |
| `box-5.1.jpg` | **1600 × 1200** | 4:3 landscape | lightbox "open" | same box, open |
| `box-6.jpg` | **1200 × 1200** | 1:1 square | square tile | your next rigid box |
| `box-6.1.jpg` | **1600 × 1200** | 4:3 landscape | lightbox "open" | same box, open |
| `also-1.jpg` | **200 × 200** | 1:1 square | 74px thumbnail | Pelham pink corrugated mailer |
| `also-2.jpg` | **200 × 200** | 1:1 square | 74px thumbnail | Medable white/blue corrugated mailer |

**All files: JPEG, sRGB colour profile, under 200KB each** (the `.1` open shots may go to 250KB —
they are only fetched when someone opens the lightbox). Strip EXIF on export.

The pixel sizes above are 2× what the page actually displays, which is deliberate: it keeps the
photos sharp on retina phones and MacBooks, where most of your traffic will be, without paying
for a 3000px file nobody sees.

**Two rules that matter more than the pixel count:**

1. **Crop to the stated aspect ratio before you upload.** The CSS crops to fill, so an uncropped
   4:3 phone photo dropped into a square slot loses the top and bottom of the box — usually the
   lid, which is the part you are selling.
2. **Leave a little air around the box.** Roughly 8–10% margin on each side. A box cropped tight
   to its own edges looks cramped, and the caption bar sits over the bottom of the tile.

Captions for boxes 1–3 are already written from the actual photos. Boxes 4–6 are marked
`REPLACE` in `index.html` — update the `data-caption` (shown in the lightbox) and the
`<figcaption>` when you add them.

**Why the mailers are in a separate labelled block.** Two of the six boxes you sent — Pelham and
Medable — are corrugated mailers, not rigid boxes. Someone who clicked an ad for "custom rigid
boxes" and lands on a page showing pink corrugated mailers gets a message-match mismatch, which
costs you both trust and ad relevance. They now sit in a small "We also print corrugated mailers"
note below the gallery, which is honest, keeps the rigid message clean, and still catches the
buyer who needs both. If you would rather not show them at all, delete the `<aside class="also">`
block.

Shooting notes for boxes 4–6, based on what works in the three you already have: the PBS drawer
photographed mid-slide and the AFC box shot open are the two strongest images in the set, because
both show the box *doing something*. Aim for that — mid-open, mid-slide, lid lifting. A closed box
on a table is a product photo; a box caught opening is a sales photo.

Export at 82% JPEG quality and run anything still over the limit through
[squoosh.app](https://squoosh.app) — free, in the browser, no upload to anyone.

**If a photo is missing, that tile hides itself** rather than showing a broken image. If all six
are missing, the whole section disappears. Safe to publish at any stage.

### YouTube Shorts — one line each, no HTML

The Shorts strip sits at the bottom of the page, right before the final call to action, and is
built from a list at the top of `assets/js/main.js`:

```js
var SHORTS = [
  'https://www.youtube.com/shorts/AbCdEfGhIjK | Magnetic closure unboxing',
  'https://www.youtube.com/shorts/LmNoPqRsTuV | Gold foil on the press',
  'https://www.youtube.com/shorts/WxYzAbCdEfG | Hand-wrapped corners',
  'https://www.youtube.com/shorts/HiJkLmNoPqR | Drawer box, ribbon pull'
];
```

Open a Short from <https://www.youtube.com/@xpertspackaging/shorts>, copy the address bar, paste
it between the quotes. Anything after the `|` becomes the caption under the tile; drop the `|`
and the caption if you do not want one. A bare 11-character video ID works too.

**There are no image files to prepare** — thumbnails are pulled from YouTube automatically at the
right vertical crop. The strip lays out 1 to 6 tiles evenly; four to six looks best. On phones it
becomes a swipeable row, one Short at a time, which is how people actually watch these.

Nothing loads from YouTube until a visitor clicks — the tile is just a thumbnail until then. That
is deliberate: a normal YouTube embed pulls ~700KB of scripts per video and would wreck the load
speed this page currently has. Clicking swaps in the real player and fires a `video_play` event.

**Which Shorts to pick:** process over product. A box being wrapped, a corner being turned, foil
hitting the press, a drawer sliding out. Static beauty shots are already covered by the gallery;
the Shorts earn their place by proving there is a real factory behind the quote form.

**With the list empty the whole section stays hidden**, so it is safe to publish now.

### Why there is no live Instagram feed

You asked about embedding Instagram. I'd advise against it *on this page* specifically:

- Instagram's official embed needs `embed.js` (heavy, third-party, and Meta has been steadily
  restricting the oEmbed API). Third-party widgets like SnapWidget or LightWidget work but add
  another render-blocking script and a monthly fee.
- More importantly, **a live feed sends paid traffic to Instagram.** You are paying $8–25 a click
  to get someone onto a page whose one job is capturing their phone number. A grid of tappable
  Instagram posts is an exit door — people leave to scroll, and they do not come back.
- A feed also shows whatever you posted last. On a rigid-boxes-only ad landing page, a mailer box
  or a holiday post dilutes the message.

The video strip above does the same job with none of those costs: your content, curated, on your
page, with the quote form still one tap away. The Instagram and YouTube links at the bottom of
that section is there for people who genuinely want to go follow you — after they have seen the
boxes.

## 6. Reviews and the sister-brand Trustpilot

The reviews block shows the **Xperts Packaging** Trustpilot rating, explicitly labelled as a
sister company. Fill it in at the top of `assets/js/main.js`:

```js
var TRUSTPILOT = {
  rating: '4.8',   // the TrustScore shown on the profile
  count:  '126'    // the review count shown on the profile
};
```

Both from <https://www.trustpilot.com/review/xpertspackaging.com>. The stars are drawn from the
rating automatically, and the block stays hidden until both values are set.

**Do not restyle this to look like Custom Boxes Experts' own reviews, and do not remove the
"From our sister company" badge or the italic note.** Presenting another business's reviews as
your own breaches Google Ads' misrepresentation policy and Trustpilot's terms of use — and on a
lead-gen account, a misrepresentation strike is one of the harder ones to appeal.

Labelled honestly it still works: shared ownership and a shared production floor is a real,
checkable claim, and a buyer who clicks through sees genuine reviews of the same team.

**The better fix is to claim your own profile.** Trustpilot is free to start: claim
`customboxesexperts.com`, then email your last 50 delivered orders an invitation. Rigid box
buyers are high-satisfaction customers when the box lands well — you will get reviews. Once you
have 20+, swap this block for your own TrustBox widget and the labelling problem disappears.

The three `REPLACE —` testimonial cards below it still need real, attributable quotes.

## 7. Google Ads setup

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

## 8. Why the page is built this way

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

## 9. On adding Packlane-style instant pricing

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

## 10. What to test first

1. **Headline** — brand-emotional (current) vs. spec-direct (`Custom Rigid Boxes From 100 Units — Quote in 1 Hour`).
2. **Offer** — free 3D mockup (current) vs. free physical sample. The sample offer lifts lead quality and cuts volume.
3. **Form length** — the current 4 fields vs. a two-step form (quantity first, contact second).
4. **Price block on/off** — it filters hard; measure cost per *qualified* lead, not cost per lead.

Give each test two weeks or 100 conversions, whichever comes later.
