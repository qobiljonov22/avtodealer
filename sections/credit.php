<?php
$c = avtodealer_get_credit();
?>
<section id="credit" class="relative overflow-hidden bg-[#181818]" aria-label="Заявка на кредит">
    <div class="absolute inset-0">
        <img
            src="<?php echo esc_url($c['image_url']); ?>"
            alt=""
            class="h-full w-full object-cover object-[70%_center] md:object-center"
            width="1600"
            height="600"
            loading="lazy">
        <div class="absolute inset-0"></div>
    </div>

    <div class="container relative mx-auto py-12 sm:py-14 md:py-16 lg:py-[72px] xl:py-20 2xl:py-24 3xl:py-28">
        <div class="max-w-[560px] xl:max-w-[620px] 2xl:max-w-[680px] 3xl:max-w-[720px]">
            <h2 class="text-[22px] font-bold uppercase leading-tight tracking-wide text-white sm:text-[28px] md:text-[32px] lg:text-[36px] xl:text-[40px] 2xl:text-[44px] 3xl:text-[48px]">
                <?php echo esc_html($c['title']); ?>
            </h2>
            <p class="mt-2 text-[13px] leading-snug text-white/70 sm:mt-3 sm:text-[14px] md:text-[15px] lg:text-[16px] xl:mt-4 3xl:text-[17px]">
                <?php echo esc_html($c['subtitle']); ?>
            </p>

            <form class="mt-6 flex flex-col gap-3 sm:mt-8 md:mt-9 lg:mt-10" action="<?php echo esc_url(rest_url('avtodealer/v1/leads')); ?>" method="post" data-credit-form>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-stretch sm:gap-3 md:gap-4">
                    <label class="block min-w-0 flex-1">
                        <span class="sr-only">Телефон</span>
                        <input
                            type="tel"
                            name="phone"
                            required
                            placeholder="<?php echo esc_attr($c['placeholder']); ?>"
                            class="w-full rounded-lg border border-white/20 bg-black/35 px-4 py-3.5 text-[14px] text-white outline-none backdrop-blur-sm placeholder:text-white/40 focus:border-[#FF9549]/70 focus:bg-black/45 sm:rounded-xl sm:py-4 md:text-[15px] lg:px-5 lg:py-[18px]">
                    </label>
                    <button type="submit" class="inline-flex w-full shrink-0 items-center justify-center gap-1.5 rounded-lg bg-[#FF9549] px-6 py-3.5 text-[13px] font-semibold text-[#181818] transition-colors hover:bg-[#FF6A00] hover:text-white sm:w-auto sm:rounded-xl sm:px-8 sm:text-[14px] md:text-[15px] lg:px-10 lg:py-4 xl:text-[16px]">
                        <?php echo esc_html($c['button']); ?>
                        <span aria-hidden="true">&gt;</span>
                    </button>
                </div>
                <label class="flex items-start gap-2.5 text-[11px] leading-snug text-white/45 sm:text-[12px] lg:text-[13px]">
                    <input type="checkbox" name="consent" required checked class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/30 bg-transparent accent-[#FF6A00]">
                    <span><?php echo esc_html($c['note']); ?></span>
                </label>
            </form>
        </div>
    </div>
</section>
