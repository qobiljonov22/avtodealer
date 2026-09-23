<?php
$c = avtodealer_get_corporate();
$role_lines = preg_split('/\r\n|\r|\n/', (string) ($c['person_role'] ?? ''));
$role_lines = array_values(array_filter(array_map('trim', $role_lines), static function ($l) {
    return $l !== '';
}));
?>
<section id="corporate" class="bg-[#181818] py-8 sm:py-10 md:py-12 lg:py-14 xl:py-16 2xl:py-[72px] 3xl:py-20" aria-label="Корпоративным клиентам">
    <div class="container mx-auto">
        <div class="flex flex-col gap-6 rounded-2xl border border-white/10 bg-[#1A1A1A] px-5 py-6 sm:flex-row sm:items-center sm:justify-between sm:gap-8 sm:px-7 sm:py-7 md:gap-10 md:px-8 md:py-8 lg:px-10 lg:py-9 xl:gap-12 xl:px-12 xl:py-10 2xl:gap-14 2xl:px-14 2xl:py-12 3xl:px-16 3xl:py-14">
            <div class="min-w-0 max-w-[560px] xl:max-w-[640px] 2xl:max-w-[720px]">
                <h2 class="text-[18px] font-bold uppercase leading-tight tracking-wide text-white sm:text-[20px] md:text-[22px] lg:text-[26px] xl:text-[30px] 2xl:text-[34px] 3xl:text-[38px]">
                    <?php echo esc_html($c['title']); ?>
                </h2>
                <a
                    href="<?php echo esc_url(avtodealer_href($c['cta_url'])); ?>"
                    data-open-lead
                    class="mt-5 inline-flex w-fit max-w-full items-center justify-center gap-1.5 rounded-lg bg-[#FF9549] px-5 py-3 text-[13px] font-semibold text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:mt-6 sm:rounded-xl sm:px-6 sm:py-3.5 sm:text-[14px] md:px-7 md:text-[15px] lg:py-4 xl:text-[16px]">
                    <?php echo esc_html($c['cta_label']); ?>
                    <span aria-hidden="true">&gt;</span>
                </a>
            </div>

            <div class="flex shrink-0 items-center gap-3 sm:gap-4 md:gap-5 xl:gap-6">
                <img
                    src="<?php echo esc_url($c['photo_url']); ?>"
                    alt="<?php echo esc_attr($c['person_name']); ?>"
                    class="h-16 w-16 rounded-full object-cover sm:h-[72px] sm:w-[72px] md:h-20 md:w-20 lg:h-24 lg:w-24 xl:h-28 xl:w-28 3xl:h-32 3xl:w-32"
                    width="112"
                    height="112"
                    loading="lazy">
                <div class="min-w-0">
                    <p class="text-[14px] font-bold uppercase leading-snug text-white sm:text-[15px] md:text-[16px] lg:text-[17px] xl:text-[18px] 2xl:text-[20px]">
                        <?php echo esc_html($c['person_name']); ?>
                    </p>
                    <p class="mt-1 text-[12px] leading-snug text-white/50 sm:text-[13px] lg:text-[14px] 3xl:text-[15px]">
                        <?php
                        foreach ($role_lines as $i => $line) {
                            if ($i > 0) {
                                echo '<br>';
                            }
                            echo esc_html($line);
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
