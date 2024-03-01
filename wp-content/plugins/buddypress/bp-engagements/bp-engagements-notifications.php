<?php
/**
 * BuddyPress engagements Activity Functions.
 *
 * These functions handle the recording, deleting and formatting of activity
 * for the user and for this specific component.
 *
 * @package BuddyPress
 * @subpackage engagementsNotifications
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Notification formatting callback for bp-engagements notifications.
 *
 * @since 1.0.0
 *
 * @param string $action            The kind of notification being rendered.
 * @param int    $item_id           The primary item ID.
 * @param int    $secondary_item_id The secondary item ID.
 * @param int    $total_items       The total number of messaging-related notifications
 *                                  waiting for the user.
 * @param string $format            'string' for BuddyBar-compatible notifications;
 *                                  'array' for WP Toolbar. Default: 'string'.
 * @return array|string
 */
function engagements_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
	$engagements_slug = bp_get_engagements_slug();

	switch ( $action ) {
		case 'engagementship_accepted':
			$link = bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'my-engagements' ) ) );

			// $action and $amount are used to generate dynamic filter names.
			$action = 'accepted';

			// Set up the string and the filter.
			if ( (int) $total_items > 1 ) {
				/* translators: %d: the number of engagements */
				$text = sprintf( __( '%d engagements accepted your engagementship requests', 'buddypress' ), (int) $total_items );
				$amount = 'multiple';
			} else {
				/* translators: %s: engagement name */
				$text = sprintf( __( '%s accepted your engagementship request', 'buddypress' ), bp_core_get_user_displayname( $item_id ) );
				$amount = 'single';
			}

			break;

		case 'engagementship_request':
			$link = add_query_arg(
				'new',
				1,
				bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'requests' ) ) )
			);

			$action = 'request';

			// Set up the string and the filter.
			if ( (int) $total_items > 1 ) {
				/* translators: %d: the number of pending requests */
				$text = sprintf( __( 'You have %d pending engagementship requests', 'buddypress' ), (int) $total_items );
				$amount = 'multiple';
			} else {
				/* translators: %s: engagement name */
				$text = sprintf( __( 'You have a engagementship request from %s', 'buddypress' ), bp_core_get_user_displayname( $item_id ) );
				$amount = 'single';
			}

			break;
	}

	// Return either an HTML link or an array, depending on the requested format.
	if ( 'string' === $format ) {

		/**
		 * Filters the format of engagementship notifications based on type and amount * of notifications pending.
		 *
		 * This is a variable filter that has four possible versions.
		 * The four possible versions are:
		 *   - bp_engagements_single_engagementship_accepted_notification
		 *   - bp_engagements_multiple_engagementship_accepted_notification
		 *   - bp_engagements_single_engagementship_request_notification
		 *   - bp_engagements_multiple_engagementship_request_notification
		 *
		 * @since 1.0.0
		 * @since 6.0.0 Adds the $secondary_item_id parameter.
		 *
		 * @param string|array $value             Depending on format, an HTML link to new requests profile tab or array with link and text.
		 * @param int          $total_items       The total number of messaging-related notifications waiting for the user.
		 * @param int          $item_id           The primary item ID.
		 * @param int          $secondary_item_id The secondary item ID.
		 */
		$return = apply_filters( 'bp_engagements_' . $amount . '_engagementship_' . $action . '_notification', '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>', (int) $total_items, $item_id, $secondary_item_id );
	} else {
		/** This filter is documented in bp-engagements/bp-engagements-notifications.php */
		$return = apply_filters( 'bp_engagements_' . $amount . '_engagementship_' . $action . '_notification', array(
			'link' => $link,
			'text' => $text
		), (int) $total_items, $item_id, $secondary_item_id );
	}

	/**
	 * Fires at the end of the bp-engagements notification format callback.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $action            The kind of notification being rendered.
	 * @param int          $item_id           The primary item ID.
	 * @param int          $secondary_item_id The secondary item ID.
	 * @param int          $total_items       The total number of messaging-related notifications
	 *                                        waiting for the user.
	 * @param array|string $return            Notification text string or array of link and text.
	 */
	do_action( 'engagements_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $return );

	return $return;
}

