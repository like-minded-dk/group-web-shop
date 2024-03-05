<?php
// $state could be 0, 1, 3, 4
// 0  = no record
// 1  = initiator record only
// 3  = reciver record only
// 4  = both record
function is_initiator($component = '') {
	$user_id = bp_loggedin_user_id();
	// $user_name = bp_get_user_firstname();

	$member_id = bp_get_member_user_id();
	if (empty($component)) {
		$component = bp_current_component();
	}
	global $wpdb;
	
	$state = 0;
	if ($component== 'friend') {
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE friend_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		if (!empty($relations1)) {
			$state += 1;
		} 
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE friend_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		if (!empty($relations2)) {
			$state += 3;
		}
	}

	if ($component== 'engagement') {
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE engagement_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		if (!empty($relations1)) {
			$state += 1;
		} 
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE engagement_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		if (!empty($relations2)) {
			$state += 3;
		}
	}
	return $state;
}

function print_initiator($component = '') {
	$user_id = bp_loggedin_user_id();
	$user_name = bp_get_user_firstname();
	$member_id = bp_get_member_user_id();
	if (empty($component)) {
		$component = bp_current_component();
	}
	global $wpdb;

	$results='';
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE friend_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE friend_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		$relations = array_merge($relations1, $relations2);
		foreach ($relations as $relation) {
			$results .= $relation->id;
			if($relation->initiator_user_id === (string) $user_id) {
				$results .= "<br> (resell) {$user_name} has frie initiator. ";
			} else {
				$results .= "<br> (resell) {$user_name} has frie receiver. ";
			}
		}
	
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE engagement_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE engagement_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		$relations = array_merge($relations1, $relations2);
		foreach ($relations as $relation) {
			$results .= $relation->id;
			if($relation->initiator_user_id === (string) $user_id) {
				$results .= "<br> (supply) {$user_name} has enga initiator. ";
			 }else {
				$results .= "<br> (supply) {$user_name} has enga receiver. ";
			}
		}
	return $results;
}

function add_engagement_button(&$buttons, $user_id, $type, $parent_class, $button_element, $parent_element) {
	if (  bp_is_active( 'engagements' ) ) {
			// It's the member's friendship requests screen
			if ( 'engagementship_request' === $type ) {
				$buttons = array(
					'accept_engagementship' => array(
						'id'                => 'accept_engagementship',
						'position'          => 5,
						'component'         => 'engagements',
						'must_be_logged_in' => true,
						'parent_element'    => $parent_element,
						'link_text'         => _x( 'Accept', 'button', 'buddypress' ),
						'parent_attr'       => array(
							'id'    => '',
							'class' => $parent_class ,
						),
						'button_element'    => $button_element,
						'button_attr'       => array(
							'class'           => 'button accept',
							'rel'             => '',
						),
					), 'reject_engagementship' => array(
						'id'                => 'reject_engagementship',
						'position'          => 15,
						'component'         => 'engagements',
						'must_be_logged_in' => true,
						'parent_element'    => $parent_element,
						'link_text'         => _x( 'Reject', 'button', 'buddypress' ),
						'parent_attr'       => array(
							'id'    => '',
							'class' => $parent_class,
						),
						'button_element'    => $button_element,
						'button_attr'       => array (
							'class'           => 'button reject',
							'rel'             => '',
						),
					),
				);

				// If button element set add nonce link to data attr
				if ( 'button' === $button_element ) {
					$buttons['accept_engagementship']['button_attr']['data-bp-nonce'] = bp_get_engagement_accept_request_link();
					$buttons['reject_engagementship']['button_attr']['data-bp-nonce'] = bp_get_engagement_reject_request_link();
				} else {
					$buttons['accept_engagementship']['button_attr']['href'] = bp_get_engagement_accept_request_link();
					$buttons['reject_engagementship']['button_attr']['href'] = bp_get_engagement_reject_request_link();
				}
			// It's any other members screen
			} else {
				$button_args = bp_get_add_engagement_button_args( $user_id );

				if ( array_filter( $button_args ) ) {
					$buttons['member_engagementship'] = array(
						'id'                => 'member_engagementship',
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
					if ( 'button' === $button_element && 'awaiting_response' !== $button_args['id'] ) {
						$buttons['member_engagementship']['button_attr']['data-bp-nonce'] = $button_args['link_href'];
					} else {
						$buttons['member_engagementship']['button_element'] = 'a';
						$buttons['member_engagementship']['button_attr']['href'] = $button_args['link_href'];
					}
				}
			}
			
		}
	return $buttons;
}

function add_friend_button(&$buttons, $user_id, $type, $parent_class, $button_element, $parent_element) {
    if ( bp_is_active( 'friends' ) ) {
        // It's the member's friendship requests screen
        if ( 'friendship_request' === $type ) {
            $buttons = array(
                'accept_friendship' => array(
                    'id'                => 'accept_friendship',
                    'position'          => 5,
                    'component'         => 'friends',
                    'must_be_logged_in' => true,
                    'parent_element'    => $parent_element,
                    'link_text'         => _x( 'Accept', 'button', 'buddypress' ),
                    'parent_attr'       => array(
                        'id'    => '',
                        'class' => $parent_class ,
                    ),
                    'button_element'    => $button_element,
                    'button_attr'       => array(
                        'class'           => 'button accept',
                        'rel'             => '',
                    ),
                ), 'reject_friendship' => array(
                    'id'                => 'reject_friendship',
                    'position'          => 15,
                    'component'         => 'friends',
                    'must_be_logged_in' => true,
                    'parent_element'    => $parent_element,
                    'link_text'         => _x( 'Reject', 'button', 'buddypress' ),
                    'parent_attr'       => array(
                        'id'    => '',
                        'class' => $parent_class,
                    ),
                    'button_element'    => $button_element,
                    'button_attr'       => array (
                        'class'           => 'button reject',
                        'rel'             => '',
                    ),
                ),
            );
    
            // If button element set add nonce link to data attr
            if ( 'button' === $button_element ) {
                $buttons['accept_friendship']['button_attr']['data-bp-nonce'] = bp_get_friend_accept_request_link();
                $buttons['reject_friendship']['button_attr']['data-bp-nonce'] = bp_get_friend_reject_request_link();
            } else {
                $buttons['accept_friendship']['button_attr']['href'] = bp_get_friend_accept_request_link();
                $buttons['reject_friendship']['button_attr']['href'] = bp_get_friend_reject_request_link();
            }
    
        // It's any other members screen
        } else {
            $button_args = bp_get_add_friend_button_args( $user_id );
    
            if ( array_filter( $button_args ) ) {
                $buttons['member_friendship'] = array(
                    'id'                => 'member_friendship',
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
                if ( 'button' === $button_element && 'awaiting_response' !== $button_args['id'] ) {
                    $buttons['member_friendship']['button_attr']['data-bp-nonce'] = $button_args['link_href'];
                } else {
                    $buttons['member_friendship']['button_element'] = 'a';
                    $buttons['member_friendship']['button_attr']['href'] = $button_args['link_href'];
                }
            }
        }
    }
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
