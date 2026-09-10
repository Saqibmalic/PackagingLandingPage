<?php
/**
 * Lead storage — SQLite.
 *
 * One row per lead, keyed by the lead_id the landing page mints. Stage 1
 * (contact details) inserts the row; stage 2 (box specs + artwork) updates
 * the same row, so the dashboard shows one complete record per enquiry.
 *
 * SQLite needs no database server — the whole store is a single file, which
 * is why this works on a bare Namecheap VPS with nothing but PHP installed.
 * The file lives outside the document root wherever possible; see
 * CBE_DATA_DIR below.
 */

declare(strict_types=1);

/** Directory holding leads.sqlite. Override with the CBE_DATA_DIR env var. */
function cbe_data_dir(): string
{
    $dir = getenv('CBE_DATA_DIR');
    if (!is_string($dir) || $dir === '') {
        $dir = dirname(__DIR__) . '/data';
    }
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    // If the data dir ends up inside the web root, stop Apache serving it.
    $guard = $dir . '/.htaccess';
    if (is_dir($dir) && !file_exists($guard)) {
        @file_put_contents($guard, "Require all denied\nDeny from all\n");
    }
    return $dir;
}

/** Every column the dashboard and the exports know about. */
const CBE_LEAD_COLUMNS = [
    'lead_id', 'created_at', 'updated_at',
    'name', 'email', 'phone', 'quantity',
    'spec_at', 'length', 'width', 'depth', 'units',
    'style', 'board', 'wrap', 'insert_type', 'finish',
    'quantity2', 'need_by', 'notes', 'files',
    'gclid', 'gbraid', 'wbraid',
    'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
    'page_url', 'ip', 'user_agent',
    'status', 'value', 'admin_notes', 'exported_at',
];

/** Statuses a lead can move through, in pipeline order. */
const CBE_STATUSES = ['new', 'contacted', 'quoted', 'won', 'lost', 'spam'];

/**
 * Open the store, creating the schema on first use.
 * Returns null instead of throwing so a storage problem can never stop a
 * lead being emailed.
 */
function cbe_db(): ?PDO
{
    static $pdo = null;
    static $tried = false;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if ($tried) {
        return null;
    }
    $tried = true;

    try {
        $file = cbe_data_dir() . '/leads.sqlite';
        $pdo  = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // WAL keeps a dashboard read from blocking a lead write.
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS leads (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                lead_id      TEXT UNIQUE,
                created_at   TEXT NOT NULL,
                updated_at   TEXT NOT NULL,
                name         TEXT DEFAULT "",
                email        TEXT DEFAULT "",
                phone        TEXT DEFAULT "",
                quantity     TEXT DEFAULT "",
                spec_at      TEXT DEFAULT "",
                length       TEXT DEFAULT "",
                width        TEXT DEFAULT "",
                depth        TEXT DEFAULT "",
                units        TEXT DEFAULT "",
                style        TEXT DEFAULT "",
                board        TEXT DEFAULT "",
                wrap         TEXT DEFAULT "",
                insert_type  TEXT DEFAULT "",
                finish       TEXT DEFAULT "",
                quantity2    TEXT DEFAULT "",
                need_by      TEXT DEFAULT "",
                notes        TEXT DEFAULT "",
                files        TEXT DEFAULT "",
                gclid        TEXT DEFAULT "",
                gbraid       TEXT DEFAULT "",
                wbraid       TEXT DEFAULT "",
                utm_source   TEXT DEFAULT "",
                utm_medium   TEXT DEFAULT "",
                utm_campaign TEXT DEFAULT "",
                utm_term     TEXT DEFAULT "",
                utm_content  TEXT DEFAULT "",
                page_url     TEXT DEFAULT "",
                ip           TEXT DEFAULT "",
                user_agent   TEXT DEFAULT "",
                status       TEXT DEFAULT "new",
                value        REAL DEFAULT 0,
                admin_notes  TEXT DEFAULT "",
                exported_at  TEXT DEFAULT ""
            )'
        );
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_leads_created ON leads (created_at DESC)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_leads_status  ON leads (status)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_leads_gclid   ON leads (gclid)');
        return $pdo;
    } catch (Throwable $e) {
        error_log('[cbe] lead store unavailable: ' . $e->getMessage());
        $pdo = null;
        return null;
    }
}

