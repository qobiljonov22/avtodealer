<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_languages()
{
    if (function_exists('pll_languages_list')) {
        $slugs = pll_languages_list(['fields' => 'slug']);
        if (is_array($slugs) && $slugs) {
            return array_values($slugs);
        }
    }

    return ['ru', 'uz', 'en'];
}

function avtodealer_default_lang()
{
    if (function_exists('pll_default_language')) {
        $lang = pll_default_language('slug');
        if ($lang) {
            return $lang;
        }
    }

    return 'ru';
}

function avtodealer_current_lang($request = null)
{
    $allowed = avtodealer_languages();

    if ($request instanceof WP_REST_Request) {
        $lang = sanitize_key((string) $request->get_param('lang'));
        if ($lang && in_array($lang, $allowed, true)) {
            return $lang;
        }
    }

    if (isset($_GET['lang'])) {
        $lang = sanitize_key((string) wp_unslash($_GET['lang']));
        if ($lang && in_array($lang, $allowed, true)) {
            return $lang;
        }
    }

    if (function_exists('pll_current_language')) {
        $lang = pll_current_language('slug');
        if ($lang && in_array($lang, $allowed, true)) {
            return $lang;
        }
    }

    return avtodealer_default_lang();
}

function avtodealer_option_key($base, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();

    return $base . '_' . $lang;
}

function avtodealer_get_option_lang($base, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $key = avtodealer_option_key($base, $lang);
    $stored = get_option($key, null);

    if (!is_array($stored)) {
        $legacy = get_option($base, []);
        if (is_array($legacy) && $legacy && $lang === avtodealer_default_lang()) {
            return $legacy;
        }

        return [];
    }

    return $stored;
}

function avtodealer_update_option_lang($base, $value, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $key = avtodealer_option_key($base, $lang);
    update_option($key, $value, false);

    return $key;
}

function avtodealer_delete_option_lang($base, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    delete_option(avtodealer_option_key($base, $lang));
}

function avtodealer_lang_meta($lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();

    return [
        'lang'       => $lang,
        'languages'  => avtodealer_languages(),
        'default'    => avtodealer_default_lang(),
        'polylang'   => function_exists('pll_current_language'),
    ];
}

function avtodealer_language_switcher()
{
    // Bitta ko‘rinadigan control: faqat 🇷🇺 RU (boshqa bayroqlar yo‘q).
    $ru_url = trailingslashit(home_url('/'));

    if (function_exists('pll_home_url')) {
        $pll = pll_home_url('ru');
        if (is_string($pll) && $pll !== '') {
            $ru_url = $pll;
        }
    } else {
        $ru_url = add_query_arg('lang', 'ru', $ru_url);
    }

    ob_start();
    ?>
    <div class="avto-lang relative shrink-0" data-lang-switcher>
        <a
            href="<?php echo esc_url($ru_url); ?>"
            class="avto-lang-ru inline-flex h-8 items-center gap-1.5 rounded border border-white/20 bg-[#1a1a1a] px-2.5 text-[12px] font-semibold tracking-wide text-white no-underline hover:border-[#FF9549]"
            data-lang-ru
            aria-label="Русский"
            title="Русский">
            <span class="text-[14px] leading-none" aria-hidden="true">🇷🇺</span>
            <span>RU</span>
        </a>
    </div>
    <?php
    return (string) ob_get_clean();
}

/**
 * Polylang default flag switcherlarini yashirish — faqat bitta RU select.
 */
function avtodealer_hide_polylang_default_switcher()
{
    ?>
    <style id="avto-hide-pll-flags">
      /* Polylang / boshqa til switcherlarini to‘liq yashirish — faqat .avto-lang-ru */
      body:not(.wp-admin) .widget_polylang,
      body:not(.wp-admin) .pll-switcher,
      body:not(.wp-admin) .pll-switcher-flags,
      body:not(.wp-admin) ul.pll-container,
      body:not(.wp-admin) .menu-item-type-pll_lang_switcher,
      body:not(.wp-admin) nav.languages,
      body:not(.wp-admin) ul.language-switcher,
      body:not(.wp-admin) .lang-item,
      body:not(.wp-admin) .lang-item img.pll-flag,
      body:not(.wp-admin) #wpadminbar li[id*="languages"],
      body:not(.wp-admin) #wpadminbar .pll-flag {
        display: none !important;
      }
    </style>
    <?php
}
add_action('wp_head', 'avtodealer_hide_polylang_default_switcher', 5);

