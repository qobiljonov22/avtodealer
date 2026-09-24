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
        'countdown_days'  => 'дни',
        'countdown_hours' => 'часа',
        'countdown_mins'  => 'минут',
        'countdown_secs'  => 'секунд',
        'feature1_title'  => 'Официальный дилер',
        'feature1_text'   => 'Гарантируем высокое качество обслуживания.',
        'feature2_title'  => 'Покупка авто за 1 день',
        'feature2_text'   => 'Удобный процесс покупки, включая оформление всех документов.',
        'feature3_title'  => 'Все комплектации в наличии',
        'feature3_text'   => 'Широкий выбор комплектаций, с полным пакетом документов.',
        'promo_title'     => 'Забронируйте автомобиль сегодня и получите дополнительную выгоду 100 000 ₽',
        'car1_title'      => 'TANK 300',
        'car1_label'      => 'TANK 300',
        'car1_url'        => '/tank-300/',
        'car1_image_id'   => 0,
        'car2_title'      => 'TANK 500',
        'car2_label'      => 'TANK 500',
        'car2_url'        => '/tank-500/',
        'car2_image_id'   => 0,
        // Figma: orange 300 left, champagne 500 right
        'car1_image_default' => $theme . '/images/' . rawurlencode('image 286.png'),
        'car2_image_default' => $theme . '/images/' . rawurlencode('tank-500-side.png'),
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
    $data = array_merge($defaults, is_array($stored) ? $stored : []);

    // Fix legacy swap: car1 was 500 / car2 was 300
    $t1 = (string) ($data['car1_title'] ?? '');
    $t2 = (string) ($data['car2_title'] ?? '');
    if (stripos($t1, '500') !== false && stripos($t2, '300') !== false) {
        foreach (['title', 'url', 'image_id'] as $k) {
            $a = 'car1_' . $k;
            $b = 'car2_' . $k;
            $tmp = $data[$a] ?? null;
            $data[$a] = $data[$b] ?? null;
            $data[$b] = $tmp;
        }
        $to_store = is_array($stored) ? $stored : [];
        foreach (['car1_title', 'car1_url', 'car1_image_id', 'car2_title', 'car2_url', 'car2_image_id'] as $key) {
            if (array_key_exists($key, $data)) {
                $to_store[$key] = $data[$key];
            }
        }
        avtodealer_update_option_lang('avtodealer_catalog', $to_store, $lang);
        $stored = $to_store;
    }

    $car1_id = absint($data['car1_image_id'] ?? 0);
    $car2_id = absint($data['car2_image_id'] ?? 0);

    $car1_url = $car1_id ? (string) wp_get_attachment_image_url($car1_id, 'full') : '';
    $car2_url = $car2_id ? (string) wp_get_attachment_image_url($car2_id, 'full') : '';

    // Match default image to title when no media ID
    $img300 = get_template_directory_uri() . '/images/' . rawurlencode('image 286.png');
    $img500 = get_template_directory_uri() . '/images/' . rawurlencode('tank-500-side.png');
    if ($car1_url === '') {
        $car1_url = (stripos((string) ($data['car1_title'] ?? ''), '500') !== false)
            ? $img500
            : ($defaults['car1_image_default'] ?? $img300);
    }
    if ($car2_url === '') {
        $car2_url = (stripos((string) ($data['car2_title'] ?? ''), '300') !== false)
            ? $img300
            : ($defaults['car2_image_default'] ?? $img500);
    }

    $end = trim((string) ($data['countdown_end'] ?? ''));
    $end_ts = $end !== '' ? strtotime($end) : false;
    if ($end === '' || $end_ts === false || $end_ts <= time()) {
        $end = gmdate('c', time() + (3 * DAY_IN_SECONDS) + (6 * HOUR_IN_SECONDS) + (14 * MINUTE_IN_SECONDS) + 55);
        $to_store = is_array($stored) ? $stored : [];
        $to_store['countdown_end'] = $end;
        avtodealer_update_option_lang('avtodealer_catalog', $to_store, $lang);
    }

    $data['car1_image_id'] = $car1_id;
    $data['car2_image_id'] = $car2_id;
    $data['car1_image_url'] = $car1_url;
    $data['car2_image_url'] = $car2_url;
    if (trim((string) ($data['car1_label'] ?? '')) === '') {
        $data['car1_label'] = (string) ($data['car1_title'] ?? '');
    }
    if (trim((string) ($data['car2_label'] ?? '')) === '') {
        $data['car2_label'] = (string) ($data['car2_title'] ?? '');
    }
    $data['countdown_end'] = $end;
    $data['offer_cta_url'] = avtodealer_href($data['offer_cta_url'] ?? '', '#credit');
    $data['car1_url'] = avtodealer_catalog_car_href(
        $data['car1_title'] ?? '',
        $data['car1_url'] ?? '',
        '/tank-300/'
    );
    $data['car2_url'] = avtodealer_catalog_car_href(
        $data['car2_title'] ?? '',
        $data['car2_url'] ?? '',
        '/tank-500/'
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
