/* Dashboard interactions: expand a lead, save status / value / notes, delete.
   Everything is optional — the page still reads fine without JavaScript. */
(function () {
  'use strict';

  var csrf = window.CBE_CSRF || '';

  function rowFor(el) {
    return el.closest('tr.lead') || el.closest('tr.detail').previousElementSibling;
  }

  function flash(row) {
    var detail = document.getElementById(row.querySelector('[data-toggle]').dataset.toggle);
    var note = detail && detail.querySelector('[data-saved]');
    if (!note) return;
    note.hidden = false;
    clearTimeout(note._t);
    note._t = setTimeout(function () { note.hidden = true; }, 1800);
  }

  /* Save the editable fields of one lead. */
  function save(row) {
    var detail = document.getElementById(row.querySelector('[data-toggle]').dataset.toggle);
    var body = new URLSearchParams();
    body.set('csrf', csrf);
    body.set('lead_id', row.dataset.lead);
    body.set('status', row.querySelector('[data-field="status"]').value);
    body.set('value', row.querySelector('[data-field="value"]').value || '0');
    var notes = detail && detail.querySelector('[data-field="admin_notes"]');
    body.set('admin_notes', notes ? notes.value : '');

    return fetch(location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
      body: body.toString(),
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d.ok) throw new Error('save rejected');
        flash(row);
      })
      .catch(function () {
        alert('Could not save that change. Check your connection and try again.');
      });
  }

  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('[data-toggle]');
    if (toggle) {
      var panel = document.getElementById(toggle.dataset.toggle);
      if (!panel) return;
      var open = panel.hidden;
      panel.hidden = !open;
      toggle.setAttribute('aria-expanded', String(open));
      toggle.textContent = open ? 'Hide' : 'Details';
      return;
    }

    if (e.target.closest('[data-save]')) {
      save(rowFor(e.target));
      return;
    }

    var del = e.target.closest('[data-delete]');
    if (del) {
      var row = rowFor(del);
      if (!confirm('Delete this lead permanently? This cannot be undone.')) return;
      var body = new URLSearchParams();
      body.set('csrf', csrf);
      body.set('action', 'delete');
      body.set('lead_id', row.dataset.lead);
      fetch(location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
        body: body.toString(),
        credentials: 'same-origin'
      }).then(function () { location.reload(); });
    }
  });

  /* Status and value save the moment they change. */
  document.addEventListener('change', function (e) {
    var field = e.target.closest('[data-field="status"],[data-field="value"]');
    if (!field) return;
    var row = rowFor(field);
    if (field.dataset.field === 'status') {
      field.className = 'status status--' + field.value;
    }
    save(row);
  });
})();