function avtodealer_remove_pll_admin_bar_flags($wp_admin_bar)
{
    if (!is_object($wp_admin_bar)) {
        return;
    }
    $wp_admin_bar->remove_node('languages');
    $wp_admin_bar->remove_node('wp-admin-bar-languages');
}
add_action('admin_bar_menu', 'avtodealer_remove_pll_admin_bar_flags', 999);

function avtodealer_defaults_by_lang($resource, $lang = null)
{
    $lang = $lang ?: avtodealer_current_lang();
    $map = [
        'header' => [
            'ru' => null,
            'uz' => [
                'address'         => 'Yaroslavskoye shosse, 2 V uy, 3-bino (MKAD, 95 km)',
                'service_label'   => 'Servisga yozilish',
                'testdrive_label' => 'Test-drayv',
                'brand'           => 'AVTORUS TANK',
                'subtitle'        => 'Rasmiy diler',
                'status'          => 'Aloqadamiz',
                'hours'           => 'Har kuni 09:00 dan 21:00 gacha',
                'callback_label'  => 'Qo‘ng‘iroq buyurtma',
            ],
            'en' => [
                'address'         => 'Yaroslavskoye Highway, possession 2 V, building 3 (MKAD, 95 km)',
                'service_label'   => 'Book a service',
                'testdrive_label' => 'Test drive',
                'brand'           => 'AVTORUS TANK',
                'subtitle'        => 'Official dealer',
                'status'          => 'We are online',
                'hours'           => 'Daily from 09:00 to 21:00',
                'callback_label'  => 'Request a call',
            ],
        ],
        'hero' => [
            'ru' => null,
            'uz' => [
                'eyebrow'   => 'Istalgan shartlarni yaxshilaymiz',
                'title'     => 'TANK 500',
                'subtitle'  => 'Faqat 5 ta avtomobil qoldi!',
                'cta_label' => 'Taklif olish',
            ],
            'en' => [
                'eyebrow'   => 'We will improve any terms',
                'title'     => 'TANK 500',
                'subtitle'  => 'Only 5 cars left!',
                'cta_label' => 'Get an offer',
            ],
        ],
        'catalog' => [
            'ru' => null,
            'uz' => [
                'offer_title'     => 'Maxsus taklif muddati:',
                'offer_cta_label' => 'Foyda bilan narxni bilish',
                'feature1_title'  => 'Rasmiy diler',
                'feature1_text'   => 'Yuqori sifatli xizmatni kafolatlaymiz.',
                'feature2_title'  => '1 kunda avto xarid',
                'feature2_text'   => 'Qulay xarid jarayoni, barcha hujjatlar bilan.',
                'feature3_title'  => 'Barcha komplektatsiyalar bor',
                'feature3_text'   => 'Keng tanlov, to‘liq hujjatlar paketi bilan.',
                'promo_title'     => 'Bugun avtomobilni bron qiling va qo‘shimcha 100 000 ₽ foyda oling',
            ],
            'en' => [
                'offer_title'     => 'Special offer valid until:',
                'offer_cta_label' => 'Get the price with benefits',
                'feature1_title'  => 'Official dealer',
                'feature1_text'   => 'We guarantee high-quality service.',
                'feature2_title'  => 'Buy a car in 1 day',
                'feature2_text'   => 'Convenient purchase process, including paperwork.',
                'feature3_title'  => 'All trims in stock',
                'feature3_text'   => 'Wide selection of trims with a full document package.',
                'promo_title'     => 'Book a car today and get an extra benefit of 100,000 ₽',
            ],
        ],
    ];

    return $map[$resource][$lang] ?? null;
}
