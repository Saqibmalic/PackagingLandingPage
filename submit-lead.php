<?php
/**
 * Custom Boxes Experts — rigid boxes landing page lead handler.
 *
 * Handles a two-stage submission:
 *   stage 1 — contact details. Emailed and stored immediately, and a
 *             lead_id is returned as JSON. This is the lead; it exists
 *             even if the buyer never completes stage 2.
 *   stage 2 — box specification and artwork for that lead_id, emailed
 *             as a follow-up and appended to the CSV.
 * A submission with no stage (JavaScript disabled) is treated as a
 * complete stage-1 lead and redirected to the thank-you page.
 *
 * If you route leads through a CRM or Zapier instead, point the forms'
 * action at that endpoint and delete this file.
 */

// ── Configuration ────────────────────────────────────────────
$TO         = 'info@customboxesexperts.com';
$BCC        = '';                                   // optional second recipient
$FROM       = 'website@customboxesexperts.com';     // must be a mailbox on your domain
$THANK_YOU  = 'thank-you.html';
$CSV_BACKUP = __DIR__ . '/leads.csv';               // move outside the web root if you can
$UPLOAD_DIR = __DIR__ . '/uploads';
$MAX_FILES  = 5;
$MAX_BYTES  = 20 * 1024 * 1024;                     // 20MB per file
$ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'pdf', 'ai', 'eps', 'zip'];

// ── Helpers ──────────────────────────────────────────────────
$isAjax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'fetch')
);

$respond = static function (array $payload, int $code = 200) use ($isAjax, $THANK_YOU) {
    if ($isAjax) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    } else {
        $q = http_build_query(array_filter(['lead' => $payload['lead_id'] ?? '']));
        header('Location: ' . $THANK_YOU . ($q !== '' ? '?' . $q : ''), true, 303);
    }
    exit;
};

$fail = static function (string $message, int $code) use ($isAjax) {
    http_response_code($code);
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => $message]);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }
    exit;
};

$clean = static function ($key, $max = 500) {
    $v = $_POST[$key] ?? '';
    if (is_array($v)) {
        $v = implode(', ', array_map('strval', $v));
    }
    $v = trim(strip_tags((string) $v));
    // Strip header-injection attempts out of anything that reaches the headers.
    $v = str_replace(["\r", "\n", '%0a', '%0d'], ' ', $v);
    return mb_substr($v, 0, $max);
};

$send = static function (string $subject, string $body, string $replyName, string $replyEmail, array $attachNames = [])
        use ($TO, $BCC, $FROM) {
    $headers  = 'From: Custom Boxes Experts Website <' . $FROM . ">\r\n";
    if ($replyEmail !== '') {
        $headers .= 'Reply-To: ' . $replyName . ' <' . $replyEmail . ">\r\n";
    }
    if ($BCC !== '') {
        $headers .= 'Bcc: ' . $BCC . "\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    if ($attachNames) {
        $body .= "\n\nFiles saved on the server: " . implode(', ', $attachNames);
    }
    @mail($TO, $subject, $body, $headers, '-f' . $FROM);
};

$csv = static function (array $row) use ($CSV_BACKUP) {
    $header = ['timestamp','lead_id','stage','name','email','phone','quantity','quantity2',
               'length','width','depth','units','style','board','wrap','insert','finish',
               'need_by','notes','files','gclid','utm_source','utm_medium','utm_campaign','utm_term','utm_content'];
    // Decide on the header before opening: in append mode ftell() reports 0
    // until the first write, so it cannot tell us whether the file is empty.
    $needsHeader = !file_exists($CSV_BACKUP) || filesize($CSV_BACKUP) === 0;
    if ($fh = @fopen($CSV_BACKUP, 'a')) {
        if (flock($fh, LOCK_EX)) {
            if ($needsHeader) {
                fputcsv($fh, $header);
            }
            fputcsv($fh, $row);
            flock($fh, LOCK_UN);
        }
        fclose($fh);
    }
};

