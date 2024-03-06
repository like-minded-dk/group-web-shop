<?php
/**
 * engagements: Remove action.
 *
 * @package BuddyPress
 * @subpackage engagementsActions
 * @since 3.0.0
 */

/**
 * Catch and process Remove engagementship requests.
 *
 * @since 1.0.1
 */
function engagements_action_remove_engagement() {
	if ( ! bp_is_engagements_component() || ! bp_is_current_action( 'remove-engagement' ) ) {
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

	if ( 'is_engagement' === $engagementship_status ) {

		if ( ! check_admin_referer( 'engagements_remove_engagement' ) ) {
			return false;
		}

		if ( ! engagements_remove_engagement( bp_loggedin_user_id(), $potential_engagement_id ) ) {
			bp_core_add_message( __( 'engagementship could not be canceled.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( 'engagementship canceled', 'buddypress' ) );
		}
	} elseif ( 'remove_friends_from_engagements' === $engagementship_status ) {
		if ( ! check_admin_referer( 'friends_remove_engagement' ) ) {
			return false;
		}

		if ( ! engagements_remove_engagement( bp_loggedin_user_id(), $potential_engagement_id ) ) {
			bp_core_add_message( __( '(friends) engagementship could not be canceled.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( '(friends) engagementship canceled', 'buddypress' ) );
		}
	} elseif ( 'not_engagements' === $engagementship_status ) {
		bp_core_add_message( __( 'You are not yet engagements with this user', 'buddypress' ), 'error' );
	} elseif ( 'not_friends_from_engagements' === $engagementship_status ) {
		bp_core_add_message( __( 'You are not yet friends with this user', 'buddypress' ), 'error' );
	} else {
		bp_core_add_message( __( 'You have a pending engagementship request with this user', 'buddypress' ), 'error' );
	}

	bp_core_redirect( wp_get_referer() );

	return false;
}
add_action( 'bp_actions', 'engagements_action_remove_engagement' );
