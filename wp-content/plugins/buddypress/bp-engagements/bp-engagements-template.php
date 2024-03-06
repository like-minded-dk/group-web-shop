<?php
/**
 * BuddyPress engagements Template Functions.
 *
 * @package BuddyPress
 * @subpackage engagementsTemplate
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
require ('bp-engagements-template-buttons.php');
/**
 * Output the engagements component slug.
 *
 * @since 1.5.0
 */
function BP_ENGAGEMENTS_SLUG() {
	echo bp_get_engagements_slug();
}
	/**
	 * Return the engagements component slug.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	function bp_get_engagements_slug() {

		/**
		 * Filters the engagements component slug.
		 *
		 * @since 1.5.0
		 *
		 * @param string $value engagements component slug.
		 */
		return apply_filters( 'bp_get_engagements_slug', buddypress()->engagements->slug );
	}

/**
 * Output the engagements component root slug.
 *
 * @since 1.5.0
 */
function bp_engagements_root_slug() {
	echo bp_get_engagements_root_slug();
}
	/**
	 * Return the engagements component root slug.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	function bp_get_engagements_root_slug() {

		/**
		 * Filters the engagements component root slug.
		 *
		 * @since 1.5.0
		 *
		 * @param string $value engagements component root slug.
		 */
		return apply_filters( 'bp_get_engagements_root_slug', buddypress()->engagements->root_slug );
	}

/**
 * Output the "Add Engagement" button in the member loop.
 *
 * @since 1.2.6
 */
function bp_member_add_engagement_button() {
	bp_add_engagement_button( bp_get_member_user_id() );
}
// error_log('add_action bp_directory_members_actions engage -----');
add_action( 'bp_directory_members_actions', 'bp_member_add_engagement_button' );

/**
 * Output the engagement count for the current member in the loop.
 *
 * @since 1.2.0
 */
function bp_member_total_engagement_count() {
	echo bp_get_member_total_engagement_count();
}
	/**
	 * Return the engagement count for the current member in the loop.
	 *
	 * Return value is a string of the form "x engagements".
	 *
	 * @global BP_Core_Members_Template $members_template The main member template loop class.
	 *
	 * @since 1.2.0
	 *
	 * @return string A string of the form "x engagements".
	 */
	function bp_get_member_total_engagement_count() {
		global $members_template;

		$total_engagement_count = (int) $members_template->member->total_engagement_count;

		/**
		 * Filters text used to denote total engagement count.
		 *
		 * @since 1.2.0
		 *
		 * @param string $value String of the form "x engagements".
		 * @param int    $value Total engagement count for current member in the loop.
		 */
		return apply_filters(
			'bp_get_member_total_engagement_count',
			/* translators: %d: total engagement count */
			sprintf( _n( '%d engagement', '%d engagements', $total_engagement_count, 'buddypress' ), number_format_i18n( $total_engagement_count ) )
		);
	}

/**
 * Output the ID of the current user in the engagement request loop.
 *
 * @since 1.2.6
 *
 * @see bp_get_potential_engagement_id() for a description of arguments.
 *
 * @param int $user_id See {@link bp_get_potential_engagement_id()}.
 */
function bp_potential_engagement_id( $user_id = 0 ) {
	echo bp_get_potential_engagement_id( $user_id );
}
	/**
	 * Return the ID of current user in the engagement request loop.
	 *
	 * @since 1.2.6
	 *
	 * @global object $engagements_template
	 *
	 * @param int $user_id Optional. If provided, the function will simply
	 *                     return this value.
	 * @return int ID of potential engagement.
	 */
	function bp_get_potential_engagement_id( $user_id = 0 ) {
		global $engagements_template;

		if ( empty( $user_id ) && isset( $engagements_template->engagementship->engagement ) ) {
			$user_id = $engagements_template->engagementship->engagement->id;
		} elseif ( empty( $user_id ) && ! isset( $engagements_template->engagementship->engagement ) ) {
			$user_id = bp_displayed_user_id();
		}

		/**
		 * Filters the ID of current user in the engagement request loop.
		 *
		 * @since 1.2.10
		 *
		 * @param int $user_id ID of current user in the engagement request loop.
		 */
		return apply_filters( 'bp_get_potential_engagement_id', (int) $user_id );
	}

