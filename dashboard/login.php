<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

cbe_session_start();
$cfg   = cbe_config();
$next  = (string) ($_GET['next'] ?? 'index.php');
if (!preg_match('~^/?[\w./?=&-]*$~', $next) || str_contains($next, '//')) {
    $next = 'index.php';                       // never redirect off-site
}
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!cbe_csrf_ok($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } elseif (cbe_login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''))) {
        header('Location: ' . $next, true, 303);
        exit;
    } else {
        $error = 'Wrong username or password.';
    }
}
if (cbe_is_logged_in() && $error === '') {
    header('Location: index.php', true, 302);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Sign in · Leads Dashboard</title>
<link rel="stylesheet" href="assets/dash.css">
</head>
<body class="login-body">

<main class="login">
  <div class="login__card">
    <div class="login__brand">
      <span class="login__mark">CB</span>
      <div>
        <strong><?= e($cfg['brand']) ?></strong>
        <span>Leads Dashboard</span>
      </div>
    </div>

    <?php if ($error !== ''): ?>
      <p class="alert alert--bad" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" autocomplete="on">
      <input type="hidden" name="csrf" value="<?= e(cbe_csrf_token()) ?>">
      <label for="u">Username</label>
      <input id="u" name="username" type="text" required autofocus autocapitalize="none"
             autocomplete="username" placeholder="admin">
      <label for="p">Password</label>
      <input id="p" name="password" type="password" required autocomplete="current-password"
             placeholder="••••••••">
      <button type="submit" class="btn btn--primary btn--block">Sign in</button>
    </form>

    <?php if (!empty($cfg['demo_mode'])): ?>
      <p class="login__demo">
        <strong>Demo mode is on</strong> — any username and password will let you in.
        Suggested: <code>admin</code> / <code>boxes123</code>.
        Set <code>'demo_mode' =&gt; false</code> in <code>dashboard/config.php</code>
        before this goes live.
      </p>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
