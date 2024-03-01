<?php
/**
 * BuddyPress engagements Activity Functions.
 *
 * These functions handle the recording, deleting and formatting of activity
 * for the user and for this specific component.
 *
 * @package BuddyPress
 * @subpackage engagementsActivity
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Record an activity item related to the engagements component.
 *
 * A wrapper for {@link bp_activity_add()} that provides some engagements-specific
 * defaults.
 *
 * @since 1.0.0
 *
 * @see bp_activity_add() for more detailed description of parameters and
 *      return values.
 *
 * @param array|string $args {
 *     An array of arguments for the new activity item. Accepts all parameters
 *     of {@link bp_activity_add()}. The one difference is the reselling
 *     argument, which has a different default here:
 *     @type string $component Default: the id of your engagements component
 *                             (usually 'engagements').
 * }
 * @return WP_Error|bool|int See {@link bp_activity_add()}.
 */
function engagements_record_activity( $args = '' ) {

	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	$r = bp_parse_args(
		$args,
		array(
			'user_id'           => bp_loggedin_user_id(),
			'action'            => '',
			'content'           => '',
			'primary_link'      => '',
			'component'         => buddypress()->engagements->id,
			'type'              => false,
			'item_id'           => false,
			'secondary_item_id' => false,
			'recorded_time'     => bp_core_current_time(),
			'hide_sitewide'     => false,
		)
	);

	return bp_activity_add( $r );
}

/**
 * Delete an activity item related to the engagements component.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     An array of arguments for the item to delete.
 *     @type int    $item_id ID of the 'item' associated with the activity item.
 *                           For engagements activity items, this is usually the user ID of one
 *                           of the engagements.
 *     @type string $type    The 'type' of the activity item (eg
 *                           'engagementship_accepted').
 *     @type int    $user_id ID of the user associated with the activity item.
 * }
 */
function engagements_delete_activity( $args ) {
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	bp_activity_delete_by_item_id( array(
		'component' => buddypress()->engagements->id,
		'item_id'   => $args['item_id'],
		'type'      => $args['type'],
		'user_id'   => $args['user_id'],
	) );
}

/**
 * Register the activity actions for bp-engagements.
 *
 * @since 1.1.0
 *
 * @return bool False if activity component is not active.
 */