/**
 * Check whether a given user is a engagement of the logged-in user.
 *
 * Returns - 'is_engagement', 'not_engagements', 'pending_engagement'.
 *
 * @since 1.2.6
 *
 * @param int $user_id ID of the potential engagement. Default: the value of
 *                     {@link bp_get_potential_engagement_id()}.
 * @return bool|string 'is_engagement', 'not_engagements', or 'pending_engagement'.
 */
function bp_is_engagement( $user_id = 0 ) {

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( empty( $user_id ) ) {
		$user_id = bp_get_potential_engagement_id( $user_id );
	}

	if ( bp_loggedin_user_id() === $user_id ) {
		return false;
	}

	/**
	 * Filters the status of engagementship between logged in user and given user.
	 *
	 * @since 1.2.10
	 *
	 * @param string $value   String status of engagementship. Possible values are 'is_engagement', 'not_engagements', 'pending_engagement'.
	 * @param int    $user_id ID of the potential engagement.
	 */
	return apply_filters( 'bp_is_engagement', engagements_check_engagementship_status( bp_loggedin_user_id(), $user_id ), $user_id );
}

/**
 * Output the Add engagement button.
 *
 * @since 1.0.0
 *
 * @see bp_get_add_engagement_button() for information on arguments.
 *
 * @param int      $potential_engagement_id See {@link bp_get_add_engagement_button()}.
 * @param int|bool $engagement_status       See {@link bp_get_add_engagement_button()}.
 */
function bp_add_engagement_button( $potential_engagement_id = 0, $engagement_status = false ) {
	echo bp_get_add_engagement_button( $potential_engagement_id, $engagement_status );
}

	/**
	 * Build engagement button arguments.
	 *
	 * @since 11.0.0
	 *
	 * @param int    $potential_engagement_id The user ID of the potential engagement.
	 * @return array The engagement button arguments.
	 */
	function bp_get_add_engagement_button_args( $potential_engagement_id = 0 ) {
		$button_args = array();

		if ( empty( $potential_engagement_id ) ) {
			$potential_engagement_id = bp_get_potential_engagement_id( $potential_engagement_id );
		}

		$engagementship_status = bp_is_engagement( $potential_engagement_id );
		$friendship_status = bp_is_friend( $potential_engagement_id );
		$engagements_slug      = bp_get_engagements_slug();

		if ( empty( $engagementship_status ) ) {
			return $button_args;
		}

		$is_initiator_f = is_initiator('friend');
		$is_initiator_e = is_initiator('engagement');
		$is_reversed = strpos($friendship_status, 'exist') !==false || strpos($engagementship_status, 'exist') !==false;
		error_log('');
		error_log('>>>>>>e $is_initiator_e e: '.$is_initiator_e);
		error_log('========$is_initiator_f e: '.$is_initiator_f);
		error_log('======friendship_status e: '.$friendship_status);
		error_log('==engagementship_status e: '.$engagementship_status);
		error_log('===========$is_reversed e: '.$is_reversed);
		error_log('potential_engagement_id e: '.$potential_engagement_id);
		error_log('======$engagements_slug e: '.$engagements_slug);
		error_log('==$bp_current_component e: '.bp_current_component());

		if ($is_reversed) {
			// if (strpos($engagementship_status, 'exist') !== false ) {
			// 	$button_args = engagement_reciver_btn_args('is_engagement', $potential_engagement_id, $engagements_slug);	
			// } else {
				$button_args = engagement_reciver_btn_args($friendship_status, $potential_engagement_id, $engagements_slug);
			// }
		} else {
			$button_args = engagement_initiator_btn_args($engagementship_status, $potential_engagement_id, $engagements_slug);
		}
		
		// // in engagement table
		// if (bp_current_component() === 'members' || $is_reversed || ($is_initiator_f ==0 && $is_initiator_e == 0)) {
		// 	error_log('==bp_current_component is engagement -e');
		// 	$button_args = engagement_initiator_btn_args($engagementship_status, $potential_engagement_id, $engagements_slug);
		// } elseif ($is_initiator_e != 0) {
		// 	error_log('is_initiator e -e: '.$is_initiator_e);
		// 	if ($is_initiator_e == 1) {
		// 		error_log('initiator in engagement -e');
		// 		$button_args = engagement_initiator_btn_args($engagementship_status, $potential_engagement_id, $engagements_slug);
		// 	} elseif ($is_initiator_e == 3) {
		// 		error_log('receiver in engagement -e');
		// 		$button_args = engagement_reciver_btn_args($engagementship_status, $potential_engagement_id, bp_get_friends_slug());
		// 	} elseif ($is_initiator_e > 3) {
		// 		error_log('both in engagement -e');
		// 		$button_args = engagement_initiator_btn_args($engagementship_status, $potential_engagement_id, bp_get_friends_slug());
		// 	}
		// } elseif ($is_initiator_f !=0) {
		// 	error_log('is_initiator f -e: '.$is_initiator_f);
		// 	if ($is_initiator_f == 1) {
		// 		error_log('initiator in friend -e');
		// 		$button_args = engagement_reciver_btn_args($engagementship_status, $potential_engagement_id, $engagements_slug);
		// 	} elseif ($is_initiator_f == 3) {
		// 		error_log('receiver in friend -e');
		// 		$button_args = engagement_reciver_btn_args($engagementship_status, $potential_engagement_id, bp_get_friends_slug());
		// 	} elseif ($is_initiator_f > 3) {
		// 		error_log('both in friend -e');
		// 		$button_args = engagement_reciver_btn_args($engagementship_status, $potential_engagement_id, bp_get_friends_slug());
		// 	}
		// } else {
		// 	error_log('is_initiator f e 0 -e');
		// 	$button_args = engagement_initiator_btn_args($engagementship_status, $potential_engagement_id, $engagements_slug);
		// }
		error_log(' ---------e------');
		error_log('');

		/**
		 * Filters the HTML for the add engagement button.
		 *
		 * @since 1.1.0
		 *
		 * @param array $button_args Button arguments for add engagement button.
		 */
		return (array) apply_filters( 'bp_get_add_engagement_button', $button_args );
	}

	/**
	 * Create the Add engagement button.
	 *
	 * @since 1.1.0
	 * @since 11.0.0 uses `bp_get_add_engagement_button_args()`.
	 *
	 * @param int  $potential_engagement_id ID of the user to whom the button
	 *                                  applies. Default: value of {@link bp_get_potential_engagement_id()}.
	 * @param bool $engagement_status       Not currently used.
	 * @return bool|string HTML for the Add engagement button. False if already engagements.
	 */
	function bp_get_add_engagement_button( $potential_engagement_id = 0, $engagement_status = false ) {
		$button_args = bp_get_add_engagement_button_args( $potential_engagement_id );

		if ( ! array_filter( $button_args ) ) {
			return false;
		}

		return bp_get_button( $button_args );
	}

