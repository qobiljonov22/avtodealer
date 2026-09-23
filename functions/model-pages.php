<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Model page context: set before get_template_part calls.
 * $avtodealer_page = [
 *   'slug'           => 'tank-300'|'tank-500',
 *   'model_id'       => 'car1'|'car2',
 *   'hero'           => 'tank300'|'tank500'|'home',
 *   'catalog_mode'   => 'offer'|'full',
 *   'models_layout'  => 'copy-left'|'copy-right',
 *   'configs_filter' => 'TANK 300'|'TANK 500',
 * ]
 */
function avtodealer_page_context($key = null, $default = null)
{
    global $avtodealer_page;
    if (!is_array($avtodealer_page)) {
        $avtodealer_page = [];
    }
    if ($key === null) {
        return $avtodealer_page;
    }

    return array_key_exists($key, $avtodealer_page) ? $avtodealer_page[$key] : $default;
}

function avtodealer_set_page_context(array $ctx)
{
    global $avtodealer_page;
    $avtodealer_page = $ctx;
}

function avtodealer_is_model_page($slug)
{
    $slug = sanitize_title((string) $slug);
    if ($slug === '') {
        return false;
    }
    if (avtodealer_page_context('slug') === $slug) {
        return true;
    }
    if (function_exists('is_page') && is_page($slug)) {
        return true;
    }
    if (function_exists('get_queried_object')) {
        $obj = get_queried_object();
        if ($obj instanceof WP_Post && $obj->post_name === $slug) {
            return true;
        }
    }
    // URL fallback (Polylang / odd permalinks)
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if ($uri !== '' && preg_match('#/(?:[a-z]{2}/)?' . preg_quote($slug, '#') . '/?(?:\?|$)#i', $uri)) {
        return true;
    }
    if (function_exists('is_page') && is_page() && function_exists('get_page_template_slug')) {
        $tpl = (string) get_page_template_slug();
        if ($tpl === 'page-' . $slug . '.php') {
            return true;
        }
    }

    return false;
}

function avtodealer_is_tank300_page()
{
    return avtodealer_is_model_page('tank-300');
}

function avtodealer_is_tank500_page()
{
    return avtodealer_is_model_page('tank-500');
}

function avtodealer_boot_tank300_context()
{
    if (!avtodealer_is_tank300_page()) {
        return;
    }
    if (avtodealer_page_context('slug') === 'tank-300') {
        return;
    }
    avtodealer_set_page_context([
        'slug'           => 'tank-300',
        'model_id'       => 'car1',
        'model_title'    => 'TANK 300',
        'hero'           => 'tank300',
        'catalog_mode'   => 'offer',
        'models_layout'  => 'copy-left',
        'configs_filter' => 'TANK 300',
        'cta_split'      => true,
    ]);
}
add_action('wp', 'avtodealer_boot_tank300_context', 5);

function avtodealer_boot_tank500_context()
{
    if (!avtodealer_is_tank500_page()) {
        return;
    }
    if (avtodealer_page_context('slug') === 'tank-500') {
        return;
    }
    avtodealer_set_page_context([
        'slug'           => 'tank-500',
        'model_id'       => 'car2',
        'model_title'    => 'TANK 500',
        'hero'           => 'tank500',
        'catalog_mode'   => 'offer',
        'models_layout'  => 'copy-right',
        'configs_filter' => 'TANK 500',
        'cta_split'      => true,
    ]);
}
add_action('wp', 'avtodealer_boot_tank500_context', 5);

function avtodealer_force_model_page_template($template)
{
    $map = [
        'tank-300' => 'page-tank-300.php',
        'tank-500' => 'page-tank-500.php',
    ];
    foreach ($map as $slug => $file) {
        $match = false;
        if (function_exists('is_page') && is_page($slug)) {
            $match = true;
        } elseif (avtodealer_is_model_page($slug)) {
            $match = true;
        }
        if (!$match) {
            continue;
        }
        $custom = get_template_directory() . '/' . $file;
        if (is_readable($custom)) {
            return $custom;
        }
    }

    return $template;
}
add_filter('template_include', 'avtodealer_force_model_page_template', 99);

