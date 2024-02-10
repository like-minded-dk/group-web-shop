<?php

function toggle_wc_product_status() {
    if ( ! isset($_GET['action'])  || ! isset($_GET['product_id'], $_GET['nonce']) ) {
        wp_die('Missing params');
    }
    if ( $_GET['action'] != 'toggle_product_status' ) {
        wp_die('Not allow toggle product status');
    }
    if ( ! wp_verify_nonce($_GET['nonce'], 'product_nonce') ) {
        wp_die('Security check failed');
    }

    $product_id = intval($_GET['product_id']);
    if ( ! current_user_can('edit_product', $product_id) ) {
        wp_die('You are not allowed to edit this product');
    }

    $product = wc_get_product($product_id);
    if ( ! $product ) {
        wp_die('Product not found');
    }

    // Toggle the product status
    $new_status = 'publish' === $product->get_status() ? 'draft' : 'publish';
    wp_update_post(array(
        'ID'          => $product_id,
        'post_status' => $new_status,
    ));

    // Redirect back to the previous page
    wp_safe_redirect(wp_get_referer() ? wp_get_referer() : home_url());
    exit;
}
add_action('admin_post_toggle_product_status', 'toggle_wc_product_status');