// ── Accept a JSON body as well as a normal form POST ─────────
// The landing page sends JSON so the same payload works against either
// this file or the Google Apps Script endpoint. Files arrive base64.
$JSON_FILES = [];
$raw = file_get_contents('php://input');
if ($raw !== '' && $raw[0] === '{') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $JSON_FILES = is_array($decoded['files'] ?? null) ? $decoded['files'] : [];
        unset($decoded['files']);
        $_POST = array_merge($_POST, $decoded);
        $isAjax = true;
    }
}

// ── Guards ───────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: index.html', true, 303);
    exit;
}

// Honeypot: bots fill the hidden field, humans never see it.
// Answer as though it succeeded so the bot does not retry.
if (!empty($_POST['website_hp'])) {
    $respond(['ok' => true, 'lead_id' => 'x']);
}

$stage    = ($clean('stage', 2) === '2') ? 2 : 1;
$gclid    = $clean('gclid', 200);
$source   = $clean('utm_source', 80);
$medium   = $clean('utm_medium', 80);
$campaign = $clean('utm_campaign', 120);
$term     = $clean('utm_term', 160);
$content  = $clean('utm_content', 120);
$pageUrl  = $clean('page_url', 300);

// ══════════════════════════════════════════════════════════════
// STAGE 1 — contact details. This is the lead.
// ══════════════════════════════════════════════════════════════
if ($stage === 1) {
    $name     = $clean('name', 120);
    $email    = $clean('email', 160);
    $phone    = $clean('phone', 40);
    $quantity = $clean('quantity', 60);

    $errors = [];
    if ($name === '')                                  { $errors[] = 'name'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))    { $errors[] = 'email'; }
    if (strlen(preg_replace('/\D/', '', $phone)) < 10) { $errors[] = 'phone'; }
    if ($quantity === '')                              { $errors[] = 'quantity'; }
    if ($errors) {
        $fail('Please go back and check these fields: ' . implode(', ', $errors), 422);
    }

    // The page mints the id so both stages agree on it; fall back if absent.
    $leadId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $clean('lead_id', 12)));
    if ($leadId === '') {
        $leadId = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    $body = implode("\n", [
        'NEW RIGID BOX LEAD — contact captured',
        str_repeat('=', 46),
        '',
        'Lead ID:   ' . $leadId,
        'Name:      ' . $name,
        'Email:     ' . $email,
        'Phone:     ' . $phone,
        'Quantity:  ' . $quantity,
        '',
        'Box specs have not been submitted yet. Call this lead now —',
        'do not wait for the spec form.',
        '',
        str_repeat('-', 46),
        'CAMPAIGN DATA',
        'GCLID:     ' . ($gclid !== '' ? $gclid : '—'),
        'Source:    ' . $source,
        'Medium:    ' . $medium,
        'Campaign:  ' . $campaign,
        'Keyword:   ' . $term,
        'Content:   ' . $content,
        'Page:      ' . $pageUrl,
        '',
        'Submitted: ' . date('Y-m-d H:i:s T'),
        'IP:        ' . ($_SERVER['REMOTE_ADDR'] ?? '—'),
    ]);

    $send('New Rigid Box Lead — ' . $name . ' [' . $leadId . ']', $body, $name, $email);

    $csv([date('c'), $leadId, 1, $name, $email, $phone, $quantity, '', '', '', '', '', '', '',
          '', '', '', '', '', '', $gclid, $source, $medium, $campaign, $term, $content]);

    $respond(['ok' => true, 'lead_id' => $leadId]);
}

// ══════════════════════════════════════════════════════════════
// STAGE 2 — box specification and artwork for an existing lead.
// ══════════════════════════════════════════════════════════════
$leadId    = $clean('lead_id', 20);
$length    = $clean('length', 20);
$width     = $clean('width', 20);
$depth     = $clean('depth', 20);
$units     = $clean('units', 10);
$style     = $clean('style', 60);
$board     = $clean('board', 60);
$wrap      = $clean('wrap', 80);
$insert    = $clean('insert', 80);
$finish    = $clean('finish', 240);
$quantity2 = $clean('quantity2', 40);
$needBy    = $clean('need_by', 20);
$notes     = $clean('notes', 2000);

// ── Artwork uploads ──────────────────────────────────────────
$saved = [];

