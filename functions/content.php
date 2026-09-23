<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_theme_image($name)
{
    return get_template_directory_uri() . '/images/' . rawurlencode($name);
}

function avtodealer_resolve_image_url($id, $default = '')
{
    $id = absint($id);
    $url = $id ? (string) wp_get_attachment_image_url($id, 'full') : '';

    return $url !== '' ? $url : (string) $default;
}

function avtodealer_sanitize_by_schema($input, $defaults, $url_keys = [], $id_keys = [], $textarea_keys = [])
{
    $out = [];

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

        if (in_array($key, $textarea_keys, true)) {
            $out[$key] = sanitize_textarea_field((string) $val);
            continue;
        }

        $out[$key] = sanitize_text_field((string) $val);
    }

    return $out;
}

function avtodealer_crud_get($base, $defaults, $lang = null, $meta_keys = [])
{
    $lang = $lang ?: avtodealer_current_lang();
    $stored = avtodealer_get_option_lang($base, $lang);
    $data = array_merge($defaults, is_array($stored) ? $stored : []);

    foreach ($meta_keys as $key) {
        unset($data[$key]);
    }

    foreach ($defaults as $key => $val) {
        if (str_ends_with($key, '_default')) {
            unset($data[$key]);
        }
    }

    $data['lang'] = $lang;

    return $data;
}

function avtodealer_crud_save($base, $defaults, $sanitize_cb, $input, $replace = false, $lang = null, $strip = [])
{
    $lang = $lang ?: avtodealer_current_lang();
    $current = $replace ? $defaults : avtodealer_crud_get($base, $defaults, $lang, $strip);

    foreach ($strip as $key) {
        unset($current[$key]);
    }
    unset($current['lang']);

    foreach (array_keys($current) as $key) {
        if (str_ends_with($key, '_default') || str_ends_with($key, '_url') && str_contains($key, 'image')) {
            // keep structure; image urls recomputed on get
        }
    }

    $clean = call_user_func($sanitize_cb, $input);
    $saved = array_merge($current, $clean);

    foreach ($strip as $key) {
        unset($saved[$key]);
    }
    unset($saved['lang']);

    foreach (array_keys($saved) as $key) {
        if (str_ends_with($key, '_default')) {
            unset($saved[$key]);
        }
    }

    avtodealer_update_option_lang($base, $saved, $lang);

    return true;
}

function avtodealer_crud_delete($base, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_delete_option_lang($base, $lang);
}

/* ---------------- CONFIGS ---------------- */

