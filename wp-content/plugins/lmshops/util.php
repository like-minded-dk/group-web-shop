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
