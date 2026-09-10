<?php
/**
 * CSV downloads.
 *
 *   type=gclid     Google Ads offline conversion import, keyed on the Google
 *                  click ID captured when the visitor landed. This is the one
 *                  that teaches Smart Bidding which clicks became leads.
 *   type=enhanced  Enhanced conversions for leads — same idea but keyed on the
 *                  buyer's email and phone (SHA-256 hashed and normalised the
 *                  way Google specifies), for leads with no GCLID.
 *   type=all       Every field, for your own records or a CRM import.
 *
 * Upload the first two in Google Ads: Goals → Conversions → Uploads → the +
 * button → Upload a file.
 */

declare(strict_types=1);
require_once __DIR__ . '/auth.php';
cbe_require_login();

$cfg  = cbe_config();
$type = (string) ($_GET['type'] ?? 'all');

$conversionName = trim((string) ($_GET['conversion_name'] ?? $cfg['conversion_name']));
$currency       = strtoupper(substr(trim((string) ($_GET['currency'] ?? $cfg['currency'])), 0, 3)) ?: 'USD';
$defaultValue   = (float) ($_GET['default_value'] ?? $cfg['default_value']);
$tz             = (string) ($_GET['tz'] ?? $cfg['timezone']);
try {
    $zone = new DateTimeZone($tz);
} catch (Throwable $e) {
    $tz   = 'UTC';
    $zone = new DateTimeZone('UTC');
}

$filters = [
    'q'          => (string) ($_GET['q'] ?? ''),
    'status'     => (string) ($_GET['status'] ?? ''),
    'from'       => (string) ($_GET['from'] ?? ''),
    'to'         => (string) ($_GET['to'] ?? ''),
    'gclid_only' => !empty($_GET['gclid_only']),
    'has_specs'  => !empty($_GET['has_specs']),
    'limit'      => 5000,
];
$leads = cbe_find_leads($filters);

/** Conversion time in the format Google Ads expects for an upload. */
$convTime = static function (string $iso) use ($zone): string {
    try {
        return (new DateTime($iso))->setTimezone($zone)->format('Y-m-d H:i:s');
    } catch (Throwable $e) {
        return gmdate('Y-m-d H:i:s');
    }
};

/** Google's normalisation: trim, lowercase, then SHA-256 hex. */
$hashEmail = static function (string $email): string {
    $email = strtolower(trim($email));
    return $email === '' ? '' : hash('sha256', $email);
};

/**
 * Phones must be E.164 before hashing. A 10-digit US number gets +1; anything
 * already carrying a country code is passed through.
 */
$hashPhone = static function (string $phone): string {
    $digits = preg_replace('/\D/', '', $phone) ?? '';
    if ($digits === '') {
        return '';
    }
    if (strlen($digits) === 10) {
        $digits = '1' . $digits;
    }
    return hash('sha256', '+' . $digits);
};

$stamp    = date('Y-m-d');
$filename = [
    'gclid'    => "google-ads-conversions-$stamp.csv",
    'enhanced' => "google-ads-enhanced-conversions-$stamp.csv",
][$type] ?? "leads-$stamp.csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
$exported = [];

if ($type === 'gclid' || $type === 'enhanced') {
    // Google reads this first line to interpret the times below it.
    fputcsv($out, ['Parameters:TimeZone=' . $tz]);

    if ($type === 'gclid') {
        fputcsv($out, ['Google Click ID', 'Conversion Name', 'Conversion Time',
                       'Conversion Value', 'Conversion Currency']);
        foreach ($leads as $l) {
            if (($l['gclid'] ?? '') === '') {
                continue;                        // nothing to attribute against
            }
            $value = (float) $l['value'] > 0 ? (float) $l['value'] : $defaultValue;
            fputcsv($out, [
                $l['gclid'],
                $conversionName,
                $convTime((string) $l['created_at']),
                number_format($value, 2, '.', ''),
                $currency,
            ]);
            $exported[] = (string) $l['lead_id'];
        }
    } else {
        fputcsv($out, ['Email', 'Phone Number', 'Conversion Name', 'Conversion Time',
                       'Conversion Value', 'Conversion Currency']);
        foreach ($leads as $l) {
            $email = $hashEmail((string) $l['email']);
            $phone = $hashPhone((string) $l['phone']);
            if ($email === '' && $phone === '') {
                continue;
            }
            $value = (float) $l['value'] > 0 ? (float) $l['value'] : $defaultValue;
            fputcsv($out, [
                $email,
                $phone,
                $conversionName,
                $convTime((string) $l['created_at']),
                number_format($value, 2, '.', ''),
                $currency,
            ]);
            $exported[] = (string) $l['lead_id'];
        }
    }
    cbe_mark_exported($exported);
} else {
    // Full export — human-readable, opens straight in Excel or Sheets.
    fputcsv($out, [
        'Lead ID', 'Received', 'Name', 'Email', 'Phone', 'Quantity',
        'Status', 'Value', 'Your notes',
        'Length', 'Width', 'Depth', 'Units', 'Style', 'Board', 'Wrap', 'Insert',
        'Finishing', 'Compare qty', 'Needed by', 'Their notes', 'Artwork',
        'GCLID', 'UTM source', 'UTM medium', 'UTM campaign', 'UTM term', 'UTM content',
        'Landing page', 'IP', 'Specs added',
    ]);
    foreach ($leads as $l) {
        fputcsv($out, [
            $l['lead_id'], $convTime((string) $l['created_at']), $l['name'], $l['email'],
            $l['phone'], $l['quantity'], $l['status'], $l['value'], $l['admin_notes'],
            $l['length'], $l['width'], $l['depth'], $l['units'], $l['style'], $l['board'],
            $l['wrap'], $l['insert_type'], $l['finish'], $l['quantity2'], $l['need_by'],
            $l['notes'], $l['files'],
            $l['gclid'], $l['utm_source'], $l['utm_medium'], $l['utm_campaign'],
            $l['utm_term'], $l['utm_content'], $l['page_url'], $l['ip'],
            $l['spec_at'] !== '' ? $convTime((string) $l['spec_at']) : '',
        ]);
    }
}

fclose($out);
