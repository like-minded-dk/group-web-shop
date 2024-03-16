<?php
/**
 * BuddyPress engagements Functions.
 *
 * Functions are where all the magic happens in BuddyPress. They will
 * handle the actual saving or manipulation of information. Usually they will
 * hand off to a database class for data access, then return
 * true or false on success or failure.
 *
 * @package BuddyPress
 * @subpackage engagementsFunctions
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Create a new engagementship.
 *
 * @since 1.0.0
 *
 * @param int  $initiator_userid ID of the "initiator" user (the user who is
 *                               sending the engagementship request).
 * @param int  $engagement_userid    ID of the "engagement" user (the user whose engagementship
 *                               is being requested).
 * @param bool $force_accept     Optional. Whether to force acceptance. When false,
 *                               running engagements_add_engagement() will result in a engagementship request.
 *                               When true, running engagements_add_engagement() will result in an accepted
 *                               engagementship, with no notifications being sent. Default: false.
 * @return bool True on success, false on failure.
 */
function engagements_add_engagement( $initiator_userid, $engagement_userid, $force_accept = false, $exist = false ) {

	// You cannot be engagements with yourself!
	if ( $initiator_userid === $engagement_userid ) {
		return false;
	}

	// Check if already engagements, and bail if so.
	if ( engagements_check_engagementship( $initiator_userid, $engagement_userid ) ) {
		return true;
	}

	// Setup the engagementship data.
	$engagementship = new BP_Engagements_Engagementship;
	$engagementship->initiator_user_id = (int) $initiator_userid;
	$engagementship->engagement_user_id    = (int) $engagement_userid;
	$engagementship->is_confirmed      = 0;
	$engagementship->is_limited        = 0;
	$engagementship->date_created      = bp_core_current_time();

	if ( ! empty( $force_accept ) ) {
		$engagementship->is_confirmed = 1;
	}

	// Bail if engagementship could not be saved (how sad!).
	if ( ! $engagementship->save() ) {
		return false;
	}	

	// Send notifications.
	if ( empty( $force_accept ) ) {
		$action = 'requested';

	// Update engagement totals.
	} else {
		$action = 'accepted';
		engagements_update_engagement_totals( $engagementship->initiator_user_id, $engagementship->engagement_user_id, 'add' );
	}

	/**
	 * Fires at the end of initiating a new engagementship connection.
	 *
	 * This is a variable hook, depending on context.
	 * The two potential hooks are: engagements_engagementship_requested, engagements_engagementship_accepted.
	 *
	 * @since 1.0.0
	 *
	 * @param int                   $id                ID of the pending engagementship connection.
	 * @param int                   $initiator_user_id ID of the engagementship initiator.
	 * @param int                   $engagement_user_id    ID of the engagement user.
	 * @param BP_Engagements_Engagementship $engagementship        The engagementship object.
	 */
	do_action( 'engagements_engagementship_' . $action, $engagementship->id, $engagementship->initiator_user_id, $engagementship->engagement_user_id, $engagementship );

	return true;
}

/**
 * Remove a engagementship.
 *
 * Will also delete the related "engagementship_accepted" activity item.
 *
 * @since 1.0.0
 *
 * @param int $initiator_userid ID of the engagementship initiator.
 * @param int $engagement_userid    ID of the engagement user.
 * @return bool True on success, false on failure.
 */
function engagements_remove_engagement( $initiator_userid, $engagement_userid ) {

	$engagementship_id = BP_Engagements_Engagementship::get_relationship_id( $initiator_userid, $engagement_userid );
	$engagementship    = new BP_Engagements_Engagementship( $engagementship_id );

	/**
	 * Fires before the deletion of a engagementship activity item
	 * for the user who canceled the engagementship.
	 *
	 * @since 1.5.0
	 *
	 * @param int $engagementship_id    ID of the engagementship object, if any, between a pair of users.
	 * @param int $initiator_userid ID of the engagementship initiator.
	 * @param int $engagement_userid    ID of the engagement user.
	 */
	do_action( 'engagements_before_engagementship_delete', $engagementship_id, $initiator_userid, $engagement_userid );

	/**
	 * Fires before the engagementship connection is removed.
	 *
	 * This hook is misleadingly named - the engagementship is not yet deleted.
	 * This is your last chance to do something while the engagementship exists.
	 *
	 * @since 1.0.0
	 *
	 * @param int $engagementship_id    ID of the engagementship object, if any, between a pair of users.
	 * @param int $initiator_userid ID of the engagementship initiator.
	 * @param int $engagement_userid    ID of the engagement user.
	 */
	do_action( 'engagements_engagementship_deleted', $engagementship_id, $initiator_userid, $engagement_userid );

	if ( $engagementship->delete() ) {
		engagements_update_engagement_totals( $initiator_userid, $engagement_userid, 'remove' );

		/**
		 * Fires after the engagementship connection is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param int $initiator_userid ID of the engagementship initiator.
		 * @param int $engagement_userid    ID of the engagement user.
		 */
		do_action( 'engagements_engagementship_post_delete', $initiator_userid, $engagement_userid );

		return true;
	}

	return false;
}

