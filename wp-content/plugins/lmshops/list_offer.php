<?php
include __DIR__ . '/delete_offer.php';
include __DIR__ . '/switch_status.php';

#list_products_by_creator_shortcode
function list_products_by_creator_shortcode($atts)
{
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
        'post_status' => array('draft', 'publish'),
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
            $status = get_post_status($product->get_id());
            $status = $status == 'draft' ? 'Draft' : 'Published';
            $currency = get_woocommerce_currency_symbol();
            $pid = get_the_ID();
            $admin_url = esc_url(admin_url('admin-post.php'));
            $product_nonce = wp_create_nonce('product_nonce');
            $edit_path = get_permalink(get_page_by_path('edit-offer'));

            $toggle_status_url = admin_url("admin-post.php?action=toggle_product_status&product_id=$pid&nonce=$product_nonce");
            $toggle_button_text = ('Published' === $status) ? 'Set to Draft' : 'Publish';

            $shortlink = HOST_STRING . "/?p=" . get_the_ID();
            $clipboard = do_shortcode("[copy_clipboard content='$shortlink' text='Copy Link'] ");

            // Customize how each product is displayed
            $output .= <<<HTML
            <li class="product">';
                <a class='product-permalink' href='$permalink'>
                    <img class='product-image' src='$image_url'/>
                    <p class='product-title'>$title</p>
                    <p class='product-title'>$status</p>
                    <p class='product-sale-price'>Sales price: $sale_price $currency</p>
                    <p class='product-regular-price'>Regular price $regular_price $currency</p>
                </a>

                <button class='mb-3 btn btn-secondary product-shortlink'>$clipboard</button>

                <form action='$toggle_status_url' method='post'>
                    <button class='btn btn-secondary product-toggle-status-btn' type="submit">
                        $toggle_button_text
                    </button>
                </form>
            
                <form class="inline" action='$admin_url' method='post'>
                    <input type="hidden" name="action" value="duplicate_product">
                    <input type="hidden" name="product_id" value="$pid">
                    <input type="hidden" name="nonce" value="$product_nonce">
                    <button class='btn btn-secondary product-clone-btn' type="submit">
                        <span class='glyphicon glyphicon-refresh'></span> Duplicate
                    </button>
                </form>
            
                <form class="inline" action='$edit_path' method='get'>
                    <input type="hidden" name="product_id" value="$pid">
                    <button class='btn btn-secondary product-clone-btn' type="submit">
                        <span class='glyphicon glyphicon-wrench'></span> Edit
                    </button>
                </form>
            
                <form class="inline" action='$admin_url' method='post'>
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="$pid">
                    <input type="hidden" name="nonce" value="$product_nonce">
                    <button class='btn btn-danger product-delete-btn' type="submit">Delete</button>
                </form>
            </li>
            HTML;
        }
    } else {
        $output .= '<p>No products found.</p>';
    }

    $output .= '</div>';

    wp_reset_postdata();

    return $output;
}
add_shortcode('products_by_creator', 'list_products_by_creator_shortcode');
