<?php
include(__DIR__.'/set_image.php');


function custom_product_creation_form_shortcode() {
    // Check if the user is logged in and has permission to create a product
    if (!is_user_logged_in() || !current_user_can('publish_posts')) {
        return '<p>You must be logged in and have sufficient permissions to create products.</p>';
    }

    $form_html = '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_custom_product_shortcode">
        
        <div class="mt-3 mb-3">
            <label class="form-label" for="product_name">Product Name:</label>
            <input class="form-control" type="text" id="product_name" name="product_name" required>
        </div>

        <div class="mt-3 mb-3">
            <label class="form-label" for="sale_price">Sale Price:</label>
            <input class="form-control" type="text" id="sale_price" name="sale_price" required>
        </div>
        
        <div class="mt-3 mb-3">
            <label class="form-label" for="regular_price">Regular Price:</label>
            <input class="form-control" type="text" id="regular_price" name="regular_price" required>
        </div>


        <div class="mt-3 mb-3">
            <label class="form-label" for="description">Product Description:</label>
            <input class="form-control" type="text" id="description" name="description" required>
        </div>

        <div class="mt-3 mb-3">
            <label class="form-label" for="offer_image">Images:</label>
            <input class="form-control" type="file" id="offer_image" name="offer_image[]" accept="image/*" multiple>
        </div>
    
        <button type="submit" class="btn btn-primary mt-3">Submit</button>
    </form>';
    return $form_html;
}
add_shortcode('lms_create_product_form', 'custom_product_creation_form_shortcode');

function init_product(string $product_name, float $regular_price, float $sale_price, string $description): WC_Product_Simple {
    $product = new WC_Product_Simple();
    $product->set_name($product_name);
    $product->set_regular_price($regular_price);
    $product->set_sale_price($sale_price);
    $product->set_description($description);
    $product->set_stock_status('instock');

    // Save the product first
    $product->save();
    return $product;
}

function handle_shortcode_create_product() {
    if (!is_user_logged_in() || !current_user_can('publish_posts')) {
        wp_die('You do not have sufficient permissions to create products.');
    }

    if (isset($_POST['product_name'], $_POST['sale_price'], $_POST['regular_price'])) {    
        $product_name = sanitize_text_field($_POST['product_name']);
        $description = sanitize_text_field($_POST['description']);
        $regular_price = wc_format_decimal($_POST['regular_price']);
        $sale_price = wc_format_decimal($_POST['sale_price']);
        $product = init_product($product_name, $regular_price, $sale_price, $description);

        set_product_images($product);

        wp_redirect(add_query_arg('product_created', 'success', get_permalink(get_page_by_path('sell-offer'))));
        exit;
    }
}

add_action('admin_post_create_custom_product_shortcode', 'handle_shortcode_create_product');
add_action('admin_post_nopriv_create_custom_product_shortcode', 'handle_shortcode_create_product'); // Optionally allow non-logged in users to create product