/**
 * Mark a engagementship request as accepted.
 *
 * Also initiates a "engagementship_accepted" activity item.
 *
 * @since 1.0.0
 *
 * @param int $engagementship_id ID of the pending engagementship object.
 * @return bool True on success, false on failure.
 */
function engagements_accept_engagement( $engagementship_id ) {
	// Get the engagementship data.
	$engagementship = new BP_Engagements_Engagementship( $engagementship_id, false, false );

	// Accepting engagementship.
	if ( empty( $engagementship->is_confirmed ) && BP_Engagements_Engagementship::accept( $engagementship_id ) ) {

		// Bump the engagementship counts.
		engagements_update_engagement_totals( $engagementship->initiator_user_id, $engagementship->engagement_user_id );

		/**
		 * Fires after a engagementship is accepted.
		 *
		 * @since 1.0.0
		 *
		 * @param int                   $id                ID of the pending engagementship object.
		 * @param int                   $initiator_user_id ID of the engagementship initiator.
		 * @param int                   $engagement_user_id    ID of the user requested engagementship with.
		 * @param BP_Engagements_Engagementship $engagementship        The engagementship object.
		 */
		do_action( 'engagements_engagementship_accepted', $engagementship->id, $engagementship->initiator_user_id, $engagementship->engagement_user_id, $engagementship );

		return true;
	}

	return false;
}

/**
 * Mark a engagementship request as rejected.
 *
 * @since 1.0.0
 *
 * @param int $engagementship_id ID of the pending engagementship object.
 * @return bool True on success, false on failure.
 */
function engagements_reject_engagement( $engagementship_id ) {
	$engagementship = new BP_Engagements_Engagementship( $engagementship_id, false, false );

	if ( empty( $engagementship->is_confirmed ) && BP_Engagements_Engagementship::reject( $engagementship_id ) ) {

		/**
		 * Fires after a engagementship request is rejected.
		 *
		 * @since 1.0.0
		 *
		 * @param int                   $engagementship_id ID of the engagementship.
		 * @param BP_Engagements_Engagementship $engagementship    The engagementship object. Passed by reference.
		 */
		do_action_ref_array( 'engagements_engagementship_rejected', array( $engagementship_id, &$engagementship ) );

		return true;
	}

	return false;
}

/**
 * Withdraw a engagementship request.
 *
 * @since 1.6.0
 *
 * @param int $initiator_userid ID of the engagementship initiator - this is the
 *                              user who requested the engagementship, and is doing the withdrawing.
 * @param int $engagement_userid    ID of the requested engagement.
 * @return bool True on success, false on failure.
 */
function engagements_withdraw_engagementship( $initiator_userid, $engagement_userid ) {
	$engagementship_id = BP_Engagements_Engagementship::get_relationship_id( $initiator_userid, $engagement_userid );
	$engagementship    = new BP_Engagements_Engagementship( $engagementship_id, false, false );

	if ( empty( $engagementship->is_confirmed ) && BP_Engagements_Engagementship::withdraw( $engagementship_id ) ) {

		// @deprecated Since 1.9
		do_action_ref_array( 'engagements_engagementship_whithdrawn', array( $engagementship_id, &$engagementship ) );

		/**
		 * Fires after a engagementship request has been withdrawn.
		 *
		 * @since 1.9.0
		 *
		 * @param int                   $engagementship_id ID of the engagementship.
		 * @param BP_Engagements_Engagementship $engagementship    The engagementship object. Passed by reference.
		 */
		do_action_ref_array( 'engagements_engagementship_withdrawn', array( $engagementship_id, &$engagementship ) );

		return true;
	}

	return false;
}

/**
 * Check whether two users are engagements.
 *
 * @since 1.0.0
 *
 * @param int $user_id            ID of the first user.
 * @param int $possible_engagement_id ID of the other user.
 * @return bool Returns true if the two users are engagements, otherwise false.
 */
function engagements_check_engagementship( $user_id, $possible_engagement_id ) {
	return ( 'is_engagement' === BP_Engagements_Engagementship::check_is_relation( $user_id, $possible_engagement_id ) );
}

