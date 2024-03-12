<?php
/**
 * Friends: Remove action.
 *
 * @package BuddyPress
 * @subpackage FriendsActions
 * @since 3.0.0
 */

/**
 * Catch and process Remove Friendship requests.
 *
 * @since 1.0.1
 */
function friends_action_remove_friend() {
	error_log(json_encode('>>>>>>>engagements_action_remove_friend'));
	if ( ! bp_is_friends_component() || ! bp_is_current_action( 'remove-friend' ) ) {
		return false;
	}

	$potential_friend_id = (int) bp_action_variable( 0 );
	if ( ! $potential_friend_id ) {
		return false;
	}

	if ( bp_loggedin_user_id() === $potential_friend_id ) {
		return false;
	}

	$friendship_status = BP_Friends_Friendship::check_is_relation( bp_loggedin_user_id(), $potential_friend_id );

	if ( 'is_friend' === $friendship_status ) {

		if ( ! check_admin_referer( 'friends_remove_friends' ) ) {
			return false;
		}

		if ( ! friends_remove_friend( bp_loggedin_user_id(), $potential_friend_id ) ) {
			bp_core_add_message( __( 'Friendship could not be canceled.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( 'Friendship canceled', 'buddypress' ) );
		}
	} elseif ( 'remove_engagements_from_receiver' === $friendship_status ) {
		if ( ! check_admin_referer( 'engagements_remove_friend' ) ) {
			return false;
		}

		if ( ! friends_remove_friend( bp_loggedin_user_id(), $potential_friend_id ) ) {
			bp_core_add_message( __( '(engagements) friendship could not be canceled.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( '(engagements) friendship canceled', 'buddypress' ) );
		}
	} elseif ( 'remove_friends' === $friendship_status ) {
		if ( ! check_admin_referer( 'friends_remove_friends' ) ) {
			return false;
		}

		if ( ! friends_remove_friend( bp_loggedin_user_id(), $potential_friend_id ) ) {
			bp_core_add_message( __( '(remove_friends) friendship could not be canceled.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( '(remove_friends) friendship canceled', 'buddypress' ) );
		}
	} elseif ( 'not_friends' === $friendship_status ) {
		bp_core_add_message( __( 'You are not yet friends with this user', 'buddypress' ), 'error' );
	} elseif ( 'add_engagement_from_receiver' === $friendship_status ) {
		bp_core_add_message( __( 'You are not yet engagements with this user', 'buddypress' ), 'error' );
	} else {
		bp_core_add_message( __( 'You have a pending friendship request with this user', 'buddypress' ), 'error' );
	}

	bp_core_redirect( wp_get_referer() );

	return false;
}
add_action( 'bp_actions', 'friends_action_remove_friend' );
