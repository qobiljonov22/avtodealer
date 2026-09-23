<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_models_defaults($lang = null)
{
    $defaults = [
        'car1_eyebrow'       => 'Только в АВТОРУСЬ',
        'car1_title'         => 'TANK 300',
        'car1_benefit'       => 'Выгода по Trade-in до 450 000 ₽',
        'car1_badge'         => 'Выгода по Trade-in до 450 000 ₽',
        'car1_offer_label'   => '',
        'car1_offer_url'     => '#credit',
        'car1_cta_label'     => 'Получить предложение',
        'car1_cta_url'       => '#credit',
        'car1_td_label'      => 'Тест-драйв',
        'car1_td_url'        => '#credit',
        'car1_credit_label'  => 'В кредит',
        'car1_credit_url'    => '#credit',
        'car1_colors'        => "#C41E3A|" . avtodealer_theme_image('tank 300.png') . "\n#F0F0F0|" . avtodealer_theme_image('image 285 (1).png') . "\n#8B8B8B|" . avtodealer_theme_image('image 286 (1).png') . "\n#1A1A1A|" . avtodealer_theme_image('image 286 (2).png'),
        'car1_perks'         => "Автомобили в наличии с ПТС\nГарантия на 5 лет или 150 000 км\nЛучшие условия кредитования\nВыкуп авто",
        'car1_image_id'      => 0,
        'car1_image_default' => avtodealer_theme_image('tank 300.png'),
        'car1_g1_id'         => 0,
        'car1_g1_default'    => avtodealer_theme_image('image 285 (1).png'),
        'car1_g2_id'         => 0,
        'car1_g2_default'    => avtodealer_theme_image('image 285 (2).png'),
        'car1_g3_id'         => 0,
        'car1_g3_default'    => avtodealer_theme_image('image 285 (3).png'),
        'car1_g4_id'         => 0,
        'car1_g4_default'    => avtodealer_theme_image('image 287.png'),
        'car1_g5_id'         => 0,
        'car1_g5_default'    => avtodealer_theme_image('image 288.png'),

        'car2_eyebrow'       => 'Только в АВТОРУСЬ',
        'car2_title'         => 'TANK 500',
        'car2_benefit'       => 'Выгода до 950 000 ₽',
        'car2_badge'         => 'Выгода до 950 000 ₽',
        'car2_offer_label'   => '',
        'car2_offer_url'     => '#credit',
        'car2_cta_label'     => 'Узнать стоимость',
        'car2_cta_url'       => '#credit',
        'car2_td_label'      => 'Тест-драйв',
        'car2_td_url'        => '#credit',
        'car2_credit_label'  => 'Рассчитать кредит',
        'car2_credit_url'    => '#credit',
        'car2_colors'        => "#FFFFFF|" . avtodealer_theme_image('Frame 6172.png') . "\n#C4A574|" . avtodealer_theme_image('image 286.png') . "\n#8B8B8B|" . avtodealer_theme_image('image 286 (1).png') . "\n#1A1A1A|" . avtodealer_theme_image('image 286 (2).png'),
        'car2_perks'         => "Кредит от 0%\nГарантия 5 лет или 150 000 км\nАвтомобили в наличии с ПТС\nTrade-in на выгодных условиях",
        'car2_image_id'      => 0,
        'car2_image_default' => avtodealer_theme_image('image 286.png'),
        'car2_g1_id'         => 0,
        'car2_g1_default'    => avtodealer_theme_image('image 286 (1).png'),
        'car2_g2_id'         => 0,
        'car2_g2_default'    => avtodealer_theme_image('image 286 (2).png'),
        'car2_g3_id'         => 0,
        'car2_g3_default'    => avtodealer_theme_image('image 286 (3).png'),
        'car2_g4_id'         => 0,
        'car2_g4_default'    => avtodealer_theme_image('image 289.png'),
        'car2_g5_id'         => 0,
        'car2_g5_default'    => avtodealer_theme_image('Frame 6172.png'),
    ];

    $extra = avtodealer_defaults_by_lang('models', $lang);
    if (is_array($extra)) {
        $defaults = array_merge($defaults, $extra);
    }

    return $defaults;
}

function avtodealer_sanitize_models($input)
{
    $defaults = avtodealer_models_defaults();
    $url_keys = [];
    $id_keys = [];
    $textarea_keys = ['car1_colors', 'car1_perks', 'car2_colors', 'car2_perks'];

    foreach (['car1', 'car2'] as $car) {
        $url_keys[] = "{$car}_cta_url";
        $url_keys[] = "{$car}_td_url";
        $url_keys[] = "{$car}_credit_url";
        $url_keys[] = "{$car}_offer_url";
        $id_keys[] = "{$car}_image_id";
        foreach (['g1', 'g2', 'g3', 'g4', 'g5'] as $g) {
            $id_keys[] = "{$car}_{$g}_id";
        }
    }

    return avtodealer_sanitize_by_schema($input, $defaults, $url_keys, $id_keys, $textarea_keys);
}

function avtodealer_models_parse_lines($raw)
{
    $lines = preg_split('/\r\n|\r|\n/', (string) $raw);
    return array_values(array_filter(array_map('trim', $lines), static function ($l) {
        return $l !== '';
    }));
}

