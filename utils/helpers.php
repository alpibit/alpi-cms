<?php

if (!function_exists('alpiEnsureSessionStarted')) {
	function alpiEnsureSessionStarted()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}
}

if (!function_exists('alpiIsAjaxRequest')) {
	function alpiIsAjaxRequest()
	{
		$requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

		return is_string($requestedWith)
			&& strcasecmp($requestedWith, 'XMLHttpRequest') === 0;
	}
}

if (!function_exists('alpiRejectAjaxOrRedirect')) {
	function alpiRejectAjaxOrRedirect($redirectUrl, $message = 'Unauthorized', $statusCode = 401)
	{
		if (alpiIsAjaxRequest()) {
			http_response_code((int) $statusCode);
			header('Content-Type: text/plain; charset=UTF-8');
			header('Cache-Control: no-store');
			echo $message;
			exit;
		}

		header('Location: ' . $redirectUrl);
		exit;
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

if (!function_exists('alpiRenderPublicErrorPage')) {
	function alpiRenderPublicErrorPage(array $options = [])
	{
		$statusCode = isset($options['statusCode']) ? (int) $options['statusCode'] : 500;
		$title = isset($options['title']) && is_string($options['title']) && trim($options['title']) !== ''
			? trim($options['title'])
			: 'We could not load this page right now.';
		$message = isset($options['message']) && is_string($options['message']) && trim($options['message']) !== ''
			? trim($options['message'])
			: 'Please try again in a moment.';
		$eyebrow = isset($options['eyebrow']) && is_string($options['eyebrow']) && trim($options['eyebrow']) !== ''
			? trim($options['eyebrow'])
			: 'Temporary issue';
		$errorCode = isset($options['errorCode']) && is_string($options['errorCode']) && trim($options['errorCode']) !== ''
			? trim($options['errorCode'])
			: null;
		$pageTitle = isset($options['pageTitle']) && is_string($options['pageTitle']) && trim($options['pageTitle']) !== ''
			? trim($options['pageTitle'])
			: $title;
		$actions = [];

		if (isset($options['actions']) && is_array($options['actions'])) {
			foreach ($options['actions'] as $action) {
				if (!is_array($action)) {
					continue;
				}

				$label = isset($action['label']) && is_string($action['label']) ? trim($action['label']) : '';
				$href = isset($action['href']) && is_string($action['href']) ? trim($action['href']) : '';
				$variant = isset($action['variant']) && $action['variant'] === 'secondary' ? 'secondary' : 'primary';
				$isButton = !empty($action['isButton']);

				if ($label === '') {
					continue;
				}

				if (!$isButton && $href === '') {
					continue;
				}

				$actions[] = [
					'label' => $label,
					'href' => $href,
					'variant' => $variant,
					'isButton' => $isButton,
				];
			}
		}

		if ($actions === []) {
			$actions[] = [
				'label' => 'Go home',
				'href' => defined('BASE_URL') ? BASE_URL : '/',
				'variant' => 'primary',
				'isButton' => false,
			];

			$actions[] = [
				'label' => 'Try again',
				'href' => '',
				'variant' => 'secondary',
				'isButton' => true,
			];
		}

		if (!headers_sent()) {
			http_response_code($statusCode);
			header('Content-Type: text/html; charset=UTF-8');
			header('Cache-Control: no-store, no-cache, must-revalidate');
		}

		require __DIR__ . '/../templates/error-shell.php';
	}
}

if (!function_exists('alpiExitWithPublicErrorPage')) {
	function alpiExitWithPublicErrorPage(array $options = [])
	{
		alpiRenderPublicErrorPage($options);
		exit;
	}
}
