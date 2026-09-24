<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_hero_defaults($lang = null)
{
    $theme = get_template_directory_uri();

    $defaults = [
        'eyebrow'         => 'Улучшим любые условия',
        'title'           => 'TANK 500',
        'subtitle'        => 'Осталось всего 5 автомобилей!',
        'cta_label'       => 'Получить предложение',
        'cta_url'         => '#credit',
        'image_id'        => 0,
        'image_mobile_id' => 0,
        // Home (asosiy) hero — Figma 1-page (garage / image 285). Tank pages use separate getters.
        'image_default'   => $theme . '/images/' . rawurlencode('image 285.png'),
        'image_mobile_default' => $theme . '/images/' . rawurlencode('image 285 mobile.png'),
    ];

    $extra = avtodealer_defaults_by_lang('hero', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_hero($input)
{
    $defaults = avtodealer_hero_defaults();
    $out = [];

    foreach ($defaults as $key => $default) {
        if (!array_key_exists($key, $input)) {
            continue;
        }

        if (str_ends_with($key, '_default')) {
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

function avtodealer_get_hero($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $stored = avtodealer_get_option_lang('avtodealer_hero', $lang);
    $defaults = avtodealer_hero_defaults($lang);
    $data = array_merge($defaults, $stored);

    $image_id = absint($data['image_id']);
    $mobile_id = absint($data['image_mobile_id']);

    $image_url = $image_id ? (string) wp_get_attachment_image_url($image_id, 'full') : '';
    $mobile_url = $mobile_id ? (string) wp_get_attachment_image_url($mobile_id, 'full') : '';

    if ($image_url === '') {
        $image_url = $defaults['image_default'];
    }
    if ($mobile_url === '') {
        $mobile_url = $defaults['image_mobile_default'];
    }

    $data['image_id'] = $image_id;
    $data['image_mobile_id'] = $mobile_id;
    $data['image_url'] = $image_url;
    $data['image_mobile_url'] = $mobile_url;
    $data['cta_url'] = avtodealer_href($data['cta_url'] ?? '', '#credit');
    $data['lang'] = $lang;
    unset($data['image_default'], $data['image_mobile_default']);

    return $data;
}

function avtodealer_save_hero($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $current = $replace ? avtodealer_hero_defaults($lang) : avtodealer_get_hero($lang);
    unset($current['image_url'], $current['image_mobile_url'], $current['image_default'], $current['image_mobile_default'], $current['lang']);
    $clean = avtodealer_sanitize_hero($input);
    $saved = array_merge($current, $clean);
    unset($saved['image_url'], $saved['image_mobile_url'], $saved['lang']);
    avtodealer_update_option_lang('avtodealer_hero', $saved, $lang);

    return avtodealer_get_hero($lang);
}

function avtodealer_delete_hero($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_delete_option_lang('avtodealer_hero', $lang);

    return avtodealer_get_hero($lang);
}
