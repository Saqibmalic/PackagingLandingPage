/* ============================================================
   Custom Boxes Experts — rigid boxes landing page
   Form validation, tracking events, UX helpers.
   No dependencies. ~3KB.
   ============================================================ */
(function () {
  'use strict';

  /* ---- Google Ads conversion labels -------------------------
     Replace with the values from Google Ads > Goals > Conversions.
     Format: 'AW-XXXXXXXXXX/AbCdEfGhIjKlMnOp'
     ---------------------------------------------------------- */
  var CONVERSIONS = {
    lead:  'AW-XXXXXXXXXX/REPLACE_LEAD_LABEL',
    call:  'AW-XXXXXXXXXX/REPLACE_CALL_LABEL'
  };

  var track = function (name, params) {
    if (typeof gtag === 'function') gtag('event', name, params || {});
  };

  /* ---- Footer year ---------------------------------------- */
  var yr = document.getElementById('yr');
  if (yr) yr.textContent = new Date().getFullYear();

  /* ---- Click tracking on every [data-track] element -------- */
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

  /* ---- Form validation + submission ------------------------ */
  var form = document.getElementById('lead-form');
  if (!form) return;

  var started = false;
  var MESSAGES = {
    name:     'Please enter your name.',
    email:    'Please enter a valid work email address.',
    phone:    'Please enter a phone number we can reach you on.',
    quantity: 'Please choose an approximate quantity.'
  };

  var isEmail = function (v) { return /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(v.trim()); };
  var isPhone = function (v) { return (v.replace(/\D/g, '').length >= 10); };

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
    var v = input.value.trim();
    var n = input.name;
    if (!v) { setError(input, MESSAGES[n] || 'This field is required.'); return false; }
    if (n === 'email' && !isEmail(v)) { setError(input, MESSAGES.email); return false; }
    if (n === 'phone' && !isPhone(v)) { setError(input, MESSAGES.phone); return false; }
    setError(input, null);
    return true;
  };

  var required = form.querySelectorAll('[required]');

  Array.prototype.forEach.call(required, function (input) {
    input.addEventListener('blur', function () { if (input.value.trim()) validate(input); });
    input.addEventListener('input', function () {
      if (input.closest('.field').classList.contains('is-bad')) validate(input);
      if (!started) { started = true; track('form_start', { form: 'rigid_quote' }); }
    });
  });

  /* Phone number formatting for US numbers, non-destructive */
  var phone = form.querySelector('input[name="phone"]');
  if (phone) {
    phone.addEventListener('input', function () {
      var d = phone.value.replace(/\D/g, '').slice(0, 10);
      if (d.length > 6)      phone.value = '(' + d.slice(0, 3) + ') ' + d.slice(3, 6) + '-' + d.slice(6);
      else if (d.length > 3) phone.value = '(' + d.slice(0, 3) + ') ' + d.slice(3);
      else if (d.length > 0) phone.value = '(' + d;
    });
  }

  form.addEventListener('submit', function (e) {
    /* honeypot — silently drop bot submissions */
    var hp = form.querySelector('input[name="website_hp"]');
    if (hp && hp.value) { e.preventDefault(); return; }

    var ok = true, firstBad = null;
    Array.prototype.forEach.call(required, function (input) {
      if (!validate(input)) { ok = false; if (!firstBad) firstBad = input; }
    });

    if (!ok) {
      e.preventDefault();
      if (firstBad) { firstBad.focus(); firstBad.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
      track('form_error', { form: 'rigid_quote' });
      return;
    }

    /* Fire the Google Ads lead conversion before navigation */
    track('conversion', { send_to: CONVERSIONS.lead });
    track('generate_lead', { form: 'rigid_quote', value: 1, currency: 'USD' });

    /* Pass the ad click context through to the CRM/email */
    var qs = new URLSearchParams(location.search);
    ['gclid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(function (k) {
      if (!qs.get(k)) return;
      var h = document.createElement('input');
      h.type = 'hidden'; h.name = k; h.value = qs.get(k);
      form.appendChild(h);
    });
    var ref = document.createElement('input');
    ref.type = 'hidden'; ref.name = 'page_url'; ref.value = location.href;
    form.appendChild(ref);

    var btn = form.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Sending your request…'; }
  });
})();
