<?php
/**
 * BuddyPress engagements Streams Loader.
 *
 * The engagements component is for users to create relationships with each other.
 *
 * @package BuddyPress
 * @subpackage engagementsLoader
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Set up the bp-engagements component.
 *
 * @since 1.6.0
 */
function bp_is_engagements_component() {
	return true;
}

function bp_is_user_engagements() {
	return (bool) ( bp_is_user() && bp_is_engagements_component() );
}

function bp_setup_engagements() {
	buddypress()->engagements = new BP_Engagements_Component();
}
add_action( 'bp_setup_components', 'bp_setup_engagements', 6 );
