<?php
include __DIR__ . '/delete_offer.php';
include __DIR__ . '/switch_status.php';

function render_output($products) {
    $output = '<div class="products-lm">';
    $output .= '<ul class="products">';
    if ($products) {
        foreach ($products as $product) {
            $image_url = wp_get_attachment_url($product->get_image_id());
            $image_url = $image_url ? $image_url : "/wp-content/uploads/woocommerce-placeholder-324x324.png";
            $sale_price = $product->get_sale_price();
            $sale_price = $sale_price ? $sale_price : '-';
            $regular_price = $product->get_regular_price();
            $permalink = get_the_permalink();
            $title = shortString($product->get_title(), 20);
            $status = get_post_status($product->get_id());
            $status = $status == 'draft' ? 'Draft' : 'Published';
            $description = $product->get_description();
            $description = $description ? shortString($description, 30) : '---';
            $currency = get_woocommerce_currency_symbol();
            $pid = $product->get_id();


            $admin_url = esc_url(admin_url('admin-post.php'));
            $product_nonce = wp_create_nonce('product_nonce');
            $edit_path = get_permalink(get_page_by_path('edit-offer'));

            $publish_icon = '<i class="bi bi-box-arrow-up"></i>';
            $draft_icon = '<i class="bi bi-box-arrow-in-down"></i>' ;
            $toggle_status_url = admin_url("admin-post.php?action=toggle_product_status&product_id=$pid&nonce=$product_nonce");
            $toggle_button_text = ('Published' === $status) ? $draft_icon : $publish_icon;
            $toggle_button_tip = ('Published' === $status) ? 'To&nbsp;Draft' : 'Publish';

            $shortlink = HOST_STRING . "/?p=" . get_the_ID();
            $copy_icon = $publish_icon = '<i class="bi bi-link"></i>';
            $clipboard = do_shortcode("[copy_clipboard content='$shortlink' text=''] $copy_icon");

            // Customize how each product is displayed
            $output .= <<<HTML
            <li class="product">
                <a class='product-permalink' href='$permalink'>
                    <img class='product-image' src='$image_url'/>
                    <p class='product-title'>$title</p>
                    <p class='product-status'>$status</p>
                    <p class='product-sale-price'>Sales price: $sale_price $currency</p>
                    <p class='product-regular-price'>Regular price $regular_price $currency</p>
                    <p class='product-description'>$description</p>
                </a>

                <div class="grid-button-container">
                    <div>
                        <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title='Copy to clipboard'>
                            <button class='btn btn-secondary product-shortlink'>$clipboard</button>
                        </span>
                    </div>
                    <form class="grid-form" action='$toggle_status_url' method='post'>
                        <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title=$toggle_button_tip>
                            <button class='btn btn-secondary product-toggle-status-btn' type="submit">
                                $toggle_button_text
                            </button>
                        </span>
                    </form>
                
                    <form class="grid-form" action='$admin_url' method='post'>
                        <input type="hidden" name="action" value="duplicate_product">
                        <input type="hidden" name="product_id" value="$pid">
                        <input type="hidden" name="nonce" value="$product_nonce">

                        <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Duplicate">
                            <button class='btn btn-secondary product-clone-btn' type="submit">
                                <i class="bi bi-copy"></i>
                            </button>
                        </span>
                    </form>
                
                    <form class="grid-form" action='$edit_path' method='get'>
                        <input type="hidden" name="product_id" value="$pid">
                        <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Duplicate">
                            <button class='btn btn-secondary product-clone-btn' type="submit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </span>
                    </form>
                
                    <form class="grid-form" action='$admin_url' method='post'>
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="product_id" value="$pid">
                        <input type="hidden" name="nonce" value="$product_nonce">
                        <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Delete">
                            <button class='btn btn-danger product-delete-btn' type="submit">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </span>
                    </form>
                </div>

                
            </li>
            HTML;
        }
    } else {
        $output .= '<p>No products found.</p>';
    }

    $output .= '</div>';

    return $output;
}

function get_current_user_offers() {
    $user_id = get_current_user_id();
    // Query arguments
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1, // -1 to show all, adjust as needed
        'author' => $user_id, // Filter by author/user ID
        'post_status' => array('draft', 'publish'),
    );

    $query = new WP_Query($args);

    $products = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            $products[] = $product;
        }
    }
    return $products;
}


function get_leader_offers() {
    global $wpdb;
    $current_user_id = get_current_user_id(); // Replace 123 with the actual ID of the current author/user
    $query = $wpdb->prepare("
        SELECT wp_posts.* FROM wp_posts
        JOIN wp_bp_follow ON wp_posts.post_author = wp_bp_follow.leader_id
        WHERE wp_posts.post_type = 'product'
        AND wp_bp_follow.follower_id = %d
        AND wp_posts.post_status IN ('draft', 'publish')
        ORDER BY wp_posts.post_date DESC
    ", $current_user_id);

    $product_ids = $wpdb->get_col($query); // Use get_col() to fetch only the IDs column

    $products = [];
    foreach ($product_ids as $product_id) {
        // Use WC_Product_Factory to get the product object
        $product = wc_get_product($product_id);
        if ($product) {
            $products[] = $product;
        }
    }
    return $products;
}

#list_products_by_creator
function list_products_by_creator()
{
    $products = get_current_user_offers();
    $output = render_output($products);
    wp_reset_postdata();
    return $output;
}
add_shortcode('products_by_creator', 'list_products_by_creator');


#list_products_by_leader
function list_products_by_leader()
{
    $products = get_leader_offers();
    $output = render_output($products);
    wp_reset_postdata();
    return $output;
}
add_shortcode('products_by_leader', 'list_products_by_leader');