/**
 * Get the engagementship status of two engagements.
 *
 * Will return 'is_engagements', 'not_engagement', 'pending_engagement' or 'awaiting_response'.
 *
 * @since 1.2.0
 *
 * @global BP_Core_Members_Template $members_template The main member template loop class.
 *
 * @param int $user_id            ID of the first user.
 * @param int $possible_engagement_id ID of the other user.
 * @return string engagement status of the two users.
 */
function engagements_check_engagementship_status( $user_id, $possible_engagement_id ) {
	global $members_template;

	// Check the BP_User_Query first
	// @see bp_engagements_filter_user_query_populate_extras().
	if ( ! empty( $members_template->in_the_loop ) ) {
		if ( isset( $members_template->member->engagementship_status ) ) {
			return $members_template->member->engagementship_status;
		}
	}

	return BP_Engagements_Engagementship::check_is_relation( $user_id, $possible_engagement_id );
}

/**
 * Get the engagement count of a given user.
 *
 * @since 1.2.0
 *
 * @param int $user_id ID of the user whose engagements are being counted.
 * @return int engagement count of the user.
 */
function engagements_get_total_engagement_count( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
	}
	$count = bp_get_user_meta( $user_id, 'total_engagement_count', true );
	if ( empty( $count ) ) {
		$count = 0;
	}

	/**
	 * Filters the total engagement count for a given user.
	 *
	 * @since 1.2.0
	 *
	 * @param int $count Total engagement count for a given user.
	 */
	return apply_filters( 'engagements_get_total_engagement_count', (int) $count );
}

/**
 * Check whether a given user has any engagements.
 *
 * @since 1.0.0
 *
 * @param int $user_id ID of the user whose engagements are being checked.
 * @return bool True if the user has engagements, otherwise false.
 */
function engagements_check_user_has_engagements( $user_id ) {
	$engagement_count = engagements_get_total_engagement_count( $user_id );

	if ( empty( $engagement_count ) ) {
		return false;
	}

	if ( ! (int) $engagement_count ) {
		return false;
	}

	return true;
}

/**
 * Get the ID of two users' engagementship, if it exists.
 *
 * @since 1.2.0
 *
 * @param int $initiator_user_id ID of the first user.
 * @param int $engagement_user_id    ID of the second user.
 * @return int|null ID of the engagementship if found, otherwise null.
 */
function engagements_get_relationship_id( $initiator_user_id, $engagement_user_id ) {
	return BP_Engagements_Engagementship::get_relationship_id( $initiator_user_id, $engagement_user_id );
}

/**
 * Get the IDs of a given user's engagements.
 *
 * @since 1.0.0
 *
 * @param int  $user_id              ID of the user whose engagements are being retrieved.
 * @param bool $engagement_requests_only Optional. Whether to fetch unaccepted
 *                                   requests only. Default: false.
 * @param bool $assoc_arr            Optional. True to receive an array of arrays keyed as
 *                                   'user_id' => $user_id; false to get a one-dimensional
 *                                   array of user IDs. Default: false.
 * @return array
 */
function engagements_get_engagement_user_ids( $user_id, $engagement_requests_only = false, $assoc_arr = false ) {
	return BP_Engagements_Engagementship::get_relation_user_ids( $user_id, $engagement_requests_only, $assoc_arr );
}

/**
 * Search the engagements of a user by a search string.
 *
 * @since 1.0.0
 *
 * @param string $search_terms The search string, matched against xprofile fields (if
 *                             available), or usermeta 'nickname' field.
 * @param int    $user_id      ID of the user whose engagements are being searched.
 * @param int    $pag_num      Optional. Max number of engagements to return.
 * @param int    $pag_page     Optional. The page of results to return. Default: null (no
 *                             pagination - return all results).
 * @return array|bool On success, an array: {
 *     @type array $engagements IDs of engagements returned by the query.
 *     @type int   $count   Total number of engagements (disregarding
 *                          pagination) who match the search.
 * }. Returns false on failure.
 */
function engagements_search_engagements( $search_terms, $user_id, $pag_num = 10, $pag_page = 1 ) {
	return BP_Engagements_Engagementship::search_relations( $search_terms, $user_id, $pag_num, $pag_page );
}

/**
 * Get a list of IDs of users who have requested engagementship of a given user.
 *
 * @since 1.2.0
 *
 * @param int $user_id The ID of the user who has received the engagementship requests.
 * @return array|bool An array of user IDs, or false if none are found.
 */
