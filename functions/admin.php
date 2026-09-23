<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_admin_can_manage()
{
    return current_user_can('manage_options') || current_user_can('edit_theme_options');
}

function avtodealer_admin_menu()
{
    if (!avtodealer_admin_can_manage()) {
        return;
    }

    add_menu_page(
        'Avtodealer Studio',
        'Avtodealer',
        'manage_options',
        'avtodealer-cms',
        'avtodealer_admin_render',
        'dashicons-car',
        3
    );
}
add_action('admin_menu', 'avtodealer_admin_menu');

function avtodealer_admin_bar_link($wp_admin_bar)
{
    if (!is_admin() || !avtodealer_admin_can_manage() || !is_object($wp_admin_bar)) {
        return;
    }

    $wp_admin_bar->add_node([
        'id'    => 'avtodealer-studio',
        'title' => 'Avtodealer Studio',
        'href'  => admin_url('admin.php?page=avtodealer-cms'),
        'meta'  => ['title' => 'Avtodealer kontent pulti'],
    ]);
}
add_action('admin_bar_menu', 'avtodealer_admin_bar_link', 80);

function avtodealer_admin_dashboard_notice()
{
    if (!avtodealer_admin_can_manage()) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'dashboard') {
        return;
    }
    $url = esc_url(admin_url('admin.php?page=avtodealer-cms'));
    echo '<div class="notice notice-info"><p><strong>Avtodealer Studio</strong> — kontent CRUD pulti: ';
    echo '<a href="' . $url . '">ochish</a> · chap menyuda <strong>Avtodealer</strong> yoki yuqorida admin bar.</p></div>';
}
add_action('admin_notices', 'avtodealer_admin_dashboard_notice');

function avtodealer_admin_assets($hook)
{
    if ($hook !== 'toplevel_page_avtodealer-cms') {
        return;
    }

    $uri = get_template_directory_uri();
    $path = get_template_directory();
    $js = $path . '/js/admin-panel.js';
    $ver = is_readable($js) ? (string) filemtime($js) : '1.0.0';

    wp_enqueue_media();
    wp_enqueue_script(
        'tailwindcss',
        $uri . '/js/tailwindcss-browser.js',
        [],
        '4.0.0',
        false
    );
    wp_enqueue_script(
        'avtodealer-admin',
        $uri . '/js/admin-panel.js',
        ['tailwindcss'],
        $ver,
        true
    );
    wp_localize_script('avtodealer-admin', 'avtodealerAdmin', [
        'restUrl'   => rest_url('avtodealer/v1/'),
        'siteUrl'   => home_url('/'),
        'nonce'     => wp_create_nonce('wp_rest'),
        'namespace' => '/avtodealer/v1/',
        'useQuery'  => true,
        'lang'      => function_exists('avtodealer_current_lang') ? avtodealer_current_lang() : 'ru',
        'languages' => function_exists('avtodealer_languages') ? avtodealer_languages() : ['ru', 'uz', 'en'],
        'resources' => array_keys(avtodealer_rest_resources()),
        'adminUrl'  => admin_url('admin.php?page=avtodealer-cms'),
    ]);
}
add_action('admin_enqueue_scripts', 'avtodealer_admin_assets');

function avtodealer_admin_tailwind_theme()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) {
        return;
    }
    $ok = ($screen->id === 'toplevel_page_avtodealer-cms');
    if (!$ok) {
        return;
    }
    ?>
    <style type="text/tailwindcss">
      @theme {
        --color-avto-bg: #0c0f12;
        --color-avto-panel: #14191f;
        --color-avto-line: rgba(255, 255, 255, 0.08);
        --color-avto-muted: #8b95a5;
        --color-avto-accent: #ff6a00;
        --color-avto-accent-2: #ff9549;
        --color-avto-ok: #3ddc84;
        --color-avto-danger: #ff5c5c;
        --color-avto-info: #5cb8ff;
        --color-avto-warn: #f5c542;
      }
      @keyframes avtoIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
      }
    </style>
    <?php
}
add_action('admin_head', 'avtodealer_admin_tailwind_theme', 7);

function avtodealer_admin_render()
{
    if (!avtodealer_admin_can_manage()) {
        wp_die('Ruxsat yo‘q.');
    }
    ?>
    <div id="avto-cms-root" class="wrap !m-0 !mb-10 !max-w-none !ml-[-12px] font-sans text-[#f3f4f6]">
        <div class="overflow-hidden rounded-[22px] border border-white/10 bg-[radial-gradient(1200px_500px_at_10%_-10%,rgba(255,106,0,0.16),transparent_55%),radial-gradient(900px_400px_at_90%_0%,rgba(92,184,255,0.08),transparent_50%),linear-gradient(180deg,#10151a_0%,#0c0f12_40%)] shadow-[0_30px_80px_rgba(0,0,0,0.35)]">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 bg-black/25 px-6 py-5">
                <div class="flex items-center gap-3.5">
                    <div class="grid h-[46px] w-[46px] place-items-center rounded-[14px] bg-gradient-to-br from-[#ff6a00] to-[#ff9549] text-base font-extrabold tracking-wide text-[#111] shadow-[0_10px_30px_rgba(255,106,0,0.35)]" aria-hidden="true">AD</div>
                    <div>
                        <h1 class="!m-0 !p-0 text-[22px] font-bold tracking-tight text-white">Avtodealer Studio</h1>
                        <p class="m-0 mt-1 text-[13px] text-[#8b95a5]">Header → Models → Footer · GET / POST / PUT / DELETE · Leads</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-3 py-2 text-xs font-semibold text-white no-underline transition hover:border-[#ff9549]/50 hover:text-[#ff9549]">
                        Saytni ochish ↗
                    </a>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-3 py-2 font-mono text-xs text-[#8b95a5]">
                        <strong class="font-bold text-[#ff9549]">API</strong>
                        <span>/avtodealer/v1</span>
                    </div>
                </div>
            </div>
            <div class="p-[18px] pb-6">
                <div id="avto-cms-panel">
                    <div class="px-5 py-12 text-center text-[#8b95a5]">Studio yuklanmoqda…</div>
                </div>
            </div>
        </div>
        <div id="avto-cms-toast" class="pointer-events-none fixed bottom-6 right-6 z-[100000] min-w-[240px] max-w-[360px] translate-y-5 rounded-[14px] border border-white/10 bg-[#151b22] px-4 py-3.5 text-white opacity-0 shadow-[0_20px_50px_rgba(0,0,0,0.4)] transition duration-250" role="status" aria-live="polite"></div>
    </div>
    <?php
}
