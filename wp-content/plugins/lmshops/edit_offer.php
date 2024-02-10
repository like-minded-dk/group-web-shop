<?php
include_once __DIR__.'/list_offer.php';

function get_image_html($product) {
    // Start by fetching the main product image
    $image_ids = [];
    $main_image_id = $product->get_image_id();
    if ($main_image_id) {
        $image_ids[] = $main_image_id; // Add the main image ID to the array
    } else {
        $image_ids[] = DEFAULT_PRODUCT_IMAGE_ID; // DEFAULT_PRODUCT_IMAGE;
    }

    // Fetch gallery images and merge with the main image ID
    $gallery_image_ids = $product->get_gallery_image_ids();
    if (!empty($gallery_image_ids)) {
        $image_ids = array_merge($image_ids, $gallery_image_ids); // Merge gallery IDs into the image IDs array
    }

    // HTML container for images
    $image_html = '<div class="mb-3 product-images-container">';

    // Loop through all image IDs and generate image HTML
    foreach ($image_ids as $image_id) {
        $image_url = wp_get_attachment_url($image_id);
        if ($image_url) {
            $image_html .= '<img class="product-image" src="' . esc_url($image_url) . '" style="margin-right: 10px;"/>';
        }
    }

    $image_html .= '</div>'; // Close the container

    return $image_html;
}

function custom_product_edit_form_shortcode() {
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    $product = wc_get_product($product_id);

    if (!$product) {
        return '<p>Product not found.</p>';
    }

    $form_html = '<form class="edit-offer-form" action="' . esc_url(admin_url('admin-post.php')) . '" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_custom_product_shortcode">
        <input type="hidden" name="product_id" value="' . esc_attr($product_id) . '">
        
        <div class="mb-3">
            <label class="form-label" for="product_name">Product Name:</label>
            <input class="form-control" type="text" id="product_name" name="product_name" value="' . esc_attr($product->get_name()) . '" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label" for="sale_price">Sales Price:</label>
            <input class="form-control" type="text" id="sale_price" name="sale_price" value="' . esc_attr($product->get_sale_price()) . '" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label" for="regular_price">Product Price:</label>
            <input class="form-control" type="text" id="regular_price" name="regular_price" value="' . esc_attr($product->get_regular_price()) . '" required>
        </div>

        <div class=""mb-3">
            <label class="form-label" for="offer_image">Images:</label>
            <input class="form-control" type="file" id="offer_image" name="offer_image[]" accept="image/*" multiple>
        </div>

        <div class=""mb-3">
            ' . get_image_html($product) . '
        </div>

        <button type="submit" class="btn btn-primary mb-3">Update Product</button>
    </form>';

    return $form_html;
}
add_shortcode('lms_edit_product_form', 'custom_product_edit_form_shortcode');


function handle_shortcode_update_product() {
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_die('You do not have sufficient permissions to edit products.');
    }

    if (isset($_POST['product_name'], $_POST['sale_price'], $_POST['regular_price'], $_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $product_name = sanitize_text_field($_POST['product_name']);
        $regular_price = wc_format_decimal($_POST['regular_price']);
        $sale_price = wc_format_decimal($_POST['sale_price']);

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_die('Product not found.');
        }

        $product->set_name($product_name);
        $product->set_price($regular_price);
        $product->set_sale_price($sale_price);
        $product->set_regular_price($regular_price);
        
        // Optionally handle image update
        set_product_images($product);

        $product->save();

        wp_redirect(add_query_arg('product_updated', 'success', get_permalink(get_page_by_path('lm-my-offers'))));
        exit;
    } else {
        wp_die('Edit in Security check failed or invalid product.');
    }
}
add_action('admin_post_update_custom_product_shortcode', 'handle_shortcode_update_product');