function engagements_get_relationship_request_user_ids( $user_id ) {
	return BP_Engagements_Engagementship::get_relationship_request_user_ids( $user_id );
}

/**
 * Get a user's most recently active engagements.
 *
 * @since 1.0.0
 *
 * @see bp_core_get_users() for a description of return value.
 *
 * @param int    $user_id  ID of the user whose engagements are being retrieved.
 * @param int    $per_page Optional. Number of results to return per page.
 *                         Default: 0 (no pagination; show all results).
 * @param int    $page     Optional. Number of the page of results to return.
 *                         Default: 0 (no pagination; show all results).
 * @param string $filter   Optional. Limit results to those matching a search
 *                         string.
 * @return array See {@link BP_Core_User::get_users()}.
 */
function engagements_get_recently_active( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	$engagements = bp_core_get_users( array(
		'type'         => 'active',
		'per_page'     => $per_page,
		'page'         => $page,
		'user_id'      => $user_id,
		'search_terms' => $filter,
	) );

	/**
	 * Filters a user's most recently active engagements.
	 *
	 * @since 1.2.0
	 *
	 * @param array $engagements {
	 *     @type int   $total_users Total number of users matched by query params.
	 *     @type array $paged_users The current page of users matched by query params.
	 * }
	 */
	return apply_filters( 'engagements_get_recently_active', $engagements );
}

/**
 * Get a user's engagements, in alphabetical order.
 *
 * @since 1.0.0
 *
 * @see bp_core_get_users() for a description of return value.
 *
 * @param int    $user_id  ID of the user whose engagements are being retrieved.
 * @param int    $per_page Optional. Number of results to return per page.
 *                         Default: 0 (no pagination; show all results).
 * @param int    $page     Optional. Number of the page of results to return.
 *                         Default: 0 (no pagination; show all results).
 * @param string $filter   Optional. Limit results to those matching a search
 *                         string.
 * @return array See {@link BP_Core_User::get_users()}.
 */
function engagements_get_alphabetically( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	$engagements = bp_core_get_users( array(
		'type'         => 'alphabetical',
		'per_page'     => $per_page,
		'page'         => $page,
		'user_id'      => $user_id,
		'search_terms' => $filter,
	) );

	/**
	 * Filters a user's engagements listed in alphabetical order.
	 *
	 * @since 1.2.0
	 *
	 * @return array $engagements {
	 *     @type int   $total_users Total number of users matched by query params.
	 *     @type array $paged_users The current page of users matched by query params.
	 * }
	 */
	return apply_filters( 'engagements_get_alphabetically', $engagements );
}

/**
 * Get a user's engagements, in the order in which they joined the site.
 *
 * @since 1.0.0
 *
 * @see bp_core_get_users() for a description of return value.
 *
 * @param int    $user_id  ID of the user whose engagements are being retrieved.
 * @param int    $per_page Optional. Number of results to return per page.
 *                         Default: 0 (no pagination; show all results).
 * @param int    $page     Optional. Number of the page of results to return.
 *                         Default: 0 (no pagination; show all results).
 * @param string $filter   Optional. Limit results to those matching a search
 *                         string.
 * @return array See {@link BP_Core_User::get_users()}.
 */
function engagements_get_newest( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	$engagements = bp_core_get_users( array(
		'type'         => 'newest',
		'per_page'     => $per_page,
		'page'         => $page,
		'user_id'      => $user_id,
		'search_terms' => $filter,
	) );

	/**
	 * Filters a user's engagements listed from newest to oldest.
	 *
	 * @since 1.2.0
	 *
	 * @param array $engagements {
	 *     @type int   $total_users Total number of users matched by query params.
	 *     @type array $paged_users The current page of users matched by query params.
	 * }
	 */
	return apply_filters( 'engagements_get_newest', $engagements );
}

/**
 * Get the last active date of many users at once.
 *
 * @since 1.0.0
 *
 * @see BP_Engagements_Engagementship::get_bulk_last_active() for a description of
 *      arguments and return value.
 *
 * @param array $engagement_ids See BP_Engagements_Engagementship::get_bulk_last_active().
 * @return array See BP_Engagements_Engagementship::get_bulk_last_active().
 */
function engagements_get_bulk_last_active( $engagement_ids ) {
	return BP_Engagements_Engagementship::get_bulk_last_active( $engagement_ids );
}

/**
 * Get a list of engagements that a user can invite into this group.
 *
 * Excludes engagements that are already in the group, and banned engagements if the
 * user is not a group admin.
 *
 * @since 1.0.0
 *
 * @param int $user_id  User ID whose engagements to see can be invited. Default:
 *                      ID of the logged-in user.
 * @param int $group_id Group to check possible invitations against.
 * @return mixed False if no engagements, array of users if engagements.
 */
