# Rigid Boxes Landing Page — Custom Boxes Experts

A single-purpose, ads-optimized landing page for **custom rigid boxes only**, plus a leads
dashboard, built to match the customboxesexperts.com theme (navy `#1A3163`, amber `#F2A65A`,
mint `#D0F3EC`, cream `#FEF6E9`).

Type is **Plus Jakarta Sans** for headings, buttons and labels — geometric, tightly drawn, and
premium next to the navy/amber palette — over **Source Sans 3** for body copy, a humanist face
built to stay readable at 17px on a phone. Both load from Google Fonts without blocking first
paint. Change the pair in one place: `--font-head` and `--font-body` in `:root`.

**Stack:** Laravel 13 · Livewire 4 · Blade · Vite · SQLite (or MySQL). The forms and the
dashboard are Livewire components; everything else is plain Blade.

```
app/
  Http/Controllers/PageController.php        landing, thank-you, privacy, terms
  Http/Controllers/Dashboard/                CSV exports, artwork downloads
  Http/Middleware/CaptureAdContext.php       records gclid / utm_* per visit
  Livewire/QuoteForm.php                     stage 1 — the contact form
  Livewire/SpecForm.php                      stage 2 — box specs + artwork
  Livewire/Dashboard/LeadsTable.php          the dashboard list
  Livewire/Dashboard/LoginForm.php           sign-in
  Mail/                                      the two alert emails
  Models/Lead.php                            one row per enquiry
  Support/AdContext.php                      click-id capture and storage
  Support/GoogleAdsExport.php                the three CSV formats
config/
  site.php       phone, email, address, Shorts links — used by every page
  leads.php      alert recipients, Google Ads conversion name, upload rules
  dashboard.php  demo mode, the seeded account, page size
  tracking.php   Google Ads + GA4 ids (read from .env)
  faq.php        the FAQ — rendered on the page AND as FAQPage schema
resources/
  views/pages/          landing, thank-you, privacy, terms, dashboard
  views/livewire/       the four Livewire component views
  views/partials/       header, footer, schema, lightbox, quote modal
  views/mail/           the two alert emails
  css/app.css           the whole landing page. Brand tokens in :root.
  css/dashboard.css     the dashboard
  js/app.js             modal, lightbox, film wall, CTA tracking
  js/shorts.js          click-to-load YouTube facades
public/assets/          photos and video (see section 5)
public/umrah/           an unrelated static microsite, served as-is
database/migrations/    users (+ username) and leads
tests/Feature/          46 tests covering the whole flow
```

---

## The two-step quote flow

Every "get a quote" button opens a modal holding the same Livewire form as the hero. Both paths
land in the same place.

```
Step 1 — name, email, phone, quantity   (hero form OR modal)
   │     ↓ Livewire validates and saves the Lead, then redirects
   │     ↓ you receive "New Rigid Box Lead — call this person now"
   ▼
/thank-you
   │     ↓ THE "Quote Form Submit" CONVERSION FIRES HERE (on page load)
   │     ↓ with the buyer's email attached for Enhanced Conversions
   ▼
Step 2 (optional, on the thank-you page) — L×W×D + units, style, board,
         wrap, insert, finishing, compare quantity, in-hands date,
         artwork upload, notes
         ↓ updates the SAME lead row, no page reload
         ↓ you receive "Box Specs Added [REFERENCE]"
         └─ or they just wait for your call — the lead + conversion
            are already counted either way
```

**Why step 1 saves by itself:** a long spec form that only submits at the end throws away every
buyer who quits halfway. Here the contact record is banked the moment it is complete, then the
visitor is sent to the thank-you page. The **one Google Ads conversion fires there, on page
load** — the reliable place for it, and where the email is present so Enhanced Conversions can
match. Box specs are then offered as an optional extra against the same lead.

**Two things the Laravel version does better than the static one it replaced.** The ad click is
captured *server-side* by `CaptureAdContext` middleware and kept in the session, so an ad
blocker or a JavaScript error can no longer cost you the `gclid` that offline conversion imports
depend on. And the conversion flag is consumed with `session()->pull()`, so a reload or a
back-button return to the thank-you page cannot count the same lead twice.

---

## 1. Before you go live — required edits

Everything below is `.env`. No code changes.

