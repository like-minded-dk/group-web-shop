<?php
require __DIR__ . '/../lm/lm-template-functions.php';
require __DIR__ . '/../lm/lm-template-button-args.php';
require __DIR__ . '/../lm/lm-template-request-btn.php';

function add_relation_button($comp, &$btns, $user_id, $type, $parent_class, $button_element, $parent_element) {
	$isf = $comp == 'friend';
	$component = $isf ? 'friends'            : 'engagements';
	$request_type      = $isf ? 'friendship_request' : 'engagementship_request';
	if (  bp_is_active( $component ) ) {
		// It's the member's friendship requests screen
		if ( $request_type === $type ) {
			$btns = add_request_button($isf, $btns, $component, $parent_class, $button_element, $parent_element);
		// It's any other members screen
		} else {
			$btns = add_member_button($isf, $btns, $user_id, $parent_class, $button_element, $parent_element);
		}
	}
	return $btns;
}

function add_request_button($isf, &$btns, $component, $parent_class, $button_element, $parent_element) {
	$accept_key = $isf ? 'accept_friend' : 'accept_engagement';
	$reject_key = $isf ? 'reject_friend' : 'reject_engagement';
	$accept_link_fn = $isf ? 'bp_get_friend_accept_request_link' : 'bp_get_engagement_accept_request_link';
	$reject_link_fn = $isf ? 'bp_get_friend_reject_request_link' : 'bp_get_engagement_reject_request_link';
	$btns = get_request_btn($component, $parent_class, $button_element, $parent_element);

	// If button element set add nonce link to data attr
	if ( 'button' === $button_element ) {
		$btns[$accept_key]['button_attr']['data-bp-nonce'] = $accept_link_fn();
		$btns[$reject_key]['button_attr']['data-bp-nonce'] = $reject_link_fn();
	} else {
		$btns[$accept_key]['button_attr']['href'] = $accept_link_fn();
		$btns[$reject_key]['button_attr']['href'] = $reject_link_fn();
	}
	return $btns;
}

function add_member_button($isf, &$btns, $user_id, $parent_class, $button_element, $parent_element) {
	$btn_key         = $isf ? 'member_friend' : 'member_engagement';
	$get_btn_args_fn = $isf ? 'bp_get_add_friend_button_args' : 'bp_get_add_engagement_button_args';
	$btn_args = $get_btn_args_fn( $user_id );
	if ( array_filter( $btn_args ) ) {
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

		// If button element set add nonce link to data attr
		if ( 'button' === $button_element && 
			 'awaiting_engagement' !== $btn_args['id'] &&
			 'awaiting_friend' !== $btn_args['id']
		) {
			$btns[$btn_key]['button_attr']['data-bp-nonce'] = $btn_args['link_href'];
		} else {
			$btns[$btn_key]['button_element'] = 'a';
			$btns[$btn_key]['button_attr']['href'] = $btn_args['link_href'];
		}
	}
	return $btns;
};


function add_profile_button(&$buttons, $type, $parent_class, $parent_element) {
	// Only add The public and private messages when not in a loop
	if ( 'profile' === $type ) {
		if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) {
			$button_args = bp_activity_get_public_message_button_args();
			if ( array_filter( $button_args ) ) {
				/*
				* This button should remain as an anchor link.
				* Hardcode the use of anchor elements if button arg passed in for other elements.
				*/
				$buttons['public_message'] = array(
					'id'                => $button_args['id'],
					'position'          => 15,
					'component'         => $button_args['component'],
					'must_be_logged_in' => $button_args['must_be_logged_in'],
					'block_self'        => $button_args['block_self'],
					'parent_element'    => $parent_element,
					'button_element'    => 'a',
					'link_text'         => $button_args['link_text'],
					'link_title'        => $button_args['link_title'],
					'parent_attr'       => array(
						'id'    => $button_args['wrapper_id'],
						'class' => $parent_class,
					),
					'button_attr'       => array(
						'href'             => $button_args['link_href'],
						'id'               => '',
						'class'            => $button_args['link_class'],
					),
				);
			}
		}

		if ( bp_is_active( 'messages' ) ) {
			$button_args = bp_get_send_message_button_args();

			if ( array_filter( $button_args ) ) {
				/*
				* This button should remain as an anchor link.
				* Hardcode the use of anchor elements if button arg passed in for other elements.
				*/
				$buttons['private_message'] = array(
					'id'                => $button_args['id'],
					'position'          => 25,
					'component'         => $button_args['component'],
					'must_be_logged_in' => $button_args['must_be_logged_in'],
					'block_self'        => $button_args['block_self'],
					'parent_element'    => $parent_element,
					'button_element'    => 'a',
					'link_text'         => $button_args['link_text'],
					'link_title'        => $button_args['link_title'],
					'parent_attr'       => array(
						'id'    => $button_args['wrapper_id'],
						'class' => $parent_class,
					),
					'button_attr'       => array(
						'href'  => bp_get_send_private_message_link(),
						'id'    => false,
						'class' => $button_args['link_class'],
						'rel'   => '',
						'title' => '',
					),
				);
			}
		}
	}
}
