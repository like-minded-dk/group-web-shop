<?php

function custom_product_edit_form_shortcode($atts) {
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    $product = wc_get_product($product_id);

    if (!$product) {
        return '<p>Product not found.</p>';
    }

    $form_html = '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_custom_product_shortcode">
        <input type="hidden" name="product_id" value="' . esc_attr($product_id) . '">
        
        <div class="form-group">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" value="' . esc_attr($product->get_name()) . '" required>
        </div>
        
        <div class="form-group">
            <label for="product_price">Product Price:</label>
            <input type="text" id="product_price" name="product_price" value="' . esc_attr($product->get_price()) . '" required>
        </div>

        <div class="form-group">
            <label for="offer_image">Image:</label>
            <input type="file" id="offer_image" name="offer_image" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>';

    return $form_html;
}
add_shortcode('lms_edit_product_form', 'custom_product_edit_form_shortcode');


function handle_shortcode_update_product() {
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_die('You do not have sufficient permissions to edit products.');
    }

    if (isset($_POST['product_name'], $_POST['product_price'], $_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $product_name = sanitize_text_field($_POST['product_name']);
        $product_price = wc_format_decimal($_POST['product_price']);

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_die('Product not found.');
        }

        $product->set_name($product_name);
        $product->set_price($product_price);
        $product->set_regular_price($product_price);
        
        // Optionally handle image update
        set_product_image($product);

        $product->save();

        wp_redirect(add_query_arg('product_updated', 'success', get_permalink(get_page_by_path('lm-my-offers'))));
        exit;
    }
}
add_action('admin_post_update_custom_product_shortcode', 'handle_shortcode_update_product');


