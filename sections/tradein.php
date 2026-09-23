<?php
$t = avtodealer_get_tradein();
$title_lines = preg_split('/\r\n|\r|\n/', (string) ($t['title'] ?? ''));
$title_lines = array_values(array_filter(array_map('trim', $title_lines), static function ($l) {
    return $l !== '';
}));
if (count($title_lines) === 1 && preg_match('/^(.*?)\s+(на\s+выгодных\s+условиях)$/iu', $title_lines[0], $m)) {
    $title_lines = [trim($m[1]), trim($m[2])];
}
if (!$title_lines) {
    $title_lines = ['Обмен по Trade-in', 'на выгодных условиях'];
}
?>
<section id="tradein" class="bg-[#181818] py-8 sm:py-10 md:py-12 lg:py-14 xl:py-16 2xl:py-[72px] 3xl:py-20" aria-label="Trade-in">
    <div class="container mx-auto">
        <div class="flex flex-col items-stretch gap-5 sm:gap-6 md:flex-row md:items-center md:gap-8 lg:gap-10 xl:gap-12 2xl:gap-14">
            <div class="w-full shrink-0 md:w-[48%] lg:w-[50%] xl:w-[52%]">
                <img
                    src="<?php echo esc_url($t['image_url']); ?>"
                    alt="<?php echo esc_attr($t['title']); ?>"
                    class="h-auto w-full rounded-xl object-cover sm:rounded-2xl md:aspect-[16/10] md:object-center lg:aspect-[16/9]"
                    width="900"
                    height="520"
                    loading="lazy">
            </div>

            <div class="flex min-w-0 flex-1 flex-col justify-center md:py-2 lg:py-4">
                <h2 class="text-[22px] font-bold uppercase leading-[1.15] tracking-wide text-white sm:text-[26px] md:text-[24px] lg:text-[30px] xl:text-[34px] 2xl:text-[38px] 3xl:text-[42px]">
                    <?php foreach ($title_lines as $i => $line) : ?>
                        <span class="block"><?php echo esc_html($line); ?></span>
                    <?php endforeach; ?>
                </h2>

                <?php if (!empty($t['text'])) : ?>
                    <p class="mt-3 text-[13px] leading-snug text-white/65 sm:text-[14px] lg:mt-4 lg:text-[15px] xl:max-w-[480px]">
                        <?php echo esc_html($t['text']); ?>
                    </p>
                <?php endif; ?>

                <a
                    href="<?php echo esc_url(avtodealer_href($t['cta_url'])); ?>"
                    data-open-lead
                    class="mt-5 inline-flex w-fit max-w-full items-center justify-center gap-1.5 rounded-lg bg-[#FF9549] px-5 py-3 text-[13px] font-semibold text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:mt-6 sm:rounded-xl sm:px-6 sm:py-3.5 sm:text-[14px] md:mt-7 lg:px-7 lg:py-4 lg:text-[15px] xl:text-[16px]">
                    <?php echo esc_html($t['cta_label']); ?>
                    <span aria-hidden="true">&gt;</span>
                </a>
            </div>
        </div>
    </div>
</section>
