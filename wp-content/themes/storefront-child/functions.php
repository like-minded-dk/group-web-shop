<?php
// function my_child_theme_styles() {
//     wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
//     wp_enqueue_style( 'child-style', get_stylesheet_uri(),
//         array( 'parent-style' ),
//         wp_get_theme()->get('Version')
//     );
// }
// add_action( 'wp_enqueue_scripts', 'my_child_theme_styles' );

// #list_products_by_creator_shortcode
// function list_products_by_creator_shortcode($atts) {
//     // Shortcode attributes for user ID
//     $atts = shortcode_atts(array(
//         'user_id' => '', // Default user ID if none provided
//     ), $atts, 'products_by_creator');

//     $user_id = get_current_user_id();

//     // Query arguments
//     $args = array(
//         'post_type' => 'product',
//         'posts_per_page' => -1, // -1 to show all, adjust as needed
//         'author' => $user_id, // Filter by author/user ID
//     );

//     $query = new WP_Query($args);

//     $output = '<div class="products-by-creator">';
//     $output .= '<ul class="products columns-3">';

//     if ($query->have_posts()) {
//         while ($query->have_posts()) {
//             $query->the_post();
//             global $product;
            
//             $image_url = wp_get_attachment_url($product->get_image_id());
//             $image_url = $image_url ? $image_url : "/wp-content/uploads/woocommerce-placeholder-324x324.png";
//             $sale_price = $product->get_sale_price();
//             $sale_price = $sale_price ? $sale_price : '-';
//             $regular_price = $product->get_regular_price();
//             $permalink = get_the_permalink();
//             $title = get_the_title();
//             $currency = get_woocommerce_currency_symbol();

//             // Customize how each product is displayed
//             $output .= '<li class="product">';
//             $output .= '<a class="product-permalink" href="permalink">';
//             $output .= "<img class='product-image' src='$image_url'/>";
//             $output .= "<p class='product-title'>$title</p>";
//             $output .= "<p class='product-sale-price'>Sales price: $sale_price $currency</p>";
//             $output .= "<p class='product-regular-price'>Regular price $regular_price $currency</p>";
//             $output .= "</a>";
//             $output .= "</li>";
//         }
//     } else {
//         $output .= '<p>No products found.</p>';
//     }

//     $output .= '</div>';

//     wp_reset_postdata();

//     return $output;
// }
// add_shortcode('products_by_creator', 'list_products_by_creator_shortcode');


// function custom_product_creation_form_shortcode() {
//     // Check if the user is logged in and has permission to create a product
//     if (!is_user_logged_in() || !current_user_can('publish_posts')) {
//         return '<p>You must be logged in and have sufficient permissions to create products.</p>';
//     }

//     $form_html = '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">
//         <input type="hidden" name="action" value="create_custom_product_shortcode">
        
//         <label for="product_name">Product Name:</label>
//         <input type="text" id="product_name" name="product_name" required>
        
//         <label for="product_price">Product Price:</label>
//         <input type="text" id="product_price" name="product_price" required>
        
//         <input type="submit" value="Create Product">
//     </form>';

//     return $form_html;
// }
// add_shortcode('create_product_form', 'custom_product_creation_form_shortcode');

// function handle_shortcode_create_product() {
//     if (isset($_POST['product_name']) && isset($_POST['product_price'])) {
//         if (!is_user_logged_in() || !current_user_can('publish_posts')) {
//             wp_die('You do not have sufficient permissions to create products.');
//         }

//         $product_name = sanitize_text_field($_POST['product_name']);
//         $product_price = wc_format_decimal($_POST['product_price']);
        
//         $product = new WC_Product_Simple();
//         $product->set_name($product_name);
//         $product->set_price($product_price);
//         $product->set_regular_price($product_price);
//         $product->set_stock_status('instock');
//         $product_id = $product->save();

//         // Redirect to the new product's page, a confirmation page, or add a query arg for a success message
//         $page = get_page_by_path('product-creation-success'); // Get the success page by its slug
//         if ($page) {
//             $redirect_url = add_query_arg('product_created', 'success', get_permalink($page->ID));
//             wp_redirect($redirect_url);
//             exit;
//         }   
//         wp_redirect(add_query_arg('product_created', 'success', get_permalink()));
//         exit;
//     }
// }
// add_action('admin_post_create_custom_product_shortcode', 'handle_shortcode_create_product');
// add_action('admin_post_nopriv_create_custom_product_shortcode', 'handle_shortcode_create_product'); // Optionally allow non-logged in users to create product

// function show_product_creation_success_message() {
//     if (isset($_GET['product_created']) && $_GET['product_created'] == 'success') {
//         return '<p>Product created successfully!</p>';
//     }
// }
// add_shortcode('show_product_creation_message', 'show_product_creation_success_message');





// # remove breadcrumbs
// add_action( 'init', 'custom_remove_storefront_breadcrumbs');

// function custom_remove_storefront_breadcrumbs() {
//     remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
// }
