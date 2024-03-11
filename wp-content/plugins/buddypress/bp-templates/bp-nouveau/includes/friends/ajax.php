<?php
/**
 * Friends Ajax functions
 *
 * @since 3.0.0
 * @version 3.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function() {
	$ajax_actions = array(
		array(
			'friends_remove_engagements_from_friends' => array(
				'function' => 'bp_nouveau_ajax_addremove_friend',
				'nopriv'   => false,
			),
		),
		array(
			'friends_not_engagements_from_friends' => array(
				'function' => 'bp_nouveau_ajax_addremove_friend',
				'nopriv'   => false,
			),
		),
		array(
			'friends_remove_friends' => array(
				'function' => 'bp_nouveau_ajax_addremove_friend',
				'nopriv'   => false,
			),
		),
		array(
			'friends_add_friends' => array(
				'function' => 'bp_nouveau_ajax_addremove_friend',
				'nopriv'   => false,
			),
		),
		array(
			'friends_withdraw_friendship' => array(
				'function' => 'bp_nouveau_ajax_addremove_friend',
				'nopriv'   => false,
			),
		),
		array(
			'friends_accept_friendship' => array(
				'function' => 'bp_nouveau_ajax_addremove_friend',
				'nopriv'   => false,
			),
		),
		array(
			'friends_reject_friendship' => array(
				'function' => 'bp_nouveau_ajax_addremove_friend',
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
function bp_nouveau_ajax_addremove_friend() {
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
	
	error_log('>>ajax_addremove_f -f 127post action '. $_POST['action']);
	error_log('$check_is_friend -f 128: '.$check_is_friend);
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

	// Trying to remove friendship.
	} elseif ( 'is_friend' === $check_is_friend ) {
		// todo : user ajax_remove_engagement 'contents' => bp_get_add_friend_button( $friend_id ) );
		ajax_remove_relation( 'friend',  $user_id, $friend_id , '>>is_friend -f 181');

	// Trying to cancel friendship in existed button..
	} elseif ( 'exist_initiator_friend' === $check_is_friend ) {
		ajax_remove_relation( 'friend',  $friend_id, $user_id , '>>exist_initiator_friend -f 205');

	// Trying to stop awaiting friendship from engagement.
	} elseif ( 'exist_more_friends' === $check_is_friend ) {
		ajax_remove_relation( 'friend',  $user_id, $friend_id , '>>exist_more_friends -f 266: ');

	// Trying to cancel pending request.
	} elseif ( 'not_friends' === $check_is_friend && $_POST['action'] == 'friends_withdraw_friendship') {
		// todo : user ajax_remove_engagement 'contents' => bp_get_add_friend_button( $friend_id ) );
		ajax_withdraw('friend',  $user_id, $friend_id, '>>not_friends -f 271');

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

	// Trying to cancel pending request.
	} elseif ( 'pending_friend' === $check_is_friend && $_POST['action'] == 'friends_withdraw_friendship' ) {
		ajax_withdraw('friend',  $user_id, $friend_id, '>friends_withdraw_friendship -f 22');

	// Trying to cancel reverse pending request.
	} elseif ( 'pending_friend' === $check_is_friend && $_POST['action'] == 'friends_remove_friends') {
		ajax_remove_relation( 'friend', $friend_id, $user_id, 'reverse pending_friend_remove_friend -215');

	// Trying to cancel pending request.
	} elseif ( 'pending_friend' === $check_is_friend ) {
		ajax_withdraw('friend',  $user_id, $friend_id, '>>pending_friend -f 235');

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