function engagements_get_engagements_invite_list( $user_id = 0, $group_id = 0 ) {

	// Default to logged in user id.
	if ( empty( $user_id ) ) {
		$user_id = bp_loggedin_user_id();
	}

	// Only group admins can invited previously banned users.
	$user_is_admin = (bool) groups_is_user_admin( $user_id, $group_id );

	// Assume no engagements.
	$engagements = array();

	/**
	 * Filters default arguments for list of engagements a user can invite into this group.
	 *
	 * @since 1.5.4
	 *
	 * @param array $value {
	 *     @type int    $user_id  User ID whose engagements too see can be invited.
	 *     @type string $type     Type of order to return a list of members.
	 *     @type int    $per_page Number of engagements per page.
	 * }
	 */
	$args = apply_filters(
		'bp_engagements_pre_get_invite_list',
		array(
			'user_id'  => $user_id,
			'type'     => 'alphabetical',
			'per_page' => 0,
		)
	);

	// User has engagements.
	if ( bp_has_members( $args ) ) {

		/**
		 * Loop through all engagements and try to add them to the invitation list.
		 *
		 * Exclude engagements that:
		 *     1. are already members of the group
		 *     2. are banned from this group if the current user is also not a
		 *        group admin.
		 */
		while ( bp_members() ) :

			// Load the member.
			bp_the_member();

			// Get the user ID of the engagement.
			$engagement_user_id = bp_get_member_user_id();

			// Skip engagement if already in the group.
			if ( groups_is_user_member( $engagement_user_id, $group_id ) ) {
				continue;
			}

			// Skip engagement if not group admin and user banned from group.
			if ( ( false === $user_is_admin ) && groups_is_user_banned( $engagement_user_id, $group_id ) ) {
				continue;
			}

			// engagement is safe, so add it to the array of possible engagements.
			$engagements[] = array(
				'id'        => $engagement_user_id,
				'full_name' => bp_get_member_name(),
			);

		endwhile;
	}

	// If no engagements, explicitly set to false.
	if ( empty( $engagements ) ) {
		$engagements = false;
	}

	/**
	 * Filters the list of potential engagements that can be invited to this group.
	 *
	 * @since 1.5.4
	 *
	 * @param array|bool $engagements  Array engagements available to invite or false for no engagements.
	 * @param int        $user_id  ID of the user checked for who they can invite.
	 * @param int        $group_id ID of the group being checked on.
	 */
	return apply_filters( 'bp_engagements_get_invite_list', $engagements, $user_id, $group_id );
}

/**
 * Get a count of a user's engagements who can be invited to a given group.
 *
 * Users can invite any of their engagements except:
 *
 * - users who are already in the group
 * - users who have a pending invite to the group
 * - users who have been banned from the group
 *
 * @since 1.0.0
 *
 * @param int $user_id  ID of the user whose engagements are being counted.
 * @param int $group_id ID of the group engagements are being invited to.
 * @return int Eligible engagement count.
 */
function engagements_count_invitable_engagements( $user_id, $group_id ) {
	return BP_Engagements_Engagementship::get_invitable_engagement_count( $user_id, $group_id );
}

/**
 * Get a total engagement count for a given user.
 *
 * @since 1.0.0
 *
 * @param int $user_id Optional. ID of the user whose engagementships you are
 *                     counting. Default: displayed user (if any), otherwise logged-in user.
 * @return int engagement count for the user.
 */
function engagements_get_engagement_count_for_user( $user_id ) {
	return BP_Engagements_Engagementship::total_engagement_count( $user_id );
}

/**
 * Return a list of a user's engagements, filtered by a search term.
 *
 * @since 1.0.0
 *
 * @param string $search_terms Search term to filter on.
 * @param int    $user_id      ID of the user whose engagements are being searched.
 * @param int    $pag_num      Number of results to return per page. Default: 0 (no
 *                             pagination - show all results).
 * @param int    $pag_page     Number of the page being requested. Default: 0 (no
 *                             pagination - show all results).
 * @return array Array of BP_Core_User objects corresponding to engagements.
 */
function engagements_search_users( $search_terms, $user_id, $pag_num = 0, $pag_page = 0 ) {
	$user_ids = BP_Engagements_Engagementship::search_users( $search_terms, $user_id, $pag_num, $pag_page );

	if ( empty( $user_ids ) ) {
		return false;
	}

	$users = array();
	for ( $i = 0, $count = count( $user_ids ); $i < $count; ++$i ) {
		$users[] = new BP_Core_User( $user_ids[ $i ] );
	}

	return array(
		'users' => $users,
		'count' => BP_Engagements_Engagementship::search_users_count( $search_terms ),
	);
}

