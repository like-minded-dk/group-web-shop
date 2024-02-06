<?php
function my_child_theme_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(),
        array( 'parent-style' ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_child_theme_styles' );

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
            $output .= "</li>";
        }
    } else {
        $output .= '<p>No products found.</p>';
    }

    $output .= '</div>';

    wp_reset_postdata();

    return $output;
}
add_shortcode('products_by_creator', 'list_products_by_creator_shortcode');

# remove breadcrumbs
add_action( 'init', 'custom_remove_storefront_breadcrumbs');

function custom_remove_storefront_breadcrumbs() {
    remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
}
