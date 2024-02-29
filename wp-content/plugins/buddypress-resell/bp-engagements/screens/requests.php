<?php
/**
 * engagements: User's "engagements > Requests" screen handler
 *
 * @package BuddyPress
 * @subpackage engagementsScreens
 * @since 3.0.0
 */

/**
 * Catch and process the Requests page.
 *
 * @since 1.0.0
 */
function engagements_screen_requests() {
	$redirect = false;

	if ( bp_is_action_variable( 'accept', 0 ) && is_numeric( bp_action_variable( 1 ) ) ) {
		// Check the nonce.
		check_admin_referer( 'engagements_accept_engagementship' );

		if ( engagements_accept_engagementship( bp_action_variable( 1 ) ) ) {
			bp_core_add_message( __( 'engagementship accepted', 'buddypress' ) );
		} else {
			bp_core_add_message( __( 'engagementship could not be accepted', 'buddypress' ), 'error' );
		}

		$redirect = true;

	} elseif ( bp_is_action_variable( 'reject', 0 ) && is_numeric( bp_action_variable( 1 ) ) ) {
		// Check the nonce.
		check_admin_referer( 'engagements_reject_engagementship' );

		if ( engagements_reject_engagementship( bp_action_variable( 1 ) ) ) {
			bp_core_add_message( __( 'engagementship rejected', 'buddypress' ) );
		} else {
			bp_core_add_message( __( 'engagementship could not be rejected', 'buddypress' ), 'error' );
		}

		$redirect = true;

	} elseif ( bp_is_action_variable( 'cancel', 0 ) && is_numeric( bp_action_variable( 1 ) ) ) {
		// Check the nonce.
		check_admin_referer( 'engagements_withdraw_engagementship' );

		if ( engagements_withdraw_engagementship( bp_loggedin_user_id(), bp_action_variable( 1 ) ) ) {
			bp_core_add_message( __( 'engagementship request withdrawn', 'buddypress' ) );
		} else {
			bp_core_add_message( __( 'engagementship request could not be withdrawn', 'buddypress' ), 'error' );
		}

		$redirect = true;
	}

	if ( $redirect ) {
		bp_core_redirect(
			bp_loggedin_user_url(
				bp_members_get_path_chunks( array( bp_get_engagements_slug(), 'requests' ) )
			)
		);
	}

	/**
	 * Fires before the loading of template for the engagements requests page.
	 *
	 * @since 1.0.0
	 */
	do_action( 'engagements_screen_requests' );

	/**
	 * Filters the template used to display the My engagements page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Path to the engagements request template to load.
	 */
	bp_core_load_template( apply_filters( 'engagements_template_requests', 'members/single/home' ) );
}
