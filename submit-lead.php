<?php
/**
 * Custom Boxes Experts — rigid boxes landing page lead handler.
 *
 * Drop-in handler for standard PHP shared hosting. It validates the
 * submission, blocks bot traffic, emails the lead to sales, appends a
 * CSV backup, and redirects to thank-you.html so the Google Ads
 * conversion fires on a real page view.
 *
 * If you route leads through a CRM or Zapier instead, point the form's
 * action at that endpoint and delete this file.
 */

// ── Configuration ────────────────────────────────────────────
$TO          = 'info@customboxesexperts.com';
$BCC         = '';                      // optional second recipient
$FROM        = 'website@customboxesexperts.com';   // must be a domain mailbox
$SUBJECT     = 'New Rigid Box Quote Request';
$THANK_YOU   = 'thank-you.html';
$CSV_BACKUP  = __DIR__ . '/leads.csv';  // move outside the web root if possible

// ── Guards ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html', true, 303);
    exit;
}

// Honeypot: bots fill the hidden field, humans never see it.
if (!empty($_POST['website_hp'])) {
    header('Location: ' . $THANK_YOU, true, 303);
    exit;
}

$clean = static function ($key, $max = 500) {
    $v = isset($_POST[$key]) ? $_POST[$key] : '';
    if (is_array($v)) {
        $v = implode(', ', $v);
    }
    $v = trim(strip_tags((string) $v));
    // Strip header-injection attempts out of anything that reaches the headers.
    $v = str_replace(["\r", "\n", "%0a", "%0d"], ' ', $v);
    return mb_substr($v, 0, $max);
};

$name     = $clean('name', 120);
$email    = $clean('email', 160);
$phone    = $clean('phone', 40);
$quantity = $clean('quantity', 60);
$style    = $clean('style', 60);
$size     = $clean('size', 80);
$finish   = $clean('finish', 200);
$notes    = $clean('notes', 2000);

$gclid    = $clean('gclid', 200);
$source   = $clean('utm_source', 80);
$medium   = $clean('utm_medium', 80);
$campaign = $clean('utm_campaign', 120);
$term     = $clean('utm_term', 160);
$content  = $clean('utm_content', 120);
$pageUrl  = $clean('page_url', 300);

// ── Validation ───────────────────────────────────────────────
$errors = [];
if ($name === '')                                        { $errors[] = 'name'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL))          { $errors[] = 'email'; }
if (strlen(preg_replace('/\D/', '', $phone)) < 10)       { $errors[] = 'phone'; }
if ($quantity === '')                                    { $errors[] = 'quantity'; }

if ($errors) {
    http_response_code(422);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Please go back and check these fields: " . implode(', ', $errors);
    exit;
}

// ── Email to sales ───────────────────────────────────────────
$lines = [
    'RIGID BOX QUOTE REQUEST',
    str_repeat('=', 46),
    '',
    'Name:      ' . $name,
    'Email:     ' . $email,
    'Phone:     ' . $phone,
    'Quantity:  ' . $quantity,
    '',
    'Box style: ' . ($style   !== '' ? $style   : '—'),
    'Size:      ' . ($size    !== '' ? $size    : '—'),
    'Finishing: ' . ($finish  !== '' ? $finish  : '—'),
    '',
    'Notes:',
    ($notes !== '' ? $notes : '—'),
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
];
$body = implode("\n", $lines);

$headers  = 'From: Custom Boxes Experts Website <' . $FROM . ">\r\n";
$headers .= 'Reply-To: ' . $name . ' <' . $email . ">\r\n";
if ($BCC !== '') {
    $headers .= 'Bcc: ' . $BCC . "\r\n";
}
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

@mail($TO, $SUBJECT . ' — ' . $name, $body, $headers, '-f' . $FROM);

// ── CSV backup so no lead is lost if mail delivery fails ─────
$row = [
    date('c'), $name, $email, $phone, $quantity, $style, $size, $finish,
    $notes, $gclid, $source, $medium, $campaign, $term, $content,
];
if ($fh = @fopen($CSV_BACKUP, 'a')) {
    if (flock($fh, LOCK_EX)) {
        if (ftell($fh) === 0) {
            fputcsv($fh, ['timestamp','name','email','phone','quantity','style','size','finish','notes','gclid','utm_source','utm_medium','utm_campaign','utm_term','utm_content']);
        }
        fputcsv($fh, $row);
        flock($fh, LOCK_UN);
    }
    fclose($fh);
}

// ── Redirect to the tracked thank-you page ───────────────────
$q = http_build_query(['gclid' => $gclid, 'qty' => $quantity]);
header('Location: ' . $THANK_YOU . ($q !== '' ? '?' . $q : ''), true, 303);
exit;
