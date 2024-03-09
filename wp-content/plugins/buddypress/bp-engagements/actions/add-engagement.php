<?php
/**
 * engagements: Add action.
 *
 * @package BuddyPress
 * @subpackage engagementsActions
 * @since 3.0.0
 */


/**
 * Catch and process engagementship requests.
 *
 * @since 1.0.1
 */
function engagements_action_add_engagement() {
	error_log(json_encode('>>>>>>>engagements_action_add_engagement'));
	if ( ! bp_is_engagements_component() || ! bp_is_current_action( 'add-engagement' ) ) {
		return false;
	}

	$potential_engagement_id = (int) bp_action_variable( 0 );
	if ( ! $potential_engagement_id ) {
		return false;
	}

	if ( bp_loggedin_user_id() === $potential_engagement_id ) {
		return false;
	}

	$engagementship_status = BP_Engagements_Engagementship::check_is_engagement( bp_loggedin_user_id(), $potential_engagement_id );

	if ( 'not_engagements' === $engagementship_status ) {
		if ( ! check_admin_referer( 'engagement' ) ) {
			return false;
		}

		if ( ! engagements_add_engagement( bp_loggedin_user_id(), $potential_engagement_id ) ) {
			bp_core_add_message( __( 'engagementship could not be requested.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( 'engagementship requested', 'buddypress' ) );
		}
	} elseif ( 'not_friends_from_engagements' === $engagementship_status ) {
		if ( ! check_admin_referer( 'engagements_add_friend' ) ) {
			return false;
		}
		// todo engagements_add_friend
		if ( ! engagements_add_engagement( bp_loggedin_user_id(), $potential_engagement_id ) ) {
			bp_core_add_message( __( '(engagements) friendsship could not be requested.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( '(engagements) friendsship requested', 'buddypress' ) );
		}
	} elseif ( 'is_engagement' === $engagementship_status ) {
		bp_core_add_message( __( 'You are already engagements with this user', 'buddypress' ), 'error' );
	} else {
		bp_core_add_message( __( 'You already have a pending engagementship request with this user', 'buddypress' ), 'error' );
	}

	bp_core_redirect( wp_get_referer() );

	return false;
}
add_action( 'bp_actions', 'engagements_action_add_engagement' );
