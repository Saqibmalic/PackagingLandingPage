<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
cbe_require_login();

$cfg = cbe_config();

/* ── Writes (status, value, notes, delete) ───────────────────
   Posted back to this same page so the dashboard works with or without
   JavaScript. The row is saved over fetch() when JS is available.       */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    $ok = false;
    if (cbe_csrf_ok($_POST['csrf'] ?? null)) {
        $leadId = (string) ($_POST['lead_id'] ?? '');
        if (($_POST['action'] ?? '') === 'delete') {
            $ok = cbe_delete_lead($leadId);
        } else {
            $ok = cbe_update_lead($leadId, [
                'status'      => (string) ($_POST['status'] ?? ''),
                'value'       => (string) ($_POST['value'] ?? '0'),
                'admin_notes' => (string) ($_POST['admin_notes'] ?? ''),
            ]);
        }
    }
    if ($wantsJson) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => $ok]);
        exit;
    }
    header('Location: ' . ($_SERVER['REQUEST_URI'] ?? 'index.php'), true, 303);
    exit;
}

/* ── Filters ─────────────────────────────────────────────── */
$filters = [
    'q'          => (string) ($_GET['q'] ?? ''),
    'status'     => (string) ($_GET['status'] ?? ''),
    'from'       => (string) ($_GET['from'] ?? ''),
    'to'         => (string) ($_GET['to'] ?? ''),
    'gclid_only' => !empty($_GET['gclid_only']),
    'has_specs'  => !empty($_GET['has_specs']),
];
$perPage = 50;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$total   = cbe_count_leads($filters);
$pages   = max(1, (int) ceil($total / $perPage));
$page    = min($page, $pages);
$leads   = cbe_find_leads($filters + ['limit' => $perPage, 'offset' => ($page - 1) * $perPage]);
$stats   = cbe_stats();
$hasAnyFilter = $filters['q'] !== '' || $filters['status'] !== '' || $filters['from'] !== ''
             || $filters['to'] !== '' || $filters['gclid_only'] || $filters['has_specs'];

/** Rebuild the current query string with one value swapped. */
$link = static function (array $over = []) use ($filters, $page): string {
    $q = array_filter([
        'q' => $filters['q'], 'status' => $filters['status'],
        'from' => $filters['from'], 'to' => $filters['to'],
        'gclid_only' => $filters['gclid_only'] ? 1 : '',
        'has_specs'  => $filters['has_specs'] ? 1 : '',
        'page' => $page,
    ], static fn ($v) => $v !== '' && $v !== null);
    return '?' . http_build_query(array_filter(array_merge($q, $over),
        static fn ($v) => $v !== '' && $v !== null));
};

/** "10 Sep 2026, 14:03" in the configured Google Ads time zone. */
$when = static function (?string $iso) use ($cfg): string {
    if (!$iso) {
        return '—';
    }
    try {
        return (new DateTime($iso))->setTimezone(new DateTimeZone($cfg['timezone']))
            ->format('d M Y, H:i');
    } catch (Throwable $e) {
        return (string) $iso;
    }
};
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Leads Dashboard · <?= e($cfg['brand']) ?></title>
<link rel="stylesheet" href="assets/dash.css">
</head>
<body>

<header class="top">
  <div class="top__in">
    <div class="top__brand">
      <span class="top__mark">CB</span>
      <div><strong><?= e($cfg['brand']) ?></strong><span>Leads Dashboard</span></div>
    </div>
    <div class="top__right">
      <span class="top__user">Signed in as <strong><?= e(cbe_user()) ?></strong></span>
      <a class="btn btn--ghost btn--sm" href="logout.php">Sign out</a>
    </div>
  </div>
</header>

