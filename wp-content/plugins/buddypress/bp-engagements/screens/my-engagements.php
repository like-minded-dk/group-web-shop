<?php
/**
 * engagements: User's "engagements" screen handler
 *
 * @package BuddyPress
 * @subpackage engagementsScreens
 * @since 3.0.0
 */

/**
 * Catch and process the My engagements page.
 *
 * @since 1.0.0
 */
function engagements_screen_my_engagements() {

	/**
	 * Fires before the loading of template for the My engagements page.
	 *
	 * @since 1.0.0
	 */
	do_action( 'engagements_screen_my_engagements' );

	/**
	 * Filters the template used to display the My engagements page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Path to the my engagements template to load.
	 */
	bp_core_load_template( apply_filters( 'engagements_template_my_engagements', 'members/single/home' ) );
}