function avtodealer_models_resolve_color_image($img, $fallback_image = '')
{
    $img = trim((string) $img);
    if ($img !== '' && ctype_digit($img)) {
        $resolved = avtodealer_resolve_image_url(absint($img), '');
        $img = $resolved !== '' ? $resolved : '';
    } elseif ($img !== '' && !preg_match('#^https?://#i', $img) && !str_starts_with($img, '/')) {
        $img = '';
    }
    if ($img === '') {
        $img = (string) $fallback_image;
    }

    return $img;
}

function avtodealer_models_parse_colors($raw, $fallback_image = '', $default_raw = '')
{
    $fallback_by_index = [];
    foreach (avtodealer_models_parse_lines($default_raw) as $i => $line) {
        if (!str_contains($line, '|')) {
            continue;
        }
        [, $dimg] = array_map('trim', explode('|', $line, 2));
        $fallback_by_index[$i] = avtodealer_models_resolve_color_image($dimg, $fallback_image);
    }

    $out = [];
    foreach (avtodealer_models_parse_lines($raw) as $i => $line) {
        $hex = $line;
        $img = '';
        if (str_contains($line, '|')) {
            [$hex, $img] = array_map('trim', explode('|', $line, 2));
        }
        $hex = preg_match('/^#?[0-9a-fA-F]{3,8}$/', $hex) ? (str_starts_with($hex, '#') ? $hex : '#' . $hex) : $hex;
        $pool = $fallback_by_index[$i] ?? (string) $fallback_image;
        $out[] = [
            'hex'   => $hex,
            'image' => avtodealer_models_resolve_color_image($img, $pool),
        ];
    }

    return $out;
}

function avtodealer_get_models($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $defaults = avtodealer_models_defaults($lang);
    $data = avtodealer_crud_get('avtodealer_models', $defaults, $lang);
    $cars = [];

    foreach (['car1', 'car2'] as $key) {
        $image = avtodealer_resolve_image_url(
            absint($data["{$key}_image_id"] ?? 0),
            $defaults["{$key}_image_default"] ?? ''
        );
        $gallery = [];
        foreach (['g1', 'g2', 'g3', 'g4', 'g5'] as $g) {
            $gallery[] = avtodealer_resolve_image_url(
                absint($data["{$key}_{$g}_id"] ?? 0),
                $defaults["{$key}_{$g}_default"] ?? ''
            );
            $data["{$key}_{$g}_url"] = end($gallery);
        }

        $data["{$key}_image_url"] = $image;
        $data["{$key}_cta_url"] = avtodealer_href($data["{$key}_cta_url"] ?? '', '#credit');
        $data["{$key}_td_url"] = avtodealer_href($data["{$key}_td_url"] ?? '', '#credit');
        $data["{$key}_credit_url"] = avtodealer_href($data["{$key}_credit_url"] ?? '', '#credit');
        $data["{$key}_offer_url"] = avtodealer_href($data["{$key}_offer_url"] ?? '', '#credit');

        $cars[] = [
            'id'           => $key,
            'anchor'       => $key === 'car1' ? 'tank-300' : 'tank-500',
            'eyebrow'      => (string) ($data["{$key}_eyebrow"] ?? ''),
            'title'        => (string) ($data["{$key}_title"] ?? ''),
            'benefit'      => (string) ($data["{$key}_benefit"] ?? ''),
            'badge'        => (string) ($data["{$key}_badge"] ?? ''),
            'offer_label'  => (string) ($data["{$key}_offer_label"] ?? 'Получить предложение'),
            'offer_url'    => $data["{$key}_offer_url"],
            'cta_label'    => (string) ($data["{$key}_cta_label"] ?? ''),
            'cta_url'      => $data["{$key}_cta_url"],
            'td_label'     => (string) ($data["{$key}_td_label"] ?? ''),
            'td_url'       => $data["{$key}_td_url"],
            'credit_label' => (string) ($data["{$key}_credit_label"] ?? ''),
            'credit_url'   => $data["{$key}_credit_url"],
            'colors'       => avtodealer_models_parse_colors(
                $data["{$key}_colors"] ?? '',
                $image,
                $defaults["{$key}_colors"] ?? ''
            ),
            'perks'        => avtodealer_models_parse_lines($data["{$key}_perks"] ?? ''),
            'image'        => $image,
            'gallery'      => $gallery,
        ];
    }

    $data['cars'] = $cars;
    $data['lang'] = $lang;

    return $data;
}

function avtodealer_save_models($input, $replace = false, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $defaults = avtodealer_models_defaults($lang);
    $strip = ['cars', 'lang'];
    foreach (['car1', 'car2'] as $key) {
        $strip[] = "{$key}_image_url";
        $strip[] = "{$key}_image_default";
        foreach (['g1', 'g2', 'g3', 'g4', 'g5'] as $g) {
            $strip[] = "{$key}_{$g}_url";
            $strip[] = "{$key}_{$g}_default";
        }
    }
    avtodealer_crud_save('avtodealer_models', $defaults, 'avtodealer_sanitize_models', $input, $replace, $lang, $strip);

    return avtodealer_get_models($lang);
}

function avtodealer_delete_models($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    avtodealer_crud_delete('avtodealer_models', $lang);

    return avtodealer_get_models($lang);
}