/**
 * Get a comma-separated list of IDs of a user's engagements.
 *
 * @since 1.2.0
 *
 * @param int $user_id Optional. Default: the displayed user's ID, or the
 *                     logged-in user's ID.
 * @return bool|string A comma-separated list of engagement IDs if any are found,
 *                      otherwise false.
 */
function bp_get_engagement_ids( $user_id = 0 ) {

	if ( empty( $user_id ) ) {
		$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
	}

	$engagement_ids = engagements_get_engagement_user_ids( $user_id );

	if ( empty( $engagement_ids ) ) {
		return false;
	}

	return implode( ',', engagements_get_engagement_user_ids( $user_id ) );
}

/**
 * Get a user's engagementship requests.
 *
 * Note that we return a 0 if no pending_engagement requests are found. This is necessary
 * because of the structure of the $include parameter in bp_has_members().
 *
 * @since 1.2.0
 *
 * @param int $user_id ID of the user whose requests are being retrieved.
 *                     Defaults to displayed user.
 * @return array|int An array of user IDs if found, or a 0 if none are found.
 */
function bp_get_engagementship_requests( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id();
	}

	if ( ! $user_id ) {
		return 0;
	}

	$requests = engagements_get_engagementship_request_user_ids( $user_id );

	if ( ! empty( $requests ) ) {
		$requests = implode( ',', (array) $requests );
	} else {
		$requests = 0;
	}

	/**
	 * Filters the total pending engagementship requests for a user.
	 *
	 * @since 1.2.0
	 * @since 2.6.0 Added the `$user_id` parameter.
	 *
	 * @param array|int $requests An array of user IDs if found, or a 0 if none are found.
	 * @param int       $user_id  ID of the queried user.
	 */
	return apply_filters( 'bp_get_engagementship_requests', $requests, $user_id );
}

