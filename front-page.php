<?php
// Home (1st page) — never inherit model-page context
if (function_exists('avtodealer_set_page_context')) {
    avtodealer_set_page_context([
        'slug'          => 'home',
        'hero'          => 'home',
        'catalog_mode'  => 'full',
        'models_layout' => 'copy-right',
        'cta_split'     => false,
    ]);
}

get_header();

get_template_part('sections/hero');
get_template_part('sections/catalog');
get_template_part('sections/models');
get_template_part('sections/configs');
get_template_part('sections/tradein');
get_template_part('sections/credit');
get_template_part('sections/corporate');
get_template_part('sections/contact');

get_footer();

