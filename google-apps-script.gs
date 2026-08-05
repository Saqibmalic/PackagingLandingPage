/**
 * Custom Boxes Experts — rigid boxes landing page → Google Sheet
 * ==============================================================
 *
 * Receives both stages of the quote form, writes ONE ROW PER LEAD, and
 * saves any uploaded artwork to Drive with a link in the row.
 *
 * SETUP (about 5 minutes)
 * -----------------------
 * 1. Create a Google Sheet. Name the first tab "Leads".
 * 2. Extensions → Apps Script. Delete the placeholder code, paste this file.
 * 3. Set NOTIFY_EMAIL below (and DRIVE_FOLDER_NAME if you want a custom name).
 * 4. Deploy → New deployment → type "Web app".
 *      Execute as:      Me
 *      Who has access:  Anyone            ← required, the page posts anonymously
 *    Deploy, authorise when prompted, and copy the /exec URL.
 * 5. Paste that URL into BACKEND.url in assets/js/main.js.
 *
 * After any code change you must Deploy → Manage deployments → edit →
 * "New version". Saving alone does not update the live URL.
 *
 * The header row is created automatically on the first submission.
 */

// ── Configuration ────────────────────────────────────────────
var SHEET_NAME        = 'Leads';
var NOTIFY_EMAIL      = 'info@customboxesexperts.com';  // '' disables email alerts
var DRIVE_FOLDER_NAME = 'Rigid Box Quote Artwork';
var MAX_FILES         = 5;

var HEADERS = [
  'Timestamp', 'Lead ID', 'Status', 'Name', 'Email', 'Phone', 'Quantity',
  'Compare Qty', 'Length', 'Width', 'Depth', 'Units', 'Box Style', 'Board',
  'Wrap Stock', 'Insert', 'Finishing', 'Needed By', 'Notes', 'Artwork',
  'GCLID', 'Source', 'Medium', 'Campaign', 'Keyword', 'Content', 'Page URL'
];

// ── Entry point ──────────────────────────────────────────────
function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.waitLock(30000);                       // serialise concurrent submissions
  try {
    var data = JSON.parse(e.postData.contents);

    // Honeypot — accept silently so the bot does not retry.
    if (data.website_hp) {
      return json({ ok: true, lead_id: 'x' });
    }

    var sheet = getSheet();
    var leadId = String(data.lead_id || '').replace(/[^A-Z0-9]/gi, '').substring(0, 12);
    if (!leadId) {
      leadId = Utilities.getUuid().replace(/-/g, '').substring(0, 8).toUpperCase();
    }

    if (String(data.stage) === '2') {
      handleSpecs(sheet, leadId, data);
    } else {
      handleContact(sheet, leadId, data);
    }

    return json({ ok: true, lead_id: leadId });

  } catch (err) {
    // Never lose a lead to a bug — record the raw payload for recovery.
    try {
      getSheet().appendRow([new Date(), 'ERROR', 'Needs review', String(err),
                            (e && e.postData ? e.postData.contents : '').substring(0, 4000)]);
    } catch (ignored) {}
    return json({ ok: false, error: String(err) });
  } finally {
    lock.releaseLock();
  }
}

function doGet() {
  return ContentService
    .createTextOutput('Custom Boxes Experts lead endpoint is live.')
    .setMimeType(ContentService.MimeType.TEXT);
}

// ── Stage 1: contact details → new row ───────────────────────
function handleContact(sheet, leadId, d) {
  sheet.appendRow([
    new Date(), leadId, 'New — call now',
    str(d.name), str(d.email), str(d.phone), str(d.quantity),
    '', '', '', '', '', '', '', '', '', '', '', '', '',
    str(d.gclid), str(d.utm_source), str(d.utm_medium),
    str(d.utm_campaign), str(d.utm_term), str(d.utm_content), str(d.page_url)
  ]);

  if (NOTIFY_EMAIL) {
    MailApp.sendEmail({
      to: NOTIFY_EMAIL,
      replyTo: str(d.email),
      subject: 'New Rigid Box Lead — ' + str(d.name) + ' [' + leadId + ']',
      body: [
        'A new rigid box lead just came in. Box specs may follow in a moment.',
        'Call this person now — do not wait for the spec form.',
        '',
        'Lead ID:  ' + leadId,
        'Name:     ' + str(d.name),
        'Email:    ' + str(d.email),
        'Phone:    ' + str(d.phone),
        'Quantity: ' + str(d.quantity),
        '',
        'Campaign: ' + str(d.utm_campaign) + '  |  Keyword: ' + str(d.utm_term),
        'GCLID:    ' + str(d.gclid),
        'Page:     ' + str(d.page_url)
      ].join('\n')
    });
  }
}

