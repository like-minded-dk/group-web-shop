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
include __DIR__.'/hide_part.php';
include __DIR__.'/duplicate_offer.php';

define("DEFAULT_PRODUCT_IMAGE", "/wp-content/uploads/woocommerce-placeholder-324x324.png");
define("DEFAULT_PRODUCT_IMAGE_ID", 112);

function lmshops_styles() {
    wp_enqueue_style('lmshops-styles', plugin_dir_url(__FILE__) . 'css/lmshops.css');
}
add_action('wp_enqueue_scripts', 'lmshops_styles');


function lm_enqueue_bootstrap() {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2', 'all');

    // Enqueue Bootstrap Optional Theme CSS
    wp_enqueue_style('bootstrap-theme-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap-theme.min.css', array(), '5.3.2', 'all');

    // Enqueue jQuery (comes with WordPress) as Bootstrap's JavaScript depends on it
    wp_enqueue_script('jquery');

    // Enqueue Bootstrap JavaScript
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js', array('jquery'), '5.3.2', true);
}

add_action('wp_enqueue_scripts', 'lm_enqueue_bootstrap');
