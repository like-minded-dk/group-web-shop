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
			'engagements_remove_engagements_from_reciver' => array(
				'function' => 'bp_nouveau_ajax_addremove_engagement',
				'nopriv'   => false,
			),
		),
		array(
			'engagements_add_engagements_from_reciver' => array(
				'function' => 'bp_nouveau_ajax_addremove_engagement',
				'nopriv'   => false,
			),
		),
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
	$action = $_POST['action'];
	error_log(json_encode('ajaxfile nonce: ' . $_POST['nonce'] . ' action: ' . $action. ' - _wpnonce: '. $_POST['_wpnonce']));
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
	$engagement_id = (int) $_POST['item_id'];

	// Check if the user exists only when the engagement ID is not a Frienship ID.
	if ( isset( $action ) && $action !== 'engagements_accept_engagementship' && $action !== 'engagements_reject_engagementship' ) {
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

	error_log('ajaxfile ajax_add remove_e POST action -e 127: '.$action);
	error_log('ajaxfile $check_is_engagement -e 128: '.$check_is_engagement);
	// In the 2 first cases the $engagement_id is a engagementship id.
	if ( ! empty( $action ) && 'engagements_accept_engagementship' === $action ) {
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
	} elseif ( ! empty( $action ) && 'engagements_reject_engagementship' === $action ) {
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
		ajax_remove_relation( 'engagement',  $user_id, $engagement_id, '>>is_engagement 181');
	
	// Trying to remove engagementship.
	} elseif ( 'e_c1_is_engagement_ini' === $check_is_engagement && 'engagements_remove_engagements') {
		ajax_remove_relation( 'engagement',  $user_id, $engagement_id, '>>is_engagement 189x');

	// Trying to remove engagementship.
	} elseif ( 'e_c1_is_reverse_engagement_rev' === $check_is_engagement && $action == 'engagements_remove_engagements') {
		ajax_remove_relation( 'engagement',  $user_id, $engagement_id, '>>is_engagement 191');

	} elseif ( 'e_c1_is_reverse_friend_rev' === $check_is_engagement && $action == 'engagements_remove_engagements_from_reciver') {
		ajax_remove_relation( 'engagement',  $engagement_id,  $user_id, '>>is_engagement 186x');

	// Trying to stop friendship from engagement reversed.
	} elseif ( 'e_c2_fm1_is_engagement_ini' === $check_is_engagement && $action == 'engagements_remove_engagements') {
		ajax_remove_relation( 'engagement', $engagement_id, $user_id, 'reverse pending_engagement_remove_engagement -187');

	// Trying to stop friendship from engagement.
	} elseif ( 'e_c2_fm1_is_engagement_rev' === $check_is_engagement && $action == 'engagements_remove_engagements_from_reciver' ) {
		ajax_remove_relation( 'engagement', $engagement_id, $user_id, '>>engagements_remove_engagements_from_reciver -f 201: ');

	// Trying to cancel friendship in existed button..
	} elseif ( 'e_c2_fm1_is_engagement_ini' === $check_is_engagement && $action == 'engagements_remove_engagements' ) {
		ajax_remove_relation( 'friend', $user_id, $engagement_id, '>>exist_initiator_friend -f 205');

	// Trying to stop awaiting friendship.
	} elseif ( 'e_c2_fm1_is_engagement_rev' === $check_is_engagement && $action == 'engagements_withdraw_engagementship' ) {
		ajax_withdraw('friend',  $user_id, $engagement_id, '>friends_withdraw_friendship -f 197');

	// Trying to cancel pending request.
	} elseif ( 'e_c1_pending_engagement_ini' === $check_is_engagement && $action == 'engagements_withdraw_engagementship' ) {
		ajax_withdraw('engagement', $user_id, $engagement_id, 'e_c1_pending_engagement_ini -e 271x');

	// Trying to request friendship.
	} elseif ( 'f_c1_is_reverse_friend_rev' === $check_is_engagement && $action = 'engagements_add_engagements_from_reciver' ) {
		ajax_add_relation('engagement',  $user_id, $engagement_id,  'ajaxfile >>engagements_add_engagements_from_reciver -e 210x: '. $user_id . ' - ' . $engagement_id);

	// Request already pending.
	} else {
		error_log('ajaxfile '. json_encode('>>> default Request Pending E 554 -f'));
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Request Pending Engagement (engagement)', 'buddypress' )
		);

		wp_send_json_error( $response );
	}
}