| Setting | What to put there |
|---|---|
| `APP_URL` | `https://your-subdomain.customboxesexperts.com` |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` — **never ship with debug on** |
| `GOOGLE_ADS_ID` | `AW-XXXXXXXXXX` from Google Ads › Admin › Account settings |
| `GOOGLE_ADS_LEAD_LABEL` | `AW-XXXXXXXXXX/AbCdEfGh` — the "Quote Form Submit" action |
| `GOOGLE_ADS_CALL_LABEL` | the click-to-call action (mark it **secondary** in Google Ads) |
| `GA4_MEASUREMENT_ID` | `G-XXXXXXXXXX`, optional but recommended |
| `ADS_CONVERSION_NAME` | must match the conversion action name **character for character** |
| `ADS_TIMEZONE` | the time zone of the **Google Ads account**, not the server |
| `LEADS_NOTIFY_TO` | the inbox that should get every lead |
| `MAIL_*` | a real mailbox on your domain (see section 4) |
| `DASHBOARD_DEMO_MODE` | **`false`** — see section 2b |
| `DASHBOARD_USERNAME` / `DASHBOARD_PASSWORD` | the dashboard account, then re-run `php artisan db:seed` |
| `SITE_CANONICAL` | the public URL of this page, with a trailing slash |

Leave any tracking id empty and **no tag is rendered at all** — the page runs clean locally and
in tests. That also means: if you forget `GOOGLE_ADS_ID`, Google Ads counts nothing.

### Optional

| Setting | Effect |
|---|---|
| `LEADS_NOTIFY_CC` | comma-separated extra recipients |
| `ADS_DEFAULT_VALUE` | the value exported for leads you have not marked won |
| `SITE_SHORTS` | comma-separated YouTube Shorts URLs — the section appears once set |
| `SITE_SHORTS_CHANNEL` | your channel's Shorts page, for the "More on YouTube Shorts" link |

---

## 2. Where the leads go

Every submission writes a row to the `leads` table and emails `LEADS_NOTIFY_TO`. There is no
second path to configure and no third-party service in the way.

**A mail failure never costs you a lead.** The row is committed first; the send is wrapped in a
try/catch that logs and moves on. If SMTP is down you still have the lead in the dashboard.

**Database.** SQLite is the default and is genuinely fine here — a landing page's lead volume is
nowhere near its limits, and it needs no database server on the VPS. To use MySQL instead, set
`DB_CONNECTION=mysql` and the usual `DB_*` values; the migrations are portable.

---

<a id="2b-the-leads-dashboard"></a>

## 2b. The leads dashboard

At **`/dashboard`**. Everything under it requires signing in, including the CSV exports and the
artwork downloads.

### What is in it

- **Headline numbers** — all-time, today, last 7 days, how many came from Google Ads, how many
  added specs, and won leads with their total value.
- **Every lead** newest first, with contact details, quantity, source and campaign.
- **Details** on any row: the full box spec, what they typed in the notes, downloadable artwork,
  the whole campaign trail (gclid, gbraid/wbraid, source, medium, campaign, keyword, ad,
  landing page, IP), and a notes field of your own.
- **Status and value**, editable inline. Both feed the exports.
- **Filters** — free-text search, status, date range, Google Ads only, has specs. The filters
  live in the URL, so a filtered view is a link you can bookmark or send to a colleague, and
  the export buttons carry the same filters.

### Signing in

Out of the box `DASHBOARD_DEMO_MODE=true`, and **any username and password will sign in**. That
is there so you can look at the dashboard before real accounts exist.

**Turn it off before the site takes live traffic.** These are real customers' names, emails and
phone numbers:

```bash
# in .env
DASHBOARD_DEMO_MODE=false
DASHBOARD_USERNAME=admin
DASHBOARD_PASSWORD=a-long-password-you-choose

