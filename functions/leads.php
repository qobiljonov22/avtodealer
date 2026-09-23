<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_leads_store($lead)
{
    $key = 'avtodealer_leads';
    $list = get_option($key, []);
    if (!is_array($list)) {
        $list = [];
    }

    array_unshift($list, $lead);
    $list = array_slice($list, 0, 200);
    update_option($key, $list, false);

    return $list;
}

function avtodealer_rest_lead_create(WP_REST_Request $request)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
    $rate_key = 'avto_lead_' . md5($ip);
    if (get_transient($rate_key)) {
        return new WP_Error('avto_rate', 'Подождите немного и попробуйте снова.', ['status' => 429]);
    }
    set_transient($rate_key, 1, 20);

    $body = avtodealer_rest_json($request);
    $phone = sanitize_text_field((string) ($body['phone'] ?? ''));
    $phone = preg_replace('/[^\d+\-\(\)\s]/', '', $phone);
    $phone = trim((string) $phone);

    if ($phone === '' || strlen(preg_replace('/\D/', '', $phone)) < 10) {
        return new WP_Error('avto_phone', 'Укажите корректный телефон.', ['status' => 400]);
    }

    $source = sanitize_text_field((string) ($body['source'] ?? 'credit'));
    $name = sanitize_text_field((string) ($body['name'] ?? ''));
    $model = sanitize_text_field((string) ($body['model'] ?? ''));
    $page = esc_url_raw((string) ($body['page'] ?? ''));

    $lead = [
        'id'         => uniqid('lead_', true),
        'phone'      => $phone,
        'name'       => $name,
        'model'      => $model,
        'source'     => $source,
        'page'       => $page,
        'created_at' => current_time('mysql'),
        'ip'         => $ip,
    ];

    avtodealer_leads_store($lead);

    $admin_email = get_option('admin_email');
    if (is_email($admin_email)) {
        $subject = '[' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES) . '] Заявка: ' . $phone;
        $message = "Новая заявка с сайта\n\n"
            . 'Телефон: ' . $phone . "\n"
            . 'Модель: ' . ($model !== '' ? $model : '—') . "\n"
            . 'Источник: ' . $source . "\n"
            . 'Имя: ' . ($name !== '' ? $name : '—') . "\n"
            . 'Страница: ' . ($page !== '' ? $page : '—') . "\n"
            . 'Время: ' . $lead['created_at'] . "\n";
        wp_mail($admin_email, $subject, $message);
    }

    return rest_ensure_response([
        'ok'      => true,
        'message' => 'Заявка принята. Мы перезвоним.',
        'lead_id' => $lead['id'],
    ]);
}

function avtodealer_rest_leads_list(WP_REST_Request $request)
{
    if (!avtodealer_rest_can_edit()) {
        return new WP_Error('avto_forbidden', 'Forbidden', ['status' => 403]);
    }

    $list = get_option('avtodealer_leads', []);
    if (!is_array($list)) {
        $list = [];
    }

    return rest_ensure_response([
        'items' => array_values($list),
        'total' => count($list),
    ]);
}
