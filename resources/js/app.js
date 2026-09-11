/* ============================================================
   Custom Boxes Experts — rigid boxes landing page

   What is NOT here any more, because Livewire owns it:
   field validation, form submission, the lead id, the ad-click
   context, and firing the Google Ads conversion. Those all moved
   server-side when the page became a Laravel app — an ad blocker
   or a JS error can no longer cost you a lead or its attribution.

   What remains is presentation: the quote modal, the gallery
   lightbox, the silent film wall, and the click-to-load YouTube
   facades.
   ============================================================ */
import './shorts';

(function () {
  'use strict';

  var $  = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  var track = function (name, params) {
    if (typeof gtag === 'function') gtag('event', name, params || {});
  };

  /* ================= Quote modal =========================== */
  /* Holds a second instance of the Livewire quote form, so every CTA
     on the page can open it without scrolling back to the hero. */
  var modal = $('#quote-modal');
  var lastFocus = null;
  var supportsDialog = modal && typeof modal.showModal === 'function';

  var openModal = function (source) {
    if (!modal) return false;
    lastFocus = document.activeElement;
    if (supportsDialog) modal.showModal();
    else { modal.setAttribute('open', ''); modal.style.display = 'block'; }
    document.body.classList.add('modal-open');
    track('quote_modal_open', { source: source || 'unknown' });
    var focusTarget = modal.querySelector('input, select, a.btn');
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
    // A click on the dialog element itself is a click on the backdrop.
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    modal.addEventListener('close', function () { document.body.classList.remove('modal-open'); });
  }

  /* Every "get a quote" CTA opens the modal. With no dialog support the
     href still jumps to the form in the hero, so nothing is unreachable. */
  $$('a[href="#quote"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (!modal) return;
      if (openModal(a.getAttribute('data-track') || 'link')) e.preventDefault();
    });
  });

  /* ================= US phone formatting =================== */
  /* Cosmetic only — the server accepts any 10+ digit number. The input
     event is dispatched by hand so Livewire sees the reformatted value
     rather than what was typed. */
  document.addEventListener('input', function (e) {
    var el = e.target;
    if (!el.matches || !el.matches('[data-phone]') || el._formatting) return;

    var digits = el.value.replace(/\D/g, '').slice(0, 10);
    var formatted = digits;
    if (digits.length > 6)      formatted = '(' + digits.slice(0, 3) + ') ' + digits.slice(3, 6) + '-' + digits.slice(6);
    else if (digits.length > 3) formatted = '(' + digits.slice(0, 3) + ') ' + digits.slice(3);
    else if (digits.length > 0) formatted = '(' + digits;

    if (formatted === el.value) return;

    el._formatting = true;
    el.value = formatted;
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el._formatting = false;
  });

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
    // Only offer the toggle when a second shot actually exists.
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
        // Warm the open shot so the toggle is instant, not a spinner.
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
    // The lightbox CTA hands over to the quote modal rather than just jumping.
    var lbCta = lb.querySelector('.lb__cta');
    if (lbCta) lbCta.addEventListener('click', function (e) {
      e.preventDefault();
      lbClose();
      openModal('lightbox');
    });
  }

  /* Photos not added yet? Hide the section rather than show broken images. */
  if ($('#gallery') && shots.length) {
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

  /* ---- Never show unfinished content to a visitor ----------
     Gallery tiles and testimonials ship with placeholder text. Rather
     than risk that text going live, anything still holding a
     placeholder removes itself, and a section whose content is
     entirely placeholder disappears with it. */
  var isPlaceholder = function (el) {
    if (!el) return false;
    var t = (el.textContent || '') + (el.getAttribute && el.getAttribute('data-caption') || '');
    return /REPLACE/.test(t);
  };

  $$('#gallery .shot').forEach(function (fig) {
    var img = fig.querySelector('img');
    var txt = (fig.textContent || '') + ' ' + (img && img.getAttribute('data-caption') || '');
    if (/REPLACE/.test(txt)) fig.hidden = true;
  });

  $$('.quote').forEach(function (fig) {
    if (isPlaceholder(fig.querySelector('blockquote'))) fig.hidden = true;
  });
  var quotesWrap = $('.quotes');
  if (quotesWrap && !$$('.quote:not([hidden])', quotesWrap).length) quotesWrap.hidden = true;

  var reviewsSec = document.getElementById('reviews');
  if (reviewsSec && (!quotesWrap || quotesWrap.hidden)) reviewsSec.hidden = true;

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

  /* ================= Tracking on all CTAs ================== */
  document.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('[data-track]');
    if (!el) return;
    var id = el.getAttribute('data-track');
    if (id.indexOf('phone-') === 0) {
      if (window.CBE_CALL_LABEL) track('conversion', { send_to: window.CBE_CALL_LABEL });
      track('phone_click', { location: id });
    } else if (id.indexOf('email-') === 0) {
      track('email_click', { location: id });
    } else if (id.indexOf('cta-') === 0) {
      track('cta_click', { location: id });
    }
  });

})();
