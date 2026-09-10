<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

cbe_session_start();
cbe_logout();
header('Location: login.php', true, 303);
exit;
