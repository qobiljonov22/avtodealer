<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hostingda siteurl/home/asset URL lar local (avtodealer.local) qolib
 * qolmasin — joriy domen bilan bir xil ishlasin (wp-admin, REST, CSS/JS).
 */
function avtodealer_request_host()
{
    $host = '';
    if (!empty($_SERVER['HTTP_HOST'])) {
        $host = strtolower((string) wp_unslash($_SERVER['HTTP_HOST']));
    } elseif (!empty($_SERVER['SERVER_NAME'])) {
        $host = strtolower((string) wp_unslash($_SERVER['SERVER_NAME']));
    }

    $host = preg_replace('/:\d+$/', '', $host);
    $host = preg_replace('/[^a-z0-9\.\-]/', '', (string) $host);

    return $host;
}

function avtodealer_request_scheme()
{
    if (function_exists('is_ssl') && is_ssl()) {
        return 'https';
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $proto = strtolower((string) wp_unslash($_SERVER['HTTP_X_FORWARDED_PROTO']));
        if (str_starts_with($proto, 'https')) {
            return 'https';
        }
    }
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return 'https';
    }

    return 'http';
}

function avtodealer_is_local_hostname($host)
{
    $host = strtolower(trim((string) $host));
    if ($host === '' || $host === 'localhost' || $host === '127.0.0.1') {
        return true;
    }
    if (str_starts_with($host, '127.')) {
        return true;
    }
    if (str_ends_with($host, '.local')) {
        return true;
    }

    return false;
}

function avtodealer_public_base()
{
    $host = avtodealer_request_host();
    if ($host === '' || avtodealer_is_local_hostname($host)) {
        return '';
    }

    return avtodealer_request_scheme() . '://' . $host;
}

function avtodealer_dynamic_site_url($url)
{
    $base = avtodealer_public_base();
    if ($base === '') {
        return $url;
    }

    $url = (string) $url;
    if ($url === '') {
        return $url;
    }

    $parsed = wp_parse_url($url);
    if (empty($parsed['host'])) {
        return $url;
    }

    $url_host = strtolower((string) $parsed['host']);
    $req_host = strtolower((string) wp_parse_url($base, PHP_URL_HOST));

    $needs_host = ($url_host !== $req_host) || avtodealer_is_local_hostname($url_host);
    $needs_https = (avtodealer_request_scheme() === 'https' && ($parsed['scheme'] ?? '') === 'http');

    if (!$needs_host && !$needs_https) {
        return $url;
    }

    $path = $parsed['path'] ?? '/';
    $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
    $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

    return $base . $path . $query . $fragment;
}

function avtodealer_rewrite_src($src)
{
    if (!is_string($src) || $src === '') {
        return $src;
    }

    return avtodealer_dynamic_site_url($src);
}

function avtodealer_rewrite_upload_dir($dirs)
{
    if (!is_array($dirs)) {
        return $dirs;
    }
    foreach (['url', 'baseurl'] as $key) {
        if (!empty($dirs[$key])) {
            $dirs[$key] = avtodealer_dynamic_site_url($dirs[$key]);
        }
    }

    return $dirs;
}

add_filter('option_home', 'avtodealer_dynamic_site_url', 20);
add_filter('option_siteurl', 'avtodealer_dynamic_site_url', 20);
add_filter('login_url', 'avtodealer_dynamic_site_url', 20);
add_filter('logout_url', 'avtodealer_dynamic_site_url', 20);
add_filter('admin_url', 'avtodealer_dynamic_site_url', 20);
add_filter('network_site_url', 'avtodealer_dynamic_site_url', 20);
add_filter('content_url', 'avtodealer_dynamic_site_url', 20);
add_filter('rest_url', 'avtodealer_dynamic_site_url', 20);
add_filter('theme_root_uri', 'avtodealer_dynamic_site_url', 20);
add_filter('plugins_url', 'avtodealer_dynamic_site_url', 20);
add_filter('script_loader_src', 'avtodealer_rewrite_src', 20);
add_filter('style_loader_src', 'avtodealer_rewrite_src', 20);
add_filter('wp_get_attachment_url', 'avtodealer_dynamic_site_url', 20);
add_filter('upload_dir', 'avtodealer_rewrite_upload_dir', 20);

/**
 * Hostingda bir marta permalink qoidalarni yangilash
 * ( /tank-300/ /tank-500/ va wp-admin yo‘llari).
 */
function avtodealer_maybe_flush_rewrites()
{
    if (get_option('avtodealer_rewrites_flushed') === '2') {
        return;
    }
    flush_rewrite_rules(false);
    update_option('avtodealer_rewrites_flushed', '2', false);
}
add_action('admin_init', 'avtodealer_maybe_flush_rewrites', 1);
add_action('after_switch_theme', static function () {
    delete_option('avtodealer_rewrites_flushed');
});
