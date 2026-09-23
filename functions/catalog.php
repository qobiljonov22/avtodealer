<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_catalog_defaults($lang = null)
{
    $theme = get_template_directory_uri();

    $defaults = [
        'offer_title'     => 'Срок действия спецпредложения:',
        'offer_cta_label' => 'Получить предложение',
        'offer_cta_url'   => '#credit',
        'countdown_end'   => '',
        'feature1_title'  => 'Официальный дилер',
        'feature1_text'   => 'Гарантируем высокое качество обслуживания.',
        'feature2_title'  => 'Покупка авто за 1 день',
        'feature2_text'   => 'Удобный процесс покупки, включая оформление всех документов.',
        'feature3_title'  => 'Все комплектации в наличии',
        'feature3_text'   => 'Широкий выбор комплектаций, с полным пакетом документов.',
        'promo_title'     => 'Забронируйте автомобиль сегодня и получите дополнительную выгоду 100 000 ₽',
        'car1_title'      => 'TANK 500',
        'car1_url'        => '/tank-500/',
        'car1_image_id'   => 0,
        'car2_title'      => 'TANK 300',
        'car2_url'        => '/tank-300/',
        'car2_image_id'   => 0,
        'car1_image_default' => $theme . '/images/' . rawurlencode('image 286.png'),
        'car2_image_default' => $theme . '/images/' . rawurlencode('tank 300.png'),
    ];

    $extra = avtodealer_defaults_by_lang('catalog', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_catalog_car_href($title, $url, $fallback = '#')
{
    $title = (string) $title;
    if (stripos($title, '300') !== false && function_exists('avtodealer_model_page_url')) {
        return avtodealer_model_page_url('tank-300');
    }
    if (stripos($title, '500') !== false && function_exists('avtodealer_model_page_url')) {
        return avtodealer_model_page_url('tank-500');
    }

    $url = trim((string) $url);
    // Old same-page anchors → dedicated model pages
    if ($url === '#tank-300' || $url === '/tank-300' || $url === '/tank-300/') {
        return function_exists('avtodealer_model_page_url')
            ? avtodealer_model_page_url('tank-300')
            : trailingslashit(home_url('/tank-300'));
    }
    if ($url === '#tank-500' || $url === '/tank-500' || $url === '/tank-500/') {
        return function_exists('avtodealer_model_page_url')
            ? avtodealer_model_page_url('tank-500')
            : trailingslashit(home_url('/tank-500'));
    }

    return avtodealer_href($url, $fallback);
}

function avtodealer_sanitize_catalog($input)
{
    $defaults = avtodealer_catalog_defaults();
    $out = [];
    $url_keys = ['offer_cta_url', 'car1_url', 'car2_url'];
    $id_keys = ['car1_image_id', 'car2_image_id'];

    foreach ($defaults as $key => $default) {
        if (!array_key_exists($key, $input)) {
            continue;
        }
        if (str_ends_with($key, '_default')) {
            continue;
        }

        $val = $input[$key];

        if (in_array($key, $id_keys, true)) {
            $out[$key] = absint($val);
            continue;
        }

        if (in_array($key, $url_keys, true)) {
            $out[$key] = esc_url_raw((string) $val);
            continue;
        }

        if ($key === 'countdown_end') {
            $out[$key] = sanitize_text_field((string) $val);
            continue;
        }

        $out[$key] = sanitize_text_field((string) $val);
    }

    return $out;
}

function avtodealer_get_catalog($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $stored = avtodealer_get_option_lang('avtodealer_catalog', $lang);
    $defaults = avtodealer_catalog_defaults($lang);
    $data = array_merge($defaults, $stored);

    $car1_id = absint($data['car1_image_id']);
    $car2_id = absint($data['car2_image_id']);

    $car1_url = $car1_id ? (string) wp_get_attachment_image_url($car1_id, 'full') : '';
    $car2_url = $car2_id ? (string) wp_get_attachment_image_url($car2_id, 'full') : '';

    if ($car1_url === '') {
        $car1_url = $defaults['car1_image_default'];
    }
    if ($car2_url === '') {
        $car2_url = $defaults['car2_image_default'];
    }

    $end = trim((string) ($data['countdown_end'] ?? ''));
    $end_ts = $end !== '' ? strtotime($end) : false;
    if ($end === '' || $end_ts === false || $end_ts <= time()) {
        // Bo‘sh yoki o‘tgan sana — backenddan yangi muddat (3 kun)
        $end = gmdate('c', time() + (3 * DAY_IN_SECONDS) + (6 * HOUR_IN_SECONDS) + (14 * MINUTE_IN_SECONDS) + 55);
        $to_store = is_array($stored) ? $stored : [];
        $to_store['countdown_end'] = $end;
        avtodealer_update_option_lang('avtodealer_catalog', $to_store, $lang);
    }

    $data['car1_image_id'] = $car1_id;
    $data['car2_image_id'] = $car2_id;
    $data['car1_image_url'] = $car1_url;
    $data['car2_image_url'] = $car2_url;
    $data['countdown_end'] = $end;
    $data['offer_cta_url'] = avtodealer_href($data['offer_cta_url'] ?? '', '#credit');
    $data['car1_url'] = avtodealer_catalog_car_href(
        $data['car1_title'] ?? '',
        $data['car1_url'] ?? '',
        '#tank-500'
    );
    $data['car2_url'] = avtodealer_catalog_car_href(
        $data['car2_title'] ?? '',
        $data['car2_url'] ?? '',
        '#tank-300'
    );
    $data['lang'] = $lang;
    unset($data['car1_image_default'], $data['car2_image_default']);

    return $data;
}

function avtodealer_save_catalog($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $current = $replace ? avtodealer_catalog_defaults($lang) : avtodealer_get_catalog($lang);
    unset(
        $current['car1_image_url'],
        $current['car2_image_url'],
        $current['car1_image_default'],
        $current['car2_image_default'],
        $current['lang']
    );
    $clean = avtodealer_sanitize_catalog($input);
    $saved = array_merge($current, $clean);
    unset($saved['car1_image_url'], $saved['car2_image_url'], $saved['lang']);
    avtodealer_update_option_lang('avtodealer_catalog', $saved, $lang);

    return avtodealer_get_catalog($lang);
}

function avtodealer_delete_catalog($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_delete_option_lang('avtodealer_catalog', $lang);

    return avtodealer_get_catalog($lang);
}
