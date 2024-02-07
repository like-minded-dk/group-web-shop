<?php
# delete handler
function handle_delete_product() {
    if (isset($_GET['product_id'], $_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'delete_product_nonce')) {
        $product_id = intval($_GET['product_id']);
        $current_user_id = get_current_user_id();
        $post_author_id = get_post_field('post_author', $product_id);

        // Check if the current user is the author of the product or has the capability to delete products
        if ($current_user_id === $post_author_id || current_user_can('delete_others_posts')) {
            if (wp_delete_post($product_id, true)) {
                wp_redirect(add_query_arg('product_deleted', 'success', get_permalink(get_page_by_path('lm-my-offers'))));
                exit;
            } else {
                wp_die('Error deleting product.');
            }
        } else {
            wp_die('You do not have permission to delete this product.');
        }
    } else {
        wp_die('Security check failed or invalid product.');
    }
}
add_action('admin_post_delete_product', 'handle_delete_product');
add_action('admin_post_nopriv_delete_product', 'handle_delete_product'); // Optionally, if you want non-logged-in users to trigger this action, though this is not recommended for deletion operations.

# delete shorcode
function show_product_deletion_success_message() {
    if (isset($_GET['product_deleted']) && $_GET['product_deleted'] == 'success') {
        return '<p>Product deleted successfully!</p>';
    }
}
add_shortcode('show_product_deletion_message', 'show_product_deletion_success_message');


#list_products_by_creator_shortcode
function list_products_by_creator_shortcode($atts) {
    // Shortcode attributes for user ID
    $atts = shortcode_atts(array(
        'user_id' => '', // Default user ID if none provided
    ), $atts, 'products_by_creator');

    $user_id = get_current_user_id();

    // Query arguments
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1, // -1 to show all, adjust as needed
        'author' => $user_id, // Filter by author/user ID
    );

    $query = new WP_Query($args);

    $output = '<div class="products-by-creator">';
    $output .= '<ul class="products columns-3">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            
            $image_url = wp_get_attachment_url($product->get_image_id());
            $image_url = $image_url ? $image_url : "/wp-content/uploads/woocommerce-placeholder-324x324.png";
            $sale_price = $product->get_sale_price();
            $sale_price = $sale_price ? $sale_price : '-';
            $regular_price = $product->get_regular_price();
            $permalink = get_the_permalink();
            $title = get_the_title();
            $currency = get_woocommerce_currency_symbol();

            // Customize how each product is displayed
            $output .= '<li class="product">';
            $output .= '<a class="product-permalink" href="permalink">';
            $output .= "<img class='product-image' src='$image_url'/>";
            $output .= "<p class='product-title'>$title</p>";
            $output .= "<p class='product-sale-price'>Sales price: $sale_price $currency</p>";
            $output .= "<p class='product-regular-price'>Regular price $regular_price $currency</p>";
            $output .= "</a>";
            $output .= '<br><button class="product-delete-btn" onclick="deleteProduct(' . get_the_ID() . ')">Delete Offer</button>';
            $output .= "</li>";

            $output .= '
                <script>
                function deleteProduct(productId) {
                    if (confirm("Are you sure you want to delete this product?")) {
                        window.location.href = "' . admin_url('admin-post.php') . '?action=delete_product&product_id=" + productId + "&nonce=' . wp_create_nonce('delete_product_nonce') . '";
                    }
                }
                </script>
            ';
        }
    } else {
        $output .= '<p>No products found.</p>';
    }

    $output .= '</div>';

    wp_reset_postdata();

    return $output;
}
add_shortcode('products_by_creator', 'list_products_by_creator_shortcode');
