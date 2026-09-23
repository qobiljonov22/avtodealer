<?php
$theme = get_template_directory_uri();
$m = avtodealer_get_models();
$cars = is_array($m['cars'] ?? null) ? $m['cars'] : [];
$perk_icons = ['clipboard-check.svg', 'certificate.svg', 'tag-01.svg', 'sale.svg'];
$model_only = avtodealer_page_context('model_id', '');
$layout = avtodealer_page_context('models_layout', 'copy-right');
$title_filter = (string) avtodealer_page_context('configs_filter', avtodealer_page_context('model_title', ''));
$copy_left = ($layout === 'copy-left');
$cta_split = (bool) avtodealer_page_context('cta_split', $copy_left);
$page_slug = (string) avtodealer_page_context('slug', '');

$is_tank300 = $page_slug === 'tank-300' || (function_exists('avtodealer_is_tank300_page') && avtodealer_is_tank300_page());
$is_tank500 = $page_slug === 'tank-500' || (function_exists('avtodealer_is_tank500_page') && avtodealer_is_tank500_page());
$is_home = $page_slug === 'home' || (function_exists('is_front_page') && is_front_page() && !$is_tank300 && !$is_tank500);

if ($is_tank300) {
    $title_filter = 'TANK 300';
    $copy_left = true;
    $cta_split = true;
}

if ($is_tank500) {
    $title_filter = 'TANK 500';
    $copy_left = false;
    $cta_split = true;
}

// Home always shows both cars — clear accidental filters
if ($is_home) {
    $title_filter = '';
    $model_only = '';
}