/**
 * Has a engagementship been confirmed (accepted)?
 *
 * @since 1.0.0
 *
 * @param int $engagementship_id The ID of the engagementship being checked.
 * @return bool True if the engagementship is confirmed, otherwise false.
 */
function engagements_is_engagementship_confirmed( $engagementship_id ) {
	$engagementship = new BP_Engagements_Engagementship( $engagementship_id );
	return (bool) $engagementship->is_confirmed;
}

/**
 * Update user engagement counts.
 *
 * engagement counts are cached in usermeta for performance reasons. After a
 * engagementship event (acceptance, deletion), call this function to regenerate
 * the cached values.
 *
 * @since 1.0.0
 *
 * @param int    $initiator_user_id ID of the first user.
 * @param int    $engagement_user_id    ID of the second user.
 * @param string $status            Optional. The engagementship event that's been triggered.
 *                                  'add' will ++ each user's engagement counts, while any other string
 *                                  will --.
 */
function engagements_update_engagement_totals( $initiator_user_id, $engagement_user_id, $status = 'add' ) {
	if ( 'add' === $status ) {
		bp_update_user_meta( $initiator_user_id, 'total_engagement_count', (int) bp_get_user_meta( $initiator_user_id, 'total_engagement_count', true ) + 1 );
		bp_update_user_meta( $engagement_user_id, 'total_engagement_count', (int) bp_get_user_meta( $engagement_user_id, 'total_engagement_count', true ) + 1 );
	} else {
		bp_update_user_meta( $initiator_user_id, 'total_engagement_count', (int) bp_get_user_meta( $initiator_user_id, 'total_engagement_count', true ) - 1 );
		bp_update_user_meta( $engagement_user_id, 'total_engagement_count', (int) bp_get_user_meta( $engagement_user_id, 'total_engagement_count', true ) - 1 );
	}
}

/**
 * Remove all engagements-related data concerning a given user.
 *
 * Removes the reselling:
 *
 * - engagementships of which the user is a member.
 * - Cached engagement count for the user.
 * - Notifications of engagementship requests sent by the user.
 *
 * @since 1.0.0
 *
 * @param int $user_id ID of the user whose engagement data is being removed.
 */
function engagements_remove_data( $user_id ) {

	/**
	 * Fires before deletion of engagement-related data for a given user.
	 *
	 * @since 1.5.0
	 *
	 * @param int $user_id ID for the user whose engagement data is being removed.
	 */
	do_action( 'engagements_before_remove_data', $user_id );

	BP_Engagements_Engagementship::delete_all_for_user( $user_id );

	// Remove usermeta.
	bp_delete_user_meta( $user_id, 'total_engagement_count' );

	/**
	 * Fires after deletion of engagement-related data for a given user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id ID for the user whose engagement data is being removed.
	 */
	do_action( 'engagements_remove_data', $user_id );
}
add_action( 'wpmu_delete_user', 'engagements_remove_data' );
add_action( 'bp_make_spam_user', 'engagements_remove_data' );

/**
 * Deletes user engagements data on the 'delete_user' hook.
 *
 * @since 6.0.0
 *
 * @param int $user_id The ID of the deleted user.
 */
function bp_engagements_remove_data_on_delete_user( $user_id ) {
	if ( ! bp_remove_user_data_on_delete_user_hook( 'engagements', $user_id ) ) {
		return;
	}

	engagements_remove_data( $user_id );
}
add_action( 'delete_user', 'bp_engagements_remove_data_on_delete_user' );

/**
 * Used by the Activity component's @mentions to print a JSON list of the current user's engagements.
 *
 * This is intended to speed up @mentions lookups for a majority of use cases.
 *
 * @since 2.1.0
 *
 * @see bp_activity_mentions_script()
 */
