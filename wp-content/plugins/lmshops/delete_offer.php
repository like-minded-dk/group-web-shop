<?php
# delete handler
function handle_delete_product() {
    if (isset($_POST['product_id'], $_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'product_nonce')) {
        $product_id = intval($_POST['product_id']);
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
// add_action('admin_post_nopriv_delete_product', 'handle_delete_product'); // Optionally, if you want non-logged-in users to trigger this action, though this is not recommended for deletion operations.

# delete shorcode, it is optional 
function show_product_deletion_success_message() {
    if (isset($_GET['product_deleted']) && $_GET['product_deleted'] == 'success') {
        return '<p>Product deleted successfully!</p>';
    }
}
add_shortcode('show_product_deletion_message', 'show_product_deletion_success_message');
