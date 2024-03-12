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
			'friends_remove_friends_from_reciver' => array(
				'function' => 'bp_nouveau_ajax_addremove_friend',
				'nopriv'   => false,
			),
		),
		array(
			'friends_add_friends_from_reciver' => array(
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
	$action = $_POST['action'];
	error_log(json_encode('ajaxfile nonce: ' . $_POST['nonce'] . ' action: ' . $action . ' - _wpnonce: '. $_POST['_wpnonce']));
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
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $action ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $action;
	}
	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		error_log(json_encode('ajaxfile verify nonce error!'));
		wp_send_json_error( $response );
	}

	// Cast fid as an integer.
	$friend_id = (int) $_POST['item_id'];

	// Check if the user exists only when the Friend ID is not a Frienship ID.
	if ( isset( $action ) && $action !== 'friends_accept_friendship' && $action !== 'friends_reject_friendship' ) {
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
	
	error_log('ajaxfile >> ajax_addremove_f -f 127 post action '. $action);
	error_log('ajaxfile $check_is_friend -f 128: '.$check_is_friend);
	// In the 2 first cases the $friend_id is a friendship id.
	if ( ! empty( $action ) && 'friends_accept_friendship' === $action ) {
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
	} elseif ( ! empty( $action ) && 'friends_reject_friendship' === $action ) {
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
		ajax_remove_relation( 'friend',  $user_id, $friend_id , '>>is_friend -f 181');
	
	// Trying to remove friendship.
	} elseif ( 'f_c1_is_friend_ini' === $check_is_friend && 'friends_remove_friends') {
		ajax_remove_relation( 'friend',  $user_id, $friend_id , '>>is_friend -f 189x');

	// Trying to remove friendship.
	} elseif ( 'f_c1_is_reverse_friend_rev' === $check_is_friend && $action == 'friends_remove_friends') {
		ajax_remove_relation( 'friend',  $friend_id , $user_id, '>>is_friend -f 191');

	} elseif ( 'f_c1_is_reverse_engagement_rev' === $check_is_friend && $action == 'friends_remove_friends_from_reciver') {
		ajax_remove_relation( 'friend', $friend_id, $user_id, '>>is_friend -f 186x');

	// Trying to stop friendship from friend reversed.
	} elseif ( 'f_c2_fm1_is_friend_ini' === $check_is_friend && $action == 'friends_remove_friends') {
		ajax_remove_relation( 'friend', $friend_id, $user_id, 'reverse friends_remove_friends -187x');

	// Trying to stop friendship from engagement.
	} elseif ( 'f_c2_fm1_is_friend_rev' === $check_is_friend && $action == 'friends_remove_friends_from_reciver' ) {
		ajax_remove_relation( 'friend', $friend_id,  $user_id, '>>friends_remove_friends_from_reciver -f 201x: ');

	// Trying to cancel pending request.
	} elseif ( 'f_c1_is_friend_ini' === $check_is_friend && $action == 'friends_remove_friends' ) {
		ajax_remove_relation('friend', $user_id, $friend_id, '>friends_remove_friends -f 206x');

	// Trying to stop awaiting friendship.
	} elseif ( 'f_c2_fm1_is_friend_rev' === $check_is_friend && $action == 'friends_withdraw_friendship' ) {
		ajax_withdraw('friend',  $user_id, $friend_id, '>friends_withdraw_friendship -f 197x');

	// Trying to cancel pending request.
	} elseif ( 'f_c1_pending_friend_ini' === $check_is_friend && $action == 'friends_withdraw_friendship') {
		ajax_withdraw('friend',  $user_id, $friend_id, '>>friends_withdraw_friendship -f 271x');

	// Trying to request friendship.
	} elseif ( 'f_c1_is_reverse_engagement_rev' === $check_is_friend && $action = 'friends_add_friends_from_reciver' ) {
		ajax_add_relation('friend',  $user_id, $friend_id,  'ajaxfile >>friends_add_friends_from_reciver -e 210x: '. $user_id . ' - ' . $friend_id);

	// Request already pending.
	} else {
		error_log('ajaxfile ' . json_encode('>>> default Request Pending F 554 -e'));
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Request Pending Friend', 'buddypress' )
		);

		wp_send_json_error( $response );
	}
}