/**
 * Stage 1 — create (or refresh) the contact record.
 * Returns true when the row was written.
 */
function cbe_save_contact(array $d): bool
{
    $pdo = cbe_db();
    if (!$pdo) {
        return false;
    }
    $now = gmdate('c');
    try {
        $sql = 'INSERT INTO leads
                  (lead_id, created_at, updated_at, name, email, phone, quantity,
                   gclid, gbraid, wbraid, utm_source, utm_medium, utm_campaign,
                   utm_term, utm_content, page_url, ip, user_agent)
                VALUES
                  (:lead_id, :created_at, :updated_at, :name, :email, :phone, :quantity,
                   :gclid, :gbraid, :wbraid, :utm_source, :utm_medium, :utm_campaign,
                   :utm_term, :utm_content, :page_url, :ip, :user_agent)
                ON CONFLICT(lead_id) DO UPDATE SET
                   updated_at = excluded.updated_at,
                   name       = excluded.name,
                   email      = excluded.email,
                   phone      = excluded.phone,
                   quantity   = excluded.quantity';
        $pdo->prepare($sql)->execute([
            ':lead_id'      => (string) ($d['lead_id'] ?? ''),
            ':created_at'   => $now,
            ':updated_at'   => $now,
            ':name'         => (string) ($d['name'] ?? ''),
            ':email'        => (string) ($d['email'] ?? ''),
            ':phone'        => (string) ($d['phone'] ?? ''),
            ':quantity'     => (string) ($d['quantity'] ?? ''),
            ':gclid'        => (string) ($d['gclid'] ?? ''),
            ':gbraid'       => (string) ($d['gbraid'] ?? ''),
            ':wbraid'       => (string) ($d['wbraid'] ?? ''),
            ':utm_source'   => (string) ($d['utm_source'] ?? ''),
            ':utm_medium'   => (string) ($d['utm_medium'] ?? ''),
            ':utm_campaign' => (string) ($d['utm_campaign'] ?? ''),
            ':utm_term'     => (string) ($d['utm_term'] ?? ''),
            ':utm_content'  => (string) ($d['utm_content'] ?? ''),
            ':page_url'     => (string) ($d['page_url'] ?? ''),
            ':ip'           => (string) ($d['ip'] ?? ''),
            ':user_agent'   => mb_substr((string) ($d['user_agent'] ?? ''), 0, 300),
        ]);
        return true;
    } catch (Throwable $e) {
        error_log('[cbe] save contact failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Stage 2 — attach box specs to an existing lead. If stage 1 never landed
 * (endpoint hiccup, direct link to the spec form) the row is created here so
 * the specs are not lost.
 */
function cbe_save_specs(array $d): bool
{
    $pdo = cbe_db();
    if (!$pdo) {
        return false;
    }
    $leadId = (string) ($d['lead_id'] ?? '');
    if ($leadId === '') {
        return false;
    }
    $now = gmdate('c');
    try {
        $pdo->prepare(
            'INSERT OR IGNORE INTO leads (lead_id, created_at, updated_at) VALUES (?, ?, ?)'
        )->execute([$leadId, $now, $now]);

        // Campaign data only overwrites when the new submission actually has
        // it — the thank-you page URL carries no gclid.
        $pdo->prepare(
            'UPDATE leads SET
                updated_at   = :updated_at,
                spec_at      = :spec_at,
                length       = :length,
                width        = :width,
                depth        = :depth,
                units        = :units,
                style        = :style,
                board        = :board,
                wrap         = :wrap,
                insert_type  = :insert_type,
                finish       = :finish,
                quantity2    = :quantity2,
                need_by      = :need_by,
                notes        = :notes,
                files        = :files,
                gclid        = CASE WHEN :gclid        != "" THEN :gclid        ELSE gclid        END,
                utm_source   = CASE WHEN :utm_source   != "" THEN :utm_source   ELSE utm_source   END,
                utm_medium   = CASE WHEN :utm_medium   != "" THEN :utm_medium   ELSE utm_medium   END,
                utm_campaign = CASE WHEN :utm_campaign != "" THEN :utm_campaign ELSE utm_campaign END,
                utm_term     = CASE WHEN :utm_term     != "" THEN :utm_term     ELSE utm_term     END,
                utm_content  = CASE WHEN :utm_content  != "" THEN :utm_content  ELSE utm_content  END
             WHERE lead_id = :lead_id'
        )->execute([
            ':updated_at'   => $now,
            ':spec_at'      => $now,
            ':length'       => (string) ($d['length'] ?? ''),
            ':width'        => (string) ($d['width'] ?? ''),
            ':depth'        => (string) ($d['depth'] ?? ''),
            ':units'        => (string) ($d['units'] ?? ''),
            ':style'        => (string) ($d['style'] ?? ''),
            ':board'        => (string) ($d['board'] ?? ''),
            ':wrap'         => (string) ($d['wrap'] ?? ''),
            ':insert_type'  => (string) ($d['insert'] ?? ''),
            ':finish'       => (string) ($d['finish'] ?? ''),
            ':quantity2'    => (string) ($d['quantity2'] ?? ''),
            ':need_by'      => (string) ($d['need_by'] ?? ''),
            ':notes'        => (string) ($d['notes'] ?? ''),
            ':files'        => (string) ($d['files'] ?? ''),
            ':gclid'        => (string) ($d['gclid'] ?? ''),
            ':utm_source'   => (string) ($d['utm_source'] ?? ''),
            ':utm_medium'   => (string) ($d['utm_medium'] ?? ''),
            ':utm_campaign' => (string) ($d['utm_campaign'] ?? ''),
            ':utm_term'     => (string) ($d['utm_term'] ?? ''),
            ':utm_content'  => (string) ($d['utm_content'] ?? ''),
            ':lead_id'      => $leadId,
        ]);
        return true;
    } catch (Throwable $e) {
        error_log('[cbe] save specs failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Fetch leads for the dashboard and the exports.
 *
 * $f accepts: q, status, from, to, gclid_only, has_specs, limit, offset.
 */
function cbe_find_leads(array $f = []): array
{
    $pdo = cbe_db();
    if (!$pdo) {
        return [];
    }
    [$where, $args] = cbe_build_where($f);
    $limit  = max(1, min(5000, (int) ($f['limit'] ?? 200)));
    $offset = max(0, (int) ($f['offset'] ?? 0));

    $sql = 'SELECT * FROM leads' . $where . ' ORDER BY datetime(created_at) DESC LIMIT ' .
           $limit . ' OFFSET ' . $offset;
    $st = $pdo->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

/** How many leads match the same filters, ignoring paging. */
function cbe_count_leads(array $f = []): int
{
    $pdo = cbe_db();
    if (!$pdo) {
        return 0;
    }
    [$where, $args] = cbe_build_where($f);
    $st = $pdo->prepare('SELECT COUNT(*) FROM leads' . $where);
    $st->execute($args);
    return (int) $st->fetchColumn();
}

/** Shared WHERE builder for the list, the count and the exports. */
function cbe_build_where(array $f): array
{
    $where = [];
    $args  = [];

    $q = trim((string) ($f['q'] ?? ''));
    if ($q !== '') {
        $where[] = '(name LIKE :q OR email LIKE :q OR phone LIKE :q OR lead_id LIKE :q
                     OR utm_campaign LIKE :q OR utm_term LIKE :q OR notes LIKE :q)';
        $args[':q'] = '%' . $q . '%';
    }
    $status = (string) ($f['status'] ?? '');
    if ($status !== '' && in_array($status, CBE_STATUSES, true)) {
        $where[] = 'status = :status';
        $args[':status'] = $status;
    }
    $from = trim((string) ($f['from'] ?? ''));
    if ($from !== '') {
        $where[] = 'date(created_at) >= date(:from)';
        $args[':from'] = $from;
    }
    $to = trim((string) ($f['to'] ?? ''));
    if ($to !== '') {
        $where[] = 'date(created_at) <= date(:to)';
        $args[':to'] = $to;
    }
    if (!empty($f['gclid_only'])) {
        $where[] = 'gclid != ""';
    }
    if (!empty($f['has_specs'])) {
        $where[] = 'spec_at != ""';
    }
    return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $args];
}

/** Headline numbers for the dashboard cards. */
function cbe_stats(): array
{
    $pdo = cbe_db();
    $zero = ['total' => 0, 'today' => 0, 'week' => 0, 'with_gclid' => 0,
             'with_specs' => 0, 'won' => 0, 'won_value' => 0.0, 'new' => 0];
    if (!$pdo) {
        return $zero;
    }
    try {
        $row = $pdo->query(
            'SELECT
               COUNT(*)                                                        AS total,
               SUM(CASE WHEN date(created_at) = date("now") THEN 1 ELSE 0 END) AS today,
               SUM(CASE WHEN date(created_at) >= date("now", "-6 days") THEN 1 ELSE 0 END) AS week,
               SUM(CASE WHEN gclid   != "" THEN 1 ELSE 0 END)                  AS with_gclid,
               SUM(CASE WHEN spec_at != "" THEN 1 ELSE 0 END)                  AS with_specs,
               SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END)                 AS won,
               SUM(CASE WHEN status = "won" THEN value ELSE 0 END)             AS won_value,
               SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END)                 AS "new"
             FROM leads'
        )->fetch();
        return array_map(static fn ($v) => $v === null ? 0 : $v, $row ?: $zero) + $zero;
    } catch (Throwable $e) {
        return $zero;
    }
}

/** Update the pipeline fields a human edits from the dashboard. */
function cbe_update_lead(string $leadId, array $fields): bool
{
    $pdo = cbe_db();
    if (!$pdo || $leadId === '') {
        return false;
    }
    $allowed = ['status', 'value', 'admin_notes'];
    $set     = [];
    $args    = [':lead_id' => $leadId, ':updated_at' => gmdate('c')];
    foreach ($allowed as $col) {
        if (!array_key_exists($col, $fields)) {
            continue;
        }
        if ($col === 'status' && !in_array($fields[$col], CBE_STATUSES, true)) {
            continue;
        }
        $set[] = $col . ' = :' . $col;
        $args[':' . $col] = $col === 'value'
            ? (float) $fields[$col]
            : mb_substr((string) $fields[$col], 0, 2000);
    }
    if (!$set) {
        return false;
    }
    try {
        $pdo->prepare('UPDATE leads SET ' . implode(', ', $set) .
                      ', updated_at = :updated_at WHERE lead_id = :lead_id')->execute($args);
        return true;
    } catch (Throwable $e) {
        error_log('[cbe] update lead failed: ' . $e->getMessage());
        return false;
    }
}

/** Delete a lead outright (spam clean-up). */
function cbe_delete_lead(string $leadId): bool
{
    $pdo = cbe_db();
    if (!$pdo || $leadId === '') {
        return false;
    }
    try {
        $pdo->prepare('DELETE FROM leads WHERE lead_id = ?')->execute([$leadId]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/** Stamp the leads just written into a Google Ads upload file. */
function cbe_mark_exported(array $leadIds): void
{
    $pdo = cbe_db();
    if (!$pdo || !$leadIds) {
        return;
    }
    try {
        $in = implode(',', array_fill(0, count($leadIds), '?'));
        $pdo->prepare('UPDATE leads SET exported_at = ? WHERE lead_id IN (' . $in . ')')
            ->execute(array_merge([gmdate('c')], $leadIds));
    } catch (Throwable $e) {
        // Non-fatal: the export itself already succeeded.
    }
}
