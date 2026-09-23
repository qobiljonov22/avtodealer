<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    register_nav_menus([
        'primary' => 'Primary',
        'footer'  => 'Footer',
    ]);
}
add_action('after_setup_theme', 'avtodealer_setup');

function avtodealer_show_admin_bar($show)
{
    return is_admin() ? $show : false;
}
add_filter('show_admin_bar', 'avtodealer_show_admin_bar');