function bp_engagements_prime_mentions_results() {

	// Stop here if user is not logged in.
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( ! bp_activity_maybe_load_mentions_scripts() ) {
		return;
	}

	// Bail out if the site has a ton of users.
	if ( bp_is_large_install() ) {
		return;
	}

	if ( engagements_get_total_engagement_count( get_current_user_id() ) > 30 ) {
		return;
	}

	$engagements_query = array(
		'count_total'     => '', // Prevents total count.
		'populate_extras' => false,
		'type'            => 'alphabetical',
		'user_id'         => get_current_user_id(),
	);

	$engagements_query = new BP_User_Query( $engagements_query );
	$results       = array();

	foreach ( $engagements_query->results as $user ) {
		$result        = new stdClass();
		$result->ID    = $user->user_nicename;
		$result->image = bp_core_fetch_avatar( array( 'html' => false, 'item_id' => $user->ID ) );

		if ( ! empty( $user->display_name ) && ! bp_disable_profile_sync() ) {
			$result->name = $user->display_name;
		} else {
			$result->name = bp_core_get_user_displayname( $user->ID );
		}

		$results[] = $result;
	}

	wp_localize_script( 'bp-mentions', 'BP_Suggestions', array(
		'engagements' => $results,
	) );
}
add_action( 'bp_activity_mentions_prime_results', 'bp_engagements_prime_mentions_results' );

/** Emails ********************************************************************/

/**
 * Send notifications related to a new engagementship request.
 *
 * When a engagementship is requested, an email and a BP notification are sent to
 * the user of whom engagementship has been requested ($engagement_id).
 *
 * @since 1.0.0
 *
 * @param int $engagementship_id ID of the engagementship object.
 * @param int $initiator_id  ID of the user who initiated the request.
 * @param int $engagement_id     ID of the request recipient.
 */
function engagements_notification_new_request( $engagementship_id, $initiator_id, $engagement_id ) {
	if ( 'no' === bp_get_user_meta( (int) $engagement_id, 'notification_engagements_engagementship_request', true ) ) {
		return;
	}

	$unsubscribe_args = array(
		'user_id'           => $engagement_id,
		'notification_type' => 'engagements-request',
	);

	$args = array(
		'tokens' => array(
			'engagement-requests.url' => esc_url(
				bp_members_get_user_url(
					$engagement_id,
					bp_members_get_path_chunks( array( bp_get_engagements_slug(), 'requests' ) )
				)
			),
			'engagement.id'           => $engagement_id,
			'engagementship.id'       => $engagementship_id,
			'initiator.id'        => $initiator_id,
			'initiator.url'       => esc_url( bp_members_get_user_url( $initiator_id ) ),
			'initiator.name'      => bp_core_get_user_displayname( $initiator_id ),
			'unsubscribe'         => esc_url( bp_email_get_unsubscribe_link( $unsubscribe_args ) ),
		),
	);
	bp_send_email( 'engagements-request', $engagement_id, $args );
}
add_action( 'engagements_engagementship_requested', 'engagements_notification_new_request', 10, 3 );

/**
 * Send notifications related to the acceptance of a engagementship request.
 *
 * When a engagementship request is accepted, an email and a BP notification are
 * sent to the user who requested the engagementship ($initiator_id).
 *
 * @since 1.0.0
 *
 * @param int $engagementship_id ID of the engagementship object.
 * @param int $initiator_id  ID of the user who initiated the request.
 * @param int $engagement_id     ID of the request recipient.
 */
function engagements_notification_accepted_request( $engagementship_id, $initiator_id, $engagement_id ) {
	if ( 'no' === bp_get_user_meta( (int) $initiator_id, 'notification_engagements_engagementship_accepted', true ) ) {
		return;
	}

	$unsubscribe_args = array(
		'user_id'           => $initiator_id,
		'notification_type' => 'engagements-request-accepted',
	);

	$args = array(
		'tokens' => array(
			'engagement.id'      => $engagement_id,
			'engagementship.url' => esc_url( bp_members_get_user_url( $engagement_id ) ),
			'engagement.name'    => bp_core_get_user_displayname( $engagement_id ),
			'engagementship.id'  => $engagementship_id,
			'initiator.id'   => $initiator_id,
			'unsubscribe'    => esc_url( bp_email_get_unsubscribe_link( $unsubscribe_args ) ),
		),
	);
	bp_send_email( 'engagements-request-accepted', $initiator_id, $args );
}
add_action( 'engagements_engagementship_accepted', 'engagements_notification_accepted_request', 10, 3 );

/**
 * Finds and exports engagementship data associated with an email address.
 *
 * @since 4.0.0
 *
 * @param string $email_address  The user's email address.
 * @param int    $page           Batch number.
 * @return array An array of personal data.
 */
