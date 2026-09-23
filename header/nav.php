<?php
$theme = get_template_directory_uri();
$h = avtodealer_get_header();
?>
<header class="fixed z-[99999] w-full bg-[#141414] text-white transition-colors duration-300">
    <div class="container mx-auto max-w-auto">
        <div class="hidden w-full items-center justify-between gap-4 py-2 lg:flex xl:py-2.5 2xl:py-2.5 3xl:py-3 4xl:py-3.5 5xl:py-4">
            <a href="<?php echo esc_url(avtodealer_href($h['address_url'])); ?>" class="flex min-w-0 items-center gap-1.5 text-[12px] leading-none text-white underline decoration-white/50 underline-offset-2 xl:text-[13px] 2xl:text-[13px] 3xl:gap-2 3xl:text-[14px] 4xl:text-[15px] 5xl:text-[16px]">
                <img src="<?php echo esc_url($theme . '/Font/location.svg'); ?>" alt="" class="h-3.5 w-3.5 shrink-0 xl:h-4 xl:w-4 3xl:h-[18px] 3xl:w-[18px] 4xl:h-5 4xl:w-5">
                <span class="truncate"><?php echo esc_html($h['address']); ?></span>
            </a>
            <nav class="flex shrink-0 items-center gap-5 text-[12px] leading-none text-white/80 xl:gap-6 xl:text-[13px] 2xl:gap-7 2xl:text-[13px] 3xl:gap-8 3xl:text-[14px] 4xl:gap-10 4xl:text-[15px] 5xl:gap-12 5xl:text-[16px]">
                <a href="<?php echo esc_url(avtodealer_href($h['service_url'])); ?>" class="flex items-center gap-1.5 transition-colors hover:text-white 3xl:gap-2">
                    <img src="<?php echo esc_url($theme . '/Font/setting.svg'); ?>" alt="" class="h-3.5 w-3.5 shrink-0 xl:h-4 xl:w-4 3xl:h-[18px] 3xl:w-[18px] 4xl:h-5 4xl:w-5">
                    <?php echo esc_html($h['service_label']); ?>
                </a>
                <a href="<?php echo esc_url(avtodealer_href($h['testdrive_url'])); ?>" class="flex items-center gap-1.5 transition-colors hover:text-white 3xl:gap-2">
                    <img src="<?php echo esc_url($theme . '/Font/speed.svg'); ?>" alt="" class="h-3.5 w-3.5 shrink-0 xl:h-4 xl:w-4 3xl:h-[18px] 3xl:w-[18px] 4xl:h-5 4xl:w-5">
                    <?php echo esc_html($h['testdrive_label']); ?>
                </a>
                <?php echo avtodealer_language_switcher(); ?>
            </nav>
        </div>

        <div class="flex items-center justify-between gap-3 py-3 sm:py-3.5 md:py-4 lg:py-3.5 xl:py-4 2xl:py-[17px] 3xl:py-5 4xl:py-6 5xl:py-7" data-header-bar>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="flex min-w-0 items-center gap-2.5 sm:gap-3 md:gap-3.5 xl:gap-4 2xl:gap-4 3xl:gap-5 4xl:gap-6">
                <img
                    src="<?php echo esc_url($h['logo_url']); ?>"
                    alt="<?php echo esc_attr($h['brand']); ?>"
                    class="h-9 w-auto object-contain sm:h-10 md:h-11 lg:h-12 xl:h-[52px] 2xl:h-14 3xl:h-16 4xl:h-[72px] 5xl:h-20">
                <span class="flex min-w-0 flex-col md:flex-row md:items-center md:gap-2">
                    <span class="block truncate text-[15px] font-bold leading-none sm:text-[17px] md:text-[18px] lg:text-[20px] xl:text-[22px] 2xl:text-[24px] 3xl:text-[28px] 4xl:text-[32px] 5xl:text-[36px]"><?php echo esc_html($h['brand']); ?></span>
                    <span class="mt-1 text-[11px] leading-none text-white/50 md:mt-0 md:text-[12px] xl:text-[13px] 2xl:text-[13px] 3xl:text-[14px] 4xl:text-[16px] 5xl:text-[17px]"><?php echo esc_html($h['subtitle']); ?></span>
                </span>
            </a>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3 md:gap-4 lg:gap-5 xl:gap-8 2xl:gap-9 3xl:gap-10 4xl:gap-12 5xl:gap-14">
                <a href="<?php echo esc_url($h['phone_href']); ?>" class="flex items-center gap-2 xl:gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-white/10 md:hidden">
                        <img src="<?php echo esc_url($theme . '/Font/phone.svg'); ?>" alt="" class="h-4 w-4 brightness-0 invert">
                    </span>
                    <span class="hidden text-right sm:block">
                        <span class="block text-[14px] font-semibold leading-none md:text-[16px] lg:text-[17px] xl:text-[18px] 2xl:text-[19px] 3xl:text-[22px] 4xl:text-[24px] 5xl:text-[26px]"><?php echo esc_html($h['phone']); ?></span>
                        <span class="mt-1 hidden items-center justify-end gap-1.5 text-[11px] leading-none text-[#3DDC84] md:flex xl:text-[12px] 2xl:text-[12px] 3xl:mt-1.5 3xl:text-[13px] 4xl:text-[14px] 5xl:text-[15px]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#3DDC84] 3xl:h-2 3xl:w-2"></span>
                            <?php echo esc_html($h['status']); ?>
                        </span>
                    </span>
                </a>

                <button type="button" data-open-lead class="hidden items-center justify-center gap-2 rounded-lg bg-[#FF9549] px-3 py-2 text-[12px] font-semibold leading-none text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:px-4 sm:text-[13px] md:px-5 md:py-2.5 lg:inline-flex lg:rounded-xl lg:text-[14px] xl:px-6 xl:py-3 xl:text-[15px] 2xl:px-6 2xl:py-3 2xl:text-[15px] 3xl:gap-2.5 3xl:px-7 3xl:py-3.5 3xl:text-[16px] 4xl:px-8 4xl:py-4 4xl:text-[18px] 5xl:px-9 5xl:py-4.5 5xl:text-[20px]">
                    <img src="<?php echo esc_url($theme . '/Font/Mobile_phone.svg'); ?>" alt="" class="h-3.5 w-3.5 sm:h-4 sm:w-4 3xl:h-5 3xl:w-5 5xl:h-6 5xl:w-6">
                    <span class="hidden sm:inline"><?php echo esc_html($h['callback_label']); ?></span>
                </button>

                <div class="lg:hidden">
                    <?php echo avtodealer_language_switcher(); ?>
                </div>

                <button
                    type="button"
                    class="hamburger flex h-9 w-9 items-center justify-center rounded-full bg-white/10 lg:hidden"
                    data-menu-btn
                    aria-expanded="false"
                    aria-label="Меню">
                    <span class="flex flex-col items-center justify-center gap-[5px]" data-menu-icon>
                        <span class="line1 block h-0.5 w-4 rounded-full bg-white"></span>
                        <span class="line2 block h-0.5 w-4 rounded-full bg-white"></span>
                        <span class="line3 block h-0.5 w-4 rounded-full bg-white"></span>
                    </span>
                </button>
            </div>
        </div>

        <div
            class="nav-links border-t border-white/10 bg-[#141414] lg:!hidden"
            data-menu>
            <div class="container mx-auto flex flex-col px-4 pb-10 pt-6 sm:px-5 sm:pt-7 md:px-6 md:pt-8">
                <div class="menu-link flex flex-col gap-1" data-menu-link>
                    <span class="text-[22px] font-bold leading-tight sm:text-[24px] md:text-[26px]"><?php echo esc_html($h['brand']); ?></span>
                    <span class="text-[13px] leading-none text-white/50 sm:text-[14px]"><?php echo esc_html($h['subtitle']); ?></span>
                </div>

                <a href="<?php echo esc_url($h['phone_href']); ?>" class="menu-link mt-6 block text-[28px] font-bold leading-none tracking-tight sm:mt-7 sm:text-[32px] md:text-[36px]" data-menu-link>
                    <?php echo esc_html($h['phone']); ?>
                </a>
                <div class="menu-link mt-2.5 flex items-center gap-2 text-[13px] leading-none text-white/55 sm:text-[14px]" data-menu-link>
                    <span class="h-2 w-2 shrink-0 rounded-full bg-[#3DDC84]"></span>
                    <?php echo esc_html($h['status']); ?>
                </div>

                <div class="menu-link mt-8 flex flex-col gap-4 sm:mt-9 sm:gap-5" data-menu-link>
                    <a href="<?php echo esc_url(avtodealer_href($h['address_url'])); ?>" class="flex items-start gap-2.5 text-[14px] leading-snug text-white/70 sm:text-[15px]">
                        <img src="<?php echo esc_url($theme . '/Font/location.svg'); ?>" alt="" class="mt-0.5 h-4 w-4 shrink-0 opacity-80">
                        <span><?php echo esc_html($h['address']); ?></span>
                    </a>
                    <div class="flex items-center gap-2.5 text-[14px] leading-snug text-white/70 sm:text-[15px]">
                        <img src="<?php echo esc_url($theme . '/Font/clock.svg'); ?>" alt="" class="h-4 w-4 shrink-0 opacity-80 brightness-0 invert">
                        <span><?php echo esc_html(!empty($h['hours']) ? $h['hours'] : 'Ежедневно с 09:00 до 21:00'); ?></span>
                    </div>
                </div>

                <div class="menu-link mt-10 flex flex-col items-center gap-5 sm:mt-12 sm:gap-6" data-menu-link>
                    <button type="button" data-open-lead class="inline-flex w-full max-w-[320px] items-center justify-center gap-2 rounded-xl bg-[#FF9549] px-6 py-3.5 text-[15px] font-semibold leading-none text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:max-w-[360px] sm:py-4 sm:text-[16px]">
                        <img src="<?php echo esc_url($theme . '/Font/Mobile_phone.svg'); ?>" alt="" class="h-4 w-4 brightness-0">
                        <?php echo esc_html($h['callback_label']); ?>
                    </button>

                    <a href="<?php echo esc_url(avtodealer_href($h['service_url'])); ?>" class="flex items-center gap-2 text-[15px] text-white sm:text-[16px]">
                        <img src="<?php echo esc_url($theme . '/Font/setting.svg'); ?>" alt="" class="h-4 w-4 shrink-0">
                        <?php echo esc_html($h['service_label']); ?>
                    </a>
                    <a href="<?php echo esc_url(avtodealer_href($h['testdrive_url'])); ?>" class="flex items-center gap-2 text-[15px] text-white sm:text-[16px]">
                        <img src="<?php echo esc_url($theme . '/Font/speed.svg'); ?>" alt="" class="h-4 w-4 shrink-0">
                        <?php echo esc_html($h['testdrive_label']); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