<main class="wrap">

  <!-- ── Stats ─────────────────────────────────────────── -->
  <section class="cards" aria-label="Summary">
    <div class="card"><span class="card__n"><?= (int) $stats['total'] ?></span><span class="card__l">Total leads</span></div>
    <div class="card"><span class="card__n"><?= (int) $stats['today'] ?></span><span class="card__l">Today</span></div>
    <div class="card"><span class="card__n"><?= (int) $stats['week'] ?></span><span class="card__l">Last 7 days</span></div>
    <div class="card"><span class="card__n"><?= (int) $stats['with_gclid'] ?></span><span class="card__l">From Google Ads</span></div>
    <div class="card"><span class="card__n"><?= (int) $stats['with_specs'] ?></span><span class="card__l">With box specs</span></div>
    <div class="card card--win"><span class="card__n"><?= (int) $stats['won'] ?></span><span class="card__l">Won · <?= e($cfg['currency']) ?> <?= number_format((float) $stats['won_value']) ?></span></div>
  </section>

  <!-- ── Google Ads export ─────────────────────────────── -->
  <section class="panel">
    <h2 class="panel__h">Download for Google Ads</h2>
    <p class="panel__p">
      Upload the file in Google Ads under <strong>Goals → Conversions → Uploads</strong>
      so the campaign learns which clicks became real leads. The conversion name and
      time zone below must match your Google Ads account exactly.
    </p>
    <form class="export" method="get" action="export.php">
      <input type="hidden" name="q"      value="<?= e($filters['q']) ?>">
      <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
      <input type="hidden" name="from"   value="<?= e($filters['from']) ?>">
      <input type="hidden" name="to"     value="<?= e($filters['to']) ?>">
      <input type="hidden" name="gclid_only" value="<?= $filters['gclid_only'] ? 1 : '' ?>">
      <input type="hidden" name="has_specs"  value="<?= $filters['has_specs'] ? 1 : '' ?>">

      <div class="export__grid">
        <label>Conversion action name
          <input name="conversion_name" type="text" value="<?= e($cfg['conversion_name']) ?>" required>
        </label>
        <label>Google Ads time zone
          <select name="tz">
            <?php foreach (['America/New_York','America/Chicago','America/Denver','America/Los_Angeles',
                            'America/Phoenix','America/Anchorage','Pacific/Honolulu','Europe/London','UTC'] as $tz): ?>
              <option value="<?= e($tz) ?>" <?= $tz === $cfg['timezone'] ? 'selected' : '' ?>><?= e($tz) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Currency
          <input name="currency" type="text" value="<?= e($cfg['currency']) ?>" maxlength="3" size="4">
        </label>
        <label>Default value per lead
          <input name="default_value" type="number" min="0" step="1" value="<?= (int) $cfg['default_value'] ?>">
        </label>
      </div>

      <div class="export__btns">
        <button class="btn btn--primary" type="submit" name="type" value="gclid">
          Google Ads conversions (GCLID)
        </button>
        <button class="btn btn--dark" type="submit" name="type" value="enhanced">
          Enhanced conversions for leads
        </button>
        <button class="btn btn--ghost" type="submit" name="type" value="all">
          All lead data (plain CSV)
        </button>
      </div>
      <p class="export__note">
        <?= $hasAnyFilter
            ? 'Your current filters are applied to the download.'
            : 'No filters set — every lead is included.' ?>
        The GCLID file only contains leads that arrived with a Google click ID
        (<?= (int) $stats['with_gclid'] ?> of <?= (int) $stats['total'] ?>).
        The enhanced-conversions file hashes email and phone with SHA-256, exactly as
        Google requires.
      </p>
    </form>
  </section>

  <!-- ── Filters ───────────────────────────────────────── -->
  <form class="filters" method="get">
    <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Search name, email, phone, campaign…">
    <select name="status">
      <option value="">All statuses</option>
      <?php foreach (CBE_STATUSES as $s): ?>
        <option value="<?= e($s) ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <label class="filters__date">From <input type="date" name="from" value="<?= e($filters['from']) ?>"></label>
    <label class="filters__date">To <input type="date" name="to" value="<?= e($filters['to']) ?>"></label>
    <label class="filters__check"><input type="checkbox" name="gclid_only" value="1" <?= $filters['gclid_only'] ? 'checked' : '' ?>> Google Ads only</label>
    <label class="filters__check"><input type="checkbox" name="has_specs" value="1" <?= $filters['has_specs'] ? 'checked' : '' ?>> Has specs</label>
    <button class="btn btn--primary btn--sm" type="submit">Apply</button>
    <?php if ($hasAnyFilter): ?><a class="btn btn--ghost btn--sm" href="index.php">Clear</a><?php endif; ?>
  </form>

  <!-- ── Leads ─────────────────────────────────────────── -->
  <p class="count"><?= $total ?> lead<?= $total === 1 ? '' : 's' ?><?= $hasAnyFilter ? ' matching your filters' : '' ?></p>

  <?php if (!$leads): ?>
    <div class="empty">
      <h3>No leads yet</h3>
      <p>When someone submits the quote form on the landing page it appears here within a second.
         If you have just deployed, submit a test enquiry to check the wiring.</p>
    </div>
  <?php else: ?>

  <div class="tablewrap">
    <table class="leads">
      <thead>
        <tr>
          <th>Received</th><th>Contact</th><th>Quantity</th><th>Source</th>
          <th>Status</th><th>Value</th><th><span class="vh">Details</span></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($leads as $l): ?>
        <?php
          $size = trim(($l['length'] ?? '') . ' × ' . ($l['width'] ?? '') . ' × ' . ($l['depth'] ?? ''), ' ×');
          $rowId = 'd-' . preg_replace('/[^A-Za-z0-9]/', '', (string) $l['lead_id']);
        ?>
        <tr class="lead" data-lead="<?= e($l['lead_id']) ?>">
          <td data-th="Received">
            <span class="when"><?= e($when($l['created_at'])) ?></span>
            <span class="lid"><?= e($l['lead_id']) ?></span>
          </td>
          <td data-th="Contact">
            <strong class="nm"><?= e($l['name'] !== '' ? $l['name'] : '—') ?></strong>
            <?php if ($l['email'] !== ''): ?><a class="lnk" href="mailto:<?= e($l['email']) ?>"><?= e($l['email']) ?></a><?php endif; ?>
            <?php if ($l['phone'] !== ''): ?><a class="lnk" href="tel:<?= e(preg_replace('/[^\d+]/', '', $l['phone'])) ?>"><?= e($l['phone']) ?></a><?php endif; ?>
          </td>
          <td data-th="Quantity"><?= e($l['quantity'] !== '' ? $l['quantity'] : '—') ?></td>
          <td data-th="Source">
            <?php if (($l['gclid'] ?? '') !== ''): ?>
              <span class="tag tag--ads">Google Ads</span>
            <?php elseif (($l['utm_source'] ?? '') !== ''): ?>
              <span class="tag"><?= e($l['utm_source']) ?></span>
            <?php else: ?>
              <span class="tag tag--dim">Direct</span>
            <?php endif; ?>
            <?php if (($l['utm_campaign'] ?? '') !== ''): ?><span class="sub"><?= e($l['utm_campaign']) ?></span><?php endif; ?>
            <?php if (($l['spec_at'] ?? '') !== ''): ?><span class="tag tag--spec">Specs</span><?php endif; ?>
          </td>
          <td data-th="Status">
            <select class="status status--<?= e($l['status']) ?>" data-field="status" aria-label="Status">
              <?php foreach (CBE_STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $l['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td data-th="Value">
            <input class="val" type="number" min="0" step="1" data-field="value"
                   value="<?= (int) $l['value'] ?>" aria-label="Deal value">
          </td>
          <td class="row-actions">
            <button type="button" class="btn btn--ghost btn--sm" data-toggle="<?= e($rowId) ?>"
                    aria-expanded="false" aria-controls="<?= e($rowId) ?>">Details</button>
          </td>
        </tr>
        <tr class="detail" id="<?= e($rowId) ?>" hidden>
          <td colspan="7">
            <div class="detail__grid">
              <div>
                <h4>Box specification</h4>
                <dl>
                  <dt>Size</dt><dd><?= $size !== '' ? e($size . ' ' . $l['units']) : '—' ?></dd>
                  <dt>Style</dt><dd><?= e($l['style'] ?: '—') ?></dd>
                  <dt>Board</dt><dd><?= e($l['board'] ?: '—') ?></dd>
                  <dt>Wrap</dt><dd><?= e($l['wrap'] ?: '—') ?></dd>
                  <dt>Insert</dt><dd><?= e($l['insert_type'] ?: '—') ?></dd>
                  <dt>Finishing</dt><dd><?= e($l['finish'] ?: '—') ?></dd>
                  <dt>Compare qty</dt><dd><?= e($l['quantity2'] ?: '—') ?></dd>
                  <dt>Needed by</dt><dd><?= e($l['need_by'] ?: '—') ?></dd>
                  <dt>Artwork</dt><dd><?= e($l['files'] ?: 'none') ?></dd>
                </dl>
                <?php if (($l['notes'] ?? '') !== ''): ?>
                  <h4>What they told us</h4>
                  <p class="quote"><?= nl2br(e($l['notes'])) ?></p>
                <?php endif; ?>
              </div>
              <div>
                <h4>Attribution</h4>
                <dl>
                  <dt>GCLID</dt><dd class="mono"><?= e($l['gclid'] ?: '—') ?></dd>
                  <dt>Source</dt><dd><?= e($l['utm_source'] ?: '—') ?></dd>
                  <dt>Medium</dt><dd><?= e($l['utm_medium'] ?: '—') ?></dd>
                  <dt>Campaign</dt><dd><?= e($l['utm_campaign'] ?: '—') ?></dd>
                  <dt>Keyword</dt><dd><?= e($l['utm_term'] ?: '—') ?></dd>
                  <dt>Ad content</dt><dd><?= e($l['utm_content'] ?: '—') ?></dd>
                  <dt>Landing page</dt><dd class="brk"><?= e($l['page_url'] ?: '—') ?></dd>
                  <dt>IP</dt><dd><?= e($l['ip'] ?: '—') ?></dd>
                  <dt>Specs added</dt><dd><?= e($when($l['spec_at'] ?: null)) ?></dd>
                  <dt>Last export</dt><dd><?= e($when($l['exported_at'] ?: null)) ?></dd>
                </dl>
              </div>
              <div>
                <h4>Your notes</h4>
                <textarea class="notes" data-field="admin_notes" rows="6"
                          placeholder="Call outcome, quoted price, next step…"><?= e($l['admin_notes']) ?></textarea>
                <div class="detail__btns">
                  <button type="button" class="btn btn--primary btn--sm" data-save>Save notes</button>
                  <button type="button" class="btn btn--danger btn--sm" data-delete>Delete lead</button>
                </div>
                <p class="saved" data-saved hidden>Saved</p>
              </div>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="pager">
      <?php if ($page > 1): ?><a class="btn btn--ghost btn--sm" href="<?= e($link(['page' => $page - 1])) ?>">← Newer</a><?php endif; ?>
      <span>Page <?= $page ?> of <?= $pages ?></span>
      <?php if ($page < $pages): ?><a class="btn btn--ghost btn--sm" href="<?= e($link(['page' => $page + 1])) ?>">Older →</a><?php endif; ?>
    </nav>
  <?php endif; ?>

  <?php endif; ?>
</main>

<script>
  window.CBE_CSRF = <?= json_encode(cbe_csrf_token()) ?>;
</script>
<script src="assets/dash.js" defer></script>
</body>
</html>
