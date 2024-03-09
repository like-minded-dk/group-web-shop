<?php
/**
 * engagements Ajax functions
 *
 * @since 3.0.0
 * @version 3.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function() {
	$ajax_actions = array(
		array(
			'engagements_remove_engagements' => array(
				'function' => 'bp_nouveau_ajax_addremove_engagement',
				'nopriv'   => false,
			),
		),
		array(
			'engagements_add_engagements' => array(
				'function' => 'bp_nouveau_ajax_addremove_engagement',
				'nopriv'   => false,
			),
		),
		array(
			'engagements_not_friends_from_engagements' => array(
				'function' => 'bp_nouveau_ajax_addremove_friends_from_engagements',
				'nopriv'   => false,
			),
		),
		array(
			'engagements_remove_friends_from_engagements' => array(
				'function' => 'bp_nouveau_ajax_addremove_friends_from_engagements',
				'nopriv'   => false,
			),
		),
		array(
			'engagements_withdraw_engagementship' => array(
				'function' => 'bp_nouveau_ajax_addremove_engagement',
				'nopriv'   => false,
			),
		),
		array(
			'engagements_accept_engagementship' => array(
				'function' => 'bp_nouveau_ajax_addremove_engagement',
				'nopriv'   => false,
			),
		),
		array(
			'engagements_reject_engagementship' => array(
				'function' => 'bp_nouveau_ajax_addremove_engagement',
				'nopriv'   => false,
			),
		),
	);

	foreach ( $ajax_actions as $ajax_action ) {
		$action = key( $ajax_action );

		add_action( 'wp_ajax_' . $action, $ajax_action[ $action ]['function'] );

		if ( ! empty( $ajax_action[ $action ]['nopriv'] ) ) {
			add_action( 'wp_ajax_nopriv_' . $action, $ajax_action[ $action ]['function'] );
		}
	}
}, 12 );

/**
 * engagement/un-engagement a user via a POST request.
 *
 * @since 3.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_addremove_engagement() {
	$user_id = bp_loggedin_user_id();
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action (engagement). Please try again.', 'buddypress' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['nonce'] ) || empty( $_POST['item_id'] ) || ! bp_is_active( 'engagements' ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_nouveau_engagements';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}
	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	// Cast fid as an integer.
	$engagement_id = (int) $_POST['item_id'];
	
	// Check if the user exists only when the engagement ID is not a Frienship ID.
	if ( isset( $_POST['action'] ) && $_POST['action'] !== 'engagements_accept_engagementship' && $_POST['action'] !== 'engagements_reject_engagementship' ) {
		$user = get_user_by( 'id', $engagement_id );
		if ( ! $user ) {
			wp_send_json_error(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'No member found by that ID.', 'buddypress' )
					),
				)
			);
		}
	}

	$check_is_engagement = BP_engagements_engagementship::check_is_engagement( $user_id, $engagement_id );

	error_log('ajax_addremove_e POST action -e 127: '.$_POST['action']);
	error_log('$check_is_engagement -e 128: '.$check_is_engagement);
	// In the 2 first cases the $engagement_id is a engagementship id.
	if ( ! empty( $_POST['action'] ) && 'engagements_accept_engagementship' === $_POST['action'] ) {
		if ( ! engagements_accept_engagementship( $engagement_id ) ) {
			wp_send_json_error(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'There was a problem accepting that request. Please try again.', 'buddypress' )
					),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'engagementship accepted.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => true,
				)
			);
		}

	// Rejecting a engagementship
	} elseif ( ! empty( $_POST['action'] ) && 'engagements_reject_engagementship' === $_POST['action'] ) {
		if ( ! engagements_reject_engagementship( $engagement_id ) ) {
			wp_send_json_error(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'There was a problem rejecting that request. Please try again.', 'buddypress' )
					),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'engagementship rejected.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => true,
				)
			);
		}

	// Trying to remove engagementship.
	} elseif ( 'is_engagement' === $check_is_engagement ) {
		error_log('>>is_engagement -e 168: '. $user_id . ' - ' . $engagement_id);

		if ( ! engagements_remove_engagement( $user_id, $engagement_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'engagementship could not be removed. 185 -e', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			$is_user = bp_is_my_profile();

			if ( ! $is_user ) {
				$response = array( 'contents' => bp_get_add_engagement_button( $engagement_id ) );
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'engagementship cancelled.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => $is_user,
				);
			}

			wp_send_json_success( $response );
		}

	// Trying to cancel engagementship in existed button..
	} elseif ( 'exist_initiator_engagement' === $check_is_engagement ) {
		// todo:
		error_log('>>exist_initiator_engagement -e 197: '. $user_id . ' - ' . $engagement_id);

		if ( ! engagements_remove_engagement( $engagement_id, $user_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'engagementship could not be cancelled. 216 -e', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			$is_user = bp_is_my_profile();

			if ( ! $is_user ) {
				$response = array( 'contents' => bp_get_add_engagement_button( $engagement_id ) );
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'engagementship cancelled.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => $is_user,
				);
			}

			wp_send_json_success( $response );
		}

	// Trying to cancel engagementship in existed button..
	} elseif ( 'exist_more_engagements' === $check_is_engagement ) {
		error_log('>>exist_more_engagements -e 241: '. $user_id . ' - ' . $engagement_id);

		if ( ! engagements_remove_engagement( $user_id, $engagement_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'engagementship could not be cancelled 403 -e.', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			$is_user = bp_is_my_profile();

			if ( ! $is_user ) {
				$response = array( 'contents' => bp_get_add_engagement_button( $engagement_id ) );
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'engagementship cancelled.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => $is_user,
				);
			}

			wp_send_json_success( $response );
		}

	// Trying to cancel pending request.
	} elseif ( 'not_engagements' === $check_is_engagement && $_POST['action'] == 'engagements_withdraw_engagementship' ) {
		error_log(json_encode('>not_engagements -f 271: friend_id '. $engagement_id));
		if ( friends_withdraw_friendship( $user_id, $engagement_id ) ) {
			wp_send_json_success( array( 'contents' => bp_get_add_engagement_button( $engagement_id ) ) );
		} else {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'engagementship request could not be cancelled.', 'buddypress' )
			);

			wp_send_json_error( $response );
		}

	// Trying to request engagementship.
	} elseif ( 'not_engagements' === $check_is_engagement ) {
		error_log(json_encode('>not_engagements -f 271'));
		if ( ! engagements_add_engagement( $user_id, $engagement_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'engagementship could not be requested.', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			wp_send_json_success( array( 'contents' => bp_get_add_engagement_button( $engagement_id ) ) );
		}
	
	// Trying to cancel pending reques
	} elseif ( 'pending_engagement' === $check_is_engagement ) {
		error_log(json_encode('>pending_engagement -f 299: friend_id '. $engagement_id));
		if ( engagements_withdraw_engagementship( $user_id, $engagement_id ) ) {
			wp_send_json_success( array( 'contents' => bp_get_add_engagement_button( $engagement_id ) ) );
		} else {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'engagementship request could not be cancelled.', 'buddypress' )
			);

			wp_send_json_error( $response );
		}

	// Request already pending.
	} else {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Request Pending Engagement (engagement)', 'buddypress' )
		);

		wp_send_json_error( $response );
	}
}

function bp_nouveau_ajax_addremove_friends_from_engagements() {
	$user_id = bp_loggedin_user_id();
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action (friend). Please try again.', 'buddypress' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['nonce'] ) || empty( $_POST['item_id'] ) || ! bp_is_active( 'friends' ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_nouveau_friends';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	// Cast fid as an integer.
	$friend_id = (int) $_POST['item_id'];

	// Check if the user exists only when the Friend ID is not a Frienship ID.
	if ( isset( $_POST['action'] ) && $_POST['action'] !== 'friends_accept_friendship' && $_POST['action'] !== 'friends_reject_friendship' ) {
		$user = get_user_by( 'id', $friend_id );
		if ( ! $user ) {
			wp_send_json_error(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'No member found by that ID.', 'buddypress' )
					),
				)
			);
		}
	}

	$check_is_friend = BP_Friends_Friendship::check_is_friend( $user_id, $friend_id );
	
	error_log('>>ajax_addremove_f from e -e 369 post action '. $_POST['action']);
	error_log('$check_is_friend -e 370: '.$check_is_friend);
	// In the 2 first cases the $friend_id is a friendship id.
	if ( ! empty( $_POST['action'] ) && 'friends_accept_friendship' === $_POST['action'] ) {
		if ( ! friends_accept_friendship( $friend_id ) ) {
			wp_send_json_error(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'There was a problem accepting that request. Please try again.', 'buddypress' )
					),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'Friendship accepted.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => true,
				)
			);
		}

	// Rejecting a friendship
	} elseif ( ! empty( $_POST['action'] ) && 'friends_reject_friendship' === $_POST['action'] ) {
		if ( ! friends_reject_friendship( $friend_id ) ) {
			wp_send_json_error(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'There was a problem rejecting that request. Please try again.', 'buddypress' )
					),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'Friendship rejected.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => true,
				)
			);
		}

	// Trying to cancel friendship.
	} elseif ( 'is_friend' === $check_is_friend ) {
		error_log('>>is_friend 386: '. $user_id . ' - ' . $friend_id);

		if ( ! friends_remove_friend( $user_id, $friend_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'Friendship could not be cancelled.', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			$is_user = bp_is_my_profile();

			if ( ! $is_user ) {
				$response = array( 'contents' => bp_get_add_friend_button( $friend_id ) );
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'Friendship cancelled.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => $is_user,
				);
			}

			wp_send_json_success( $response );
		}

	// Trying to cancel friendship in existed button..
	} elseif ( 'exist_more_friends' === $check_is_friend ) {
		error_log('>>exist_more_friends -e 230: '. $user_id . ' - ' . $friend_id);
		
		if ( ! friends_remove_friend( $friend_id, $user_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'friendship could not be cancelled. 230 -e', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			$is_user = bp_is_my_profile();

			if ( ! $is_user ) {
				$response = array( 'contents' => bp_get_add_friend_button( $friend_id ) );
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'friendship cancelled.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => $is_user,
				);
			}

			wp_send_json_success( $response );
		}
	// Trying to stop awaiting friendship from engagement.
	} elseif ( 'awaiting_response' === $check_is_friend &&  $_POST['action'] == 'engagements_remove_friends_from_engagements') {
		// todo:
		error_log('>>exist_more_friends -e 497: '. $user_id . ' - ' . $friend_id);
		
		if ( ! friends_remove_friend( $user_id, $friend_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'friendship could not be cancelled. 230 -e', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			$is_user = bp_is_my_profile();

			if ( ! $is_user ) {
				$response = array( 'contents' => bp_get_add_friend_button( $friend_id ) );
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'friendship cancelled.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => $is_user,
				);
			}

			wp_send_json_success( $response );
		}
	// Trying to request friendship.
	} elseif ( 'not_friends' === $check_is_friend &&  $_POST['action'] == 'engagements_remove_friends_from_engagements') {
		error_log('>>not_friends -e 502: '. $user_id . ' - ' . $friend_id);
		
		if ( ! engagements_remove_engagement( $user_id, $friend_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'friendship could not be cancelled. 507 -e', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			$is_user = bp_is_my_profile();

			if ( ! $is_user ) {
				$response = array( 'contents' => bp_get_add_friend_button( $friend_id ) );
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'friendship cancelled.', 'buddypress' )
					),
					'type'     => 'success',
					'is_user'  => $is_user,
				);
			}

			wp_send_json_success( $response );
		}
	} elseif ( 'not_friends' === $check_is_friend ) {
		error_log('>>not_friends -e 473: '. $user_id . ' - ' . $friend_id);
		if ( ! friends_add_friend( $user_id, $friend_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'Friendship could not be requested.', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		}

	// Trying to request friendship in existed button.
	} elseif ( 'exist_initiator_friend' === $check_is_friend ) {
		error_log('>>exist_initiator_friend: '. $user_id . ' - ' . $friend_id);
		if ( ! friends_add_friend( $friend_id, $user_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'Friendship could not be requested. (exist_initiator_friend) 417', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		}

	// Trying to cancel pending request - reverse.
	} elseif ( 'pending_friend' === $check_is_friend && $_POST['action'] == 'engagements_remove_friends_from_engagements') {
		error_log(json_encode('pending_friend -e 536'));
		if ( friends_withdraw_friendship( $friend_id, $user_id ) ) {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		} else {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'Friendship request could not be cancelled. 542', 'buddypress' )
			);

			wp_send_json_error( $response );
		}

	// Trying to cancel pending request.
	} elseif ( 'pending_friend' === $check_is_friend ) {
		error_log(json_encode('pending_friend -e 500'));
		if ( friends_withdraw_friendship( $user_id, $friend_id ) ) {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		} else {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'Friendship request could not be cancelled.', 'buddypress' )
			);

			wp_send_json_error( $response );
		}

	// Request already pending.
	} else {
		error_log(json_encode('>>> default Request Pending Frien 554 -e'));
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Request Pending Friend', 'buddypress' )
		);

		wp_send_json_error( $response );
	}
}