// Artwork sent as base64 inside the JSON payload.
if ($JSON_FILES) {
    if (!is_dir($UPLOAD_DIR)) {
        @mkdir($UPLOAD_DIR, 0755, true);
    }
    $guard = $UPLOAD_DIR . '/.htaccess';
    if (!file_exists($guard)) {
        @file_put_contents($guard, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phps\nDeny from all\n");
    }
    foreach (array_slice($JSON_FILES, 0, $MAX_FILES) as $f) {
        $ext = strtolower(pathinfo((string) ($f['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, $ALLOWED_EXT, true)) {
            continue;
        }
        $bytes = base64_decode((string) ($f['data'] ?? ''), true);
        if ($bytes === false || strlen($bytes) > $MAX_BYTES) {
            continue;
        }
        $safeName = ($leadId !== '' ? $leadId . '-' : '') . bin2hex(random_bytes(6)) . '.' . $ext;
        if (@file_put_contents($UPLOAD_DIR . '/' . $safeName, $bytes) !== false) {
            $saved[] = $safeName . ' (was "' . preg_replace('/[^\w.\- ]/', '', (string) $f['name']) . '")';
        }
    }
}

if (!empty($_FILES['artwork']['name'][0])) {
    if (!is_dir($UPLOAD_DIR)) {
        @mkdir($UPLOAD_DIR, 0755, true);
    }
    // Belt and braces: stop anything in here from ever being executed.
    $guard = $UPLOAD_DIR . '/.htaccess';
    if (!file_exists($guard)) {
        @file_put_contents($guard, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phps\nDeny from all\n");
    }

    $count = min(count($_FILES['artwork']['name']), $MAX_FILES);
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['artwork']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        if ($_FILES['artwork']['size'][$i] > $MAX_BYTES) {
            continue;
        }
        $orig = $_FILES['artwork']['name'][$i];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, $ALLOWED_EXT, true)) {
            continue;
        }
        $safeName = ($leadId !== '' ? $leadId . '-' : '') . bin2hex(random_bytes(6)) . '.' . $ext;
        if (move_uploaded_file($_FILES['artwork']['tmp_name'][$i], $UPLOAD_DIR . '/' . $safeName)) {
            $saved[] = $safeName . ' (was "' . preg_replace('/[^\w.\- ]/', '', $orig) . '")';
        }
    }
}

$size = ($length !== '' || $width !== '' || $depth !== '')
    ? trim($length . ' × ' . $width . ' × ' . $depth . ' ' . $units)
    : '—';

$body = implode("\n", [
    'BOX SPECS ADDED — lead ' . ($leadId !== '' ? $leadId : 'unknown'),
    str_repeat('=', 46),
    '',
    'Size:       ' . $size,
    'Box style:  ' . ($style     !== '' ? $style     : '— (asked us to recommend)'),
    'Board:      ' . ($board     !== '' ? $board     : '— (asked us to recommend)'),
    'Wrap:       ' . ($wrap      !== '' ? $wrap      : '—'),
    'Insert:     ' . ($insert    !== '' ? $insert    : '—'),
    'Finishing:  ' . ($finish    !== '' ? $finish    : '—'),
    'Compare qty:' . ($quantity2 !== '' ? ' ' . $quantity2 : ' —'),
    'Needed by:  ' . ($needBy    !== '' ? $needBy    : '—'),
    '',
    'Notes:',
    ($notes !== '' ? $notes : '—'),
    '',
    'Artwork:    ' . ($saved ? count($saved) . ' file(s) uploaded' : 'none'),
    '',
    str_repeat('-', 46),
    'Campaign: ' . $campaign . ' | Keyword: ' . $term . ' | GCLID: ' . ($gclid !== '' ? $gclid : '—'),
    'Submitted: ' . date('Y-m-d H:i:s T'),
]);

$send('Box Specs Added [' . ($leadId !== '' ? $leadId : 'no id') . ']', $body, '', '', $saved);

$csv([date('c'), $leadId, 2, '', '', '', '', $quantity2, $length, $width, $depth, $units,
      $style, $board, $wrap, $insert, $finish, $needBy, $notes, implode(' | ', $saved),
      $gclid, $source, $medium, $campaign, $term, $content]);

$respond(['ok' => true, 'lead_id' => $leadId]);
