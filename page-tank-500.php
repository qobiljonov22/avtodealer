<?php
/**
 * Template Name: TANK 500
 * Template Post Type: page
 *
 * 3rd page — dedicated TANK 500. Must NOT look like home (/).
 */

avtodealer_set_page_context([
    'slug'           => 'tank-500',
    'model_id'       => 'car2',
    'model_title'    => 'TANK 500',
    'hero'           => 'tank500',
    'catalog_mode'   => 'offer',
    'models_layout'  => 'copy-right',
    'configs_filter' => 'TANK 500',
    'cta_split'      => true,
]);

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
