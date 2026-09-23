<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style id="avto-page-loader-css">
      #avto-page-loader{
        position:fixed;inset:0;z-index:2147483000;
        display:flex;align-items:center;justify-content:center;
        background:#0e0e0e;
        transition:opacity .55s ease,visibility .55s ease;
      }
      #avto-page-loader.is-done{
        opacity:0;visibility:hidden;pointer-events:none;
      }
      #avto-page-loader .avto-loader-inner{
        display:flex;flex-direction:column;align-items:center;gap:22px;
        text-align:center;padding:24px;
      }
      #avto-page-loader .avto-loader-mark{
        position:relative;width:72px;height:72px;
      }
      #avto-page-loader .avto-loader-ring{
        position:absolute;inset:0;border-radius:50%;
        border:2px solid rgba(255,255,255,.08);
        border-top-color:#ff6a00;
        animation:avtoSpin .85s linear infinite;
      }
      #avto-page-loader .avto-loader-core{
        position:absolute;inset:14px;border-radius:50%;
        background:linear-gradient(145deg,#1a1a1a,#0a0a0a);
        box-shadow:inset 0 0 0 1px rgba(255,255,255,.06);
        display:grid;place-items:center;
        font:700 13px/1 system-ui,sans-serif;letter-spacing:.08em;color:#ff9549;
      }
      #avto-page-loader .avto-loader-brand{
        margin:0;font:700 clamp(18px,3.2vw,28px)/1.15 system-ui,sans-serif;
        color:#fff;letter-spacing:.02em;
      }
      #avto-page-loader .avto-loader-sub{
        margin:0;font:500 13px/1.4 system-ui,sans-serif;color:rgba(255,255,255,.45);
      }
      #avto-page-loader .avto-loader-bar{
        width:min(220px,56vw);height:2px;border-radius:999px;overflow:hidden;
        background:rgba(255,255,255,.08);
      }
      #avto-page-loader .avto-loader-bar > span{
        display:block;height:100%;width:35%;
        background:linear-gradient(90deg,#ff6a00,#ff9549);
        border-radius:inherit;
        animation:avtoBar 1.1s ease-in-out infinite;
      }
      @keyframes avtoSpin{to{transform:rotate(360deg)}}
      @keyframes avtoBar{
        0%{transform:translateX(-120%)}
        100%{transform:translateX(320%)}
      }
      html.avto-loading,html.avto-loading body{overflow:hidden}
    </style>
    <script>document.documentElement.classList.add('avto-loading');</script>
</head>
<body <?php body_class('bg-[#141414] text-white'); ?>>
<?php wp_body_open(); ?>
<?php
$avto_loader_brand = 'АВТОРУСЬ TANK';
if (function_exists('avtodealer_get_header')) {
    $avto_h = avtodealer_get_header();
    if (!empty($avto_h['brand'])) {
        $avto_loader_brand = $avto_h['brand'];
    }
}
?>
<div id="avto-page-loader" role="status" aria-live="polite" aria-busy="true">
    <div class="avto-loader-inner">
        <div class="avto-loader-mark" aria-hidden="true">
            <div class="avto-loader-ring"></div>
            <div class="avto-loader-core">AD</div>
        </div>
        <p class="avto-loader-brand"><?php echo esc_html($avto_loader_brand); ?></p>
        <p class="avto-loader-sub">Yuklanmoqda…</p>
        <div class="avto-loader-bar" aria-hidden="true"><span></span></div>
    </div>
</div>

<?php get_template_part('header/nav'); ?>

<main class="bg-[#141414] pt-[60px] sm:pt-[68px] md:pt-[76px] lg:pt-[140px] xl:pt-[148px] 2xl:pt-[156px] 3xl:pt-[168px] 4xl:pt-[184px] 5xl:pt-[200px]">