function avtodealer_model_page_body_class($classes)
{
    if (function_exists('avtodealer_is_tank300_page') && avtodealer_is_tank300_page()) {
        $classes[] = 'page-tank-300';
        $classes[] = 'is-model-page';
    } elseif (function_exists('avtodealer_is_tank500_page') && avtodealer_is_tank500_page()) {
        $classes[] = 'page-tank-500';
        $classes[] = 'is-model-page';
    } elseif (function_exists('is_front_page') && is_front_page()) {
        $classes[] = 'page-home';
    }

    return $classes;
}
add_filter('body_class', 'avtodealer_model_page_body_class');

function avtodealer_hero_tank300_defaults($lang = null)
{
    $theme = get_template_directory_uri();

    $defaults = [
        'eyebrow'              => 'Покоряйте любые условия',
        'title'                => 'TANK 300',
        'subtitle'             => 'Осталось всего 8 автомобилей!',
        'cta_label'            => 'Получить предложение',
        'cta_url'              => '#credit',
        'image_id'             => 0,
        'image_mobile_id'      => 0,
        'image_default'        => $theme . '/images/' . rawurlencode('image 285 (3).png'),
        'image_mobile_default' => $theme . '/images/' . rawurlencode('image 285 (3).png'),
    ];

    $extra = avtodealer_defaults_by_lang('hero_tank300', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_hero_tank300($input)
{
    $defaults = avtodealer_hero_tank300_defaults();
    $out = [];

    foreach ($defaults as $key => $default) {
        if (!array_key_exists($key, $input) || str_ends_with($key, '_default')) {
            continue;
        }
        $val = $input[$key];
        if (in_array($key, ['image_id', 'image_mobile_id'], true)) {
            $out[$key] = absint($val);
            continue;
        }
        if ($key === 'cta_url') {
            $out[$key] = esc_url_raw((string) $val);
            continue;
        }
        $out[$key] = sanitize_text_field((string) $val);
    }

    return $out;
}

function avtodealer_get_hero_tank300($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $defaults = avtodealer_hero_tank300_defaults($lang);
    $stored = avtodealer_get_option_lang('avtodealer_hero_tank300', $lang);
    $data = array_merge($defaults, is_array($stored) ? $stored : []);

    $image_id = absint($data['image_id'] ?? 0);
    $mobile_id = absint($data['image_mobile_id'] ?? 0);
    $image_url = $image_id ? (string) wp_get_attachment_image_url($image_id, 'full') : '';
    $mobile_url = $mobile_id ? (string) wp_get_attachment_image_url($mobile_id, 'full') : '';

    $data['image_id'] = $image_id;
    $data['image_mobile_id'] = $mobile_id;
    $data['image_url'] = $image_url !== '' ? $image_url : $defaults['image_default'];
    $data['image_mobile_url'] = $mobile_url !== '' ? $mobile_url : $defaults['image_mobile_default'];
    $data['cta_url'] = avtodealer_href($data['cta_url'] ?? '', '#credit');
    $data['lang'] = $lang;
    unset($data['image_default'], $data['image_mobile_default']);

    return $data;
}

function avtodealer_save_hero_tank300($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $current = $replace ? avtodealer_hero_tank300_defaults($lang) : avtodealer_get_hero_tank300($lang);
    unset($current['image_url'], $current['image_mobile_url'], $current['lang']);
    $clean = avtodealer_sanitize_hero_tank300($input);
    $saved = array_merge($current, $clean);
    unset($saved['image_url'], $saved['image_mobile_url'], $saved['lang']);
    avtodealer_update_option_lang('avtodealer_hero_tank300', $saved, $lang);

    return avtodealer_get_hero_tank300($lang);
}

function avtodealer_delete_hero_tank300($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_delete_option_lang('avtodealer_hero_tank300', $lang);

    return avtodealer_get_hero_tank300($lang);
}

function avtodealer_hero_tank500_defaults($lang = null)
{
    $theme = get_template_directory_uri();

    $defaults = [
        'eyebrow'              => 'Улучшим любые условия',
        'title'                => 'TANK 500',
        'subtitle'             => 'Осталось всего 3 автомобиля!',
        'cta_label'            => 'Получить предложение',
        'cta_url'              => '#credit',
        'image_id'             => 0,
        'image_mobile_id'      => 0,
        'image_default'        => $theme . '/images/' . rawurlencode('image 286 (3).png'),
        'image_mobile_default' => $theme . '/images/' . rawurlencode('image 286 (3).png'),
    ];

    $extra = avtodealer_defaults_by_lang('hero_tank500', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_hero_tank500($input)
{
    $defaults = avtodealer_hero_tank500_defaults();
    $out = [];

    foreach ($defaults as $key => $default) {
        if (!array_key_exists($key, $input) || str_ends_with($key, '_default')) {
            continue;
        }
        $val = $input[$key];
        if (in_array($key, ['image_id', 'image_mobile_id'], true)) {
            $out[$key] = absint($val);
            continue;
        }
        if ($key === 'cta_url') {
            $out[$key] = esc_url_raw((string) $val);
            continue;
        }
        $out[$key] = sanitize_text_field((string) $val);
    }

    return $out;
}

function avtodealer_get_hero_tank500($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $defaults = avtodealer_hero_tank500_defaults($lang);
    $stored = avtodealer_get_option_lang('avtodealer_hero_tank500', $lang);
    $data = array_merge($defaults, is_array($stored) ? $stored : []);

    $image_id = absint($data['image_id'] ?? 0);
    $mobile_id = absint($data['image_mobile_id'] ?? 0);
    $image_url = $image_id ? (string) wp_get_attachment_image_url($image_id, 'full') : '';
    $mobile_url = $mobile_id ? (string) wp_get_attachment_image_url($mobile_id, 'full') : '';

    $data['image_id'] = $image_id;
    $data['image_mobile_id'] = $mobile_id;
    $data['image_url'] = $image_url !== '' ? $image_url : $defaults['image_default'];
    $data['image_mobile_url'] = $mobile_url !== '' ? $mobile_url : $defaults['image_mobile_default'];
    $data['cta_url'] = avtodealer_href($data['cta_url'] ?? '', '#credit');
    $data['lang'] = $lang;
    unset($data['image_default'], $data['image_mobile_default']);

    return $data;
}

function avtodealer_save_hero_tank500($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $current = $replace ? avtodealer_hero_tank500_defaults($lang) : avtodealer_get_hero_tank500($lang);
    unset($current['image_url'], $current['image_mobile_url'], $current['lang']);
    $clean = avtodealer_sanitize_hero_tank500($input);
    $saved = array_merge($current, $clean);
    unset($saved['image_url'], $saved['image_mobile_url'], $saved['lang']);
    avtodealer_update_option_lang('avtodealer_hero_tank500', $saved, $lang);

    return avtodealer_get_hero_tank500($lang);
}

function avtodealer_delete_hero_tank500($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_delete_option_lang('avtodealer_hero_tank500', $lang);

    return avtodealer_get_hero_tank500($lang);
}

function avtodealer_ensure_model_pages()
{
    $pages = [
        'tank-300' => [
            'title'    => 'TANK 300',
            'template' => 'page-tank-300.php',
        ],
        'tank-500' => [
            'title'    => 'TANK 500',
            'template' => 'page-tank-500.php',
        ],
    ];

    foreach ($pages as $slug => $meta) {
        $existing = get_page_by_path($slug);
        if ($existing instanceof WP_Post) {
            update_post_meta($existing->ID, '_wp_page_template', $meta['template']);
            if ($existing->post_status !== 'publish') {
                wp_update_post([
                    'ID'          => $existing->ID,
                    'post_status' => 'publish',
                ]);
            }
            continue;
        }

        $id = wp_insert_post([
            'post_title'   => $meta['title'],
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ], true);

        if (!is_wp_error($id) && $id) {
            update_post_meta((int) $id, '_wp_page_template', $meta['template']);
        }
    }
}
add_action('after_switch_theme', 'avtodealer_ensure_model_pages');
add_action('after_switch_theme', static function () {
    flush_rewrite_rules();
});
add_action('init', 'avtodealer_ensure_model_pages', 20);

function avtodealer_model_page_url($slug = 'tank-300')
{
    $slug = sanitize_title($slug);
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post && $page->post_status === 'publish') {
        $url = get_permalink($page);
        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    // Always absolute path — never #anchor
    return trailingslashit(home_url('/' . $slug));
}
