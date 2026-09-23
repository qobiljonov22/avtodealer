<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Production PHP hostingda siteurl/home localhost qolib login
 * buzilmasin: joriy host bilan URLlarni moslashtirish.
 * Netlify/Vercel (statik) da WordPress ishlamaydi — bu faqat PHP host uchun.
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
    if (is_ssl()) {
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

function avtodealer_dynamic_site_url($url)
{
    if (is_admin() && function_exists('wp_doing_ajax') && wp_doing_ajax()) {
        // keep default for most ajax; still rewrite host below
    }

    $host = avtodealer_request_host();
    if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local')) {
        return $url;
    }

    $parsed = wp_parse_url((string) $url);
    $current = wp_parse_url(avtodealer_request_scheme() . '://' . $host);
    if (empty($parsed['host']) || empty($current['host'])) {
        return $url;
    }

    if (strtolower((string) $parsed['host']) === strtolower((string) $current['host'])) {
        // Same host — still force https if request is https
        if (avtodealer_request_scheme() === 'https' && (($parsed['scheme'] ?? '') === 'http')) {
            return set_url_scheme($url, 'https');
        }

        return $url;
    }

    $path = $parsed['path'] ?? '/';
    $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
    $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

    return avtodealer_request_scheme() . '://' . $host . $path . $query . $fragment;
}

add_filter('option_home', 'avtodealer_dynamic_site_url', 20);
add_filter('option_siteurl', 'avtodealer_dynamic_site_url', 20);
add_filter('login_url', 'avtodealer_dynamic_site_url', 20);
add_filter('admin_url', 'avtodealer_dynamic_site_url', 20);
add_filter('network_site_url', 'avtodealer_dynamic_site_url', 20);
