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

    $user_id = $atts['user_id'];

    // Query arguments
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1, // -1 to show all, adjust as needed
        'author' => $user_id, // Filter by author/user ID
    );

    $query = new WP_Query($args);

    $output = '<div class="products-by-creator">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;

            // Customize how each product is displayed
            $output .= '<div class="product">';
            $output .= '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>'; // Product link and title
            $output .= '</div>';
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