php artisan db:seed --force    # creates or updates that account
php artisan config:cache
```

Sign-ins are rate limited to five attempts a minute per username and IP, so the dashboard cannot
be quietly brute-forced.

### The three downloads

Each file contains exactly the leads matching the filters on screen when you press the button.

| Button | Format | Use it for |
|---|---|---|
| **GCLID conversions** | `Parameters:TimeZone=…` line, then `Google Click ID, Conversion Name, Conversion Time, Conversion Value, Conversion Currency` | The main path. Google Ads › Tools & Settings › Conversions › **Uploads**. Only includes leads that carried a click id. |
| **Enhanced conversions** | `Email, Phone Number, Conversion Name, …` with email and phone **SHA-256 hashed** | Leads with no click id. Hashing happens on your server, so no personal data leaves it in readable form. |
| **Everything (CSV)** | every field held on the lead | Your CRM, or a spreadsheet. |

The first two also stamp `exported_at` on the leads they contain, so you can see at a glance
what has already gone to Google.

**The workflow that makes this worth doing:** mark leads that turned into real business as
**won**, put the order value against them, then upload. That is what teaches Smart Bidding which
clicks are worth paying for. Without it, Google optimises for form fills rather than revenue.

Two things Google rejects files over, both of which this handles for you: the conversion action
name must match exactly (`ADS_CONVERSION_NAME`), and conversion times must be in the Google Ads
account's time zone (`ADS_TIMEZONE`) — which is usually **not** your server's.

---

## 3. Deploying to your Namecheap VPS

The app is served from `public/`, so the vhost root is **`/var/www/rigid/public`** — not the
project folder. Pointing it at the project folder exposes `.env`; this is the single most
important line in this section.

### Requirements

PHP **8.2+** with `pdo_sqlite` (or `pdo_mysql`), `mbstring`, `openssl`, `tokenizer`, `xml`,
`ctype`, `json`, `fileinfo` · Composer · Node 18+ **for the build only** · nginx or Apache.

### First deploy

```bash
sudo mkdir -p /var/www/rigid && sudo chown -R $USER:www-data /var/www/rigid
cd /var/www/rigid
git clone -b claude/rigid-boxes-landing-page-at2dae <repo-url> .

composer install --no-dev --optimize-autoloader
npm ci && npm run build          # writes public/build — needed once per deploy

cp .env.example .env
php artisan key:generate
nano .env                        # fill in section 1

touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force      # creates the dashboard account

# Laravel writes to these two; the web user must own them
sudo chown -R www-data:www-data storage bootstrap/cache database
sudo chmod -R 775 storage bootstrap/cache

php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### nginx

```nginx
server {
    listen 443 ssl http2;
    server_name rigid.customboxesexperts.com;

    # public/ — NOT /var/www/rigid
    root /var/www/rigid/public;
    index index.php;

    client_max_body_size 24M;   # artwork uploads are capped at 20MB per file

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

Then `sudo certbot --nginx -d rigid.customboxesexperts.com`.

Nothing else needs blocking: `.env`, `storage/` and the SQLite database all sit above the
document root and are unreachable over HTTP by construction. Uploaded artwork lands in
`storage/app/private/artwork/` and is served only through the authenticated
`/dashboard/artwork/…` route, so nothing uploaded can be fetched, let alone executed.

### Later deploys

```bash
cd /var/www/rigid
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

### Backups

The whole lead history is one file. Copy it while the app is running — `.backup` takes a
consistent snapshot even mid-write:

```bash
sqlite3 /var/www/rigid/database/database.sqlite ".backup '/root/backups/leads-$(date +%F).sqlite'"
```

Back up `storage/app/private/artwork/` alongside it — that is the customers' artwork.

### Testing after deploy

1. Load the page over HTTPS with `?gclid=TEST123` on the URL.
2. Submit the quote form. You should land on `/thank-you` with a reference shown.
3. Check the alert email arrived. **If it did not, fix `MAIL_*` before spending on ads** — a
   lead you never see is worse than no lead.
4. Add box specs on the thank-you page. Confirm it stays **one row** in the dashboard.
5. Sign in at `/dashboard`, confirm the lead is there with `TEST123` against it.
6. Mark it won, give it a value, download the GCLID CSV, confirm the value appears.
7. Delete the test lead.

---

## 4. Email deliverability

Laravel sends through whatever `MAIL_*` describes. Two options:

- **SMTP on a mailbox you own** (`MAIL_MAILER=smtp`) — simplest, and what most Namecheap setups
  already have. Use the real mailbox credentials for the domain.
- **A transactional provider** (Postmark, SES, Resend) if alerts start landing in spam.

Whichever you pick, `MAIL_FROM_ADDRESS` must be a real address **on your domain**. A From
address the domain does not own fails SPF and DMARC, and lead alerts quietly stop arriving.

Both alert emails set `Reply-To` to the buyer, so hitting reply in your inbox answers them
directly.

---

