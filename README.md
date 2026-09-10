# Rigid Boxes Landing Page — Custom Boxes Experts

A single-purpose, ads-optimized landing page for **custom rigid boxes only**, built to match the
customboxesexperts.com theme (navy `#1A3163`, amber `#F2A65A`, mint `#D0F3EC`, cream `#FEF6E9`).

Type is **Plus Jakarta Sans** for headings, buttons and labels — geometric, tightly drawn, and
premium next to the navy/amber palette — over **Source Sans 3** for body copy, a humanist face
built to stay readable at 17px on a phone. Both load from Google Fonts without blocking first
paint. Change the pair in one place: `--font-head` and `--font-body` in `:root`.

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
assets/js/main.js     Two-step flow, validation, tracking, gallery, video autoplay
assets/img/boxes/     Gallery photos (some still placeholders)
assets/video/         Silent looping clips + their poster frames
uploads/              Created on first artwork upload (see Security below)
```

## The two-step quote flow

Every "get a quote" button opens a modal. The hero form is step 1 inline; both paths land in
the same place.

```
Step 1 — name, email, phone, quantity   (hero form OR modal)
   │     ↓ posts on its own, banks the lead, gets a lead_id
   │     ↓ you receive "New Rigid Box Lead — call this person now"
   ▼
thank-you.html
   │     ↓ THE "Quote Form Submit" CONVERSION FIRES HERE (on page load)
   │     ↓ with the buyer's email attached for Enhanced Conversions
   ▼
Step 2 (optional, on the thank-you page) — L×W×D + units, style, board,
         wrap, insert, finishing, compare quantity, in-hands date,
         artwork upload, notes
         ↓ posts as a follow-up against the same lead_id
         ↓ you receive "Box Specs Added [LEAD ID]"
         └─ or they just wait for your call — the lead + conversion
            are already counted either way
```

**Why step 1 posts by itself:** a long spec form that only submits at the end throws away every
buyer who quits halfway. Here the contact record is banked the moment it is complete, then the
visitor is sent to the thank-you page. The **one Google Ads conversion fires there, on page
load** — the reliable place for it, and where the email is present so Enhanced Conversions can
match. Box specs are then offered on the thank-you page as an optional extra; the lead and the
conversion are already counted, so nothing depends on them being filled.

If JavaScript is unavailable the hero form posts normally as a complete stage-1 lead and
redirects to the thank-you page, where the conversion still fires. Nothing is lost.

---

## 1. Before you go live — 8 required edits

| # | What | Where |
|---|------|-------|
| 1 | Replace `AW-XXXXXXXXXX` with your Google Ads conversion ID | `index.html`, `thank-you.html` (head) |
| 2 | Replace `G-XXXXXXXXXX` with your GA4 measurement ID | `index.html`, `thank-you.html` (head) |
| 3 | Replace `REPLACE_LEAD_LABEL` / `REPLACE_CALL_LABEL` conversion labels | `assets/js/main.js` (top) — fired on `thank-you.html` |
| 4 | Replace the three `REPLACE —` testimonials with **real, attributable** quotes | `index.html` → `#reviews` |
| 4b | Add your box photos (section 5) | `assets/img/boxes/` |
| 4c | Paste the Trustpilot figures into `TRUSTPILOT` (section 6) | `assets/js/main.js` (top) |
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

### Option B — `submit-lead.php` + the leads dashboard (this is the shipped default)

Emails each stage to `$TO`, appends to `leads.csv`, saves artwork under `uploads/`, **and**
writes the lead into a SQLite database that powers the dashboard at `/dashboard/`.
Needs PHP 8.0+ with `pdo_sqlite` (standard on any Namecheap VPS) and a working `mail()`.