/**
 * Clear engagement-related notifications when ?new=1
 *
 * @since 1.2.0
 */
function engagements_clear_engagement_notifications() {
	if ( isset( $_GET['new'] ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->engagements->id, 'engagementship_accepted' );
	}
}
add_action( 'bp_activity_screen_my_activity', 'engagements_clear_engagement_notifications' );

/**
 * Delete any engagementship request notifications for the logged in user.
 *
 * @since 1.9.0
 */
function bp_engagements_mark_engagementship_request_notifications_by_type() {
	if ( isset( $_GET['new'] ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->engagements->id, 'engagementship_request' );
	}
}
add_action( 'engagements_screen_requests', 'bp_engagements_mark_engagementship_request_notifications_by_type' );

/**
 * Delete any engagementship acceptance notifications for the logged in user.
 *
 * @since 1.9.0
 */
function bp_engagements_mark_engagementship_accepted_notifications_by_type() {
	bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->engagements->id, 'engagementship_accepted' );
}
add_action( 'engagements_screen_my_engagements', 'bp_engagements_mark_engagementship_accepted_notifications_by_type' );

/**
 * Notify one use that another user has requested their virtual engagementship.
 *
 * @since 1.9.0
 *
 * @param int $engagementship_id     The unique ID of the engagementship.
 * @param int $initiator_user_id The engagementship initiator user ID.
 * @param int $engagement_user_id    The engagementship request receiver user ID.
 */
function bp_engagements_engagementship_requested_notification( $engagementship_id, $initiator_user_id, $engagement_user_id ) {
	bp_notifications_add_notification( array(
		'user_id'           => $engagement_user_id,
		'item_id'           => $initiator_user_id,
		'secondary_item_id' => $engagementship_id,
		'component_name'    => buddypress()->engagements->id,
		'component_action'  => 'engagementship_request',
		'date_notified'     => bp_core_current_time(),
		'is_new'            => 1,
	) );
}
add_action( 'engagements_engagementship_requested', 'bp_engagements_engagementship_requested_notification', 10, 3 );

/**
 * Remove engagement request notice when a member rejects another members
 *
 * @since 1.9.0
 *
 * @param int                   $engagementship_id engagementship ID (not used).
 * @param BP_Engagements_Engagementship $engagementship    The engagementship object.
 */
function bp_engagements_mark_engagementship_rejected_notifications_by_item_id( $engagementship_id, $engagementship ) {
	bp_notifications_mark_notifications_by_item_id( $engagementship->engagement_user_id, $engagementship->initiator_user_id, buddypress()->engagements->id, 'engagementship_request' );
}
add_action( 'engagements_engagementship_rejected', 'bp_engagements_mark_engagementship_rejected_notifications_by_item_id', 10, 2 );

/**
 * Notify a member when another member accepts their virtual engagementship request.
 *
 * @since 1.9.0
 *
 * @param int $engagementship_id     The unique ID of the engagementship.
 * @param int $initiator_user_id The engagementship initiator user ID.
 * @param int $engagement_user_id    The engagementship request receiver user ID.
 */
function bp_engagements_add_engagementship_accepted_notification( $engagementship_id, $initiator_user_id, $engagement_user_id ) {
	// Remove the engagement request notice.
	bp_notifications_mark_notifications_by_item_id( $engagement_user_id, $initiator_user_id, buddypress()->engagements->id, 'engagementship_request' );

	// Add a engagement accepted notice for the initiating user.
	bp_notifications_add_notification(  array(
		'user_id'           => $initiator_user_id,
		'item_id'           => $engagement_user_id,
		'secondary_item_id' => $engagementship_id,
		'component_name'    => buddypress()->engagements->id,
		'component_action'  => 'engagementship_accepted',
		'date_notified'     => bp_core_current_time(),
		'is_new'            => 1,
	) );
}
add_action( 'engagements_engagementship_accepted', 'bp_engagements_add_engagementship_accepted_notification', 10, 3 );

/**
 * Remove engagement request notice when a member withdraws their engagement request.
 *
 * @since 1.9.0
 *
 * @param int                   $engagementship_id engagementship ID (not used).
 * @param BP_Engagements_Engagementship $engagementship    The engagementship object.
 */
