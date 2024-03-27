<?php
function shortString($string, $cut = 50, $ellipsis = '...') {
    // Check if the string is longer than $cut characters
    if (mb_strlen($string) > $cut) {
        // Cut the string to the first $cut characters
        return mb_substr($string, 0, $cut) . $ellipsis;
    } else {
        // Return the original string if it's $cut characters or less
        return $string;
    }
}

function is_product_owner( $product_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$user_id = get_current_user_id();

	if ( ! dokan_is_user_seller( $user_id ) ) {
		return false;
	}

	$product_author = get_post_field( 'post_author', $product_id );

	if ( $user_id != $product_author ) {
		return false;
	}
    
	return true;
}


// Path: wp-content/plugins/lmshops/util.php
add_filter('woocommerce_add_to_cart_validation', 'enforce_minimum_quantity', 10, 3);
function enforce_minimum_quantity($passed, $product_id, $quantity) {
    $minimum_quantity = get_post_meta($product_id, 'minimum_quantity', true);
    
    if ($minimum_quantity > 0 && $quantity < $minimum_quantity) {
        // Notice to the customer
        wc_add_notice(sprintf('You must purchase a minimum of %s units for this product.', $minimum_quantity), 'error');
        return false;
    }

    return $passed;
}


// Assuming you have a product ID
$product_id = get_the_ID(); // Or any other method to get the product ID
$offer_expiry_date = get_post_meta($product_id, 'offer_expiry_date', true);

if (!empty($offer_expiry_date)) {
    $expiry_timestamp = strtotime($offer_expiry_date);
    $current_timestamp = current_time('timestamp');

    if ($expiry_timestamp < $current_timestamp) {
        // Product is expired
        // You can customize your action here, e.g., display a message, hide the product, etc.
        echo 'This offer has expired.';
    } else {
        // Product is not expired
        // Normal display or action for the product
    }
}
