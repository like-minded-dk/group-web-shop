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
				'function' => 'bp_nouveau_ajax_addremove_from_engagement_ext',
				'nopriv'   => false,
			),
		),
		array(
			'engagements_remove_friends_from_engagements' => array(
				'function' => 'bp_nouveau_ajax_addremove_from_engagement_ext',
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
		// todo : user ajax_remove_engagement 'contents' => bp_get_add_friend_button( $friend_id ) );
		ajax_remove_relation( 'engagement',  $user_id, $engagement_id, '>>is_engagement 168');

	// Trying to cancel engagementship in existed button.
	} elseif ( 'exist_initiator_engagement' === $check_is_engagement ) {
		ajax_remove_relation( 'engagement',  $engagement_id, $user_id, 'exist_initiator_engagement 197' );

	// Trying to stop in existed button.
	} elseif ( 'exist_more_engagements' === $check_is_engagement ) {
		ajax_remove_relation( 'engagement', $user_id, $engagement_id, 'exist_more_engagements 241');

	// Trying to cancel pending request.
	} elseif ( 'not_engagements' === $check_is_engagement && $_POST['action'] == 'engagements_withdraw_engagementship' ) {
		// todo : user ajax_remove_engagement 'contents' => bp_get_add_friend_button( $friend_id ) );
		ajax_withdraw('engagement', $user_id, $engagement_id, 'not_engagements -e 271');

	// Trying to request engagementship.
	} elseif ( 'not_engagements' === $check_is_engagement ) {
		error_log('>>not_engagements -f 420: '. $user_id . ' - ' . $engagement_id);
		if ( ! engagements_add_engagement( $user_id, $engagement_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error">%s</div>',
				esc_html__( 'engagementship could not be requested.', 'buddypress' )
			);

			wp_send_json_error( $response );
		} else {
			wp_send_json_success( array( 'contents' => bp_get_add_engagement_button( $engagement_id ) ) );
		}

	// Trying to cancel pending request
	} elseif ( 'pending_engagement' === $check_is_engagement && $_POST['action'] == 'engagements_withdraw_engagementship') {
		ajax_withdraw('engagement', $engagement_id, $user_id, '>pending_engagement -f 220');

	// Trying to cancel pending request - reversed.
	} elseif ( 'pending_engagement' === $check_is_engagement && $_POST['action'] == 'engagements_remove_engagements') {
		ajax_remove_relation( 'engagement', $engagement_id, $user_id, 'reverse pending_engagement_remove_engagement -215');

	// Trying to cancel pending request.
	} elseif ( 'pending_engagement' === $check_is_engagement ) {
		ajax_withdraw('engagement', $user_id, $engagement_id, '>pending_engagement -f 234');

	// Request already pending.
	} else {
		error_log(json_encode('>>> default Request Pending Frien 554 -f'));
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Request Pending Engagement (engagement)', 'buddypress' )
		);

		wp_send_json_error( $response );
	}
}

function bp_nouveau_ajax_addremove_from_engagement_ext() {
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
		// todo : user ajax_remove_engagement 'contents' => bp_get_add_friend_button( $friend_id ) );
		ajax_remove_relation( 'friend', $user_id, $friend_id, '>>is_friend 386');

	// Trying to cancel friendship in existed button..
	} elseif ( 'exist_more_friends' === $check_is_friend ) {
		ajax_remove_relation( 'friend', $friend_id, $user_id, '>>exist_more_friends -e 230');

	// Trying to stop awaiting friendship from engagement.
	} elseif ( 'awaiting_response' === $check_is_friend &&  $_POST['action'] == 'engagements_remove_friends_from_engagements') {
		ajax_remove_relation( 'friend', $user_id, $friend_id, '>>exist_more_friends -e 497');

	// Trying to request friendship.
	} elseif ( 'not_friends' === $check_is_friend &&  $_POST['action'] == 'engagements_remove_friends_from_engagements') {
		// todo : user ajax_remove_engagement 'contents' => bp_get_add_friend_button( $friend_id ) );
		ajax_remove_relation( 'friend',  $user_id, $friend_id, 'not_friends -e 502' ); 

	// Trying to request friendship.
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
		ajax_add_relation($friend_id, $user_id , '>>exist_initiator_friend: '. $user_id . ' - ' . $friend_id );

	// Trying to cancel pending request - reverse.
	} elseif ( 'pending_friend' === $check_is_friend && $_POST['action'] == 'engagements_remove_friends_from_engagements') {
		ajax_withdraw('friend', $friend_id, $user_id, 'pending_friend -e 536');

	// Trying to cancel pending request.
	} elseif ( 'pending_friend' === $check_is_friend ) {
		ajax_withdraw('friend', $user_id, $friend_id, '>pending_friend -f 500');

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
