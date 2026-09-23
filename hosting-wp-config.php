<?php
/**
 * Hosting wp-config.php ga qo‘shing — "That's all, stop editing!" QATORIDAN OLDIN.
 * Bu WP_HOME / WP_SITEURL ni joriy domen bilan bog‘laydi (local URL qolib ketmasin).
 *
 * Misol:
 *   require_once __DIR__ . '/wp-content/themes/avtodealer/hosting-wp-config.php';
 */

if (!defined('ABSPATH') && !defined('WPINC')) {
    // wp-config ichida chaqiriladi — ABSPATH hali yo‘q bo‘lishi mumkin
}

if (!defined('WP_HOME') || !defined('WP_SITEURL')) {
    $avto_host = '';
    if (!empty($_SERVER['HTTP_HOST'])) {
        $avto_host = strtolower((string) $_SERVER['HTTP_HOST']);
    } elseif (!empty($_SERVER['SERVER_NAME'])) {
        $avto_host = strtolower((string) $_SERVER['SERVER_NAME']);
    }
    $avto_host = preg_replace('/:\d+$/', '', $avto_host);
    $avto_host = preg_replace('/[^a-z0-9\.\-]/', '', (string) $avto_host);

    $is_local = ($avto_host === '' || $avto_host === 'localhost' || str_ends_with($avto_host, '.local') || str_starts_with($avto_host, '127.'));

    if (!$is_local) {
        $https = false;
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            $https = true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && str_starts_with(strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']), 'https')) {
            $https = true;
        }
        $scheme = $https ? 'https' : 'http';
        if (!defined('WP_HOME')) {
            define('WP_HOME', $scheme . '://' . $avto_host);
        }
        if (!defined('WP_SITEURL')) {
            define('WP_SITEURL', $scheme . '://' . $avto_host);
        }
        if ($https && !defined('FORCE_SSL_ADMIN')) {
            define('FORCE_SSL_ADMIN', true);
        }
    }
}
