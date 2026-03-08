<?php

if (!function_exists('alpiGetCsrfToken')) {
	function alpiGetCsrfToken()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}

		return $_SESSION['csrf_token'];
	}
}

if (!function_exists('alpiRegenerateCsrfToken')) {
	function alpiRegenerateCsrfToken()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

		return $_SESSION['csrf_token'];
	}
}

if (!function_exists('alpiVerifyCsrfToken')) {
	function alpiVerifyCsrfToken($token)
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$sessionToken = $_SESSION['csrf_token'] ?? null;

		return is_string($token)
			&& $token !== ''
			&& is_string($sessionToken)
			&& $sessionToken !== ''
			&& hash_equals($sessionToken, $token);
	}
}