function avtodealer_configs_defaults($lang = null)
{
    $defaults = [
        'title'            => 'Подберите комплектацию',
        'filter1_label'    => 'Модель',
        'filter1_options'  => "Модель\nTANK 300\nTANK 500",
        'filter2_label'    => 'Комплектация',
        'filter2_options'  => "Комплектация\nCity\nAdventure\nPremium",
        'filter3_label'    => 'Двигатель',
        'filter3_options'  => "Двигатель\n2.0 л / 220 л.с.\n3.0 л / 354 л.с.",
        'more_label'       => 'Загрузить еще',
        'cta_label'        => 'Получить предложение',
        'cta_url'          => '#credit',
        'link_label'       => 'Тест-драйв',
        'visible_count'    => 3,
    ];

    $trims = [
        ['TANK 300 Adventure', '2.0 л · 220 л.с. · АКПП · 4WD', 'от 4 549 000 ₽', "Trade-in до 300 000 ₽\nКредит от 0,01%", 'tank 300.png'],
        ['TANK 300 Premium', '2.0 л · 220 л.с. · АКПП · 4WD', 'от 4 799 000 ₽', "КАСКО в подарок\nTrade-in до 350 000 ₽", 'tank 300.png'],
        ['TANK 500 City', '3.0 л · 354 л.с. · АКПП · 4WD', 'от 6 349 000 ₽', "Trade-in до 400 000 ₽\nСервис 2 года", 'image 286.png'],
        ['TANK 500 Premium', '3.0 л · 354 л.с. · АКПП · 4WD', 'от 6 899 000 ₽', "Выгода до 500 000 ₽\nКАСКО в подарок", 'Frame 6172.png'],
        ['TANK 300 City', '2.0 л · 220 л.с. · АКПП · 4WD', 'от 4 299 000 ₽', 'Trade-in до 250 000 ₽', 'tank 300.png'],
        ['TANK 500 Adventure', '3.0 л · 354 л.с. · АКПП · 4WD', 'от 6 549 000 ₽', "Кредит от 0,01%\nTrade-in до 450 000 ₽", 'image 286.png'],
    ];

    foreach ($trims as $i => $t) {
        $n = $i + 1;
        $defaults["trim{$n}_title"] = $t[0];
        $defaults["trim{$n}_specs"] = $t[1];
        $defaults["trim{$n}_price"] = $t[2];
        $defaults["trim{$n}_perks"] = $t[3];
        $defaults["trim{$n}_image_id"] = 0;
        $defaults["trim{$n}_image_default"] = avtodealer_theme_image($t[4]);
    }

    $extra = avtodealer_defaults_by_lang('configs', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_configs($input)
{
    $defaults = avtodealer_configs_defaults();
    $url_keys = ['cta_url'];
    $id_keys = ['visible_count'];
    $textarea_keys = ['filter1_options', 'filter2_options', 'filter3_options'];

    for ($i = 1; $i <= 6; $i++) {
        $id_keys[] = "trim{$i}_image_id";
        $textarea_keys[] = "trim{$i}_perks";
    }

    $out = avtodealer_sanitize_by_schema($input, $defaults, $url_keys, $id_keys, $textarea_keys);
    if (isset($out['visible_count'])) {
        $out['visible_count'] = max(1, min(6, absint($out['visible_count'])));
    }

    return $out;
}

function avtodealer_get_configs($lang = null)
{
    $defaults = avtodealer_configs_defaults($lang);
    $data = avtodealer_crud_get('avtodealer_configs', $defaults, $lang);
    $items = [];

    for ($i = 1; $i <= 6; $i++) {
        $def = $defaults["trim{$i}_image_default"] ?? '';
        $id = absint($data["trim{$i}_image_id"] ?? 0);
        $url = avtodealer_resolve_image_url($id, $def);
        $data["trim{$i}_image_id"] = $id;
        $data["trim{$i}_image_url"] = $url;
        $perks_raw = (string) ($data["trim{$i}_perks"] ?? '');
        $perks = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $perks_raw))));
        $items[] = [
            'title' => (string) ($data["trim{$i}_title"] ?? ''),
            'specs' => (string) ($data["trim{$i}_specs"] ?? ''),
            'price' => (string) ($data["trim{$i}_price"] ?? ''),
            'perks' => $perks,
            'image' => $url,
            'image_id' => $id,
        ];
    }

    $data['items'] = $items;
    $data['visible_count'] = max(1, min(6, absint($data['visible_count'] ?? 3)));
    $data['cta_url'] = avtodealer_href($data['cta_url'] ?? '', '#credit');
    $data['lang'] = $lang ?: avtodealer_current_lang();

    return $data;
}

function avtodealer_save_configs($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $defaults = avtodealer_configs_defaults($lang);
    $strip = ['items', 'lang'];
    for ($i = 1; $i <= 6; $i++) {
        $strip[] = "trim{$i}_image_url";
        $strip[] = "trim{$i}_image_default";
    }
    avtodealer_crud_save('avtodealer_configs', $defaults, 'avtodealer_sanitize_configs', $input, $replace, $lang, $strip);

    return avtodealer_get_configs($lang);
}

function avtodealer_delete_configs($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_delete('avtodealer_configs', $lang);

    return avtodealer_get_configs($lang);
}

/* ---------------- TRADEIN ---------------- */

