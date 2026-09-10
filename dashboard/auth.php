<?php
/**
 * Session handling, login checks and the CSRF token.
 * Every dashboard page starts by requiring this file.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/leads-store.php';

/** @return array<string,mixed> */
function cbe_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/config.php';
    }
    return $cfg;
}

function cbe_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('cbe_dash');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $https,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Idle timeout.
    $limit = max(5, (int) cbe_config()['session_minutes']) * 60;
    if (isset($_SESSION['seen']) && (time() - (int) $_SESSION['seen']) > $limit) {
        cbe_logout();
    }
    $_SESSION['seen'] = time();
}

function cbe_is_logged_in(): bool
{
    cbe_session_start();
    return !empty($_SESSION['user']);
}

function cbe_user(): string
{
    return (string) ($_SESSION['user'] ?? '');
}

/**
 * Verify credentials. In demo mode any non-empty pair is accepted; otherwise
 * the username must exist in config and the password must match its hash.
 */
function cbe_login(string $user, string $pass): bool
{
    cbe_session_start();
    $cfg  = cbe_config();
    $user = trim($user);

    $ok = false;
    if (!empty($cfg['demo_mode'])) {
        $ok = $user !== '' && $pass !== '';
    } elseif (isset($cfg['users'][$user])) {
        $ok = password_verify($pass, (string) $cfg['users'][$user]);
    }
    if (!$ok) {
        return false;
    }

    session_regenerate_id(true);            // no session fixation
    $_SESSION['user'] = $user;
    $_SESSION['seen'] = time();
    return true;
}

function cbe_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Send anyone who is not logged in to the login screen. */
function cbe_require_login(): void
{
    if (cbe_is_logged_in()) {
        return;
    }
    $back = $_SERVER['REQUEST_URI'] ?? 'index.php';
    header('Location: login.php?next=' . rawurlencode($back), true, 302);
    exit;
}

function cbe_csrf_token(): string
{
    cbe_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['csrf'];
}

function cbe_csrf_ok(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf'])
        && hash_equals((string) $_SESSION['csrf'], $token);
}

/** Shorthand for escaping into HTML. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
