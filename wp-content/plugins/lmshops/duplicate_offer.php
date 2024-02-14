<?php
include(__DIR__.'/duplicate_image.php');

function duplicate_wc_product($original_product_id) {
    $original_product = wc_get_product($original_product_id);

    // Create a new product object (WC_Product_Simple, WC_Product_Variable, etc.)
    $new_product = new WC_Product_Simple();

    // Set properties from the original product
    $new_product->set_name($original_product->get_name() . ' - Copy');
    $new_product->set_status('draft'); // Set the status of the new product to draft
    $new_product->set_regular_price($original_product->get_regular_price());
    $new_product->set_sale_price($original_product->get_sale_price());
    $new_product->set_image_id($original_product->get_image_id());
    $new_product->set_gallery_image_ids($original_product->get_gallery_image_ids());
    // ... Copy other properties as needed

    // Save the new product to get an ID
    $new_product_id = $new_product->save();

    // Duplicate the image
    $original_image_id = $original_product->get_image_id();
    if ($original_image_id) {
        // Assume duplicate_image function exists and returns the new attachment ID
        $new_image_id = duplicate_image($original_image_id);
        if ($new_image_id) {
            $new_product->set_image_id($new_image_id);
            $new_product->save();
        }
    }

    // Optionally, copy product meta data
    $meta_data = get_post_meta($original_product_id);
    foreach ($meta_data as $key => $value) {
        if (strpos($key, '_') !== 0) { // Skip internal meta keys
            update_post_meta($new_product_id, $key, $value[0]);
        }
    }

    // Handle special cases (like copying product variations for variable products)

    return $new_product_id; // Return the new product ID
}


function handle_duplicate_wc_product() {
    if (isset($_POST['product_id'], $_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'product_nonce')) {
        $product_id = intval($_POST['product_id']);
        $current_user_id = get_current_user_id();
        $post_author_id = get_post_field('post_author', $product_id);

        // Check if the current user is the author of the product or has the capability to delete products
        if ($current_user_id === $post_author_id || current_user_can('edit_posts')) {
            $pid = duplicate_wc_product($product_id);
            if ($pid) {
                wp_redirect(add_query_arg('product_deplicate', 'success', get_permalink(get_page_by_path('lm-my-offers'))));
                exit;
            } else {
                wp_die('Error duplicate product.');
            }
        } else {
            wp_die('You do not have permission to duplicate this product.');
        }
    } else {
        wp_die('Security check failed or invalid product for duplicating.');
    }
}
add_action('admin_post_duplicate_product', 'handle_duplicate_wc_product');

# short code is optional;
// function handle_shortcode_duplicate_product() {
//     if (!is_user_logged_in() || !current_user_can('edit_posts')) {
//         wp_die('You do not have sufficient permissions to edit products.');
//     }

//     if (isset($_POST['product_name'], $_POST['product_price'], $_POST['product_id'])) {
//         $product_id = intval($_POST['product_id']);

//         duplicate_wc_product($product_id);

//         wp_redirect(add_query_arg('product_duplicated', 'success', get_permalink(get_page_by_path('lm-my-offers'))));
//         exit;
//     }
// }
// add_action('admin_post_duplicate_custom_product_shortcode', 'handle_shortcode_duplicate_product');
