<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_assets()
{
    $uri = get_template_directory_uri();

    wp_enqueue_script(
        'tailwindcss',
        $uri . '/js/tailwindcss-browser.js',
        [],
        '4.0.0',
        false
    );

    wp_enqueue_script(
        'avtodealer-main',
        $uri . '/js/main.js',
        ['tailwindcss'],
        (string) filemtime(get_template_directory() . '/js/main.js'),
        true
    );

    wp_localize_script('avtodealer-main', 'avtodealerFront', [
        'restUrl'  => rest_url('avtodealer/v1/'),
        'siteUrl'  => home_url('/'),
        'useQuery' => true,
        'nonce'    => wp_create_nonce('wp_rest'),
    ]);
}
add_action('wp_enqueue_scripts', 'avtodealer_assets');

function avtodealer_tailwind_theme()
{
    $file = get_template_directory() . '/style/main.css';
    if (!is_readable($file)) {
        return;
    }

    // Tailwind browser o'zi @import "tailwindcss" qo'shadi — DOM da yozmaslik 404 beradi.
    $css = file_get_contents($file);
    $css = preg_replace('/^\s*@import\s+["\']tailwindcss["\']\s*;\s*/m', '', $css);

    echo '<style type="text/tailwindcss">' . "\n";
    echo $css;
    echo "\n</style>\n";
}
add_action('wp_head', 'avtodealer_tailwind_theme', 7);