if ($title_filter !== '') {
    $cars = array_values(array_filter($cars, static function ($car) use ($title_filter) {
        return stripos((string) ($car['title'] ?? ''), $title_filter) !== false;
    }));
} elseif ($model_only !== '') {
    $cars = array_values(array_filter($cars, static function ($car) use ($model_only) {
        return ($car['id'] ?? '') === $model_only;
    }));
}
?>
<section id="models" class="bg-[#181818] py-8 sm:py-10 md:py-12 lg:py-14 xl:py-16 2xl:py-[72px] 3xl:py-20" aria-label="Модели">
    <div class="container mx-auto space-y-12 sm:space-y-14 md:space-y-16 lg:space-y-20 xl:space-y-24 2xl:space-y-28">
        <?php foreach ($cars as $car) :
            if (trim((string) ($car['title'] ?? '')) === '') {
                continue;
            }
            $gallery = array_values(array_filter((array) ($car['gallery'] ?? [])));
            if (!$gallery && !empty($car['image'])) {
                $gallery = [$car['image']];
            }
            $perks = array_values((array) ($car['perks'] ?? []));
            $gallery_json = wp_json_encode(array_values($gallery), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $perks_json = wp_json_encode(array_slice($perks, 0, 4), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $media_order = $copy_left ? 'order-1 lg:order-2' : '';
            $copy_order = $copy_left ? 'order-2 lg:order-1' : '';
            $colors_side = $copy_left
                ? 'order-2 justify-center lg:order-2 lg:flex-col'
                : 'order-2 justify-center lg:order-1 lg:flex-col';
            $image_side = $copy_left
                ? 'order-1 lg:order-1'
                : 'order-1 lg:order-2';
            ?>
            <article
                id="<?php echo esc_attr($car['anchor']); ?>"
                class="w-full"
                data-model-card
                data-model-title="<?php echo esc_attr($car['title']); ?>"
                data-model-badge="<?php echo esc_attr($car['badge']); ?>"
                data-model-image="<?php echo esc_url($car['image']); ?>"
                data-model-credit-url="<?php echo esc_url(avtodealer_href($car['credit_url'])); ?>"
                data-model-credit-label="<?php echo esc_attr($car['credit_label'] ?: 'Рассчитать кредит'); ?>"
                data-model-gallery="<?php echo esc_attr($gallery_json); ?>"
                data-model-perks="<?php echo esc_attr($perks_json); ?>">

                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:gap-7 lg:grid-cols-12 lg:items-center lg:gap-8 xl:gap-10 2xl:gap-12">
                    <!-- Media: image + colors -->
                    <div class="lg:col-span-7 xl:col-span-8 <?php echo esc_attr($media_order); ?>">
                        <div class="flex flex-col gap-3 sm:gap-4 md:gap-5 lg:flex-row lg:items-center lg:gap-4 xl:gap-5 2xl:gap-6">
                            <?php if (!empty($car['colors']) && !$copy_left) : ?>
                                <div
                                    class="flex shrink-0 flex-row gap-2.5 sm:gap-3 <?php echo esc_attr($colors_side); ?> lg:gap-3 xl:gap-3.5 2xl:gap-4"
                                    data-model-colors
                                    role="listbox"
                                    aria-label="Цвет">
                                    <?php foreach ($car['colors'] as $ci => $color) :
                                        $hex = is_array($color) ? (string) ($color['hex'] ?? '') : (string) $color;
                                        $cimg = is_array($color) ? (string) ($color['image'] ?? '') : '';
                                        if ($cimg === '') {
                                            $cimg = (string) ($car['image'] ?? '');
                                        }
                                        $hex = preg_match('/^#?[0-9a-fA-F]{3,8}$/', $hex) ? (str_starts_with($hex, '#') ? $hex : '#' . $hex) : $hex;
                                        ?>
                                        <button
                                            type="button"
                                            class="relative h-8 w-8 rounded-full border-2 border-white/25 transition sm:h-9 sm:w-9 md:h-10 md:w-10 xl:h-11 xl:w-11 2xl:h-12 2xl:w-12 <?php echo $ci === 0 ? 'ring-2 ring-white ring-offset-2 ring-offset-[#181818]' : ''; ?>"
                                            style="background-color: <?php echo esc_attr($hex); ?>"
                                            data-color
                                            data-color-value="<?php echo esc_attr($hex); ?>"
                                            data-color-image="<?php echo esc_url($cimg); ?>"
                                            aria-pressed="<?php echo $ci === 0 ? 'true' : 'false'; ?>"
                                            aria-label="<?php echo esc_attr('Цвет ' . ($ci + 1)); ?>">
                                            <span class="pointer-events-none absolute inset-0 grid place-items-center text-white drop-shadow <?php echo $ci === 0 ? '' : 'hidden'; ?>" data-color-check aria-hidden="true">
                                                <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" viewBox="0 0 16 16" fill="none"><path d="M3 8.5l3.2 3.2L13 4.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="relative min-w-0 flex-1 <?php echo esc_attr($image_side); ?>">
                                <?php if (!empty($car['badge'])) : ?>
                                    <div class="pointer-events-none absolute left-0 top-2 z-10 max-w-[85%] sm:top-3 md:top-4 lg:left-1" aria-hidden="true">
                                        <span class="inline-block origin-left -skew-x-12 bg-[#FF9549] px-3 py-1.5 text-[10px] font-bold uppercase leading-tight text-[#181818] sm:px-3.5 sm:py-2 sm:text-[11px] md:text-[12px] xl:text-[13px]">
                                            <span class="inline-block skew-x-12"><?php echo esc_html($car['badge']); ?></span>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <button
                                    type="button"
                                    class="group relative block w-full overflow-hidden rounded-xl text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF9549] sm:rounded-2xl"
                                    data-open-gallery
                                    data-gallery-index="0"
                                    aria-label="<?php echo esc_attr(($car['title'] ?: 'Авто') . ' — открыть галерею'); ?>">
                                    <img
                                        src="<?php echo esc_url($car['image']); ?>"
                                        alt="<?php echo esc_attr($car['title']); ?>"
                                        class="h-auto w-full object-contain transition duration-300 group-hover:scale-[1.02]"
                                        width="900"
                                        height="480"
                                        loading="lazy"
                                        data-model-image>
                                </button>
                            </div>

                            <?php if (!empty($car['colors']) && $copy_left) : ?>
                                <div
                                    class="flex shrink-0 flex-row gap-2.5 sm:gap-3 <?php echo esc_attr($colors_side); ?> lg:gap-3 xl:gap-3.5 2xl:gap-4"
                                    data-model-colors
                                    role="listbox"
                                    aria-label="Цвет">
                                    <?php foreach ($car['colors'] as $ci => $color) :
                                        $hex = is_array($color) ? (string) ($color['hex'] ?? '') : (string) $color;
                                        $cimg = is_array($color) ? (string) ($color['image'] ?? '') : '';
                                        if ($cimg === '') {
                                            $cimg = (string) ($car['image'] ?? '');
                                        }
                                        $hex = preg_match('/^#?[0-9a-fA-F]{3,8}$/', $hex) ? (str_starts_with($hex, '#') ? $hex : '#' . $hex) : $hex;
                                        ?>
                                        <button
                                            type="button"
                                            class="relative h-8 w-8 rounded-full border-2 border-white/25 transition sm:h-9 sm:w-9 md:h-10 md:w-10 xl:h-11 xl:w-11 2xl:h-12 2xl:w-12 <?php echo $ci === 0 ? 'ring-2 ring-white ring-offset-2 ring-offset-[#181818]' : ''; ?>"
                                            style="background-color: <?php echo esc_attr($hex); ?>"
                                            data-color
                                            data-color-value="<?php echo esc_attr($hex); ?>"
                                            data-color-image="<?php echo esc_url($cimg); ?>"
                                            aria-pressed="<?php echo $ci === 0 ? 'true' : 'false'; ?>"
                                            aria-label="<?php echo esc_attr('Цвет ' . ($ci + 1)); ?>">
                                            <span class="pointer-events-none absolute inset-0 grid place-items-center text-white drop-shadow <?php echo $ci === 0 ? '' : 'hidden'; ?>" data-color-check aria-hidden="true">
                                                <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" viewBox="0 0 16 16" fill="none"><path d="M3 8.5l3.2 3.2L13 4.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Copy + CTAs -->
                    <div class="lg:col-span-5 xl:col-span-4 <?php echo esc_attr($copy_order); ?>">
                        <?php if (!empty($car['eyebrow'])) : ?>
                            <p class="<?php echo $copy_left
                                ? 'text-[12px] font-bold uppercase tracking-wide text-[#FF9549] sm:text-[13px] md:text-[14px] xl:text-[15px]'
                                : 'text-[12px] font-medium text-white/90 sm:text-[13px] md:text-[14px] lg:text-[14px] xl:text-[15px] 2xl:text-[16px]'; ?>">
                                <?php echo esc_html($car['eyebrow']); ?>
                            </p>
                        <?php endif; ?>

                        <h2 class="mt-1 text-[28px] font-bold uppercase leading-none tracking-tight sm:text-[36px] md:text-[40px] lg:text-[42px] xl:text-[48px] 2xl:text-[52px] 3xl:text-[56px] <?php echo $is_tank500 ? 'text-[#FF9549]' : 'text-white'; ?>">
                            <?php echo esc_html($car['title']); ?>
                        </h2>

                        <?php if (!empty($car['benefit'])) : ?>
                            <p class="mt-2 text-[14px] font-bold uppercase leading-snug sm:mt-2.5 sm:text-[16px] md:text-[18px] lg:text-[17px] xl:text-[20px] 2xl:text-[22px] <?php echo ($copy_left && !$is_tank500) ? 'text-white' : 'text-[#FF9549]'; ?>">
                                <?php echo esc_html($car['benefit']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!$copy_left && !empty($car['offer_label'])) : ?>
                            <a
                                href="<?php echo esc_url(avtodealer_href($car['offer_url'] ?? '#credit')); ?>"
                                data-open-lead
                                class="mt-3 inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#FF9549] transition-colors hover:text-[#FF6A00] sm:mt-4 sm:text-[14px] xl:text-[15px]">
                                <img src="<?php echo esc_url($theme . '/Font/location.svg'); ?>" alt="" class="h-3.5 w-3.5 shrink-0 sm:h-4 sm:w-4" style="filter: invert(62%) sepia(68%) saturate(1200%) hue-rotate(339deg) brightness(103%) contrast(101%);">
                                <span><?php echo esc_html($car['offer_label']); ?></span>
                                <span aria-hidden="true">›</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($cta_split) : ?>
                            <div class="mt-5 flex flex-col gap-2.5 sm:mt-6 sm:max-w-[400px] lg:max-w-none">
                                <a href="<?php echo esc_url(avtodealer_href($car['cta_url'])); ?>" data-open-lead class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-[#FF9549] px-5 py-3.5 text-[13px] font-semibold uppercase tracking-wide text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:rounded-xl sm:text-[14px] md:py-4 lg:text-[15px]">
                                    <span><?php echo esc_html($car['cta_label']); ?></span>
                                </a>
                                <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                                    <a href="<?php echo esc_url(avtodealer_href($car['credit_url'])); ?>" data-open-lead class="inline-flex items-center justify-center rounded-lg border border-white/70 bg-transparent px-3 py-3 text-[12px] font-semibold text-white transition-colors hover:border-[#FF9549] hover:bg-[#FF9549] hover:text-white sm:rounded-xl sm:text-[13px] md:py-3.5 xl:text-[14px]">
                                        <?php echo esc_html($car['credit_label']); ?>
                                    </a>
                                    <a href="<?php echo esc_url(avtodealer_href($car['td_url'])); ?>" class="inline-flex items-center justify-center rounded-lg border border-white/70 bg-transparent px-3 py-3 text-[12px] font-semibold text-white transition-colors hover:border-[#FF9549] hover:bg-[#FF9549] hover:text-white sm:rounded-xl sm:text-[13px] md:py-3.5 xl:text-[14px]">
                                        <?php echo esc_html($car['td_label']); ?>
                                    </a>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="mt-5 flex flex-col gap-2.5 sm:mt-6 sm:max-w-[380px] sm:gap-3 md:max-w-[400px] lg:max-w-none xl:gap-3.5">
                                <a href="<?php echo esc_url(avtodealer_href($car['cta_url'])); ?>" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-[#FF9549] px-5 py-3.5 text-[13px] font-semibold text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:rounded-xl sm:text-[14px] md:py-4 lg:text-[15px] xl:text-[16px]">
                                    <span><?php echo esc_html($car['cta_label']); ?></span>
                                    <span aria-hidden="true">›</span>
                                </a>
                                <a href="<?php echo esc_url(avtodealer_href($car['td_url'])); ?>" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-white/80 bg-transparent px-5 py-3 text-[13px] font-semibold text-white transition-colors hover:border-[#FF9549] hover:bg-[#FF9549] hover:text-white sm:rounded-xl sm:text-[14px] md:py-3.5 xl:text-[15px]">
                                    <span><?php echo esc_html($car['td_label']); ?></span>
                                    <span aria-hidden="true">›</span>
                                </a>
                                <a href="<?php echo esc_url(avtodealer_href($car['credit_url'])); ?>" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-white/80 bg-transparent px-5 py-3 text-[13px] font-semibold text-white transition-colors hover:border-[#FF9549] hover:bg-[#FF9549] hover:text-white sm:rounded-xl sm:text-[14px] md:py-3.5 xl:text-[15px]" data-open-lead>
                                    <span><?php echo esc_html($car['credit_label']); ?></span>
                                    <span aria-hidden="true">›</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($perks)) : ?>
                    <ul class="mt-7 grid grid-cols-1 gap-2.5 sm:mt-8 sm:grid-cols-2 sm:gap-3 md:mt-9 md:gap-3.5 lg:mt-10 lg:grid-cols-4 lg:gap-4 xl:mt-12 xl:gap-5 2xl:gap-6">
                        <?php foreach (array_slice($perks, 0, 4) as $pi => $perk) :
                            $icon = $perk_icons[$pi % count($perk_icons)];
                            ?>
                            <li class="flex items-center gap-3 rounded-xl border border-[#FF9549]/40 bg-[#1A1A1A] px-4 py-3.5 sm:px-4 sm:py-4 md:gap-3.5 lg:flex-col lg:items-start lg:gap-3 xl:px-5 xl:py-5 2xl:px-6 2xl:py-6">
                                <img src="<?php echo esc_url($theme . '/Font/' . $icon); ?>" alt="" class="h-6 w-6 shrink-0 sm:h-7 sm:w-7 xl:h-8 xl:w-8" style="filter: invert(62%) sepia(68%) saturate(1200%) hue-rotate(339deg) brightness(103%) contrast(101%);">
                                <span class="text-[12px] leading-snug text-white sm:text-[13px] lg:text-[13px] xl:text-[14px] 2xl:text-[15px]"><?php echo esc_html($perk); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($gallery)) : ?>
                    <div class="mt-5 grid grid-cols-3 gap-2 sm:mt-6 sm:grid-cols-5 sm:gap-2.5 md:mt-7 md:gap-3 lg:mt-8 xl:mt-10 xl:gap-4">
                        <?php foreach ($gallery as $gi => $gurl) : ?>
                            <button
                                type="button"
                                class="overflow-hidden rounded-lg border border-white/10 transition hover:border-[#FF9549] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF9549] sm:rounded-xl <?php echo $gi >= 3 ? 'hidden sm:block' : ''; ?>"
                                data-open-gallery
                                data-gallery-index="<?php echo (int) $gi; ?>"
                                aria-label="<?php echo esc_attr('Фото ' . ($gi + 1)); ?>">
                                <img src="<?php echo esc_url($gurl); ?>" alt="" class="aspect-[4/3] h-auto w-full object-cover sm:aspect-[16/10]" width="240" height="150" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php
global $avtodealer_gallery_modal_printed;
if (empty($avtodealer_gallery_modal_printed)) :
    $avtodealer_gallery_modal_printed = true;
    ?>
    <div
        id="avto-gallery-modal"
        class="fixed inset-0 z-[100000] hidden items-center justify-center p-3 sm:p-6 md:p-8 lg:p-10"
        data-gallery-modal
        aria-hidden="true"
        role="dialog"
        aria-modal="true">
        <button type="button" class="absolute inset-0 bg-[#181818]/92 backdrop-blur-[2px]" data-gallery-close tabindex="-1" aria-label="Закрыть"></button>

        <div class="relative z-[1] w-full max-w-[920px] rounded-2xl bg-[#181818] p-4 shadow-[0_30px_80px_rgba(0,0,0,0.55)] sm:p-5 md:max-w-[1000px] md:p-6 lg:max-w-[1100px] xl:max-w-[1200px] 2xl:max-w-[1280px] 3xl:max-w-[1360px]">
            <button
                type="button"
                class="absolute right-3 top-3 z-[2] grid h-9 w-9 place-items-center rounded-full text-white/80 transition hover:bg-white/10 hover:text-white sm:right-4 sm:top-4"
                data-gallery-close
                aria-label="Закрыть">
                <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M4 4l8 8M12 4L4 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>

            <div class="overflow-hidden rounded-xl">
                <img src="" alt="" class="mx-auto max-h-[55dvh] w-full object-contain sm:max-h-[60dvh] lg:max-h-[65dvh] xl:max-h-[70dvh]" data-gallery-main width="1200" height="700">
            </div>

            <div class="mt-3 grid grid-cols-5 gap-2 sm:mt-4 sm:gap-2.5 md:gap-3" data-gallery-thumbs></div>
        </div>
    </div>
<?php endif; ?>
