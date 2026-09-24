<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_admin_can_manage()
{
    return current_user_can('manage_options') || current_user_can('edit_theme_options');
}

function avtodealer_admin_menu()
{
    if (!avtodealer_admin_can_manage()) {
        return;
    }

    add_menu_page(
        'Avtodealer',
        'Avtodealer',
        'manage_options',
        'avtodealer-cms',
        'avtodealer_admin_render',
        'dashicons-car',
        3
    );
}
add_action('admin_menu', 'avtodealer_admin_menu');

function avtodealer_admin_bar_link($wp_admin_bar)
{
    if (!is_admin() || !avtodealer_admin_can_manage() || !is_object($wp_admin_bar)) {
        return;
    }

    $wp_admin_bar->add_node([
        'id'    => 'avtodealer-studio',
        'title' => 'Avtodealer',
        'href'  => admin_url('admin.php?page=avtodealer-cms'),
        'meta'  => ['title' => 'Avtodealer kontent'],
    ]);
}
add_action('admin_bar_menu', 'avtodealer_admin_bar_link', 80);

function avtodealer_admin_dashboard_notice()
{
    if (!avtodealer_admin_can_manage()) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'dashboard') {
        return;
    }
    $url = esc_url(admin_url('admin.php?page=avtodealer-cms'));
    echo '<div class="notice notice-info"><p><strong>Avtodealer</strong> — ';
    echo '<a href="' . $url . '">kontentni tahrirlash</a>.</p></div>';
}
add_action('admin_notices', 'avtodealer_admin_dashboard_notice');

function avtodealer_admin_assets($hook)
{
    if ($hook !== 'toplevel_page_avtodealer-cms') {
        return;
    }

    $uri = get_template_directory_uri();
    $path = get_template_directory();
    $js = $path . '/js/admin-panel.js';
    $ver = is_readable($js) ? (string) filemtime($js) : '1.0.0';

    wp_enqueue_media();
    wp_enqueue_script(
        'tailwindcss',
        $uri . '/js/tailwindcss-browser.js',
        [],
        '4.0.0',
        false
    );
    wp_enqueue_script(
        'avtodealer-admin',
        $uri . '/js/admin-panel.js',
        ['tailwindcss'],
        $ver,
        true
    );
    wp_localize_script('avtodealer-admin', 'avtodealerAdmin', [
        'restUrl'   => rest_url('avtodealer/v1/'),
        'siteUrl'   => home_url('/'),
        'nonce'     => wp_create_nonce('wp_rest'),
        'namespace' => '/avtodealer/v1/',
        'useQuery'  => true,
        'lang'      => function_exists('avtodealer_current_lang') ? avtodealer_current_lang() : 'ru',
        'languages' => function_exists('avtodealer_languages') ? avtodealer_languages() : ['ru', 'uz', 'en'],
        'resources' => array_keys(avtodealer_rest_resources()),
        'adminUrl'  => admin_url('admin.php?page=avtodealer-cms'),
        'userName'  => wp_get_current_user()->display_name ?: 'admin',
    ]);
}
add_action('admin_enqueue_scripts', 'avtodealer_admin_assets');

function avtodealer_admin_tailwind_theme()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'toplevel_page_avtodealer-cms') {
        return;
    }
    ?>
    <style type="text/tailwindcss">
      @theme {
        --color-wp-blue: #2271b1;
        --color-wp-blue-dark: #135e96;
        --color-wp-border: #c3c4c7;
        --color-wp-muted: #646970;
        --color-wp-bg: #f0f0f1;
        --color-wp-trash: #b32d2e;
      }
    </style>
    <?php
}
add_action('admin_head', 'avtodealer_admin_tailwind_theme', 7);

function avtodealer_admin_render()
{
    if (!avtodealer_admin_can_manage()) {
        wp_die('Ruxsat yo‘q.');
    }
    ?>
    <div id="avto-cms-root" class="wrap !max-w-[1200px] font-sans text-[13px] text-[#1d2327]">
        <div id="avto-cms-app">
            <p class="text-[#646970]">Yuklanmoqda…</p>
        </div>
    </div>
    <?php
}
