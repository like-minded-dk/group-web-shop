<?php
function add_member_button($isf, &$btns, $comp, $user_id, $css_args) {
	[ $parent_class, $button_element, $parent_element] = $css_args;
	$btn_key         = $isf ? 'member_friend' : 'member_engagement';
	$get_btn_args_fn = $isf ? 'bp_get_add_friend_button_args' : 'bp_get_add_engagement_button_args';
	// $accept_key = $isf ? 'accept_key' : 'accept_friend';
	// $reject_key = $isf ? 'reject_key' : 'reject_friend';
	$btn_args = $get_btn_args_fn( $user_id );
	if ( array_filter( $btn_args ) ) {
		// If button element set add nonce link to data attr
		if ( 'awaiting_engagement' == $btn_args['id'] ||
			 'awaiting_friend' == $btn_args['id'] ) {
			// todo lm remove button
			add_request_button($isf, $btns, $comp, $user_id, $css_args);
			// get_member_btn_args($btns, $accept_key, $btn_args, $css_args);
			// get_member_btn_args($btns, $reject_key, $btn_args, $css_args);
			// $btns[$btn_key]['button_attr']['class'] = $btns[$btn_key]['button_attr']['class'] . ' hidden';
		} else {
			get_member_btn_args($btns, $btn_key, $btn_args, $css_args);
			if ( 'button' === $button_element) {
				$btns[$btn_key]['button_attr']['data-bp-nonce'] = $btn_args['link_href'];
			} else {
				$btns[$btn_key]['button_element'] = 'a';
				$btns[$btn_key]['button_attr']['href'] = $btn_args['link_href'];
			}
		}
	}
};


function get_member_btn_args(&$btns, $btn_key, $btn_args, $css_args){
	[ $parent_class, $button_element, $parent_element] = $css_args;
	$btns[$btn_key] = array(
		'id'                => $btn_key,
		'position'          => 5,
		'component'         => $btn_args['component'],
		'must_be_logged_in' => $btn_args['must_be_logged_in'],
		'block_self'        => $btn_args['block_self'],
		'parent_element'    => $parent_element,
		'link_text'         => $btn_args['link_text'],
		'link_title'        => $btn_args['link_title'],
		'parent_attr'       => array(
			'id'    => $btn_args['wrapper_id'],
			'class' => $parent_class . ' ' . $btn_args['wrapper_class'],
		),
		'button_element'    => $button_element,
		'button_attr'       => array(
			'id'    => $btn_args['link_id'],
			'class' => $btn_args['link_class'],
			'rel'   => $btn_args['link_rel'],
			'title' => '',
		),
	);
	return $btns;
}