See [§2b — The leads dashboard](#2b-the-leads-dashboard) below.

You can also point `BACKEND.url` at a Zapier or Make webhook, or a CRM endpoint — anything that
accepts a JSON POST. The payload keys are the field `name` attributes plus `stage`, `lead_id`,
`gclid`, the `utm_*` set, `page_url`, and `files[]` as `{name, type, data}` with base64 `data`.

<a id="2b-the-leads-dashboard"></a>
## 2b. The leads dashboard

Every lead the form captures lands in a private dashboard at **`https://yourdomain.com/dashboard/`**.
It is plain PHP + SQLite — no database server to install, no monthly fee, nothing to sign up for.

### What is in it

- **Summary cards** — total leads, today, last 7 days, how many came from Google Ads,
  how many completed the spec step, and how much won business you have logged.
- **One row per enquiry.** Stage 1 (contact) creates the row, stage 2 (box specs + artwork)
  fills the same row in, so you never see the same person twice.
- **Details** opens the full record: box size, style, board, wrap, insert, finishing, needed-by
  date, what they typed in the notes box, plus the full attribution trail
  (GCLID, source, medium, campaign, keyword, ad content, landing page, IP).
- **Status and value you can edit.** Move a lead through New → Contacted → Quoted → Won /
  Lost / Spam and type in what the deal was worth. It saves the moment you change it.
- **Your own notes** per lead — call outcome, quoted price, next step.
- **Filters** — free-text search, status, date range, Google-Ads-only, has-specs.
  Whatever you filter to is exactly what the downloads contain.

### Signing in

Go to `/dashboard/`. Out of the box **demo mode is on, so any username and password works** —
`admin` / `boxes123` is the suggested pair.

**Turn demo mode off before you send real traffic.** In `dashboard/config.php` set
`'demo_mode' => false` and add your own account:

```bash
php -r 'echo password_hash("your-real-password", PASSWORD_DEFAULT), "\n";'
```

Paste the hash it prints into the `users` array. Sessions time out after 4 hours idle.

### The three downloads

| Button | What it is | What to do with it |
|---|---|---|
| **Google Ads conversions (GCLID)** | One row per lead that arrived with a Google click ID | Google Ads → Goals → Conversions → **Uploads** → upload the file |
| **Enhanced conversions for leads** | Email + phone, SHA-256 hashed and normalised exactly as Google specifies | Same upload screen — use this for leads with no GCLID |
| **All lead data (plain CSV)** | Every field, human-readable | Excel, Google Sheets, or a CRM import |

Before downloading, check the four boxes above the buttons:

- **Conversion action name** must match the name in Google Ads *character for character*
  (Goals → Conversions → Summary). If it does not match, the upload is rejected.
- **Time zone** must be your **Google Ads account's** time zone, not your server's. It is written
  into the file's first line as `Parameters:TimeZone=…` and Google reads the timestamps against it.
- **Currency** and **default value per lead**. A lead with its own value typed in overrides the
  default — so mark your won deals with their real value, filter to `Won`, and upload that:
  Smart Bidding then optimises for revenue instead of raw form fills.

Uploading these is what closes the loop. Without it Google only knows a form was submitted;
with it Google learns which *keywords and audiences* actually produce paying customers.

### Deploying it to your Namecheap VPS

The dashboard is PHP, and **GitHub Pages cannot run PHP** — so the site needs to be served
from your VPS (or any PHP host) for both the form handler and the dashboard to work.

```bash
# on the VPS, as the web user
cd /var/www/customboxesexperts.com          # your document root
git clone -b claude/rigid-boxes-landing-page-at2dae \
    https://github.com/saqibmalic/packaginglandingpage.git .

# the app writes to these three; the web user must own them
mkdir -p data uploads
chown -R www-data:www-data data uploads
chmod 750 data
```

That is all the setup there is — the database file creates itself on the first lead.

**Keep the lead data out of the web root.** `data/` ships with an `.htaccess` that denies
access, but on nginx (which ignores `.htaccess`) point the app somewhere private instead:

```nginx
# nginx: block the data directory outright
location ^~ /data/ { deny all; return 404; }
```

or set `CBE_DATA_DIR=/var/lib/cbe-leads` in your PHP-FPM pool and the store follows it there.

Back it up with a plain file copy — `data/leads.sqlite` *is* the whole database:

```bash
sqlite3 data/leads.sqlite ".backup '/root/backups/leads-$(date +%F).sqlite'"
```

### Testing after deploy

Submit a real enquiry through your own form, then open `/dashboard/` — it should be at the top
of the list within a second, tagged **Google Ads** if you clicked through an ad. Click
**Details** to check the attribution came through, then delete it with **Delete lead**.

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
| `box-1.jpg` | ✅ **done** — Botanica | 4:3 landscape | wide tile | navy base, printed botanical lid |
| `box-1.1.jpg` | ✅ **done** — Botanica | 4:3 landscape | lightbox "open" | same box, lid lifted |
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

Box 1 is done — the Botanica lid-and-base shots are in place, closed in the grid and open in the
lightbox. Captions for boxes 2–3 are written from your earlier photos. Boxes 4–6 are marked
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

### Videos — self-hosted, silent, autoplaying

Two sections carry video, and both are built from files in `assets/video/`:

| Section | Files | Size on screen |
|---|---|---|
| **Film wall** (`#film`, above the photo gallery) | `rigid-book`, `rigid-lidbase` | 340px wide, 9:16 |
| **Folding-carton note** (below the gallery) | `carton-flower`, `carton-soap`, `carton-tart`, `carton-otriea` | 74px thumbnails |

Each needs two files with the same stem: `name.mp4` (the clip) and `name.jpg` (the poster
frame). To add one, encode it to match and copy the `<figure class="film">` block in
`index.html`.

**Encoding recipe** — this is what the current clips were made with:

```bash
ffmpeg -ss 3 -t 10 -i source.mp4 -an \
  -vf "scale=540:960:force_original_aspect_ratio=increase,crop=540:960" \
  -c:v libx264 -profile:v high -pix_fmt yuv420p \
  -crf 30 -preset slow -movflags +faststart out.mp4

ffmpeg -ss 4.5 -i source.mp4 -frames:v 1 \
  -vf "scale=540:960:force_original_aspect_ratio=increase,crop=540:960" \
  -q:v 5 out.jpg
```

Four parts of that matter:

- **`-an` strips the audio.** These are silent loops. A landing page that starts talking is a
  landing page people close.
- **`-movflags +faststart`** moves the index to the front of the file so playback begins on the
  first chunk instead of after the whole download.
- **`-ss` / `-t` cut a short clip.** 8–13 seconds is the target. A loop is more compelling than
  a long take, and a 30-second clip is three times the bytes for less effect.
- **540×960** is roughly 1.6× the on-screen size — sharp on a retina phone without paying for
  a file nobody can see the detail in.

Keep each clip **under ~750KB**. All six together are 2.7MB, and a visitor who never scrolls
past the hero downloads **none** of it.

**How playback works.** No `<video>` has a `src` in the HTML — only `data-src`. An
IntersectionObserver in `main.js` assigns the real URL when a tile is about to enter the
viewport, plays it, and pauses it again when it leaves. So nothing competes with the LCP,
off-screen clips do not drain a phone battery, and anyone whose OS asks for reduced motion gets
the poster frame with a play button instead.

**Why not YouTube.** An embed costs ~700KB per tile before a frame plays, stamps someone else's
branding on your product, and puts a "watch on YouTube" exit door on a page you are paying
\$8–25 a click to fill. Self-hosting is smaller, cleaner, and keeps the visitor on the form.

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
page, with the quote form still one tap away.

**The Shorts strip.** Paste your own Shorts links into `SHORTS` at the top of
`assets/js/main.js` — a full URL or a bare video ID, optionally followed by `| a caption`. Four
to six is the sweet spot. Thumbnails come from YouTube automatically and nothing loads from
youtube.com until a visitor clicks, so the tiles cost the page nothing. Leave the list empty and
the section hides itself. Set `SHORTS_CHANNEL` to your channel's Shorts URL to show the "More on
YouTube Shorts" line underneath; leave it empty and that line stays hidden rather than pointing
at a dead link.

## 6. Reviews

The three `REPLACE —` testimonial cards need real, attributable quotes before you run traffic.
Until they are filled in, `main.js` hides each placeholder card, and hides the whole reviews
section if none of them are real yet — so nothing unfinished can reach a visitor.

Google Ads prohibits fabricated testimonials, so use quotes you can actually stand behind: a
name, a title and a brand, from a buyer who agreed to be quoted. One specific line ("the
pre-production sample landed on a Tuesday and we shipped the run three weeks later") outperforms
three generic ones.

**Claim your own Trustpilot profile.** It is free to start: claim `customboxesexperts.com`, then
email your last 50 delivered orders an invitation. Rigid box buyers are high-satisfaction
customers when the box lands well. Once you have 20 or more reviews, drop a TrustBox widget into
this section.

## 7. Google Ads setup

### Conversion actions — the exact setup this page is wired for

**Two Primary conversions. Everything else Secondary or removed.** Mixing five "primary"
actions teaches the algorithm to chase whatever is easiest to trigger; two clean primaries
teach it to chase buyers.

**1. Quote Form Submit — Primary (the money conversion).**
Type: Website. It fires on **`thank-you.html`, on page load**, after Step 1 is saved — not on
the button click. (Firing on click counts people who never actually submitted, and fires before
the lead is even saved.) The page is already wired this way: `main.js` fires
`gtag('event','conversion',{send_to: CONVERSIONS.lead})` on the thank-you page. Paste your real
label into `CONVERSIONS.lead` in `assets/js/main.js`. Counting: **One**.

**2. Calls from ads — Primary.**
This is a **Google Ads dashboard setting, not website code.** Turn on call reporting, add a
**call asset** with a **Google forwarding number**, and set the phone-call conversion to a
**60-second minimum**. B2B packaging buyers pick up the phone; if this is Secondary the algorithm
learns to ignore your best leads. Nothing to change on the site for this one.

**3. Enhanced Conversions for leads.**
The page hands Google the buyer's **email** on the thank-you page so Google can match the lead
back to the ad click on your weekly upload. It is wired **two ways**, so it works however you tag:
- **gtag (works today, no GTM needed):** `main.js` calls `gtag('set','user_data',{email})`
  before the conversion. Google hashes the email in the browser — the raw value never leaves it.
- **GTM (if you route through a container):** the email is also written into a hidden
  `#ec-email` field and pushed to `dataLayer` as `lead_submitted.enhanced_conversion.email`.
  In GTM, create a **User-Provided Data** variable → **Manual** → Email = that field (or the
  dataLayer key) and enable Enhanced Conversions on the tag.
Then turn Enhanced Conversions **on** in Google Ads (Conversion action → Settings → Enhanced
conversions → Google tag / GTM).

**4. GCLID capture.**
Every form now has a hidden `gclid` field (plus `gbraid`, `wbraid` and the `utm_*` set),
filled from the URL on load and written to your lead sheet next to the email and timestamp —
that is the `GCLID` column in the Google Sheet. This is what lets you upload **offline
conversions** later (which leads actually closed), so Google optimises toward revenue, not
toward form-fills. Keep the sheet's `Timestamp`, `Email` and `GCLID` columns — the offline
upload needs all three.

Set a **value** on the Quote Form Submit action (e.g. margin × close-rate) once you have data;
with values in place you can graduate from Maximize Conversions to **tROAS**.

> The website also fires a click-to-call conversion on every `tel:` link (`CONVERSIONS.call`).
> That is a *different* action from "Calls from ads" above — mark this website-call action
> **Secondary** so it doesn't compete with the forwarding-number call conversion.

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
