<?php
/**
 * Plugin Name: Likeminded Shops
 * Description: Adds custom shortcodes and styles.
 * Version: 1.0
 * Author: Zheng Dai
 */

include __DIR__.'/util.php';
include __DIR__.'/list_offer.php';
include __DIR__.'/create_offer.php';
include __DIR__.'/edit_offer.php';
include __DIR__.'/hide_part.php';
include __DIR__.'/duplicate_offer.php';

define("DEFAULT_PRODUCT_IMAGE", "/wp-content/uploads/woocommerce-placeholder-324x324.png");
define("DEFAULT_PRODUCT_IMAGE_ID", 112);
define("HOST_STRING", "https://wp.like-minded.dk");

function lmshops_styles() {
    wp_enqueue_style('lmshops-styles', plugin_dir_url(__FILE__) . 'css/lmshops.css');
}
add_action('wp_enqueue_scripts', 'lmshops_styles');


// disable this to all allow wp-admin

// add_filter( 'woocommerce_prevent_admin_access', '__return_true' );


function lm_enqueue_bootstrap() {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2', 'all');

    // Enqueue jQuery (comes with WordPress) as Bootstrap's JavaScript depends on it
    wp_enqueue_script('jquery');

    // Enqueue Bootstrap JavaScript
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js', array('jquery'), '5.3.2', true);
}

add_action('wp_enqueue_scripts', 'lm_enqueue_bootstrap');



function my_plugin_enqueue_scripts() {
    wp_enqueue_script('lm-script', plugins_url('/js/lm-script.js', __FILE__), array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_scripts');


// function remove_specific_capability() {
//     foreach (wp_roles()->roles as $role_name => $role_info) {
//         // Get the role object
//         $role = get_role($role_name);

//         $role_list = ["cuar_access_admin_panel",
//             "cuar_edit_account",
//             "cuar_pf_assign_categories",
//             "cuar_pf_delete",
//             "cuar_pf_delete_categories",
//             "cuar_pf_edit",
//             "cuar_pf_edit_categories",
//             "cuar_pf_list_all",
//             "cuar_pf_manage_attachments",
//             "cuar_pf_manage_categories",
//             "cuar_pf_read",
//             "cuar_pp_assign_categories",
//             "cuar_pp_delete",
//             "cuar_pp_delete_categories",
//             "cuar_pp_edit",
//             "cuar_pp_edit_categories",
//             "cuar_pp_list_all",
//             "cuar_pp_manage_categories",
//             "cuar_pp_read",
//             "cuar_view_account",
//             "cuar_view_any_cuar_private_file",
//             "cuar_view_any_cuar_private_page",
//             "cuar_view_files",
//             "cuar_view_pages",
//             "cuar_view_top_bar"];

//         // Loop through each capability you want to remove
//         foreach ($role_list as $cap) {
//             // Remove the capability from the current role
//             $role->remove_cap($cap);
//         }
//     }
// }

// add_action('init', 'remove_specific_capability');
