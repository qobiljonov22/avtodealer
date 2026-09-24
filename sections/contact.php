<?php
$theme = get_template_directory_uri();
$c = avtodealer_get_contact();
$h = avtodealer_get_header();
$map_query = rawurlencode((string) ($c['map_query'] ?? ''));
?>
<section id="contact" class="bg-[#181818] pb-10 pt-4 sm:pb-12 md:pb-14 lg:pb-16 xl:pb-[72px] 2xl:pb-20 3xl:pb-24" aria-label="Контакты">
    <div class="container mx-auto">
        <div class="relative overflow-hidden rounded-2xl border border-white/10">
            <div class="h-[240px] w-full bg-[#dce3ea] sm:h-[320px] md:h-[400px] lg:h-[460px] xl:h-[520px] 2xl:h-[560px] 3xl:h-[620px]">
                <iframe
                    title="Карта дилерского центра"
                    class="h-full w-full border-0 grayscale-[15%] contrast-[0.95]"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://maps.google.com/maps?q=<?php echo esc_attr($map_query); ?>&z=14&output=embed"></iframe>
            </div>

            <div class="relative z-[1] -mt-16 px-3 pb-3 sm:-mt-20 sm:px-4 md:absolute md:inset-x-4 md:bottom-4 md:mt-0 md:px-0 lg:inset-x-6 lg:bottom-6 xl:inset-x-8 xl:bottom-8 2xl:inset-x-10 3xl:bottom-10">
                <div class="rounded-2xl border border-white/10 bg-[#1A1A1A]/95 p-4 shadow-[0_20px_50px_rgba(0,0,0,0.35)] backdrop-blur sm:p-5 md:flex md:items-center md:justify-between md:gap-6 lg:p-6 xl:gap-8 xl:p-7 2xl:p-8">
                    <div class="grid min-w-0 flex-1 gap-4 sm:grid-cols-3 sm:gap-5 md:gap-6 lg:gap-8">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-white/45 sm:text-[12px]">Адрес</p>
                            <a href="<?php echo esc_url(avtodealer_maps_url($c['address'] ?? '', '')); ?>" target="_blank" rel="noopener" class="mt-1.5 block text-[13px] leading-snug text-white transition-colors hover:text-[#FF6A00] sm:text-[14px] lg:text-[15px]"><?php echo esc_html($c['address']); ?></a>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-white/45 sm:text-[12px]">Телефон</p>
                            <a href="<?php echo esc_url($c['phone_href']); ?>" class="mt-1.5 block text-[16px] font-bold leading-snug text-white sm:text-[17px] lg:text-[18px] xl:text-[19px]"><?php echo esc_html($c['phone']); ?></a>
                            <p class="mt-1 flex items-center gap-1.5 text-[12px] text-[#3DDC84]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#3DDC84]"></span>
                                <?php echo esc_html($h['status'] ?? ''); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-white/45 sm:text-[12px]">Режим работы</p>
                            <p class="mt-1.5 text-[13px] leading-snug text-white sm:text-[14px] lg:text-[15px]"><?php echo esc_html($c['hours']); ?></p>
                        </div>
                    </div>
                    <button type="button" data-open-lead class="mt-4 inline-flex w-full shrink-0 items-center justify-center gap-1.5 rounded-lg bg-[#FF9549] px-5 py-3.5 text-[13px] font-semibold uppercase tracking-wide text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:rounded-xl sm:text-[14px] md:mt-0 md:w-auto md:px-6 lg:px-7 lg:py-4 lg:text-[15px] xl:text-[16px]">
                        <?php echo esc_html($c['callback_label']); ?>
                        <span aria-hidden="true">&gt;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