/**
 * Output the ID of the engagementship between the logged-in user and the current user in the loop.
 *
 * @since 1.2.0
 */
function bp_engagement_engagementship_id() {
	echo bp_get_engagement_engagementship_id();
}
	/**
	 * Return the ID of the engagementship between the logged-in user and the current user in the loop.
	 *
	 * @since 1.2.0
	 *
	 * @global BP_Core_Members_Template $members_template The main member template loop class.
	 *
	 * @return int ID of the engagementship.
	 */
	function bp_get_engagement_engagementship_id() {
		global $members_template;

		if ( ! $engagementship_id = wp_cache_get( 'engagementship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), 'bp' ) ) {
			$engagementship_id = engagements_get_engagementship_id( $members_template->member->id, bp_loggedin_user_id() );
			wp_cache_set( 'engagementship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), $engagementship_id, 'bp' );
		}

		/**
		 * Filters the ID of the engagementship between the logged in user and the current user in the loop.
		 *
		 * @since 1.2.0
		 *
		 * @param int $engagementship_id ID of the engagementship.
		 */
		return apply_filters( 'bp_get_engagement_engagementship_id', $engagementship_id );
	}

/**
 * Output the URL for accepting the current engagementship request in the loop.
 *
 * @since 1.0.0
 */
function bp_engagement_accept_request_link() {
	echo bp_get_engagement_accept_request_link();
}
	/**
	 * Return the URL for accepting the current engagementship request in the loop.
	 *
	 * @since 1.0.0
	 *
	 * @global BP_Core_Members_Template $members_template The main member template loop class.
	 *
	 * @return string accept-engagementship URL.
	 */
	function bp_get_engagement_accept_request_link() {
		global $members_template;

		if ( ! $engagementship_id = wp_cache_get( 'engagementship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), 'bp' ) ) {
			$engagementship_id = engagements_get_engagementship_id( $members_template->member->id, bp_loggedin_user_id() );
			wp_cache_set( 'engagementship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), $engagementship_id, 'bp' );
		}

		$url = wp_nonce_url(
			bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_engagements_slug(), 'requests', array( 'accept', $engagementship_id ) ) ) ),
			'engagements_accept_engagementship'
		);

		/**
		 * Filters the URL for accepting the current engagementship request in the loop.
		 *
		 * @since 1.0.0
		 * @since 2.6.0 Added the `$engagementship_id` parameter.
		 *
		 * @param string $url           Accept-engagementship URL.
		 * @param int    $engagementship_id ID of the engagementship.
		 */
		return apply_filters( 'bp_get_engagement_accept_request_link', $url, $engagementship_id );
	}

/**
 * Output the URL for rejecting the current engagementship request in the loop.
 *
 * @since 1.0.0
 */
function bp_engagement_reject_request_link() {
	echo bp_get_engagement_reject_request_link();
}
	/**
	 * Return the URL for rejecting the current engagementship request in the loop.
	 *
	 * @since 1.0.0
	 *
	 * @global BP_Core_Members_Template $members_template The main member template loop class.
	 *
	 * @return string reject-engagementship URL.
	 */
	function bp_get_engagement_reject_request_link() {
		global $members_template;

		if ( ! $engagementship_id = wp_cache_get( 'engagementship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), 'bp' ) ) {
			$engagementship_id = engagements_get_engagementship_id( $members_template->member->id, bp_loggedin_user_id() );
			wp_cache_set( 'engagementship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), $engagementship_id, 'bp' );
		}

		$url = wp_nonce_url(
			bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_engagements_slug(), 'requests', array( 'reject', $engagementship_id ) ) ) ),
			'engagements_reject_engagementship'
		);

		/**
		 * Filters the URL for rejecting the current engagementship request in the loop.
		 *
		 * @since 1.0.0
		 * @since 2.6.0 Added the `$engagementship_id` parameter.
		 *
		 * @param string $url           Reject-engagementship URL.
		 * @param int    $engagementship_id ID of the engagementship.
		 */
		return apply_filters( 'bp_get_engagement_reject_request_link', $url, $engagementship_id );
	}

