<?php
class SecurityHeaders
{
    public static function apply()
    {
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://www.youtube.com https://player.vimeo.com https://www.googletagmanager.com https://www.google-analytics.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://www.googletagmanager.com https://www.google-analytics.com; connect-src 'self' https://www.googletagmanager.com https://www.google-analytics.com https://*.google-analytics.com; font-src 'self'; object-src 'none'; media-src 'self'; frame-src https://www.youtube.com https://player.vimeo.com; child-src 'none'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

        // Only advertise HSTS over HTTPS; sending it over plain HTTP can lock out
        // adopters who haven't set up TLS yet.
        if (self::isHttpsRequest()) {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }
    }

    private static function isHttpsRequest()
    {
        $forwardedProto = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));

        return (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || ($forwardedProto !== '' && trim(explode(',', $forwardedProto)[0]) === 'https')
            || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
    }
}
