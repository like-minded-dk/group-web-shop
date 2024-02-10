<?php

# remove breadcrumbs
add_action( 'init', 'custom_remove_storefront_breadcrumbs');

function custom_remove_storefront_breadcrumbs() {
    remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
}


add_action('after_setup_theme', 'hide_admin_bar_for_specific_roles');
function hide_admin_bar_for_specific_roles() {
    if (current_user_can('group_leader') ) {
        add_filter( 'show_admin_bar', '__return_false');
    }
}
