<?php
$m = function_exists('avtodealer_get_modal') ? avtodealer_get_modal() : [];
$models = is_array($m['models'] ?? null) ? $m['models'] : ['TANK 300', 'TANK 500'];
?>
<div
    id="avto-lead-modal"
    class="fixed inset-0 z-[100000] hidden items-center justify-center p-4 sm:p-6 md:p-8"
    data-lead-modal
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="avto-lead-title">
    <button type="button" class="absolute inset-0 bg-black/70 backdrop-blur-[2px]" data-lead-close tabindex="-1" aria-label="Закрыть"></button>

    <div class="relative z-[1] w-full max-w-[360px] rounded-2xl border border-white/10 bg-[#1A1A1A] p-5 shadow-[0_30px_80px_rgba(0,0,0,0.55)] sm:max-w-[420px] sm:p-6 md:max-w-[480px] md:p-7 lg:max-w-[520px] lg:p-8 xl:max-w-[560px] 2xl:max-w-[600px] 3xl:max-w-[640px]">
        <button
            type="button"
            class="absolute right-3 top-3 grid h-9 w-9 place-items-center rounded-full text-white/70 transition hover:bg-white/10 hover:text-white sm:right-4 sm:top-4"
            data-lead-close
            aria-label="Закрыть">
            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M4 4l8 8M12 4L4 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>

        <h2 id="avto-lead-title" class="pr-8 text-center text-[20px] font-bold uppercase leading-tight tracking-wide text-white sm:text-[22px] md:text-[24px] lg:text-[26px] xl:text-[28px]">
            <?php echo esc_html($m['title'] ?? 'Получить предложение'); ?>
        </h2>
        <p class="mt-2 text-center text-[12px] leading-snug text-white/55 sm:mt-3 sm:text-[13px] md:text-[14px] lg:text-[15px]">
            <?php echo esc_html($m['subtitle'] ?? ''); ?>
        </p>

        <form class="mt-5 space-y-3.5 sm:mt-6 sm:space-y-4" data-lead-form novalidate>
            <label class="block">
                <span class="mb-1.5 block text-[12px] text-white/55 sm:text-[13px]"><?php echo esc_html($m['model_label'] ?? 'Модель'); ?></span>
                <select
                    name="model"
                    required
                    class="w-full appearance-none rounded-xl border border-white/15 bg-[#141414] bg-[length:12px] bg-[right_1rem_center] bg-no-repeat px-4 py-3.5 pr-10 text-[14px] text-white outline-none focus:border-[#FF6A00]/55 sm:py-4 sm:text-[15px]"
                    style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%228%22 viewBox=%220 0 12 8%22%3E%3Cpath fill=%22%23888%22 d=%22M1 1l5 5 5-5%22/%3E%3C/svg%3E')">
                    <option value="" disabled selected><?php echo esc_html($m['model_label'] ?? 'Модель'); ?></option>
                    <?php foreach ($models as $opt) : ?>
                        <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block">
                <span class="mb-1.5 block text-[12px] text-white/55 sm:text-[13px]"><?php echo esc_html($m['phone_label'] ?? 'Телефон'); ?></span>
                <input
                    type="tel"
                    name="phone"
                    required
                    autocomplete="tel"
                    inputmode="tel"
                    placeholder="<?php echo esc_attr($m['phone_placeholder'] ?? '+7 (___) ___-__-__'); ?>"
                    class="w-full rounded-xl border border-white/15 bg-[#141414] px-4 py-3.5 text-[14px] text-white outline-none placeholder:text-white/35 focus:border-[#FF6A00]/55 sm:py-4 sm:text-[15px]">
            </label>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-[#FF9549] px-5 py-3.5 text-[14px] font-semibold text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:py-4 sm:text-[15px] md:text-[16px]">
                <?php echo esc_html($m['button'] ?? 'Получить предложение'); ?>
                <span aria-hidden="true">&gt;</span>
            </button>

            <label class="flex items-start gap-2.5 text-[11px] leading-snug text-white/45 sm:text-[12px]">
                <input type="checkbox" name="consent" required checked class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/30 bg-transparent accent-[#FF6A00]">
                <span><?php echo esc_html($m['consent'] ?? ''); ?></span>
            </label>
        </form>
    </div>
</div>
