<?php
$theme = get_template_directory_uri();
$f = avtodealer_get_footer();
$legal = trim((string) (($f['legal1'] ?? '') . "\n\n" . ($f['legal2'] ?? '')));
$icon_orange = 'filter: invert(62%) sepia(68%) saturate(1200%) hue-rotate(339deg) brightness(103%) contrast(101%);';
?>
<div class="border-t border-white/10 bg-[#121212]">
    <div class="container mx-auto py-8 sm:py-10 md:py-12 lg:py-14 xl:py-16 2xl:py-[72px] 3xl:py-20">
        <?php if ($legal !== '') : ?>
            <details class="group" data-disclaimer open>
                <summary class="flex cursor-pointer list-none items-center gap-2 text-[14px] font-semibold text-white sm:text-[15px] md:text-[16px] [&::-webkit-details-marker]:hidden">
                    <span>Дисклеймер</span>
                    <svg class="h-3.5 w-3.5 shrink-0 text-white/60 transition group-open:rotate-180 sm:h-4 sm:w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M3 6l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </summary>
                <div class="mt-4 max-w-[1920px] space-y-3 text-[11px] leading-relaxed text-white/40 sm:mt-5 sm:space-y-4 sm:text-[12px] lg:text-[13px] lg:leading-[1.55]">
                    <?php if (!empty($f['legal1'])) : ?>
                        <p><?php echo esc_html($f['legal1']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($f['legal2'])) : ?>
                        <p><?php echo esc_html($f['legal2']); ?></p>
                    <?php endif; ?>
                </div>
            </details>
        <?php endif; ?>

        <div class="mt-8 grid grid-cols-1 gap-8 border-t border-white/10 pt-8 sm:mt-10 sm:gap-10 sm:pt-10 md:mt-12 md:pt-12 lg:grid-cols-2 lg:gap-12 xl:mt-14 xl:gap-16 2xl:gap-20">
            <div class="min-w-0">
                <p class="text-[16px] font-bold leading-tight text-white sm:text-[18px] md:text-[20px] lg:text-[22px] xl:text-[24px]">
                    <?php echo esc_html($f['brand']); ?>
                </p>
                <?php if (!empty($f['subtitle'])) : ?>
                    <p class="mt-1 text-[12px] text-white/50 sm:text-[13px] lg:text-[14px]">
                        <?php echo esc_html($f['subtitle']); ?>
                    </p>
                <?php endif; ?>

                <a href="<?php echo esc_url(avtodealer_maps_url($f['address'] ?? '', '')); ?>" target="_blank" rel="noopener" class="mt-4 flex items-start gap-2 text-[12px] leading-snug text-white transition-colors hover:text-[#FF9549] sm:mt-5 sm:text-[13px] lg:text-[14px] xl:max-w-[480px]">
                    <img src="<?php echo esc_url($theme . '/Font/location.svg'); ?>" alt="" class="mt-0.5 h-4 w-4 shrink-0" style="<?php echo esc_attr($icon_orange); ?>">
                    <span><?php echo esc_html($f['address']); ?></span>
                </a>

                <?php if (!empty($f['copy_note'])) : ?>
                    <p class="mt-4 text-[11px] leading-relaxed text-white/35 sm:mt-5 sm:text-[12px] lg:max-w-[520px] lg:text-[13px]">
                        <?php echo esc_html($f['copy_note']); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="flex min-w-0 flex-col lg:items-end">
                <a href="<?php echo esc_url($f['phone_href']); ?>" class="text-[24px] font-bold leading-none tracking-tight text-white sm:text-[26px] md:text-[28px] lg:text-[30px] xl:text-[32px]">
                    <?php echo esc_html($f['phone']); ?>
                </a>
                <?php if (!empty($f['status'])) : ?>
                    <p class="mt-2 flex items-center gap-1.5 text-[12px] text-white/55 sm:text-[13px] lg:justify-end">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#3DDC84]"></span>
                        <?php echo esc_html($f['status']); ?>
                    </p>
                <?php endif; ?>

                <button type="button" data-open-lead class="mt-4 inline-flex w-full max-w-[320px] items-center justify-center gap-2 rounded-lg bg-[#FF9549] px-5 py-3.5 text-[13px] font-semibold text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:mt-5 sm:w-auto sm:rounded-xl sm:px-6 sm:text-[14px] md:py-4 lg:text-[15px]">
                    <img src="<?php echo esc_url($theme . '/Font/Mobile_phone.svg'); ?>" alt="" class="h-4 w-4 brightness-0 lg:h-5 lg:w-5">
                    <?php echo esc_html($f['callback_label']); ?>
                </button>

                <div class="mt-4 flex w-full flex-col gap-3 sm:mt-5 lg:items-end">
                    <a href="<?php echo esc_url(avtodealer_href($f['service_url'])); ?>" class="flex items-center gap-2 text-[13px] font-semibold text-white transition-colors hover:text-[#FF9549] sm:text-[14px]">
                        <img src="<?php echo esc_url($theme . '/Font/setting.svg'); ?>" alt="" class="h-4 w-4 shrink-0" style="<?php echo esc_attr($icon_orange); ?>">
                        <?php echo esc_html($f['service_label']); ?>
                    </a>
                    <a href="<?php echo esc_url(avtodealer_href($f['testdrive_url'])); ?>" class="flex items-center gap-2 text-[13px] font-semibold text-white transition-colors hover:text-[#FF9549] sm:text-[14px]">
                        <img src="<?php echo esc_url($theme . '/Font/speed.svg'); ?>" alt="" class="h-4 w-4 shrink-0" style="<?php echo esc_attr($icon_orange); ?>">
                        <?php echo esc_html($f['testdrive_label']); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 border-t border-white/10 pt-5 text-[11px] text-white/40 sm:mt-10 sm:flex-row sm:items-center sm:justify-between sm:pt-6 md:mt-12 lg:text-[12px]">
            <p>© <?php echo esc_html(gmdate('Y')); ?>, <?php echo esc_html($f['brand']); ?></p>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="#disclaimer" class="transition-colors hover:text-white" data-open-disclaimer>Правовая информация</a>
                <a href="#disclaimer" class="transition-colors hover:text-white" data-open-disclaimer>Условия акции</a>
            </div>
        </div>
    </div>
</div>
