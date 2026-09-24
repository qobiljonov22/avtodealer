<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_modal_defaults($lang = null)
{
    $defaults = [
        'title'           => 'Получить предложение',
        'subtitle'        => 'Пожалуйста, укажите свои данные. Наш менеджер свяжется с вами в течение 15 минут.',
        'model_label'     => 'Модель',
        'model_options'   => "TANK 300\nTANK 500",
        'phone_label'     => 'Телефон',
        'phone_placeholder'=> '+7 (___) ___-__-__',
        'button'          => 'Получить предложение',
        'consent'         => 'Согласен на обработку персональных данных.',
        'success'         => 'Заявка принята. Мы перезвоним.',
        'close_label'     => 'Закрыть',
    ];

    $extra = avtodealer_defaults_by_lang('modal', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_modal($input)
{
    return avtodealer_sanitize_by_schema(
        $input,
        avtodealer_modal_defaults(),
        [],
        [],
        ['subtitle', 'model_options', 'consent']
    );
}

function avtodealer_get_modal($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $defaults = avtodealer_modal_defaults($lang);
    $data = avtodealer_crud_get('avtodealer_modal', $defaults, $lang);
    $opts = preg_split('/\r\n|\r|\n/', (string) ($data['model_options'] ?? ''));
    $data['models'] = array_values(array_filter(array_map('trim', $opts), static function ($l) {
        return $l !== '';
    }));
    if (!$data['models']) {
        $data['models'] = ['TANK 300', 'TANK 500'];
    }
    $data['lang'] = $lang;

    return $data;
}

function avtodealer_save_modal($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_save(
        'avtodealer_modal',
        avtodealer_modal_defaults($lang),
        'avtodealer_sanitize_modal',
        $input,
        $replace,
        $lang,
        ['models', 'lang']
    );

    return avtodealer_get_modal($lang);
}

function avtodealer_delete_modal($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_delete('avtodealer_modal', $lang);

    return avtodealer_get_modal($lang);
}