// ── Stage 2: box specs → update that lead's row ──────────────
function handleSpecs(sheet, leadId, d) {
  var row = findRow(sheet, leadId);
  var files = saveFiles(d.files, leadId);

  var values = [
    str(d.quantity2), str(d.length), str(d.width), str(d.depth), str(d.units),
    str(d.style), str(d.board), str(d.wrap), str(d.insert),
    Array.isArray(d.finish) ? d.finish.join(', ') : str(d.finish),
    str(d.need_by), str(d.notes), files.join('\n')
  ];

  if (row > 0) {
    // Columns H..T hold the specification fields.
    sheet.getRange(row, 8, 1, values.length).setValues([values]);
    sheet.getRange(row, 3).setValue('Specs received');
  } else {
    // Stage 1 never landed (rare). Write a standalone row so nothing is lost.
    sheet.appendRow([new Date(), leadId, 'Specs only — no contact row',
                     '', '', '', ''].concat(values,
                     [str(d.gclid), str(d.utm_source), str(d.utm_medium),
                      str(d.utm_campaign), str(d.utm_term), str(d.utm_content), str(d.page_url)]));
  }

  if (NOTIFY_EMAIL) {
    MailApp.sendEmail({
      to: NOTIFY_EMAIL,
      subject: 'Box Specs Added [' + leadId + ']',
      body: [
        'Size:        ' + str(d.length) + ' × ' + str(d.width) + ' × ' + str(d.depth) + ' ' + str(d.units),
        'Box style:   ' + (str(d.style)  || '— asked us to recommend'),
        'Board:       ' + (str(d.board)  || '— asked us to recommend'),
        'Wrap:        ' + (str(d.wrap)   || '—'),
        'Insert:      ' + (str(d.insert) || '—'),
        'Finishing:   ' + (Array.isArray(d.finish) ? d.finish.join(', ') : '—'),
        'Compare qty: ' + (str(d.quantity2) || '—'),
        'Needed by:   ' + (str(d.need_by)   || '—'),
        '',
        'Notes:',
        str(d.notes) || '—',
        '',
        'Artwork:',
        files.length ? files.join('\n') : 'none'
      ].join('\n')
    });
  }
}

// ── Helpers ──────────────────────────────────────────────────
function getSheet() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName(SHEET_NAME) || ss.insertSheet(SHEET_NAME);
  if (sheet.getLastRow() === 0) {
    sheet.appendRow(HEADERS);
    sheet.getRange(1, 1, 1, HEADERS.length)
         .setFontWeight('bold').setBackground('#1A3163').setFontColor('#FFFFFF');
    sheet.setFrozenRows(1);
  }
  return sheet;
}

function findRow(sheet, leadId) {
  var last = sheet.getLastRow();
  if (last < 2) return 0;
  var ids = sheet.getRange(2, 2, last - 1, 1).getValues();
  for (var i = ids.length - 1; i >= 0; i--) {          // newest first
    if (String(ids[i][0]).trim() === leadId) return i + 2;
  }
  return 0;
}

function saveFiles(files, leadId) {
  if (!files || !files.length) return [];
  var folders = DriveApp.getFoldersByName(DRIVE_FOLDER_NAME);
  var folder = folders.hasNext() ? folders.next() : DriveApp.createFolder(DRIVE_FOLDER_NAME);
  var out = [];
  for (var i = 0; i < Math.min(files.length, MAX_FILES); i++) {
    try {
      var f = files[i];
      var blob = Utilities.newBlob(
        Utilities.base64Decode(f.data),
        f.type || 'application/octet-stream',
        leadId + '-' + String(f.name).replace(/[^\w.\- ]/g, '')
      );
      out.push(folder.createFile(blob).getUrl());
    } catch (err) {
      out.push('upload failed: ' + String(err));
    }
  }
  return out;
}

function str(v) {
  return (v === null || v === undefined) ? '' : String(v).substring(0, 4000);
}

function json(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj))
                       .setMimeType(ContentService.MimeType.JSON);
}
