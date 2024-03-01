<?php
/**
 * BuddyPress engagements Caching.
 *
 * Caching functions handle the clearing of cached objects and pages on specific
 * actions throughout BuddyPress.
 *
 * @package BuddyPress
 * @subpackage engagementsCaching
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Clear engagements-related cache for members of a specific engagementship.
 *
 * @since 1.0.0
 *
 * @param int $engagementship_id ID of the engagementship whose two members should
 *                           have their engagements cache busted.
 * @return bool
 */
function engagements_clear_engagement_object_cache( $engagementship_id ) {
	$engagementship = new BP_Engagements_Engagementship( $engagementship_id );
	if ( ! $engagementship ) {
		return false;
	}

	wp_cache_delete( 'engagements_engagement_ids_' . $engagementship->initiator_user_id, 'bp' );
	wp_cache_delete( 'engagements_engagement_ids_' . $engagementship->engagement_user_id, 'bp' );
}

// List actions to clear object caches on.
add_action( 'engagements_engagementship_accepted', 'engagements_clear_engagement_object_cache' );
add_action( 'engagements_engagementship_deleted', 'engagements_clear_engagement_object_cache' );

/**
 * Clear engagementship caches on engagementship changes.
 *
 * @since 2.7.0
 *
 * @param int $engagementship_id     ID of the engagementship that has changed.
 * @param int $initiator_user_id ID of the first user.
 * @param int $engagement_user_id    ID of the second user.
 */
function bp_engagements_clear_bp_engagements_engagementships_cache( $engagementship_id, $initiator_user_id, $engagement_user_id ) {
	// Clear engagementship ID cache for each user.
	wp_cache_delete( $initiator_user_id, 'bp_engagements_engagementships_for_user' );
	wp_cache_delete( $engagement_user_id, 'bp_engagements_engagementships_for_user' );

	// Clear the engagementship object cache.
	wp_cache_delete( $engagementship_id, 'bp_engagements_engagementships' );

	// Clear incremented cache.
	$engagementship = new stdClass;
	$engagementship->initiator_user_id = $initiator_user_id;
	$engagementship->engagement_user_id    = $engagement_user_id;
	bp_engagements_delete_cached_engagementships_on_engagementship_save( $engagementship );
}
add_action( 'engagements_engagementship_requested', 'bp_engagements_clear_bp_engagements_engagementships_cache', 10, 3 );
add_action( 'engagements_engagementship_accepted', 'bp_engagements_clear_bp_engagements_engagementships_cache', 10, 3 );
add_action( 'engagements_engagementship_deleted', 'bp_engagements_clear_bp_engagements_engagementships_cache', 10, 3 );

/**
 * Clear engagementship caches on engagementship changes.
 *
 * @since 2.7.0
 *
 * @param int                   $engagementship_id The engagementship ID.
 * @param BP_Engagements_Engagementship $engagementship    The engagementship object.
 */
function bp_engagements_clear_bp_engagements_engagementships_cache_remove( $engagementship_id, $engagementship ) {
	// Clear engagementship ID cache for each user.
	wp_cache_delete( $engagementship->initiator_user_id, 'bp_engagements_engagementships_for_user' );
	wp_cache_delete( $engagementship->engagement_user_id, 'bp_engagements_engagementships_for_user' );

	// Clear the engagementship object cache.
	wp_cache_delete( $engagementship_id, 'bp_engagements_engagementships' );

	// Clear incremented cache.
	bp_engagements_delete_cached_engagementships_on_engagementship_save( $engagementship );
}
add_action( 'engagements_engagementship_withdrawn', 'bp_engagements_clear_bp_engagements_engagementships_cache_remove', 10, 2 );
add_action( 'engagements_engagementship_rejected', 'bp_engagements_clear_bp_engagements_engagementships_cache_remove', 10, 2 );

/**
 * Clear the engagement request cache for the user not initiating the engagementship.
 *
 * @since 2.0.0
 *
 * @param int $engagement_user_id The user ID not initiating the engagementship.
 */
function bp_engagements_clear_request_cache( $engagement_user_id ) {
	wp_cache_delete( $engagement_user_id, 'bp_engagements_requests' );
}

/**
 * Clear the engagement request cache when a engagementship is saved.
 *
 * A engagementship is deemed saved when a engagementship is requested or accepted.
 *
 * @since 2.0.0
 *
 * @param int $engagementship_id     The engagementship ID.
 * @param int $initiator_user_id The user ID initiating the engagementship.
 * @param int $engagement_user_id    The user ID not initiating the engagementship.
 */
function bp_engagements_clear_request_cache_on_save( $engagementship_id, $initiator_user_id, $engagement_user_id ) {
	bp_engagements_clear_request_cache( $engagement_user_id );
}
add_action( 'engagements_engagementship_requested', 'bp_engagements_clear_request_cache_on_save', 10, 3 );
add_action( 'engagements_engagementship_accepted', 'bp_engagements_clear_request_cache_on_save', 10, 3 );

/**
 * Clear the engagement request cache when a engagementship is removed.
 *
 * A engagementship is deemed removed when a engagementship is withdrawn or rejected.
 *
 * @since 2.0.0
 *
 * @param int                   $engagementship_id The engagementship ID.
 * @param BP_Engagements_Engagementship $engagementship    The engagementship object.
 */
function bp_engagements_clear_request_cache_on_remove( $engagementship_id, $engagementship ) {
	bp_engagements_clear_request_cache( $engagementship->engagement_user_id );
}
add_action( 'engagements_engagementship_withdrawn', 'bp_engagements_clear_request_cache_on_remove', 10, 2 );
add_action( 'engagements_engagementship_rejected', 'bp_engagements_clear_request_cache_on_remove', 10, 2 );

/**
 * Delete individual engagementships from the cache when they are changed.
 *
 * @since 3.0.0
 *
 * @param BP_Engagements_Engagementship $engagementship The engagementship object.
 */
function bp_engagements_delete_cached_engagementships_on_engagementship_save( $engagementship ) {
	bp_core_delete_incremented_cache( $engagementship->engagement_user_id . ':' . $engagementship->initiator_user_id, 'bp_engagements' );
	bp_core_delete_incremented_cache( $engagementship->initiator_user_id . ':' . $engagementship->engagement_user_id, 'bp_engagements' );
}
add_action( 'engagements_engagementship_after_save', 'bp_engagements_delete_cached_engagementships_on_engagementship_save' );

// List actions to clear super cached pages on, if super cache is installed.
add_action( 'engagements_engagementship_rejected',  'bp_core_clear_cache' );
add_action( 'engagements_engagementship_accepted',  'bp_core_clear_cache' );
add_action( 'engagements_engagementship_deleted',   'bp_core_clear_cache' );
add_action( 'engagements_engagementship_requested', 'bp_core_clear_cache' );
