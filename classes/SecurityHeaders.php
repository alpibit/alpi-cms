<?php
class SecurityHeaders
{
    public static function apply()
    {
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://www.youtube.com https://player.vimeo.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; font-src 'self'; object-src 'none'; media-src 'self'; frame-src https://www.youtube.com https://player.vimeo.com; child-src 'none'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    }
}