function avtodealer_tradein_defaults($lang = null)
{
    $defaults = [
        'title'         => "Обмен по Trade-in\nна выгодных условиях",
        'text'          => '',
        'cta_label'     => 'Отправить заявку',
        'cta_url'       => '#credit',
        'image_id'      => 0,
        'image_default' => avtodealer_theme_image('image 290.png'),
    ];
    $extra = avtodealer_defaults_by_lang('tradein', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_tradein($input)
{
    return avtodealer_sanitize_by_schema(
        $input,
        avtodealer_tradein_defaults(),
        ['cta_url'],
        ['image_id'],
        ['text']
    );
}

function avtodealer_get_tradein($lang = null)
{
    $defaults = avtodealer_tradein_defaults($lang);
    $data = avtodealer_crud_get('avtodealer_tradein', $defaults, $lang);
    $id = absint($data['image_id'] ?? 0);
    $data['image_id'] = $id;
    $data['image_url'] = avtodealer_resolve_image_url($id, $defaults['image_default'] ?? '');
    $data['cta_url'] = avtodealer_href($data['cta_url'] ?? '', '#credit');
    $data['lang'] = $lang ?: avtodealer_current_lang();

    return $data;
}

function avtodealer_save_tradein($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_save(
        'avtodealer_tradein',
        avtodealer_tradein_defaults($lang),
        'avtodealer_sanitize_tradein',
        $input,
        $replace,
        $lang,
        ['image_url', 'image_default', 'lang']
    );

    return avtodealer_get_tradein($lang);
}

function avtodealer_delete_tradein($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_delete('avtodealer_tradein', $lang);

    return avtodealer_get_tradein($lang);
}

/* ---------------- CREDIT ---------------- */

function avtodealer_credit_defaults($lang = null)
{
    $defaults = [
        'title'       => 'Оставьте заявку на кредит',
        'subtitle'    => 'и получите одобрение за 1 день',
        'placeholder' => '+7 (___) ___ - __ - __',
        'button'      => 'Отправить заявку',
        'note'        => 'Нажимая кнопку, вы соглашаетесь с политикой обработки персональных данных.',
        'image_id'    => 0,
        'image_default' => avtodealer_theme_image('image 281.png'),
    ];
    $extra = avtodealer_defaults_by_lang('credit', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_credit($input)
{
    return avtodealer_sanitize_by_schema(
        $input,
        avtodealer_credit_defaults(),
        [],
        ['image_id'],
        ['subtitle', 'note']
    );
}

function avtodealer_get_credit($lang = null)
{
    $defaults = avtodealer_credit_defaults($lang);
    $data = avtodealer_crud_get('avtodealer_credit', $defaults, $lang);
    $id = absint($data['image_id'] ?? 0);
    $data['image_id'] = $id;
    $data['image_url'] = avtodealer_resolve_image_url($id, $defaults['image_default'] ?? '');
    $data['lang'] = $lang ?: avtodealer_current_lang();

    return $data;
}

function avtodealer_save_credit($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_save(
        'avtodealer_credit',
        avtodealer_credit_defaults($lang),
        'avtodealer_sanitize_credit',
        $input,
        $replace,
        $lang,
        ['image_url', 'image_default', 'lang']
    );

    return avtodealer_get_credit($lang);
}

function avtodealer_delete_credit($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_delete('avtodealer_credit', $lang);

    return avtodealer_get_credit($lang);
}

/* ---------------- CORPORATE ---------------- */

function avtodealer_corporate_defaults($lang = null)
{
    $defaults = [
        'title'        => 'Индивидуальное предложение для корпоративных клиентов',
        'cta_label'    => 'Отправить заявку',
        'cta_url'      => '#credit',
        'person_name'  => 'Татьяна Санникова',
        'person_role'  => "Отдел корпоративных продаж\nАВТОРУСЬ",
        'photo_id'     => 0,
        'photo_default'=> avtodealer_theme_image('image 295.png'),
    ];
    $extra = avtodealer_defaults_by_lang('corporate', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_corporate($input)
{
    return avtodealer_sanitize_by_schema(
        $input,
        avtodealer_corporate_defaults(),
        ['cta_url'],
        ['photo_id'],
        ['person_role']
    );
}

function avtodealer_get_corporate($lang = null)
{
    $defaults = avtodealer_corporate_defaults($lang);
    $data = avtodealer_crud_get('avtodealer_corporate', $defaults, $lang);
    $id = absint($data['photo_id'] ?? 0);
    $data['photo_id'] = $id;
    $data['photo_url'] = avtodealer_resolve_image_url($id, $defaults['photo_default'] ?? '');
    $data['cta_url'] = avtodealer_href($data['cta_url'] ?? '', '#credit');
    $data['lang'] = $lang ?: avtodealer_current_lang();

    return $data;
}

function avtodealer_save_corporate($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_save(
        'avtodealer_corporate',
        avtodealer_corporate_defaults($lang),
        'avtodealer_sanitize_corporate',
        $input,
        $replace,
        $lang,
        ['photo_url', 'photo_default', 'lang']
    );

    return avtodealer_get_corporate($lang);
}

function avtodealer_delete_corporate($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_delete('avtodealer_corporate', $lang);

    return avtodealer_get_corporate($lang);
}

/* ---------------- CONTACT (map block) ---------------- */

function avtodealer_contact_defaults($lang = null)
{
    $defaults = [
        'map_query'       => 'Ярославское шоссе 2В Москва TANK',
        'address'         => 'Ярославское шоссе, владение 2 В, строение 3 (МКАД, 95 км)',
        'phone'           => '+7 (999) 999-99-99',
        'phone_href'      => 'tel:+79999999999',
        'hours'           => 'Ежедневно с 09:00 до 21:00',
        'callback_label'  => 'Заказать звонок',
        'use_header'      => '1',
    ];
    $extra = avtodealer_defaults_by_lang('contact', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_contact($input)
{
    return avtodealer_sanitize_by_schema(
        $input,
        avtodealer_contact_defaults(),
        ['phone_href'],
        [],
        ['address']
    );
}

function avtodealer_get_contact($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $defaults = avtodealer_contact_defaults($lang);
    $data = avtodealer_crud_get('avtodealer_contact', $defaults, $lang);

    if (!empty($data['use_header']) && $data['use_header'] !== '0' && function_exists('avtodealer_get_header')) {
        $h = avtodealer_get_header($lang);
        $data['address'] = $h['address'] ?? $data['address'];
        $data['phone'] = $h['phone'] ?? $data['phone'];
        $data['phone_href'] = $h['phone_href'] ?? $data['phone_href'];
        $data['hours'] = $h['hours'] ?? $data['hours'];
        $data['callback_label'] = $h['callback_label'] ?? $data['callback_label'];
    }

    $data['lang'] = $lang;

    return $data;
}

function avtodealer_save_contact($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_save(
        'avtodealer_contact',
        avtodealer_contact_defaults($lang),
        'avtodealer_sanitize_contact',
        $input,
        $replace,
        $lang,
        ['lang']
    );

    return avtodealer_get_contact($lang);
}

function avtodealer_delete_contact($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_delete('avtodealer_contact', $lang);

    return avtodealer_get_contact($lang);
}

/* ---------------- FOOTER ---------------- */

function avtodealer_footer_defaults($lang = null)
{
    $defaults = [
        'legal1'    => 'TANK 300 за 3 799 000руб с учетом поддержек. Цена на модель TANK (ТЭНК) 300 в комплектации Adventure (Эдвенчер) с двигателем 2,0T 4WD, 2023 года производства, цвет автомобиля: белый, с учетом выгоды по трейд-ин 300 000 рублей. В трейд-ин принимаются автомобили с пробегом со сроком владения и регистрации (постановки на учет) в органах ГИБДД не менее 6 месяцев (в отношении автомобилей бренда TANK, Haval, Great Wall – 3 месяца) до сдачи автомобиля в трейд-ин. В качестве документов, подтверждающих срок владения сдаваемого в трейд-ин автомобиля, собственнику необходимо предоставить копию ПТС или СТС или карточку учета ТС из ГИБДД с печатью и подписью. Подробности уточняйте у менеджеров отдела продаж TANK АВТОРУСЬ. Предложение ограничено, не является офертой и действует с 01.04.2024г.',
        'legal2'    => 'TANK 500 за 5 349 000 руб с учетом поддержек. Цена на модель TANK (ТЭНК) 500 в комплектации Adventure (Эдвенчер) с двигателем 3,0T 4WD, 2023 года производства, цвет автомобиля: белый, с учетом прямой выгоды в 950 000 рублей. Подробности уточняйте у менеджеров отдела продаж TANK АВТОРУСЬ. Предложение ограничено, не является офертой и действует с 01.04.2024г.',
        'copy_note' => 'Официальный дилер ООО «ГК АВТОРУСЬ МЫТИЩИ» | ОГРН – 1147746893635, ИНН – 7728882903',
        'use_header'=> '1',
    ];
    $extra = avtodealer_defaults_by_lang('footer', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_footer($input)
{
    return avtodealer_sanitize_by_schema(
        $input,
        avtodealer_footer_defaults(),
        [],
        [],
        ['legal1', 'legal2']
    );
}

function avtodealer_get_footer($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $defaults = avtodealer_footer_defaults($lang);
    $data = avtodealer_crud_get('avtodealer_footer', $defaults, $lang);

    // Sync shared contact fields from header when footer leaves them empty / use_header on
    if (
        (!empty($data['use_header']) && $data['use_header'] !== '0')
        || trim((string) ($data['phone'] ?? '')) === ''
    ) {
        if (function_exists('avtodealer_get_header')) {
            $h = avtodealer_get_header($lang);
            foreach (['brand', 'subtitle', 'address', 'phone', 'phone_href', 'callback_label', 'status', 'logo_url', 'service_label', 'service_url', 'testdrive_label', 'testdrive_url'] as $key) {
                if (trim((string) ($data[$key] ?? '')) === '' && isset($h[$key])) {
                    $data[$key] = $h[$key];
                }
            }
            if (empty($data['logo_url']) && !empty($h['logo_url'])) {
                $data['logo_url'] = $h['logo_url'];
            }
        }
    }

    $data['lang'] = $lang;

    return $data;
}

function avtodealer_save_footer($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_save(
        'avtodealer_footer',
        avtodealer_footer_defaults($lang),
        'avtodealer_sanitize_footer',
        $input,
        $replace,
        $lang,
        ['lang', 'brand', 'subtitle', 'address', 'phone', 'phone_href', 'callback_label', 'logo_url', 'service_label', 'service_url', 'testdrive_label', 'testdrive_url']
    );

    return avtodealer_get_footer($lang);
}

function avtodealer_delete_footer($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_delete('avtodealer_footer', $lang);

    return avtodealer_get_footer($lang);
}
