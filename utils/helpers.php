<?php

if (!function_exists('alpiEnsureSessionStarted')) {
	function alpiEnsureSessionStarted()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}
}

if (!function_exists('alpiGetCsrfToken')) {
	function alpiGetCsrfToken()
	{
		alpiEnsureSessionStarted();

		if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}

		return $_SESSION['csrf_token'];
	}
}

if (!function_exists('alpiRegenerateCsrfToken')) {
	function alpiRegenerateCsrfToken()
	{
		alpiEnsureSessionStarted();

		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

		return $_SESSION['csrf_token'];
	}
}

if (!function_exists('alpiVerifyCsrfToken')) {
	function alpiVerifyCsrfToken($token)
	{
		alpiEnsureSessionStarted();

		$sessionToken = $_SESSION['csrf_token'] ?? null;

		return is_string($token)
			&& $token !== ''
			&& is_string($sessionToken)
			&& $sessionToken !== ''
			&& hash_equals($sessionToken, $token);
	}
}

if (!function_exists('alpiSetFlashValue')) {
	function alpiSetFlashValue($key, $value)
	{
		alpiEnsureSessionStarted();

		if (!isset($_SESSION['_alpi_flash']) || !is_array($_SESSION['_alpi_flash'])) {
			$_SESSION['_alpi_flash'] = [];
		}

		$_SESSION['_alpi_flash'][$key] = $value;
	}
}

if (!function_exists('alpiConsumeFlashValue')) {
	function alpiConsumeFlashValue($key, $default = null)
	{
		alpiEnsureSessionStarted();

		if (!isset($_SESSION['_alpi_flash']) || !is_array($_SESSION['_alpi_flash']) || !array_key_exists($key, $_SESSION['_alpi_flash'])) {
			return $default;
		}

		$value = $_SESSION['_alpi_flash'][$key];
		unset($_SESSION['_alpi_flash'][$key]);

		if (empty($_SESSION['_alpi_flash'])) {
			unset($_SESSION['_alpi_flash']);
		}

		return $value;
	}
}
