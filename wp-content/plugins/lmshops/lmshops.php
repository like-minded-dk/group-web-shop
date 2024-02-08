<?php
/**
 * Plugin Name: Likeminded Shops
 * Description: Adds custom shortcodes and styles.
 * Version: 1.0
 * Author: Zheng Dai
 */

include __DIR__.'/list_offer.php';
include __DIR__.'/create_offer.php';
include __DIR__.'/edit_offer.php';

function my_custom_styles() {
    wp_enqueue_style('my-custom-styles', plugin_dir_url(__FILE__) . 'css/lmshops.css');
}
add_action('wp_enqueue_scripts', 'my_custom_styles');



# remove breadcrumbs
add_action( 'init', 'custom_remove_storefront_breadcrumbs');

function custom_remove_storefront_breadcrumbs() {
    remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
}
