<?php
function custom_product_creation_form_shortcode() {
    // Check if the user is logged in and has permission to create a product
    if (!is_user_logged_in() || !current_user_can('publish_posts')) {
        return '<p>You must be logged in and have sufficient permissions to create products.</p>';
    }

    $form_html = '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_custom_product_shortcode">
        
        <div class="form-group">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required>
        </div>
        
        <div class="form-group">
            <label for="product_price">Product Price:</label>
            <input type="text" id="product_price" name="product_price" required>
        </div>

        <div class="form-group">
            <label for="offer_image">Image:</label>
            <input type="file" id="offer_image" name="offer_image" accept="image/*">
        </div>
    
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>';
    return $form_html;
}
add_shortcode('lms_create_product_form', 'custom_product_creation_form_shortcode');

function init_product(string $product_name, float $product_price): WC_Product_Simple {
    $product = new WC_Product_Simple();
    $product->set_name($product_name);
    $product->set_regular_price($product_price);
    $product->set_stock_status('instock');

    // Save the product first
    $product->save();
    return $product;
}

function set_product_image(WC_Product_Simple $product) {
    // Check if an image was uploaded
    if (isset($_FILES['offer_image']) && !empty($_FILES['offer_image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // $_FILES array key for 'offer_image' matches the 'name' attribute in the form
        $attachment_id = media_handle_upload('offer_image', 0); // 0 denotes no parent post ID

        if (is_wp_error($attachment_id)) {
            // Handle errors, e.g., file wasn't uploaded or isn't an image
            wp_die('An error occurred uploading the image: ' . $attachment_id->get_error_message());
        } else {
            // No error, set the product image
            $product->set_image_id($attachment_id);
            // Save the product again to update the image
            $product->save();
        }
    }
}

function handle_shortcode_create_product() {
    if (!is_user_logged_in() || !current_user_can('publish_posts')) {
        wp_die('You do not have sufficient permissions to create products.');
    }

    if (isset($_POST['product_name'], $_POST['product_price'])) {    
        $product_name = sanitize_text_field($_POST['product_name']);
        $product_price = wc_format_decimal($_POST['product_price']);
        $product = init_product($product_name, $product_price);

        set_product_image($product);

        wp_redirect(add_query_arg('product_created', 'success', get_permalink(get_page_by_path('lm-my-offers'))));
        exit;
    }
}

add_action('admin_post_create_custom_product_shortcode', 'handle_shortcode_create_product');
add_action('admin_post_nopriv_create_custom_product_shortcode', 'handle_shortcode_create_product'); // Optionally allow non-logged in users to create product
