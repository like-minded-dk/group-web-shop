<?php
require __DIR__ . '/../lm/lm-template-functions.php';
require __DIR__ . '/../lm/lm-template-button-args.php';
require __DIR__ . '/../lm/lm-template-request-btn.php';

function add_relation_button($comp, &$buttons, $user_id, $type, $parent_class, $button_element, $parent_element) {
	$conf = array (
		'friend' => array(
			'component' => 'friends',
			'request_type' => 'friendship_request',
			'accept_ship' => 'accept_friend',
			'reject_ship' => 'reject_friend',
			'member_ship' => 'member_friend',
			'accept_link' => 'bp_get_friend_accept_request_link',
			'reject_link' => 'bp_get_friend_reject_request_link',
			'add_btn_args' => 'bp_get_add_friend_button_args'
		),
		'engagement' => array(
			'component' => 'engagements',
			'request_type' => 'engagementship_request',
			'accept_ship' => 'accept_engagement',
			'reject_ship' => 'reject_engagement',
			'member_ship' => 'member_engagement',
			'accept_link' => 'bp_get_engagement_accept_request_link',
			'reject_link' => 'bp_get_engagement_reject_request_link',
			'add_btn_args' => 'bp_get_add_engagement_button_args'
		)
	);
	$cf = $conf[$comp];

	if (  bp_is_active( $cf['component'] ) ) {
		// It's the member's friendship requests screen
		if ( $cf['request_type'] === $type ) {
			$buttons = get_request_btn($cf['component'], $parent_element, $button_element, $parent_class );

			// If button element set add nonce link to data attr
			if ( 'button' === $button_element ) {
				$buttons[$cf['accept_ship']]['button_attr']['data-bp-nonce'] = $cf['accept_link']();
				$buttons[$cf['reject_ship']]['button_attr']['data-bp-nonce'] = $cf['reject_link']();
			} else {
				$buttons[$cf['accept_ship']]['button_attr']['href'] = $cf['accept_link']();
				$buttons[$cf['reject_ship']]['button_attr']['href'] = $cf['reject_link']();
			}
		// It's any other members screen
		} else {
			$button_args = $cf['add_btn_args']( $user_id );

			if ( array_filter( $button_args ) ) {
				$buttons[$cf['member_ship']] = array(
					'id'                => $cf['member_ship'],
					'position'          => 5,
					'component'         => $button_args['component'],
					'must_be_logged_in' => $button_args['must_be_logged_in'],
					'block_self'        => $button_args['block_self'],
					'parent_element'    => $parent_element,
					'link_text'         => $button_args['link_text'],
					'link_title'        => $button_args['link_title'],
					'parent_attr'       => array(
						'id'    => $button_args['wrapper_id'],
						'class' => $parent_class . ' ' . $button_args['wrapper_class'],
					),
					'button_element'    => $button_element,
					'button_attr'       => array(
						'id'    => $button_args['link_id'],
						'class' => $button_args['link_class'],
						'rel'   => $button_args['link_rel'],
						'title' => '',
					),
				);

				// If button element set add nonce link to data attr
				if ( 'button' === $button_element && 
					 'awaiting_engagement' !== $button_args['id'] &&
					 'awaiting_friend' !== $button_args['id']
				) {
					$buttons[$cf['member_ship']]['button_attr']['data-bp-nonce'] = $button_args['link_href'];
				} else {
					$buttons[$cf['member_ship']]['button_element'] = 'a';
					$buttons[$cf['member_ship']]['button_attr']['href'] = $button_args['link_href'];
				}
			}
		}
	}
	return $buttons;
}


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
