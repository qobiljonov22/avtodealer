<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_header_defaults($lang = null)
{
    $defaults = [
        'address'         => 'Ярославское шоссе, владение 2 В, строение 3 (МКАД, 95 км)',
        'address_url'     => '',
        'service_label'   => 'Записаться на сервис',
        'service_url'     => '#contact',
        'testdrive_label' => 'Тест-драйв',
        'testdrive_url'   => '#credit',
        'brand'           => 'АВТОРУСЬ TANK',
        'subtitle'        => 'Официальный дилер',
        'phone'           => '+7 (999) 999-99-99',
        'phone_href'      => 'tel:+79999999999',
        'status'          => 'Мы на связи',
        'hours'           => 'Ежедневно с 09:00 до 21:00',
        'callback_label'  => 'Заказать звонок',
        'menu_label'      => 'Меню',
        'loader_text'     => 'Yuklanmoqda…',
        'logo_id'         => 0,
    ];

    $extra = avtodealer_defaults_by_lang('header', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_header($input)
{
    $defaults = avtodealer_header_defaults();
    $out = [];

    foreach ($defaults as $key => $default) {
        if (!array_key_exists($key, $input)) {
            continue;
        }

        $val = $input[$key];

        if ($key === 'logo_id') {
            $out[$key] = absint($val);
            continue;
        }

        if (in_array($key, ['address_url', 'service_url', 'testdrive_url', 'phone_href'], true)) {
            $out[$key] = esc_url_raw((string) $val);
            continue;
        }

        $out[$key] = sanitize_text_field((string) $val);
    }

    return $out;
}

function avtodealer_get_header($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $stored = avtodealer_get_option_lang('avtodealer_header', $lang);
    $data = array_merge(avtodealer_header_defaults($lang), $stored);
    $logo_id = absint($data['logo_id']);
    $logo_url = $logo_id ? (string) wp_get_attachment_image_url($logo_id, 'full') : '';

    if ($logo_url === '') {
        $logo_url = get_template_directory_uri() . '/images/main-logo.png';
    }

    $phone_href = trim((string) $data['phone_href']);
    if ($phone_href === '') {
        $digits = preg_replace('/[^\d+]/', '', (string) $data['phone']);
        $phone_href = 'tel:' . $digits;
    }

    $data['logo_id']     = $logo_id;
    $data['logo_url']    = $logo_url;
    $data['phone_href']  = $phone_href;
    $data['address_url'] = avtodealer_maps_url($data['address'] ?? '', $data['address_url'] ?? '');
    $data['service_url'] = avtodealer_href($data['service_url'] ?? '', '#contact');
    $data['testdrive_url'] = avtodealer_href($data['testdrive_url'] ?? '', '#credit');
    $data['lang']        = $lang;

    return $data;
}

function avtodealer_header($key, $default = '')
{
    $data = avtodealer_get_header();

    if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
        return $default;
    }

    return $data[$key];
}

function avtodealer_save_header($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $current = $replace ? avtodealer_header_defaults($lang) : avtodealer_get_header($lang);
    unset($current['logo_url'], $current['lang']);
    $clean = avtodealer_sanitize_header($input);
    $saved = array_merge($current, $clean);
    unset($saved['logo_url'], $saved['lang']);
    avtodealer_update_option_lang('avtodealer_header', $saved, $lang);

    return avtodealer_get_header($lang);
}

function avtodealer_delete_header($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_delete_option_lang('avtodealer_header', $lang);

    return avtodealer_get_header($lang);
}

function avtodealer_href($url, $fallback = '#')
{
    $url = trim((string) $url);
    if ($url === '' || $url === '#') {
        return $fallback;
    }

    return $url;
}

function avtodealer_maps_url($address, $url = '')
{
    $url = trim((string) $url);
    if ($url !== '' && $url !== '#') {
        return $url;
    }

    $address = trim((string) $address);
    if ($address === '') {
        return '#contact';
    }

    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
}

