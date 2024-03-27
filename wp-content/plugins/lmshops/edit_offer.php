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
    $product_simple = wc_get_product($product_id);

    if (!$product_simple) {
        return '<p>Product not found.</p>';
    }

    ob_start();
    $product = array(
        'product_id' => esc_attr($product_id),
        'product_name' => esc_attr($product_simple->get_name()),
        'sale_price' => esc_attr($product_simple->get_sale_price()),
        'regular_price' => esc_attr($product_simple->get_regular_price()) ,
        'description' => esc_attr($product_simple->get_description()),
        'product_short_description' => esc_attr($product_simple->get_short_description()),
        'minimum_quantity' => esc_attr(get_post_meta($product_id, 'minimum_quantity', true)),
        'offer_expiry' => esc_attr(get_post_meta($product_id, 'offer_expiry', true)),
        'ship_to_option' => esc_attr(get_post_meta($product_id, 'ship_to_option', true)),
        'pay_ship' => esc_attr(get_post_meta($product_id, 'pay_ship', true)),
        'offer_avaliability' => esc_attr(get_post_meta($product_id, 'offer_avaliability', true)),
        'brand_description' => esc_attr(get_post_meta($product_id, 'brand_description', true)),

    );
    include "offer.html";
    // Get the contents of the output buffer (the included file's content)
    $offer_html = ob_get_clean();
    // Now $offer_html contains the content of offer.html, and you can concatenate it as intended
    $url = esc_url(admin_url('admin-post.php'));

    $form_html = <<<HTML
        <form action="{$url}" method="post" enctype="multipart/form-data">'
            <input type="hidden" name="action" value="update_custom_product_shortcode">
            {$offer_html}
        </form>
    HTML;


    return $form_html;
}
add_shortcode('lms_edit_product_form', 'custom_product_edit_form_shortcode');


function handle_shortcode_update_product() {
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_die('You do not have sufficient permissions to edit products.');
    }

    if (isset($_POST['product_name'], $_POST['description'], $_POST['sale_price'], $_POST['regular_price'], $_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $product_name = sanitize_text_field($_POST['product_name']);
        $description = sanitize_text_field($_POST['description']);
        $regular_price = wc_format_decimal($_POST['regular_price']);
        $sale_price = wc_format_decimal($_POST['sale_price']);

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_die('Product not found.');
        }

        $product->set_name($product_name);
        $product->set_description($description);
        $product->set_price($regular_price);
        $product->set_sale_price($sale_price);
        $product->set_regular_price($regular_price);
        
        // Optionally handle image update
        set_product_images($product);

        $product->save();

        wp_redirect(add_query_arg('product_updated', 'success', get_permalink(get_page_by_path('sell-offer'))));
        exit;
    } else {
        wp_die('Edit in Security check failed or invalid product.');
    }
}
add_action('admin_post_update_custom_product_shortcode', 'handle_shortcode_update_product');
