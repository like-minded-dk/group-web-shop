<?php
/**
 * BuddyPress engagement Filters.
 *
 * @package BuddyPress
 * @subpackage engagementsFilters
 * @since 1.7.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Format numerical output.
add_filter( 'engagements_get_total_engagement_count', 'bp_core_number_format' );
add_filter( 'bp_get_total_engagement_count', 'bp_core_number_format' );

/**
 * Filter BP_User_Query::populate_extras to add confirmed engagementship status.
 *
 * Each member in the user query is checked for confirmed engagementship status
 * against the logged-in user.
 *
 * @since 1.7.0
 *
 * @param BP_User_Query $user_query   The BP_User_Query object.
 * @param string        $user_ids_sql Comma-separated list of user IDs to fetch extra
 *                                    data for, as determined by BP_User_Query.
 */
function bp_engagements_filter_user_query_populate_extras( $user_query, $user_ids_sql ) {

	// Stop if user isn't logged in.
	$user_id = bp_loggedin_user_id();
	if ( ! $user_id ) {
		return;
	}

	$maybe_engagement_ids = wp_parse_id_list( $user_ids_sql );

	// Bulk prepare the engagementship cache.
	BP_Engagements_Engagementship::update_bp_relations_cache( $user_id, $maybe_engagement_ids );

	foreach ( $maybe_engagement_ids as $engagement_id ) {
		$status = BP_Engagements_Engagementship::check_is_relation( $user_id, $engagement_id );
		$user_query->results[ $engagement_id ]->engagementship_status = $status;
		if ( 'is_engagement' === $status ) {
			$user_query->results[ $engagement_id ]->is_engagement = 1;
		}
	}
}
add_filter( 'bp_user_query_populate_extras', 'bp_engagements_filter_user_query_populate_extras', 4, 2 );

/**
 * Registers engagements personal data exporter.
 *
 * @since 4.0.0
 * @since 5.0.0 adds an `exporter_bp_engagemently_name` param to exporters.
 *
 * @param array $exporters  An array of personal data exporters.
 * @return array An array of personal data exporters.
 */
function bp_engagements_register_personal_data_exporters( $exporters ) {
	$exporters['buddypress-engagements'] = array(
		'exporter_engagemently_name'    => __( 'BuddyPress engagements', 'buddypress' ),
		'callback'                  => 'bp_engagements_personal_data_exporter',
		'exporter_bp_engagemently_name' => _x( 'engagements', 'BuddyPress engagements data exporter engagemently name', 'buddypress' ),
	);

	$exporters['buddypress-engagements-pending-sent-requests'] = array(
		'exporter_engagemently_name'    => __( 'BuddyPress engagement Requests (Sent)', 'buddypress' ),
		'callback'                  => 'bp_engagements_pending_sent_requests_personal_data_exporter',
		'exporter_bp_engagemently_name' => _x( 'engagement Requests (Sent)', 'BuddyPress engagement Requests data exporter engagemently name', 'buddypress' ),
	);

	$exporters['buddypress-engagements-pending-received-requests'] = array(
		'exporter_engagemently_name'    => __( 'BuddyPress engagement Requests (Received)', 'buddypress' ),
		'callback'                  => 'bp_engagements_pending_received_requests_personal_data_exporter',
		'exporter_bp_engagemently_name' => _x( 'engagement Requests (Received)', 'BuddyPress engagement Requests data exporter engagemently name', 'buddypress' ),
	);

	return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'bp_engagements_register_personal_data_exporters' );
