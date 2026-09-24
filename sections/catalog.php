<?php
$theme = get_template_directory_uri();
$c = avtodealer_get_catalog();
$catalog_mode = avtodealer_page_context('catalog_mode', 'full');
// Model pages always use offer bar only (never home dual-car promo)
if (
    (function_exists('avtodealer_is_tank300_page') && avtodealer_is_tank300_page())
    || (function_exists('avtodealer_is_tank500_page') && avtodealer_is_tank500_page())
    || in_array((string) avtodealer_page_context('slug', ''), ['tank-300', 'tank-500'], true)
) {
    $catalog_mode = 'offer';
}
$is_offer = ($catalog_mode === 'offer');
?>
<section id="catalog" class="catalog bg-[#141414] <?php echo $is_offer ? 'py-6 sm:py-7 md:py-8 lg:py-9 xl:py-10 2xl:py-12' : 'py-8 sm:py-10 md:py-12 lg:py-14 xl:py-16 2xl:py-[72px] 3xl:py-20'; ?>" aria-label="<?php echo esc_attr($c['offer_title'] ?? ''); ?>">
    <div class="container mx-auto">
        <div class="flex flex-col items-center gap-5 rounded-2xl border border-white/10 bg-[#1A1A1A] px-4 py-5 sm:gap-6 sm:px-6 sm:py-6 md:gap-6 lg:flex-row lg:items-center lg:justify-between lg:gap-5 lg:px-8 lg:py-7 xl:min-h-[130px] xl:gap-6 2xl:px-10 2xl:py-8 3xl:min-h-[150px]">
            <p class="w-full text-center text-[13px] font-bold uppercase leading-tight tracking-wide text-white sm:text-[15px] md:text-[16px] lg:w-auto lg:max-w-[180px] lg:shrink-0 lg:text-left lg:text-[16px] xl:max-w-[220px] xl:text-[18px] 2xl:max-w-[260px] 2xl:text-[20px] 3xl:text-[22px]">
                <?php echo esc_html($c['offer_title']); ?>
            </p>

            <div
                class="flex max-w-full flex-wrap items-center justify-center gap-1.5 sm:gap-2.5 md:gap-3 lg:flex-nowrap lg:gap-3 xl:gap-4"
                data-countdown
                data-countdown-end="<?php echo esc_attr($c['countdown_end']); ?>">
                <?php
                $units = [
                    ['key' => 'days', 'label' => $c['countdown_days'] ?? '', 'max' => 7],
                    ['key' => 'hours', 'label' => $c['countdown_hours'] ?? '', 'max' => 24],
                    ['key' => 'minutes', 'label' => $c['countdown_mins'] ?? '', 'max' => 60],
                    ['key' => 'seconds', 'label' => $c['countdown_secs'] ?? '', 'max' => 60],
                ];
                foreach ($units as $i => $unit) :
                    ?>
                    <?php if ($i > 0) : ?>
                        <span class="hidden flex-col gap-1 px-0.5 text-white/70 sm:flex" aria-hidden="true">
                            <span class="block h-1 w-1 rounded-full bg-current"></span>
                            <span class="block h-1 w-1 rounded-full bg-current"></span>
                        </span>
                    <?php endif; ?>
                    <div class="relative grid h-[64px] w-[64px] place-items-center sm:h-[72px] sm:w-[72px] md:h-[76px] md:w-[76px] lg:h-[84px] lg:w-[84px] xl:h-[88px] xl:w-[88px] 2xl:h-[92px] 2xl:w-[92px] 3xl:h-[100px] 3xl:w-[100px]" data-unit="<?php echo esc_attr($unit['key']); ?>" data-max="<?php echo (int) $unit['max']; ?>">
                        <svg class="absolute inset-0 h-full w-full -rotate-90" viewBox="0 0 88 88" aria-hidden="true">
                            <circle cx="44" cy="44" r="38" fill="none" stroke="rgba(255,255,255,0.14)" stroke-width="3"></circle>
                            <circle
                                data-ring
                                cx="44"
                                cy="44"
                                r="38"
                                fill="none"
                                stroke="#FF6A00"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="0"></circle>
                        </svg>
                        <div class="relative z-[1] flex flex-col items-center leading-none">
                            <span class="text-[20px] font-bold tabular-nums text-white sm:text-[22px] md:text-[24px] lg:text-[28px] xl:text-[30px] 3xl:text-[32px]" data-value>0</span>
                            <span class="mt-1 text-[9px] uppercase text-white/55 sm:text-[10px] lg:text-[11px] 3xl:text-[12px]"><?php echo esc_html($unit['label']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="<?php echo esc_url(avtodealer_href($c['offer_cta_url'])); ?>" data-open-lead class="inline-flex w-full max-w-full items-center justify-center gap-2 rounded-xl bg-[#FF9549] px-5 py-3.5 text-[13px] font-semibold leading-none text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:text-[14px] md:text-[15px] lg:w-auto lg:shrink-0 lg:px-7 lg:py-4 lg:text-[15px] xl:px-8 xl:text-[16px] 2xl:py-[18px] 3xl:text-[17px]">
                <?php echo esc_html($c['offer_cta_label'] ?? ''); ?>
                <span aria-hidden="true">&gt;</span>
            </a>
        </div>

        <?php if (!$is_offer) : ?>
            <div class="mt-4 grid grid-cols-1 gap-2.5 sm:mt-5 sm:gap-3 md:mt-6 md:grid-cols-3 md:gap-4 lg:mt-7 lg:gap-5 xl:gap-6 2xl:mt-8">
                <?php
                $features = [
                    ['icon' => 'award-03.svg', 'title' => $c['feature1_title'], 'text' => $c['feature1_text']],
                    ['icon' => 'clock-rewind.svg', 'title' => $c['feature2_title'], 'text' => $c['feature2_text']],
                    ['icon' => 'clipboard-check.svg', 'title' => $c['feature3_title'], 'text' => $c['feature3_text']],
                ];
                foreach ($features as $f) :
                    ?>
                    <article class="flex items-center gap-3 rounded-xl border border-white/10 bg-[#1A1A1A] px-4 py-3.5 sm:gap-4 sm:rounded-2xl sm:px-5 sm:py-4 md:items-start md:py-5 lg:px-6 lg:py-6 xl:gap-5">
                        <img src="<?php echo esc_url($theme . '/Font/' . $f['icon']); ?>" alt="" class="h-7 w-7 shrink-0 sm:h-8 sm:w-8 lg:h-10 lg:w-10 3xl:h-11 3xl:w-11">
                        <div class="min-w-0">
                            <h3 class="text-[12px] font-bold uppercase leading-snug text-white sm:text-[14px] lg:text-[15px] xl:text-[16px] 3xl:text-[17px]"><?php echo esc_html($f['title']); ?></h3>
                            <p class="mt-1.5 hidden text-[12px] leading-snug text-white/55 md:block sm:text-[13px] lg:text-[14px]"><?php echo esc_html($f['text']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="mt-10 flex flex-col items-center sm:mt-12 md:mt-14 lg:mt-16 xl:mt-[72px] 3xl:mt-20">
                <h2 class="mx-auto max-w-[280px] text-center text-[16px] font-bold uppercase leading-[1.25] tracking-wide text-white sm:max-w-[420px] sm:text-[22px] md:max-w-[560px] md:text-[26px] lg:max-w-[720px] lg:text-[30px] xl:max-w-[820px] xl:text-[34px] 2xl:max-w-[900px] 2xl:text-[38px] 3xl:text-[40px]">
                    <?php echo esc_html($c['promo_title']); ?>
                </h2>

                <?php /* Figma 1:1 — centered pair: 300 (orange) + label, 500 (champagne) + label */ ?>
                <div class="mt-8 flex w-full items-end justify-center gap-8 sm:mt-10 sm:gap-12 md:mt-12 md:gap-16 lg:mt-14 lg:gap-[72px] xl:gap-20 2xl:gap-24">
                    <a href="<?php echo esc_url($c['car1_url']); ?>" class="group flex w-[42%] max-w-[420px] flex-col items-center sm:w-auto sm:max-w-[460px] lg:max-w-[500px]">
                        <img
                            src="<?php echo esc_url($c['car1_image_url']); ?>"
                            alt="<?php echo esc_attr($c['car1_title']); ?>"
                            class="h-auto w-full max-w-[380px] object-contain object-bottom drop-shadow-[0_12px_24px_rgba(0,0,0,0.45)] transition-transform duration-300 group-hover:scale-[1.02] sm:max-w-[420px] lg:max-w-[480px] xl:max-w-[520px]"
                            width="520"
                            height="240"
                            decoding="async">
                        <span class="mt-2.5 text-center text-[12px] font-semibold uppercase tracking-[0.08em] text-white sm:mt-3 sm:text-[13px] md:text-[14px] lg:mt-3.5 lg:text-[15px] xl:text-[16px]">
                            <?php echo esc_html($c['car1_title']); ?>
                        </span>
                    </a>
                    <a href="<?php echo esc_url($c['car2_url']); ?>" class="group flex w-[42%] max-w-[420px] flex-col items-center sm:w-auto sm:max-w-[460px] lg:max-w-[500px]">
                        <img
                            src="<?php echo esc_url($c['car2_image_url']); ?>"
                            alt="<?php echo esc_attr($c['car2_title']); ?>"
                            class="h-auto w-full max-w-[380px] object-contain object-bottom drop-shadow-[0_12px_24px_rgba(0,0,0,0.45)] transition-transform duration-300 group-hover:scale-[1.02] sm:max-w-[420px] lg:max-w-[480px] xl:max-w-[520px]"
                            width="520"
                            height="240"
                            decoding="async">
                        <span class="mt-2.5 text-center text-[12px] font-semibold uppercase tracking-[0.08em] text-white sm:mt-3 sm:text-[13px] md:text-[14px] lg:mt-3.5 lg:text-[15px] xl:text-[16px]">
                            <?php echo esc_html($c['car2_title']); ?>
                        </span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
