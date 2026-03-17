<?php
require_once 'includes/config.php';

// Fully clear session data and invalidate the session cookie.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_unset();
session_destroy();
session_regenerate_id(true);

header("Location: index.php?logged_out=1");
exit;
?>