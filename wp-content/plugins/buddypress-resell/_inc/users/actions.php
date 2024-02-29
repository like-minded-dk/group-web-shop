<?php
/**
 * BP Resell Actions
 *
 * @package BP-Resell
 * @subpackage Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Catches clicks on a "Resell" button and tries to make that happen.
 *
 * @uses check_admin_referer() Checks to make sure the WP security nonce matches.
 * @uses bp_resell_is_reselling() Checks to see if a user is reselling another user already.
 * @uses bp_resell_start_reselling() Starts a user reselling another user.
 * @uses bp_core_add_message() Adds an error/success message to be displayed after redirect.
 * @uses bp_core_redirect() Safe redirects the user to a particular URL.
 */
function bp_resell_action_start() {
	$bp = $GLOBALS['bp'];

	if ( ! bp_is_current_component( $bp->resell->resellers->slug ) || ! bp_is_current_action( 'start' ) ) {
		return;
	}

	if ( bp_displayed_user_id() === bp_loggedin_user_id() ) {
		return;
	}

	check_admin_referer( 'start_reselling' );

	if ( bp_resell_is_reselling( array( 'leader_id' => bp_displayed_user_id(), 'reseller_id' => bp_loggedin_user_id() ) ) ) {
		bp_core_add_message( sprintf( __( 'You are already reselling %s.', 'buddypress-resellers' ), bp_get_displayed_user_fullname() ), 'error' );

	} else {
		if ( ! bp_resell_start_reselling( array( 'leader_id' => bp_displayed_user_id(), 'reseller_id' => bp_loggedin_user_id() ) ) ) {
			bp_core_add_message( sprintf( __( 'There was a problem when trying to resell %s, please try again.', 'buddypress-resellers' ), bp_get_displayed_user_fullname() ), 'error' );
		} else {
			bp_core_add_message( sprintf( __( 'You are now reselling %s.', 'buddypress-resellers' ), bp_get_displayed_user_fullname() ) );
		}
	}

	// it's possible that wp_get_referer() returns false, so let's fallback to the displayed user's page.
	$redirect = wp_get_referer() ? wp_get_referer() : bp_displayed_user_domain();
	bp_core_redirect( $redirect );
}
add_action( 'bp_actions', 'bp_resell_action_start' );

/**
 * Catches clicks on a "Stop-Resell" button and tries to make that happen.
 *
 * @uses check_admin_referer() Checks to make sure the WP security nonce matches.
 * @uses bp_resell_is_reselling() Checks to see if a user is reselling another user already.
 * @uses bp_resell_stop_reselling() Stops a user reselling another user.
 * @uses bp_core_add_message() Adds an error/success message to be displayed after redirect.
 * @uses bp_core_redirect() Safe redirects the user to a particular URL.
 */
function bp_resell_action_stop() {
	$bp = $GLOBALS['bp'];

	if ( ! bp_is_current_component( $bp->resell->resellers->slug ) || ! bp_is_current_action( 'stop' ) ) {
		return;
	}

	if ( bp_displayed_user_id() === bp_loggedin_user_id() ) {
		return;
	}

	check_admin_referer( 'stop_reselling' );

	if ( ! bp_resell_is_reselling( array( 'leader_id' => bp_displayed_user_id(), 'reseller_id' => bp_loggedin_user_id() ) ) ) {
		bp_core_add_message( sprintf( __( 'You are not reselling %s.', 'buddypress-resellers' ), bp_get_displayed_user_fullname() ), 'error' );

	} else {
		if ( ! bp_resell_stop_reselling( array( 'leader_id' => bp_displayed_user_id(), 'reseller_id' => bp_loggedin_user_id() ) ) ) {
			bp_core_add_message( sprintf( __( 'There was a problem when trying to stop reselling %s, please try again.', 'buddypress-resellers' ), bp_get_displayed_user_fullname() ), 'error' );
		} else {
			bp_core_add_message( sprintf( __( 'You are no longer reselling %s.', 'buddypress-resellers' ), bp_get_displayed_user_fullname() ) );
		}
	}

	// it's possible that wp_get_referer() returns false, so let's fallback to the displayed user's page.
	$redirect = wp_get_referer() ? wp_get_referer() : bp_displayed_user_domain();
	bp_core_redirect( $redirect );
}
add_action( 'bp_actions', 'bp_resell_action_stop' );

/**
 * Add RSS feed support for a user's reselling activity.
 *
 * Ex.: example.com/members/USERNAME/activity/reselling/feed/
 *
 * Only available in BuddyPress 1.8+.
 *
 * @since 1.2.1
 * @author r-a-y
 */
function bp_resell_my_reselling_feed() {
	// only available in BP 1.8+.
	if ( ! class_exists( 'BP_Activity_Feed' ) ) {
		return;
	}

	if ( ! bp_is_user_activity() || ! bp_is_current_action( constant( 'BP_RESELLING_SLUG' ) ) || ! bp_is_action_variable( 'feed', 0 ) ) {
		return false;
	}

	$bp = $GLOBALS['bp'];

	// setup the feed.
	$bp->activity->feed = new BP_Activity_Feed( array(
		'id'            => 'myreselling',

		/* translators: User's reselling activity RSS title - "[Site Name] | [User Display Name] | Reselling Activity" */
		'title'         => sprintf( __( '%1$s | %2$s | Reselling Activity', 'buddypress-resellers' ), bp_get_site_name(), bp_get_displayed_user_fullname() ),

		'link'          => bp_resell_get_user_url( bp_displayed_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELLING_SLUG' ) ) ),
		'description'   => sprintf( __( "Activity feed for people that %s is reselling.", 'buddypress' ), bp_get_displayed_user_fullname() ),
		'activity_args' => array(
			'user_id'  => bp_get_reselling_ids(),
			'display_comments' => 'threaded'
		)
	) );
}
add_action( 'bp_actions', 'bp_resell_my_reselling_feed' );
