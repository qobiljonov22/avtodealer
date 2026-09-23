<?php
$variant = (string) avtodealer_page_context('hero', 'home');
$slug = (string) avtodealer_page_context('slug', '');

if ($slug === 'tank-300' || (function_exists('avtodealer_is_tank300_page') && avtodealer_is_tank300_page())) {
    $variant = 'tank300';
} elseif ($slug === 'tank-500' || (function_exists('avtodealer_is_tank500_page') && avtodealer_is_tank500_page())) {
    $variant = 'tank500';
} elseif ($slug === 'home' || is_front_page()) {
    $variant = 'home';
}

if ($variant === 'tank300' && function_exists('avtodealer_get_hero_tank300')) {
    $h = avtodealer_get_hero_tank300();
} elseif ($variant === 'tank500' && function_exists('avtodealer_get_hero_tank500')) {
    $h = avtodealer_get_hero_tank500();
} else {
    $h = avtodealer_get_hero();
}

// Normalize keys used in markup
$eyebrow = (string) ($h['eyebrow'] ?? '');
$title = (string) ($h['title'] ?? '');
$subtitle = (string) ($h['subtitle'] ?? '');
$cta_label = (string) ($h['cta_label'] ?? $h['cta_label'] ?? 'Получить предложение');
$cta_url = (string) ($h['cta_url'] ?? '#credit');
$image_url = (string) ($h['image_url'] ?? '');
$image_mobile_url = (string) ($h['image_mobile_url'] ?? $h['image_mobile_url'] ?? $image_url);
?>
<section
    id="hero"
    class="hero relative flex w-full flex-col overflow-hidden bg-[#141414]
        sm:bg-transparent
        <?php echo $variant === 'tank500'
            ? 'sm:min-h-[480px] md:min-h-[520px] lg:min-h-[580px] xl:min-h-[640px] 2xl:min-h-[700px] 3xl:min-h-[740px] 4xl:min-h-[780px] 5xl:min-h-[820px]'
            : 'sm:min-h-[calc(100vh-68px)] md:min-h-[calc(100vh-76px)] lg:min-h-[calc(100vh-104px)] xl:min-h-[calc(100vh-112px)] 2xl:min-h-[calc(100vh-120px)] 3xl:min-h-[calc(100vh-144px)] 4xl:min-h-[calc(100vh-168px)] 5xl:min-h-[calc(100vh-184px)]'; ?>"
    data-hero-variant="<?php echo esc_attr($variant); ?>"
    aria-label="Hero">
    <div class="relative w-full shrink-0 sm:hidden">
        <img
            src="<?php echo esc_url($image_mobile_url); ?>"
            alt="<?php echo esc_attr($title); ?>"
            class="block h-auto w-full object-cover"
            width="375"
            height="300"
            decoding="async">
    </div>

    <div
        class="absolute inset-0 hidden bg-cover bg-center bg-no-repeat sm:block"
        style="background-image: url('<?php echo esc_url($image_url); ?>');"
        aria-hidden="true">
    </div>

    <div class="relative z-10 flex flex-1 flex-col bg-[#141414] px-4 py-8 sm:absolute sm:inset-0 sm:flex sm:items-center sm:justify-center sm:bg-transparent sm:px-0 sm:py-0">
        <div class="container relative z-10 w-full">
            <div class="max-w-[520px] text-white xl:max-w-[600px] 2xl:max-w-[640px] 3xl:max-w-[680px]">
                <?php if ($eyebrow !== '') : ?>
                    <p class="m-0 text-[14px] leading-none text-white sm:text-[15px] md:text-[16px] xl:text-[17px] 3xl:text-[18px]">
                        <?php echo esc_html($eyebrow); ?>
                    </p>
                <?php endif; ?>
                <h1 class="m-0 mt-3 text-[40px] font-bold leading-none tracking-tight text-white sm:mt-4 sm:text-[52px] md:text-[60px] lg:text-[64px] xl:text-[72px] 2xl:text-[80px] 3xl:mt-5 3xl:text-[88px] 4xl:text-[96px]">
                    <?php echo esc_html($title); ?>
                </h1>
                <?php if ($subtitle !== '') : ?>
                    <p class="m-0 mt-3 text-[16px] leading-snug text-white sm:mt-4 sm:text-[18px] md:text-[20px] xl:text-[22px] 3xl:text-[24px]">
                        <?php echo esc_html($subtitle); ?>
                    </p>
                <?php endif; ?>
                <a
                    href="<?php echo esc_url(avtodealer_href($cta_url)); ?>"
                    data-open-lead
                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#FF9549] px-5 py-3.5 text-[14px] font-semibold leading-none text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:mt-6 sm:w-auto sm:rounded-lg sm:px-6 sm:py-3.5 sm:text-[15px] md:text-[16px] xl:mt-7 xl:px-7 xl:py-4 3xl:mt-8 3xl:text-[17px]">
                    <?php echo esc_html($cta_label); ?>
                    <span aria-hidden="true">&gt;</span>
                </a>
            </div>
        </div>
    </div>
</section>
