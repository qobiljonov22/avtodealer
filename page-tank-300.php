<?php
/**
 * Template Name: TANK 300
 * Template Post Type: page
 */

if (function_exists('avtodealer_boot_tank300_context')) {
    avtodealer_boot_tank300_context();
}

avtodealer_set_page_context([
    'slug'           => 'tank-300',
    'model_id'       => 'car1',
    'model_title'    => 'TANK 300',
    'hero'           => 'tank300',
    'catalog_mode'   => 'offer',
    'models_layout'  => 'copy-left',
    'configs_filter' => 'TANK 300',
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
