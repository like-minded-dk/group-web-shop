<?php
include(__DIR__.'/set_image.php');


function custom_product_creation_form_shortcode() {
    // Check if the user is logged in and has permission to create a product
    if (!is_user_logged_in() || !current_user_can('publish_posts')) {
        return '<p>You must be logged in and have sufficient permissions to create products.</p>';
    }

    $url = esc_url(admin_url('admin-post.php'));
    // Start output buffering
    ob_start();
    // $product = array();
    include "offer.html";
    // Get the contents of the output buffer (the included file's content)
    $offer_html = ob_get_clean();

    // Now $offer_html contains the content of offer.html, and you can concatenate it as intended
    $form_html = <<<HTML
        <form action="{$url}" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_custom_product_shortcode">
            {$offer_html}
        </form>
    HTML;

    
    return $form_html;
}
add_shortcode('lms_create_product_form', 'custom_product_creation_form_shortcode');

function init_product($args): WC_Product_Simple {
    $product = new WC_Product_Simple();


    //  need buy product support limitation add_filter('woocommerce_add_to_cart_validation', 'enforce_minimum_quantity', 10, 3);
    $product->set_name($args['product_name']);
    $product->set_regular_price($args['regular_price']);
    $product->set_sale_price($args['sale_price']);
    $product->set_description($args['description']);
    $product->set_short_description($args['product_short_description']);
    $product->update_meta_data('minimum_quantity', $args['minimum_quantity']);
    $product->update_meta_data('offer_expiry_date',  $args['offer_expiry']);
    $product->update_meta_data('ship_to_option', $args['ship_to_option']);
    $product->update_meta_data('pay_ship_by', $args['pay_ship_by']);
    $product->update_meta_data('offer_avaliability', $args['offer_avaliability']);
    $product->update_meta_data('brand_description', $args['brand_description']);

    $product->set_stock_status($args['instock']);

    // Save the product first
    $product->save();
    return $product;
}

function handle_shortcode_create_product() {
    if (!is_user_logged_in() || !current_user_can('publish_posts')) {
        wp_die('You do not have sufficient permissions to create products.');
    }

    if (isset($_POST['product_name'], $_POST['sale_price'], $_POST['regular_price'])) {    
        $args = array(
            'sale_price' => wc_format_decimal($_POST['sale_price']),
            'regular_price' => wc_format_decimal($_POST['regular_price']),
            'minimum_quantity' => sanitize_text_field($_POST['minimum_quantity']),
            'offer_expiry' => sanitize_text_field($_POST['offer_expiry']),
            'ship_to_option' => sanitize_text_field($_POST['ship_to_option']),
            'pay_ship' => sanitize_text_field($_POST['pay_ship']),
            'offer_avaliability' => sanitize_text_field($_POST['offer_avaliability']),
            'product_name' => sanitize_text_field($_POST['product_name']),
            'product_short_description' => sanitize_text_field($_POST['product_short_description']),
            'description' => sanitize_text_field($_POST['description']),
            'brand_description' => sanitize_text_field($_POST['brand_description']),
        );

        $product = init_product($args);

        set_product_images($product);

        wp_redirect(add_query_arg('product_created', 'success', get_permalink(get_page_by_path('sell-offer'))));
        exit;
    }
}

add_action('admin_post_create_custom_product_shortcode', 'handle_shortcode_create_product');
add_action('admin_post_nopriv_create_custom_product_shortcode', 'handle_shortcode_create_product'); // Optionally allow non-logged in users to create product
