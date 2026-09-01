/* ============================================================
   Custom Boxes Experts — rigid boxes landing page
   Two-step quote flow, validation, tracking. No dependencies.

   FLOW
   ----
   Step 1 (contact) posts on its own and is banked as a lead
   immediately — an abandon on step 2 still leaves a contactable
   person. Step 2 enriches that same lead_id with box specs.
   The Google Ads lead conversion fires when step 1 completes.
   ============================================================ */
(function () {
  'use strict';

  /* ---- Google Ads conversion labels -------------------------
     Replace with the values from Google Ads > Goals > Conversions.
     Format: 'AW-XXXXXXXXXX/AbCdEfGhIjKlMnOp'
     ---------------------------------------------------------- */
  var CONVERSIONS = {
    lead: 'AW-XXXXXXXXXX/REPLACE_LEAD_LABEL',
    call: 'AW-XXXXXXXXXX/REPLACE_CALL_LABEL'
  };

  /* ---- Where leads go ---------------------------------------
     'sheets' — Google Apps Script web app writing to a Google Sheet.
                Works on any static host (GitHub Pages, Netlify, S3).
                Paste your /exec URL below. See google-apps-script.gs.
     'php'    — submit-lead.php on your own PHP hosting.
     Both receive the same JSON payload.
     ---------------------------------------------------------- */
  var BACKEND = {
    mode: 'sheets',
    url:  'PASTE_YOUR_APPS_SCRIPT_EXEC_URL_HERE'
    /* Own PHP hosting instead? Use:
       mode: 'php',
       url:  'submit-lead.php'                                                    */
  };

  if (BACKEND.url.indexOf('PASTE_YOUR') === 0) {
    console.warn('[Custom Boxes Experts] No lead endpoint configured. ' +
                 'Deploy google-apps-script.gs and paste the /exec URL into BACKEND.url ' +
                 'in assets/js/main.js — until you do, form submissions will not be saved.');
  }

  /* ---- YouTube Shorts strip ---------------------------------
     Paste Shorts links from https://www.youtube.com/@xpertspackaging/shorts
     — open a Short, copy the address bar, paste it between the quotes.
     Full URL or bare video ID both work. Add a caption after a "|"
     if you want one under the tile. Four to six is the sweet spot.
     Thumbnails are pulled from YouTube automatically; nothing else
     to upload. Leave them empty and the whole section stays hidden.
     ---------------------------------------------------------- */
  var SHORTS = [
    'https://www.youtube.com/shorts/CENCqM33ino',   // e.g. 'https://www.youtube.com/shorts/AbCdEfGhIjK | Magnetic closure unboxing'
    'https://www.youtube.com/shorts/-sCSFOT5Nmk',
    'https://www.youtube.com/shorts/mUTy10ssnS0',
    'https://www.youtube.com/shorts/HDSudQpWsdM'
  ];

  /* ---- Sister-brand Trustpilot ------------------------------
     From https://www.trustpilot.com/review/xpertspackaging.com —
     type the TrustScore and the review count exactly as shown there.
     The block stays hidden until both are filled, and it is labelled
     as Xperts Packaging's rating on purpose: presenting another
     company's reviews as your own breaches Google Ads' policy.
     ---------------------------------------------------------- */
  var TRUSTPILOT = {
    rating: '',   // e.g. '4.8'
    count:  ''    // e.g. '126'
  };

  var MAX_FILES = 5;
  var MAX_FILE_BYTES = 10 * 1024 * 1024;   // 10MB — base64 inflates payloads ~33%

  var $  = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  var track = function (name, params) {
    if (typeof gtag === 'function') gtag('event', name, params || {});
  };

  var yr = $('#yr');
  if (yr) yr.textContent = new Date().getFullYear();

  /* ================= Validation helpers ==================== */
  var MESSAGES = {
    name:     'Please enter your name.',
    email:    'Please enter a valid work email address.',
    phone:    'Please enter a phone number we can reach you on.',
    quantity: 'Please choose an approximate quantity.'
  };
  var isEmail = function (v) { return /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(v.trim()); };
  var isPhone = function (v) { return v.replace(/\D/g, '').length >= 10; };

  var setError = function (input, msg) {
    var field = input.closest('.field');
    if (!field) return;
    var err = field.querySelector('[data-err]');
    if (msg) {
      field.classList.add('is-bad');
      input.setAttribute('aria-invalid', 'true');
      if (err) err.textContent = msg;
    } else {
      field.classList.remove('is-bad');
      input.removeAttribute('aria-invalid');
      if (err) err.textContent = '';
    }
  };

  var validate = function (input) {
    var v = input.value.trim(), n = input.name;
    if (!v) { setError(input, MESSAGES[n] || 'This field is required.'); return false; }
    if (n === 'email' && !isEmail(v)) { setError(input, MESSAGES.email); return false; }
    if (n === 'phone' && !isPhone(v)) { setError(input, MESSAGES.phone); return false; }
    setError(input, null);
    return true;
  };

  var validateForm = function (form) {
    var ok = true, first = null;
    $$('[required]', form).forEach(function (input) {
      if (!validate(input)) { ok = false; if (!first) first = input; }
    });
    if (!ok && first) {
      first.focus();
      first.scrollIntoView({ block: 'center', behavior: 'smooth' });
      track('form_error', { form: form.id });
    }
    return ok;
  };

  /* Live feedback + US phone formatting on any contact form */
  var wireForm = function (form) {
    var started = false;
    $$('[required]', form).forEach(function (input) {
      input.addEventListener('blur', function () { if (input.value.trim()) validate(input); });
      input.addEventListener('input', function () {
        if (input.closest('.field').classList.contains('is-bad')) validate(input);
        if (!started) { started = true; track('form_start', { form: form.id }); }
      });
    });
    var phone = form.querySelector('input[name="phone"]');
    if (phone) {
      phone.addEventListener('input', function () {
        var d = phone.value.replace(/\D/g, '').slice(0, 10);
        if (d.length > 6)      phone.value = '(' + d.slice(0, 3) + ') ' + d.slice(3, 6) + '-' + d.slice(6);
        else if (d.length > 3) phone.value = '(' + d.slice(0, 3) + ') ' + d.slice(3);
        else if (d.length > 0) phone.value = '(' + d;
      });
    }
  };

  /* ---- Ad click context, carried into every submission ----- */
  var adContext = function () {
    var qs = new URLSearchParams(location.search), out = { page_url: location.href };
    ['gclid', 'gbraid', 'wbraid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content']
      .forEach(function (k) { if (qs.get(k)) out[k] = qs.get(k); });
    return out;
  };

  /* ---- Lead id, minted on the client -----------------------
     Generating it here means the flow never depends on being able
     to read the server's response — which matters because a Google
     Apps Script endpoint is cross-origin. Both stages send the same
     id and the backend keys the record on it. */
  var leadId = null;
  var newLeadId = function () {
    var s = '', hex = '0123456789ABCDEF';
    for (var i = 0; i < 8; i++) s += hex[Math.floor(Math.random() * 16)];
    return s;
  };

  /* ---- One transport for both backends --------------------- */
  var post = function (payload) {
    return fetch(BACKEND.url, {
      method: 'POST',
      /* text/plain keeps this a "simple" CORS request, so the browser
         sends no preflight — Apps Script cannot answer OPTIONS. */
      headers: { 'Content-Type': 'text/plain;charset=utf-8' },
      body: JSON.stringify(payload)
    });
  };

  var formValues = function (form) {
    var out = {};
    new FormData(form).forEach(function (v, k) {
      if (v instanceof File) return;
      if (k.slice(-2) === '[]') {
        var key = k.slice(0, -2);
        (out[key] = out[key] || []).push(v);
      } else {
        out[k] = v;
      }
    });
    return out;
  };

  /* ================= Modal ================================= */
  var modal    = $('#quote-modal');
  var step1    = $('#modal-step1');
  var specForm = $('#spec-form');
  var donePane = $('#modal-done');
  var stepLbl  = $('#modal-step');
  var progress = $('#modal-progress');
  var lastFocus = null;
  var supportsDialog = modal && typeof modal.showModal === 'function';

  var showPane = function (which) {
    if (!modal) return;
    [step1, specForm, donePane].forEach(function (el) { if (el) el.hidden = true; });
    if (which === 1) {
      step1.hidden = false;
      stepLbl.textContent = 'Step 1 of 2 · Contact';
      progress.style.width = '50%';
    } else if (which === 2) {
      specForm.hidden = false;
      stepLbl.textContent = 'Step 2 of 2 · Box specification';
      progress.style.width = '100%';
    } else {
      donePane.hidden = false;
      stepLbl.textContent = 'Request received';
      progress.style.width = '100%';
    }
    modal.querySelector('.modal__body').scrollTop = 0;
  };

  var openModal = function (which, source) {
    if (!modal) return false;
    lastFocus = document.activeElement;
    showPane(which);
    if (supportsDialog) modal.showModal();
    else { modal.setAttribute('open', ''); modal.style.display = 'block'; }
    document.body.classList.add('modal-open');
    track('quote_modal_open', { step: which, source: source || 'unknown' });
    var focusTarget = modal.querySelector('.modal__pane:not([hidden]) input, .modal__pane:not([hidden]) a.btn');
    if (focusTarget) setTimeout(function () { focusTarget.focus(); }, 60);
    return true;
  };

  var closeModal = function () {
    if (!modal) return;
    if (supportsDialog && modal.open) modal.close();
    else { modal.removeAttribute('open'); modal.style.display = 'none'; }
    document.body.classList.remove('modal-open');
    if (lastFocus) lastFocus.focus();
  };

  if (modal) {
    $$('[data-modal-close]').forEach(function (b) { b.addEventListener('click', closeModal); });
    /* click on the backdrop (the dialog element itself) closes */
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    modal.addEventListener('close', function () { document.body.classList.remove('modal-open'); });

    var skip = $('[data-modal-skip]');
    if (skip) skip.addEventListener('click', function () {
      track('spec_step_skipped', {});
      showPane(3);
    });
  }

  /* ================= Work gallery lightbox ================= */
  var lb      = $('#lightbox');
  var lbImg   = $('#lb-img');
  var lbCap   = $('#lb-cap');
  var shots   = $$('#gallery .shot img');
  var lbIndex = 0;

  var lbToggle = $('#lb-toggle');
  var lbToggleTxt = $('#lb-toggle-txt');
  var lbClosedSrc = '', lbOpenSrc = '', lbIsOpen = false;

  var lbShow = function (i) {
    if (!shots.length) return;
    lbIndex = (i + shots.length) % shots.length;
    var img = shots[lbIndex];
    lbClosedSrc = img.currentSrc || img.src;
    lbOpenSrc   = img.getAttribute('data-open') || '';
    lbIsOpen    = false;
    lbImg.src = lbClosedSrc;
    lbImg.alt = img.alt || '';
    lbCap.textContent = img.getAttribute('data-caption') || img.alt || '';
    /* Only offer the toggle when a second shot actually exists */
    if (lbToggle) {
      lbToggle.hidden = !lbOpenSrc;
      if (lbToggleTxt) lbToggleTxt.textContent = 'See it open';
    }
  };

  if (lbToggle) {
    lbToggle.addEventListener('click', function () {
      lbIsOpen = !lbIsOpen;
      lbImg.src = lbIsOpen ? lbOpenSrc : lbClosedSrc;
      if (lbToggleTxt) lbToggleTxt.textContent = lbIsOpen ? 'See it closed' : 'See it open';
      if (lbIsOpen) track('gallery_view_inside', { index: lbIndex });
    });
  }

  if (lb && shots.length) {
    shots.forEach(function (img, i) {
      img.addEventListener('click', function () {
        lbShow(i);
        if (typeof lb.showModal === 'function') lb.showModal();
        else lb.setAttribute('open', '');
        document.body.classList.add('modal-open');
        track('gallery_open', { index: i, box: img.getAttribute('data-caption') || '' });
        /* Warm the open shot so the toggle is instant, not a spinner */
        var openSrc = img.getAttribute('data-open');
        if (openSrc) { var pre = new Image(); pre.src = openSrc; }
      });
    });

    var lbClose = function () {
      if (typeof lb.close === 'function' && lb.open) lb.close();
      else lb.removeAttribute('open');
      document.body.classList.remove('modal-open');
    };

    $$('[data-lb-close]').forEach(function (b) { b.addEventListener('click', lbClose); });
    $$('[data-lb-prev]').forEach(function (b) { b.addEventListener('click', function () { lbShow(lbIndex - 1); }); });
    $$('[data-lb-next]').forEach(function (b) { b.addEventListener('click', function () { lbShow(lbIndex + 1); }); });
    lb.addEventListener('click', function (e) { if (e.target === lb) lbClose(); });
    lb.addEventListener('close', function () { document.body.classList.remove('modal-open'); });
    lb.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft')  lbShow(lbIndex - 1);
      if (e.key === 'ArrowRight') lbShow(lbIndex + 1);
    });
    /* The lightbox CTA should hand over to the quote modal, not just jump */
    var lbCta = lb.querySelector('.lb__cta');
    if (lbCta) lbCta.addEventListener('click', function (e) {
      e.preventDefault();
      lbClose();
      openModal(1, 'lightbox');
    });
  }

  /* Photos not added yet? Hide the section rather than show broken images. */
  var gallery = $('#gallery');
  if (gallery) {
    var missing = 0, checked = 0;
    shots.forEach(function (img) {
      var done = function (ok) {
        checked++;
        if (!ok) { missing++; img.closest('.shot').hidden = true; }
        if (checked === shots.length && missing === shots.length) {
          var sec = document.getElementById('work');
          if (sec) sec.hidden = true;
        }
      };
      if (img.complete) done(img.naturalWidth > 0);
      else {
        img.addEventListener('load',  function () { done(true); });
        img.addEventListener('error', function () { done(false); });
      }
    });
  }

  /* ================= YouTube Shorts strip =================== */
  /* Tiles are generated from SHORTS above so adding a video is a
     one-line paste, never an HTML edit. Thumbnails come from
     i.ytimg.com; Shorts publish a vertical maxresdefault, and we fall
     back to hqdefault for the odd upload that does not. */
  var shortsWrap = document.getElementById('shorts');
  if (shortsWrap) {
    SHORTS.forEach(function (entry) {
      var parts   = String(entry || '').split('|');
      var raw     = parts[0].trim();
      var caption = (parts[1] || '').trim();
      if (!raw) return;

      // Accept a full watch/shorts/youtu.be URL or a bare ID.
      var m = raw.match(/(?:shorts\/|watch\?v=|youtu\.be\/|embed\/)?([\w-]{11})(?:[?&#].*)?$/);
      if (!m) return;
      var id = m[1];

      var fig = document.createElement('figure');
      fig.className = 'vid vid--tall';
      fig.innerHTML =
        '<button class="vfacade" type="button" data-video="' + id + '" data-track="video-play" ' +
                'aria-label="Play video' + (caption ? ': ' + caption : '') + '">' +
          '<img src="https://i.ytimg.com/vi/' + id + '/maxresdefault.jpg" alt="" ' +
               'width="405" height="720" loading="lazy" decoding="async" ' +
               'onerror="this.onerror=null;this.src=\'https://i.ytimg.com/vi/' + id + '/hqdefault.jpg\'">' +
          '<span class="vfacade__play" aria-hidden="true">' +
            '<svg viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>' +
          '</span>' +
        '</button>' +
        (caption ? '<figcaption>' + caption + '</figcaption>' : '');
      shortsWrap.appendChild(fig);
    });
  }

  /* ---- Never show unfinished content to a visitor ----------
     Tiles, testimonials and the Trustpilot figures all ship with
     placeholder text. Rather than risk that text going live, anything
     still holding a placeholder removes itself, and a section whose
     content is entirely placeholder disappears with it. */
  var isPlaceholder = function (el) {
    if (!el) return false;
    var t = (el.textContent || '') + (el.getAttribute && el.getAttribute('data-caption') || '');
    return /REPLACE|\bRATING\b|\bCOUNT\b/.test(t);
  };

  /* Gallery tiles that have not been described yet */
  $$('#gallery .shot').forEach(function (fig) {
    var img = fig.querySelector('img');
    var txt = (fig.textContent || '') + ' ' + (img && img.getAttribute('data-caption') || '');
    if (/REPLACE/.test(txt)) fig.hidden = true;
  });

  /* Testimonials still carrying placeholder copy */
  $$('.quote').forEach(function (fig) {
    if (isPlaceholder(fig.querySelector('blockquote'))) fig.hidden = true;
  });
  var quotesWrap = $('.quotes');
  if (quotesWrap && !$$('.quote:not([hidden])', quotesWrap).length) quotesWrap.hidden = true;

  /* Trustpilot block — filled from TRUSTPILOT, hidden until it is set */
  var tp = $('.tp');
  if (tp) {
    var rating = parseFloat(TRUSTPILOT.rating);
    if (rating > 0 && String(TRUSTPILOT.count).replace(/\D/g, '')) {
      var count = String(TRUSTPILOT.count).replace(/\D/g, '');
      $('.tp__num', tp).textContent   = TRUSTPILOT.rating;
      $('.tp__count', tp).textContent = Number(count).toLocaleString('en-US') +
                                        ' reviews on Trustpilot';
      // Whole stars only — a half star drawn as a full one overstates the score.
      $('.tp__stars', tp).textContent = new Array(Math.round(rating) + 1).join('\u2605');
      $('.tp__score', tp).setAttribute('aria-label',
        TRUSTPILOT.rating + ' out of 5 on Trustpilot, from ' + count + ' reviews');
    } else {
      tp.hidden = true;
    }
  }

  var reviewsSec = document.getElementById('reviews');
  if (reviewsSec && (!quotesWrap || quotesWrap.hidden) && (!tp || tp.hidden)) {
    reviewsSec.hidden = true;
  }

  /* ================= Film wall ============================== */
  /* Silent looping clips that behave like moving photographs.

     Three rules make this cheap enough for a paid landing page:
       1. No <video> carries a src until it is about to be seen, so a
          visitor who never scrolls past the hero downloads nothing.
       2. A tile that leaves the viewport pauses — otherwise six clips
          decode in the background and drain a phone battery.
       3. Anyone who has asked their OS for reduced motion gets the
          poster frame and a play button instead of autoplay.

     Autoplay is only permitted because every clip is muted and carries
     `playsinline`; without both, iOS refuses and the tile would sit on
     its poster frame forever. */
  var reduceMotion = window.matchMedia &&
                     window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var films = $$('.film__vid');

  var loadFilm = function (v) {
    if (v.getAttribute('src')) return;
    var src = v.getAttribute('data-src');
    if (src) v.src = src;
  };

  var playFilm = function (v) {
    loadFilm(v);
    var p = v.play();
    // Safari rejects the promise if the tab is backgrounded mid-call.
    if (p && p.catch) p.catch(function () {});
  };

  var syncToggle = function (v) {
    var btn = v.parentNode.querySelector('.film__toggle');
    if (!btn) return;
    var paused = v.paused;
    btn.setAttribute('data-paused', paused ? '1' : '0');
    btn.setAttribute('aria-label', paused ? 'Play video' : 'Pause video');
  };

  if (films.length) {
    films.forEach(function (v) {
      v.addEventListener('play',  function () { syncToggle(v); });
      v.addEventListener('pause', function () { syncToggle(v); });
      syncToggle(v);
    });

    // Tapping the tile itself toggles playback, so the whole surface is
    // the control on a phone — not just the 38px button.
    $$('.film__toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var v = btn.parentNode.querySelector('.film__vid');
        if (!v) return;
        if (v.paused) { playFilm(v); track('video_play', { src: v.getAttribute('data-src') }); }
        else v.pause();
      });
    });

    if (reduceMotion || !('IntersectionObserver' in window)) {
      // No observer, or motion is unwelcome: load on demand only. The
      // poster still shows, so the section never looks broken.
      films.forEach(function (v) { v.setAttribute('data-paused', '1'); syncToggle(v); });
    } else {
      var filmObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          var v = entry.target;
          if (entry.isIntersecting) {
            if (!v.dataset.userPaused) playFilm(v);
          } else if (!v.paused) {
            v.pause();
          }
        });
      }, { rootMargin: '150px 0px', threshold: 0.35 });

      films.forEach(function (v) {
        filmObserver.observe(v);
        // A deliberate pause must survive scrolling away and back.
        v.addEventListener('pause', function () {
          if (!v.ended && document.visibilityState === 'visible' &&
              v.getBoundingClientRect().top < window.innerHeight &&
              v.getBoundingClientRect().bottom > 0) {
            v.dataset.userPaused = '1';
          }
        });
        v.addEventListener('play', function () { delete v.dataset.userPaused; });
      });
    }
  }

  /* ================= Video facades ========================= */
  /* Nothing is requested from YouTube until someone clicks, so the
     embeds cost no page weight and cannot affect Core Web Vitals. */
  $$('.vfacade').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-video');
      if (!id) return;
      var frame = document.createElement('iframe');
      frame.src = 'https://www.youtube-nocookie.com/embed/' + id +
                  '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
      frame.title = btn.getAttribute('aria-label') || 'Rigid box video';
      frame.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
      frame.allowFullscreen = true;
      btn.replaceWith(frame);
      track('video_play', { video_id: id });
    });
  });

  /* No Shorts pasted into SHORTS yet? Drop the section entirely. */
  var videoSec = document.getElementById('video');
  if (videoSec) {
    videoSec.hidden = !$$('.vfacade', videoSec).length;
  }

  /* ================= Tracking on all CTAs ================== */
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-track]');
    if (!el) return;
    var id = el.getAttribute('data-track');
    if (id.indexOf('phone-') === 0) {
      track('conversion', { send_to: CONVERSIONS.call });
      track('phone_click', { location: id });
    } else if (id.indexOf('email-') === 0) {
      track('email_click', { location: id });
    } else if (id.indexOf('cta-') === 0) {
      track('cta_click', { location: id });
    }
  });

  /* Every "get a quote" CTA opens the modal at step 1.
     Falls back to the in-page anchor if the dialog is unavailable. */
  $$('a[href="#quote"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (!modal) return;
      var src = a.getAttribute('data-track') || 'link';
      if (openModal(1, src)) e.preventDefault();
    });
  });

  /* ================= Step 1 submission ===================== */
  /* Posts the contact record, then advances to specs. The lead is
     considered captured the moment this succeeds. */
  var submitStep1 = function (form, e) {
    e.preventDefault();

    var hp = form.querySelector('input[name="website_hp"]');
    if (hp && hp.value) return;                          // bot
    if (!validateForm(form)) return;

    track('conversion', { send_to: CONVERSIONS.lead });
    track('generate_lead', { form: form.id, value: 1, currency: 'USD' });

    var btn = form.querySelector('button[type=submit]');
    var label = btn ? btn.textContent : '';
    if (btn) { btn.disabled = true; btn.textContent = 'Saving your details…'; }

    leadId = leadId || newLeadId();
    var payload = formValues(form);
    payload.stage = '1';
    payload.lead_id = leadId;
    var ctx = adContext();
    Object.keys(ctx).forEach(function (k) { payload[k] = ctx[k]; });

    var advance = function () {
      $('#spec-lead-id').value = leadId;
      if (modal && (modal.open || modal.hasAttribute('open'))) showPane(2);
      else openModal(2, 'step1-complete');
      track('contact_step_complete', {});
    };

    post(payload)
      .then(advance)
      .catch(function () {
        /* Endpoint unreachable. Fall back to a native form POST so the
           lead still reaches you rather than vanishing. */
        form.removeEventListener('submit', form._handler);
        form.submit();
      })
      .finally(function () {
        if (btn) { btn.disabled = false; btn.textContent = label; }
      });
  };

  [$('#lead-form'), step1].forEach(function (form) {
    if (!form) return;
    wireForm(form);
    form._handler = function (e) { submitStep1(form, e); };
    form.addEventListener('submit', form._handler);
  });

  /* ================= Step 2 submission ===================== */
  /* Reads any artwork as base64 so specs and files travel in one
     JSON payload — the same shape for both backends. */
  var readFile = function (file) {
    return new Promise(function (resolve, reject) {
      var fr = new FileReader();
      fr.onload = function () {
        resolve({
          name: file.name,
          type: file.type || 'application/octet-stream',
          data: String(fr.result).split(',')[1]          // strip the data: prefix
        });
      };
      fr.onerror = reject;
      fr.readAsDataURL(file);
    });
  };

  if (specForm) {
    specForm.addEventListener('submit', function (e) {
      e.preventDefault();

      var input = $('#s-files');
      var files = input ? Array.prototype.slice.call(input.files) : [];

      if (files.length > MAX_FILES) {
        alert('Please attach no more than ' + MAX_FILES + ' files. Email the rest to ' +
              'info@customboxesexperts.com and we will match them to your quote.');
        return;
      }
      var oversize = files.filter(function (f) { return f.size > MAX_FILE_BYTES; });
      if (oversize.length) {
        alert('"' + oversize[0].name + '" is larger than ' + (MAX_FILE_BYTES / 1048576) +
              'MB. Please email it to info@customboxesexperts.com instead.');
        return;
      }

      var btn = specForm.querySelector('button[type=submit]');
      var label = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Sending your specs…'; }

      var payload = formValues(specForm);
      payload.stage = '2';
      payload.lead_id = leadId || $('#spec-lead-id').value || newLeadId();
      var ctx = adContext();
      Object.keys(ctx).forEach(function (k) { payload[k] = ctx[k]; });

      Promise.all(files.map(readFile))
        .then(function (encoded) {
          payload.files = encoded;
          return post(payload);
        })
        .then(function () {
          track('spec_step_complete', {});
          location.href = 'thank-you.html';
        })
        .catch(function () {
          if (btn) { btn.disabled = false; btn.textContent = label; }
          alert('We could not send the specs just now, but your contact details are already ' +
                'saved and a specialist will call you. You can also reach us on (888) 716-1078.');
          showPane(3);
        });
    });
  }
})();