function bp_engagements_mark_engagementship_withdrawn_notifications_by_item_id( $engagementship_id, $engagementship ) {
	bp_notifications_delete_notifications_by_item_id( $engagementship->engagement_user_id, $engagementship->initiator_user_id, buddypress()->engagements->id, 'engagementship_request' );
}
add_action( 'engagements_engagementship_withdrawn', 'bp_engagements_mark_engagementship_withdrawn_notifications_by_item_id', 10, 2 );

/**
 * Remove engagementship requests FROM user, used primarily when a user is deleted.
 *
 * @since 1.9.0
 *
 * @param int $user_id ID of the user whose notifications are removed.
 */
function bp_engagements_remove_notifications_data( $user_id = 0 ) {
	bp_notifications_delete_notifications_from_user( $user_id, buddypress()->engagements->id, 'engagementship_request' );
}
add_action( 'engagements_remove_data', 'bp_engagements_remove_notifications_data', 10, 1 );

/**
 * Add engagements-related settings to the Settings > Notifications page.
 *
 * @since 1.0.0
 */
function engagements_screen_notification_settings() {

	if ( ! $send_requests = bp_get_user_meta( bp_displayed_user_id(), 'notification_engagements_engagementship_request', true ) ) {
		$send_requests = 'yes';
	}

	if ( ! $accept_requests = bp_get_user_meta( bp_displayed_user_id(), 'notification_engagements_engagementship_accepted', true ) )
		$accept_requests = 'yes'; ?>

	<table class="notification-settings" id="engagements-notification-settings">
		<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _ex( 'engagements', 'engagement settings on notification settings page', 'buddypress' ); ?></th>
				<th class="yes"><?php esc_html_e( 'Yes', 'buddypress' ); ?></th>
				<th class="no"><?php esc_html_e( 'No', 'buddypress' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr id="engagements-notification-settings-request">
				<td></td>
				<td><?php _ex( 'A member sends you a engagementship request', 'engagement settings on notification settings page', 'buddypress' ); ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_engagements_engagementship_request]" id="notification-engagements-engagementship-request-yes" value="yes" <?php checked( $send_requests, 'yes', true ) ?>/><label for="notification-engagements-engagementship-request-yes" class="bp-screen-reader-text"><?php
					/* translators: accessibility text */
					esc_html_e( 'Yes, send email', 'buddypress' );
				?></label></td>
				<td class="no"><input type="radio" name="notifications[notification_engagements_engagementship_request]" id="notification-engagements-engagementship-request-no" value="no" <?php checked( $send_requests, 'no', true ) ?>/><label for="notification-engagements-engagementship-request-no" class="bp-screen-reader-text"><?php
					/* translators: accessibility text */
					esc_html_e( 'No, do not send email', 'buddypress' );
				?></label></td>
			</tr>
			<tr id="engagements-notification-settings-accepted">
				<td></td>
				<td><?php _ex( 'A member accepts your engagementship request', 'engagement settings on notification settings page', 'buddypress' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_engagements_engagementship_accepted]" id="notification-engagements-engagementship-accepted-yes" value="yes" <?php checked( $accept_requests, 'yes', true ) ?>/><label for="notification-engagements-engagementship-accepted-yes" class="bp-screen-reader-text"><?php
					/* translators: accessibility text */
					esc_html_e( 'Yes, send email', 'buddypress' );
				?></label></td>
				<td class="no"><input type="radio" name="notifications[notification_engagements_engagementship_accepted]" id="notification-engagements-engagementship-accepted-no" value="no" <?php checked( $accept_requests, 'no', true ) ?>/><label for="notification-engagements-engagementship-accepted-no" class="bp-screen-reader-text"><?php
					/* translators: accessibility text */
					esc_html_e( 'No, do not send email', 'buddypress' );
				?></label></td>
			</tr>

			<?php

			/**
			 * Fires after the last table row on the engagements notification screen.
			 *
			 * @since 1.0.0
			 */
			do_action( 'engagements_screen_notification_settings' ); ?>

		</tbody>
	</table>

<?php
}
add_action( 'bp_notification_settings', 'engagements_screen_notification_settings' );
