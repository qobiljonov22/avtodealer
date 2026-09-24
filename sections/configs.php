<?php
$theme = get_template_directory_uri();
$c = avtodealer_get_configs();
$h = avtodealer_get_header();
$items = is_array($c['items'] ?? null) ? $c['items'] : [];
$configs_filter = (string) avtodealer_page_context('configs_filter', '');
if ($configs_filter !== '') {
    $items = array_values(array_filter($items, static function ($trim) use ($configs_filter) {
        return stripos((string) ($trim['title'] ?? ''), $configs_filter) !== false;
    }));
}
$visible = max(1, min(6, absint($c['visible_count'] ?? 3)));
$perk_icons = ['tag-01.svg', 'padarak.svg'];

$parse_opts = static function ($raw) {
    $lines = preg_split('/\r\n|\r|\n/', (string) $raw);
    $lines = array_values(array_filter(array_map('trim', $lines), static function ($l) {
        return $l !== '';
    }));

    return $lines ?: [''];
};
?>
<section id="configs" class="bg-[#141414] py-10 sm:py-12 md:py-14 lg:py-16 xl:py-[72px] 2xl:py-20 3xl:py-24" aria-label="Комплектации">
    <div class="container mx-auto">
        <h2 class="text-center text-[22px] font-bold uppercase leading-tight tracking-wide text-white sm:text-[26px] md:text-[30px] lg:text-[34px] xl:text-[38px] 2xl:text-[42px] 3xl:text-[46px]">
            <?php echo esc_html($c['title']); ?>
        </h2>

        <div class="mt-6 grid grid-cols-1 gap-3 sm:mt-8 sm:grid-cols-2 sm:gap-4 md:mt-9 md:gap-5 lg:mt-10 xl:gap-6 <?php echo $configs_filter !== '' ? '' : 'lg:grid-cols-3'; ?>">
            <?php
            $filters = [
                [$c['filter1_label'], $c['filter1_options']],
                [$c['filter2_label'], $c['filter2_options']],
            ];
            if ($configs_filter === '') {
                $filters[] = [$c['filter3_label'], $c['filter3_options']];
            }
            foreach ($filters as $filter) :
                $opts = $parse_opts($filter[1]);
                ?>
                <label class="block">
                    <span class="mb-1.5 block text-[12px] font-medium text-white/55 sm:text-[13px]"><?php echo esc_html($filter[0]); ?></span>
                    <select class="w-full appearance-none rounded-xl border border-white/10 bg-[#1A1A1A] bg-[length:12px] bg-[right_1rem_center] bg-no-repeat px-4 py-3.5 pr-10 text-[13px] text-white outline-none focus:border-[#FF6A00]/50 sm:text-[14px]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%228%22 viewBox=%220 0 12 8%22%3E%3Cpath fill=%22%23888%22 d=%22M1 1l5 5 5-5%22/%3E%3C/svg%3E')">
                        <?php foreach ($opts as $opt) : ?>
                            <option><?php echo esc_html($opt); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endforeach; ?>
        </div>

        <ul class="mt-6 space-y-4 sm:mt-8 sm:space-y-4" data-configs-list>
            <?php foreach ($items as $i => $trim) :
                if (trim((string) ($trim['title'] ?? '')) === '') {
                    continue;
                }
                ?>
                <li class="overflow-hidden rounded-2xl border border-white/10 bg-[#1A1A1A] <?php echo $i >= $visible ? 'hidden' : ''; ?>" <?php echo $i >= $visible ? 'data-config-more' : ''; ?>>
                    <div class="flex flex-col md:flex-row md:items-center md:gap-6 lg:gap-8 md:p-6 lg:px-7 lg:py-6">
                        <div class="border-b border-white/5 px-4 pt-4 md:w-[200px] md:shrink-0 md:border-0 md:p-0 lg:w-[240px] xl:w-[280px]">
                            <img
                                src="<?php echo esc_url($trim['image']); ?>"
                                alt="<?php echo esc_attr($trim['title']); ?>"
                                class="mx-auto h-auto w-full max-w-[280px] object-contain md:max-w-none"
                                width="280"
                                height="160"
                                loading="lazy">
                        </div>
                        <div class="flex min-w-0 flex-1 flex-col gap-4 p-4 sm:p-5 md:flex-row md:items-center md:justify-between md:gap-6 md:p-0">
                            <div class="min-w-0">
                                <h3 class="text-[18px] font-bold uppercase leading-snug text-white sm:text-[20px] md:text-[17px] lg:text-[18px] xl:text-[19px] 3xl:text-[20px]"><?php echo esc_html($trim['title']); ?></h3>
                                <p class="mt-1 text-[12px] leading-snug text-white/50 sm:text-[13px] lg:text-[14px]"><?php echo esc_html($trim['specs']); ?></p>
                                <p class="mt-2 text-[20px] font-bold text-white sm:text-[22px] md:text-[19px] lg:text-[20px] xl:text-[22px] 3xl:text-[24px]"><?php echo esc_html($trim['price']); ?></p>
                                <?php if (!empty($trim['perks'])) : ?>
                                    <ul class="mt-3 space-y-2">
                                        <?php foreach ($trim['perks'] as $pi => $perk) :
                                            $icon = $perk_icons[$pi % count($perk_icons)];
                                            ?>
                                            <li class="flex items-start gap-2 text-[12px] leading-snug text-white/70 sm:text-[13px] lg:text-[14px]">
                                                <img src="<?php echo esc_url($theme . '/Font/' . $icon); ?>" alt="" class="mt-0.5 h-4 w-4 shrink-0">
                                                <span><?php echo esc_html($perk); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <div class="flex w-full shrink-0 flex-col gap-2 md:w-auto md:min-w-[200px] lg:min-w-[220px] xl:min-w-[240px]">
                                <a href="<?php echo esc_url(avtodealer_href($c['cta_url'])); ?>" data-open-lead class="inline-flex items-center justify-center gap-1 rounded-xl bg-[#FF9549] px-5 py-3.5 text-[13px] font-semibold text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:text-[14px] md:py-3 lg:text-[15px] xl:px-6">
                                    <?php echo esc_html($c['cta_label'] ?? ''); ?>
                                    <span aria-hidden="true">&gt;</span>
                                </a>
                                <a href="<?php echo esc_url(avtodealer_href($h['testdrive_url'] ?? '#credit')); ?>" class="inline-flex items-center justify-center gap-1 rounded-xl border border-white/20 bg-transparent px-5 py-3 text-[13px] font-semibold text-white transition-colors hover:border-[#FF6A00]/50 sm:text-[14px] lg:text-[15px]">
                                    <?php echo esc_html($c['link_label'] ?? ''); ?>
                                    <span aria-hidden="true">&gt;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (count($items) > $visible) : ?>
            <div class="mt-6 flex justify-center sm:mt-8">
                <button type="button" class="inline-flex w-full max-w-[420px] items-center justify-center rounded-xl border border-white/20 bg-transparent px-6 py-3.5 text-[13px] font-semibold text-white transition-colors hover:border-[#FF6A00]/50 hover:text-[#FF6A00] sm:w-auto sm:px-8 sm:text-[14px]" data-configs-more>
                    <?php echo esc_html($c['more_label']); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>