function engagements_register_activity_actions() {

	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	$bp = buddypress();

	// These two added in BP 1.6.
	bp_activity_set_action(
		$bp->engagements->id,
		'engagementship_accepted',
		__( 'engagementships accepted', 'buddypress' ),
		'bp_engagements_format_activity_action_engagementship_accepted',
		__( 'engagementships', 'buddypress' ),
		array( 'activity', 'member' )
	);

	bp_activity_set_action(
		$bp->engagements->id,
		'engagementship_created',
		__( 'New engagementships', 'buddypress' ),
		'bp_engagements_format_activity_action_engagementship_created',
		__( 'engagementships', 'buddypress' ),
		array( 'activity', 'member' )
	);

	// < BP 1.6 backpat.
	bp_activity_set_action( $bp->engagements->id, 'engagements_register_activity_action', __( 'New engagementship created', 'buddypress' ) );

	/**
	 * Fires after all default bp-engagements activity actions have been registered.
	 *
	 * @since 1.1.0
	 */
	do_action( 'engagements_register_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'engagements_register_activity_actions' );

/**
 * Format 'engagementship_accepted' activity actions.
 *
 * @since 2.0.0
 *
 * @param string $action   Activity action string.
 * @param object $activity Activity data.
 * @return string Formatted activity action.
 */
function bp_engagements_format_activity_action_engagementship_accepted( $action, $activity ) {
	$initiator_link = bp_core_get_userlink( $activity->user_id );
	$engagement_link    = bp_core_get_userlink( $activity->secondary_item_id );

	/* translators: 1: the initiator user link. 2: the engagement user link. */
	$action = sprintf( esc_html__( '%1$s and %2$s are now engagements', 'buddypress' ), $initiator_link, $engagement_link );

	// Backward compatibility for legacy filter
	// The old filter has the $engagementship object passed to it. We want to
	// avoid having to build this object if it's not necessary.
	if ( has_filter( 'engagements_activity_engagementship_accepted_action' ) ) {
		$engagementship = new BP_Engagements_Engagementship( $activity->item_id );
		$action     = apply_filters( 'engagements_activity_engagementsip_accepted_action', $action, $engagementship );
	}

	/**
	 * Filters the 'engagementship_accepted' activity action format.
	 *
	 * @since 2.0.0
	 *
	 * @param string $action   String text for the 'engagementship_accepted' action.
	 * @param object $activity Activity data.
	 */
	return apply_filters( 'bp_engagements_format_activity_action_engagementship_accepted', $action, $activity );
}

/**
 * Format 'engagementship_created' activity actions.
 *
 * @since 2.0.0
 *
 * @param string $action   Static activity action.
 * @param object $activity Activity data.
 * @return string Formatted activity action.
 */
function bp_engagements_format_activity_action_engagementship_created( $action, $activity ) {
	$initiator_link = bp_core_get_userlink( $activity->user_id );
	$engagement_link    = bp_core_get_userlink( $activity->secondary_item_id );

	/* translators: 1: the initiator user link. 2: the engagement user link. */
	$action = sprintf( esc_html__( '%1$s and %2$s are now engagements', 'buddypress' ), $initiator_link, $engagement_link );

	// Backward compatibility for legacy filter
	// The old filter has the $engagementship object passed to it. We want to
	// avoid having to build this object if it's not necessary.
	if ( has_filter( 'engagements_activity_engagementship_accepted_action' ) ) {
		$engagementship = new BP_Engagements_Engagementship( $activity->item_id );
		$action     = apply_filters( 'engagements_activity_engagementsip_accepted_action', $action, $engagementship );
	}

	/**
	 * Filters the 'engagementship_created' activity action format.
	 *
	 * @since 2.0.0
	 *
	 * @param string $action   String text for the 'engagementship_created' action.
	 * @param object $activity Activity data.
	 */
	return apply_filters( 'bp_engagements_format_activity_action_engagementship_created', $action, $activity );
}

/**
 * Fetch data related to engagemented users at the beginning of an activity loop.
 *
 * This reduces database overhead during the activity loop.
 *
 * @since 2.0.0
 *
 * @param array $activities Array of activity items.
 * @return array
 */
function bp_engagements_prefetch_activity_object_data( $activities ) {
	if ( empty( $activities ) ) {
		return $activities;
	}

	$engagement_ids = array();

	foreach ( $activities as $activity ) {
		if ( buddypress()->engagements->id !== $activity->component ) {
			continue;
		}

		$engagement_ids[] = $activity->secondary_item_id;
	}

	if ( ! empty( $engagement_ids ) ) {
		// Fire a user query to prime user caches.
		new BP_User_Query( array(
			'user_ids'          => $engagement_ids,
			'populate_extras'   => false,
			'update_meta_cache' => false,
		) );
	}

	return $activities;
}
add_filter( 'bp_activity_prefetch_object_data', 'bp_engagements_prefetch_activity_object_data' );

/**
 * Set up activity arguments for use with the 'engagements' scope.
 *
 * For details on the syntax, see {@link BP_Activity_Query}.
 *
 * @since 2.2.0
 *
 * @param array $retval Empty array by default.
 * @param array $filter Current activity arguments.
 * @return array
 */
function bp_engagements_filter_activity_scope( $retval = array(), $filter = array() ) {

	// Determine the user_id.
	if ( ! empty( $filter['user_id'] ) ) {
		$user_id = $filter['user_id'];
	} else {
		$user_id = bp_displayed_user_id()
			? bp_displayed_user_id()
			: bp_loggedin_user_id();
	}

	// Determine engagements of user.
	$engagements = engagements_get_engagement_user_ids( $user_id );
	if ( empty( $engagements ) ) {
		$engagements = array( 0 );
	}

	$retval = array(
		'relation' => 'AND',
		array(
			'column'  => 'user_id',
			'compare' => 'IN',
			'value'   => (array) $engagements,
		),

		// We should only be able to view sitewide activity content for engagements.
		array(
			'column' => 'hide_sitewide',
			'value'  => 0,
		),

		// Overrides.
		'override' => array(
			'filter'      => array( 'user_id' => 0 ),
			'show_hidden' => true,
		),
	);

	return $retval;
}
add_filter( 'bp_activity_set_engagements_scope_args', 'bp_engagements_filter_activity_scope', 10, 2 );

/**
 * Set up activity arguments for use with the 'just-me' scope.
 *
 * For details on the syntax, see {@link BP_Activity_Query}.
 *
 * @since 2.2.0
 *
 * @param array $retval Empty array by default.
 * @param array $filter Current activity arguments.
 * @return array
 */
function bp_engagements_filter_activity_just_me_scope( $retval = array(), $filter = array() ) {

	// Determine the user_id.
	if ( ! empty( $filter['user_id'] ) ) {
		$user_id = $filter['user_id'];
	} else {
		$user_id = bp_displayed_user_id()
			? bp_displayed_user_id()
			: bp_loggedin_user_id();
	}

	// Get the requested action.
	$action = isset( $filter['filter']['action'] ) ? $filter['filter']['action'] : array();

	// Make sure actions are listed in an array.
	if ( ! is_array( $action ) ) {
		$action = explode( ',', $filter['filter']['action'] );
	}

	$action = array_flip( array_filter( $action ) );

	/**
	 * If filtering activities for something other than the engagementship_created
	 * action return without changing anything
	 */
	if ( ! empty( $action ) && ! isset( $action['engagementship_created'] ) ) {
		return $retval;
	}

	// Juggle existing override value.
	$override = array();
	if ( ! empty( $retval['override'] ) ) {
		$override = $retval['override'];
		unset( $retval['override'] );
	}

	/**
	 * Else make sure to get the engagementship_created action, the user is involved in
	 * - user initiated the engagementship
	 * - user has been requested a engagementship
	 */
	$retval = array(
		'relation' => 'OR',
		$retval,
		array(
			'relation' => 'AND',
			array(
				'column' => 'component',
				'value'  => 'engagements',
			),
			array(
				'column' => 'secondary_item_id',
				'value'  => $user_id,
			),
		),
	);

	// Juggle back override value.
	if ( ! empty( $override ) ) {
		$retval['override'] = $override;
	}

	return $retval;
}
add_filter( 'bp_activity_set_just-me_scope_args', 'bp_engagements_filter_activity_just_me_scope', 20, 2 );

/**
 * Add activity stream items when one members accepts another members request
 * for virtual engagementship.
 *
 * @since 1.9.0
 *
 * @param int $engagementship_id     ID of the engagementship.
 * @param int $initiator_user_id ID of engagementship initiator.
 * @param int $engagement_user_id    ID of user whose engagementship is requested.
 */
function bp_engagements_engagementship_accepted_activity( $engagementship_id, $initiator_user_id, $engagement_user_id ) {
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	// Record in activity streams for the initiator.
	engagements_record_activity( array(
		'user_id'           => $initiator_user_id,
		'type'              => 'engagementship_created',
		'item_id'           => $engagementship_id,
		'secondary_item_id' => $engagement_user_id,
	) );
}
add_action( 'engagements_engagementship_accepted', 'bp_engagements_engagementship_accepted_activity', 10, 3 );

/**
 * Deletes engagementship activity items when a user is deleted.
 *
 * @since 2.5.0
 *
 * @param int $user_id The ID of the user being deleted.
 */
function bp_engagements_delete_activity_on_user_delete( $user_id = 0 ) {
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	bp_activity_delete(
		array(
			'component'         => buddypress()->engagements->id,
			'type'              => 'engagementship_created',
			'secondary_item_id' => $user_id,
		)
	);
}
add_action( 'engagements_remove_data', 'bp_engagements_delete_activity_on_user_delete' );

/**
 * Remove engagementship activity item when a engagementship is deleted.
 *
 * @since 3.2.0
 *
 * @param int $engagementship_id ID of the engagementship.
 */
function bp_engagements_delete_activity_on_engagementship_delete( $engagementship_id ) {
	engagements_delete_activity(
		array(
			'item_id' => $engagementship_id,
			'type'    => 'engagementship_created',
			'user_id' => 0,
		)
	);
}
add_action( 'engagements_engagementship_deleted', 'bp_engagements_delete_activity_on_engagementship_delete' );