## Running it locally

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run dev          # in one terminal
php artisan serve    # in another
```

`MAIL_MAILER=log` by default locally, so alert emails land in `storage/logs/laravel.log` rather
than being sent.

**Tests:**

```bash
php artisan test
```

46 feature tests cover both form stages, the honeypot, the ad-click capture, the thank-you
guard, uploads, sign-in and rate limiting, every dashboard filter, and all three CSV formats —
including the SHA-256 hashes and the time zone conversion Google Ads is strict about.

---

## 5. Adding your photos and videos

### Photos — drop-in replacement, no code

The gallery holds **six rigid boxes, each with two shots**: a closed hero shot in the grid, and an
open shot revealed by a "See it open" button in the lightbox. That second shot is the one that
sells — nobody buys a rigid box for the outside.

Drop your files into `public/assets/img/boxes/` using these names. The `.1` files follow your own
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
`REPLACE` in `resources/views/pages/landing.blade.php` — update the `data-caption` (shown in the lightbox) and the
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

Two sections carry video, and both are built from files in `public/assets/video/`:

| Section | Files | Size on screen |
|---|---|---|
| **Film wall** (`#film`, above the photo gallery) | `rigid-book`, `rigid-lidbase` | 340px wide, 9:16 |
| **Folding-carton note** (below the gallery) | `carton-flower`, `carton-soap`, `carton-tart`, `carton-otriea` | 74px thumbnails |

Each needs two files with the same stem: `name.mp4` (the clip) and `name.jpg` (the poster
frame). To add one, encode it to match and add a line to the `$film` array near the top of the
film-wall section in `resources/views/pages/landing.blade.php` — the markup is generated from
that array, so there is no block to copy.

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
IntersectionObserver in `resources/js/app.js` assigns the real URL when a tile is about to enter the
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

**The Shorts strip.** Put your own Shorts links in `SITE_SHORTS` in `.env`, comma separated — a
full URL or a bare video ID, each optionally followed by `| a caption`. Four to six is the sweet
spot. Thumbnails come from YouTube automatically and nothing loads from youtube.com until a
visitor clicks, so the tiles cost the page nothing. Leave it empty and the section hides itself.
Set `SITE_SHORTS_CHANNEL` to your channel's Shorts URL to show the "More on YouTube Shorts" line
underneath; leave it empty and that line stays hidden rather than pointing at a dead link.

## 6. Reviews

The three `REPLACE —` testimonial cards need real, attributable quotes before you run traffic.
Until they are filled in, `resources/js/app.js` hides each placeholder card, and hides the whole reviews
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
Type: Website. It fires on **`/thank-you`, on page load**, after Step 1 is saved — not on
the button click. (Firing on click counts people who never actually submitted, and fires before
the lead is even saved.) The page is already wired this way: the thank-you view fires
`gtag('event','conversion',{send_to: …})` on load, and only when the lead that was just saved
put the flag in the session — so a reload cannot double-count. Put your real label in
`GOOGLE_ADS_LEAD_LABEL`. Counting: **One**.

**2. Calls from ads — Primary.**
This is a **Google Ads dashboard setting, not website code.** Turn on call reporting, add a
**call asset** with a **Google forwarding number**, and set the phone-call conversion to a
**60-second minimum**. B2B packaging buyers pick up the phone; if this is Secondary the algorithm
learns to ignore your best leads. Nothing to change on the site for this one.

**3. Enhanced Conversions for leads.**
The page hands Google the buyer's **email** on the thank-you page so Google can match the lead
back to the ad click on your weekly upload. It is wired **two ways**, so it works however you tag:
- **gtag (works today, no GTM needed):** the thank-you page calls
  `gtag('set','user_data',{email})` before the conversion, with the email rendered server-side
  from the saved lead. Google hashes it in the browser — the raw value never leaves it.
- **GTM (if you route through a container):** the same email is pushed to `dataLayer` as
  `lead_submitted.enhanced_conversion.email`. In GTM, create a **User-Provided Data** variable →
  **Manual** → Email = that dataLayer key, and enable Enhanced Conversions on the tag.
Then turn Enhanced Conversions **on** in Google Ads (Conversion action → Settings → Enhanced
conversions → Google tag / GTM).

**4. GCLID capture.**
`CaptureAdContext` middleware reads `gclid` (plus `gbraid`, `wbraid` and the `utm_*` set) from
the landing URL, keeps it in the session for the rest of the visit, and stores it on the lead —
server-side, so no ad blocker or script error can lose it. A 90-day cookie carries it for the
visitor who clicks today and fills the form in next week. This is what lets you upload **offline
conversions** later (which leads actually closed), so Google optimises toward revenue rather
than form fills. The dashboard's GCLID export builds that file for you.

Set a **value** on the Quote Form Submit action (e.g. margin × close-rate) once you have data;
with values in place you can graduate from Maximize Conversions to **tROAS**.

> The website also fires a click-to-call conversion on every `tel:` link
> (`GOOGLE_ADS_CALL_LABEL`).
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