function bp_engagements_personal_data_exporter( $email_address, $page ) {
	$number         = 50;
	$email_address  = trim( $email_address );
	$data_to_export = array();
	$user           = get_user_by( 'email', $email_address );

	if ( ! $user ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$engagementships = BP_Engagements_Engagementship::get_relationships( $user->ID, array(
		'is_confirmed' => true,
		'page'         => $page,
		'per_page'     => $number,
	) );
	

	foreach ( $engagementships as $engagementship ) {
		if ( (int) $user->ID === (int) $engagementship->initiator_user_id ) {
			$engagement_id         = $engagementship->engagement_user_id;
			$user_is_initiator = true;
		} else {
			$engagement_id         = $engagementship->initiator_user_id;
			$user_is_initiator = false;
		}

		$item_data = array(
			array(
				'name'  => __( 'engagement', 'buddypress' ),
				'value' => bp_core_get_userlink( $engagement_id ),
			),
			array(
				'name'  => __( 'Initiated By Me', 'buddypress' ),
				'value' => $user_is_initiator ? __( 'Yes', 'buddypress' ) : __( 'No', 'buddypress' ),
			),
			array(
				'name'  => __( 'engagementship Date', 'buddypress' ),
				'value' => $engagementship->date_created,
			),
		);

		$data_to_export[] = array(
			'group_id'    => 'bp_engagements',
			'group_label' => __( 'Engagements', 'buddypress' ),
			'item_id'     => "bp-engagements-{$engagement_id}",
			'data'        => $item_data,
		);
	}

	// Tell core if we have more items to process.
	$done = count( $engagementships ) < $number;

	return array(
		'data' => $data_to_export,
		'done' => $done,
	);
}

/**
 * Finds and exports pending sent engagementship request data associated with an email address.
 *
 * @since 4.0.0
 *
 * @param string $email_address  The user's email address.
 * @param int    $page           Batch number.
 * @return array An array of personal data.
 */
function bp_engagements_pending_sent_requests_personal_data_exporter( $email_address, $page ) {
	$number         = 50;
	$email_address  = trim( $email_address );
	$data_to_export = array();
	$user           = get_user_by( 'email', $email_address );

	if ( ! $user ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$engagementships = BP_Engagements_Engagementship::get_relationships('engagement', $user->ID, array(
		'is_confirmed'      => false,
		'initiator_user_id' => $user->ID,
		'page'              => $page,
		'per_page'          => $number,
	) );

	foreach ( $engagementships as $engagementship ) {
		$item_data = array(
			array(
				'name'  => __( 'Recipient', 'buddypress' ),
				'value' => bp_core_get_userlink( $engagementship->engagement_user_id ),
			),
			array(
				'name'  => __( 'Date Sent', 'buddypress' ),
				'value' => $engagementship->date_created,
			),
		);

		$data_to_export[] = array(
			'group_id'    => 'bp_engagements_pending_sent_requests',
			'group_label' => __( 'Pending engagement Requests (Sent)', 'buddypress' ),
			'item_id'     => "bp-engagements-pending-sent-request-{$engagementship->engagement_user_id}",
			'data'        => $item_data,
		);
	}

	// Tell core if we have more items to process.
	$done = count( $engagementships ) < $number;

	return array(
		'data' => $data_to_export,
		'done' => $done,
	);
}

/**
 * Finds and exports pending received engagementship request data associated with an email address.
 *
 * @since 4.0.0
 *
 * @param string $email_address  The user's email address.
 * @param int    $page           Batch number.
 * @return array An array of personal data.
 */
function bp_engagements_pending_received_requests_personal_data_exporter( $email_address, $page ) {
	$number         = 50;
	$email_address  = trim( $email_address );
	$data_to_export = array();
	$user           = get_user_by( 'email', $email_address );

	if ( ! $user ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$engagementships = BP_Engagements_Engagementship::get_relationships( $user->ID, array(
		'is_confirmed'   => false,
		'engagement_user_id' => $user->ID,
		'page'           => $page,
		'per_page'       => $number,
	) );

	foreach ( $engagementships as $engagementship ) {
		$item_data = array(
			array(
				'name'  => __( 'Requester', 'buddypress' ),
				'value' => bp_core_get_userlink( $engagementship->initiator_user_id ),
			),
			array(
				'name'  => __( 'Date Sent', 'buddypress' ),
				'value' => $engagementship->date_created,
			),
		);

		$data_to_export[] = array(
			'group_id'    => 'bp_engagements_pending_received_requests',
			'group_label' => __( 'Pending engagement Requests (Received)', 'buddypress' ),
			'item_id'     => "bp-engagements-pending-received-request-{$engagementship->initiator_user_id}",
			'data'        => $item_data,
		);
	}

	// Tell core if we have more items to process.
	$done = count( $engagementships ) < $number;

	return array(
		'data' => $data_to_export,
		'done' => $done,
	);
}