/**
 * Output the total engagement count for a given user.
 *
 * @since 1.2.0
 *
 * @param int $user_id See {@link engagements_get_total_engagement_count()}.
 */
function bp_total_engagement_count( $user_id = 0 ) {
	echo bp_get_total_engagement_count( $user_id );
}
	/**
	 * Return the total engagement count for a given user.
	 *
	 * @since 1.2.0
	 *
	 * @param int $user_id See {@link engagements_get_total_engagement_count()}.
	 * @return int Total engagement count.
	 */
	function bp_get_total_engagement_count( $user_id = 0 ) {

		/**
		 * Filters the total engagement count for a given user.
		 *
		 * @since 1.2.0
		 * @since 2.6.0 Added the `$user_id` parameter.
		 *
		 * @param int $value   Total engagement count.
		 * @param int $user_id ID of the queried user.
		 */
		return apply_filters( 'bp_get_total_engagement_count', engagements_get_total_engagement_count( $user_id ), $user_id );
	}

/**
 * Output the total engagementship request count for a given user.
 *
 * @since 1.2.0
 *
 * @param int $user_id ID of the user whose requests are being counted.
 *                     Default: ID of the logged-in user.
 */
function bp_engagement_total_requests_count( $user_id = 0 ) {
	echo bp_engagement_get_total_requests_count( $user_id );
}
	/**
	 * Return the total engagementship request count for a given user.
	 *
	 * @since 1.2.0
	 *
	 * @param int $user_id ID of the user whose requests are being counted.
	 *                     Default: ID of the logged-in user.
	 * @return int engagement count.
	 */
	function bp_engagement_get_total_requests_count( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		/**
		 * Filters the total engagementship request count for a given user.
		 *
		 * @since 1.2.0
		 * @since 2.6.0 Added the `$user_id` parameter.
		 *
		 * @param int $value   engagementship request count.
		 * @param int $user_id ID of the queried user.
		 */
		return apply_filters( 'bp_engagement_get_total_requests_count', count( BP_Engagements_Engagementship::get_engagement_user_ids( $user_id, true ) ), $user_id );
	}

/** Stats **********************************************************************/

/**
 * Display the number of engagements in user's profile.
 *
 * @since 2.0.0
 *
 * @param array|string $args before|after|user_id.
 */
function bp_engagements_profile_stats( $args = '' ) {
	echo bp_engagements_get_profile_stats( $args );
}
add_action( 'bp_members_admin_user_stats', 'bp_engagements_profile_stats', 7, 1 );

/**
 * Return the number of engagements in user's profile.
 *
 * @since 2.0.0
 *
 * @param array|string $args before|after|user_id.
 * @return string HTML for stats output.
 */
function bp_engagements_get_profile_stats( $args = '' ) {

	// Parse the args.
	$r = bp_parse_args(
		$args,
		array(
			'before'  => '<li class="bp-engagements-profile-stats">',
			'after'   => '</li>',
			'user_id' => bp_displayed_user_id(),
			'engagements' => 0,
			'output'  => '',
		),
		'engagements_get_profile_stats'
	);

	// Allow completely overloaded output.
	if ( empty( $r['output'] ) ) {

		// Only proceed if a user ID was passed.
		if ( ! empty( $r['user_id'] ) ) {

			// Get the user's engagements.
			if ( empty( $r['engagements'] ) ) {
				$r['engagements'] = absint( engagements_get_total_engagement_count( $r['user_id'] ) );
			}

			// If engagements exist, show some formatted output.
			$r['output'] = $r['before'];

			/* translators: %s: total engagement count */
			$r['output'] .= sprintf( _n( '%s engagement', '%s engagements', $r['engagements'], 'buddypress' ), '<strong>' . number_format_i18n( $r['engagements'] ) . '</strong>' );
			$r['output'] .= $r['after'];
		}
	}

	/**
	 * Filters the number of engagements in user's profile.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Formatted string displaying total engagements count.
	 * @param array  $r     Array of arguments for string formatting and output.
	 */
	return apply_filters( 'bp_engagements_get_profile_stats', $r['output'], $r );
}
