<?php
/**
 * BP Resell Functions
 *
 * @package BP-Resell
 * @subpackage Functions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Builds a user's BP URL.
 *
 * @since 1.3.0
 *
 * @param int $user_id The user ID.
 * @param array $path_chunks A list of path chunks.
 * @return string The user's BP URL.
 */
function bp_resell_get_user_url( $user_id = 0, $path_chunks = array() ) {
	$user_url = '';

	if ( ! $user_id ) {
		return $user_url;
	}

	if ( function_exists( 'bp_core_get_query_parser' ) ) {
		$user_url = bp_members_get_user_url( $user_id, bp_members_get_path_chunks( $path_chunks ) );
	} else {
		$user_url = bp_core_get_user_domain( $user_id );

		if ( $path_chunks ) {
			$action_variables = end( $path_chunks );
			if ( is_array( $action_variables ) ) {
				array_pop( $path_chunks );
				$path_chunks = array_merge( $path_chunks, $action_variables );
			}

			$user_url = trailingslashit( $user_url ) . trailingslashit( implode( '/', $path_chunks ) );
		}
	}

	return $user_url;
}

/**
 * Start reselling an item.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int    $leader_id     The object ID we want to resell. Defaults to the displayed user ID.
 *     @type int    $reseller_id   The object ID creating the request. Defaults to the logged-in user ID.
 *     @type string $resell_type   The resell type. Leave blank to resell users. Default: ''
 *     @type string $date_recorded The date that this relationship is to be recorded.
 * }
 * @return bool
 */
function bp_resell_start_reselling( $args = '' ) {

	$r = wp_parse_args( $args, array(
		'leader_id'     => bp_displayed_user_id(),
		'reseller_id'   => bp_loggedin_user_id(),
		'resell_type'   => '',
		'date_recorded' => bp_core_current_time(),
	) );

	$resell = new BP_Resell( $r['leader_id'], $r['reseller_id'], $r['resell_type'] );

	// existing resell already exists.
	if ( ! empty( $resell->id ) ) {
		return false;
	}

	// add other properties before save.
	$resell->date_recorded = $r['date_recorded'];

	// save!
	if ( ! $resell->save() ) {
		return false;
	}

	// hooks!p_
	if ( empty( $r['resell_type'] ) ) {
		do_action_ref_array( 'bp_resell_start_reselling', array( &$resell ) );
	} else {
		do_action_ref_array( 'bp_resell_start_reselling_' . $r['resell_type'], array( &$resell ) );
	}

	return true;
}

/**
 * Stop reselling an item.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int    $leader_id     The object ID we want to stop reselling. Defaults to the displayed user ID.
 *     @type int    $reseller_id   The object ID stopping the request. Defaults to the logged-in user ID.
 *     @type string $resell_type   The resell type. Leave blank for users. Default: ''
 * }
 * @return bool
 */
function bp_resell_stop_reselling( $args = '' ) {

	$r = wp_parse_args( $args, array(
		'leader_id'   => bp_displayed_user_id(),
		'reseller_id' => bp_loggedin_user_id(),
		'resell_type' => '',
	) );

	$resell = new BP_Resell( $r['leader_id'], $r['reseller_id'], $r['resell_type'] );

	if ( empty( $resell->id ) || ! $resell->delete() ) {
		return false;
	}

	if ( empty( $r['resell_type'] ) ) {
		do_action_ref_array( 'bp_resell_stop_reselling', array( &$resell ) );
	} else {
		do_action_ref_array( 'bp_resell_stop_reselling_' . $r['resell_type'], array( &$resell ) );
	}

	return true;
}

/**
 * Check if an item is already reselling an item.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int    $leader_id   The object ID of the item we want to check. Defaults to the displayed user ID.
 *     @type int    $reseller_id The object ID creating the request. Defaults to the logged-in user ID.
 *     @type string $resell_type The resell type. Leave blank for users. Default: ''
 * }
 * @return bool
 */
function bp_resell_is_reselling( $args = '' ) {

	$r = wp_parse_args( $args, array(
		'leader_id'   => bp_displayed_user_id(),
		'reseller_id' => bp_loggedin_user_id(),
		'resell_type' => '',
	) );

	$resell = new BP_Resell( $r['leader_id'], $r['reseller_id'], $r['resell_type'] );

	if ( empty( $r['resell_type'] ) ) {
		$retval = apply_filters( 'bp_resell_is_reselling', (int) $resell->id, $resell );
	} else {
		$retval = apply_filters( 'bp_resell_is_reselling_' . $r['resell_type'], (int) $resell->id, $resell );
	}

	return $retval;
}

/**
 * Fetch the IDs for the resellers of a particular item.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int $user_id The user ID to get resellers for.
 *     @type string $resell_type The resell type
 *     @type array $query_args The query args.  See $query_args parameter in
 *           {@link BP_Resell::get_resellers()}.
 * }
 * @return array
 */
function bp_resell_get_resellers( $args = '' ) {

	$r = bp_resell_get_common_args( wp_parse_args( $args, array(
		'user_id' => bp_displayed_user_id(),
	) ) );

	$retval   = array();
	$do_query = true;

	// Set up filter name.
	if ( ! empty( $r['resell_type'] ) ) {
		$filter = 'bp_resell_get_resellers_' . $r['object'];
	} else {
		$filter = 'bp_resell_get_resellers';
	}

	// check for cache if 'query_args' is empty.
	if ( empty( $r['query_args'] ) ) {
		$retval = wp_cache_get( $r['object_id'], "bp_resell_{$r['object']}_resellers_query" );

		if ( false !== $retval ) {
			$do_query = false;
		}
	}

	// query if necessary.
	if ( true === $do_query ) {
		$retval = BP_Resell::get_resellers( $r['object_id'], $r['resell_type'], $r['query_args'] );

		// cache if no extra query args - we only cache default args for now.
		if ( empty( $r['query_args'] ) ) {
			wp_cache_set( $r['object_id'], $retval, "bp_resell_{$r['object']}_resellers_query" );

			// cache count while we're at it.
			wp_cache_set( $r['object_id'], $GLOBALS['wpdb']->num_rows, "bp_resell_{$r['object']}_resellers_count" );
		}
	}

	/**
	 * Dynamic filter for resellers query.
	 *
	 * By default, the filter name is 'bp_resell_get_resellers', which filters
	 * the displayed user's resellers.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Filter is now dynamic.
	 *
	 * @param array $retval Array of reseller IDs.
	 */
	return apply_filters( $filter, $retval );
}

/**
 * Fetch the IDs that a particular item is reselling.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int $user_id The user ID to fetch reselling user IDs for.
 *     @type string $resell_type The resell type
 *     @type array $query_args The query args.  See $query_args parameter in
 *           {@link BP_Resell::get_reselling()}.
 * }
 * @return array
 */
function bp_resell_get_reselling( $args = '' ) {

	$r = bp_resell_get_common_args( wp_parse_args( $args, array(
		'user_id' => bp_displayed_user_id(),
	) ) );

	$retval   = array();
	$do_query = true;

	// setup some variables based on the resell type.
	if ( ! empty( $r['resell_type'] ) ) {
		$filter = 'bp_resell_get_reselling_' . $r['object'];
	} else {
		$filter = 'bp_resell_get_reselling';
	}

	// check for cache if 'query_args' is empty.
	if ( empty( $r['query_args'] ) ) {
		$retval = wp_cache_get( $r['object_id'], "bp_resell_{$r['object']}_reselling_query" );

		if ( false !== $retval ) {
			$do_query = false;
		}
	}

	// query if necessary.
	if ( true === $do_query ) {
		$retval = BP_Resell::get_reselling( $r['object_id'], $r['resell_type'], $r['query_args'] );

		// cache if no extra query args - we only cache default args for now.
		if ( empty( $r['query_args'] ) ) {
			wp_cache_set( $r['object_id'], $retval, "bp_resell_{$r['object']}_reselling_query" );

			// cache count while we're at it.
			wp_cache_set( $r['object_id'], $GLOBALS['wpdb']->num_rows, "bp_resell_{$r['object']}_reselling_count" );
		}
	}

	/**
	 * Dynamic filter for reselling query.
	 *
	 * By default, the filter name is 'bp_resell_get_reselling', which filters
	 * the displayed user's reselling.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Filter is now dynamic.
	 *
	 * @param array $retval Array of reseller IDs.
	 */
	return apply_filters( $filter, $retval );
}

/**
 * Output a comma-separated list of user_ids for a given user's resellers.
 *
 * @param array $args See bp_get_reseller_ids().
 */
function bp_reseller_ids( $args = '' ) {
	echo bp_get_reseller_ids( $args );
}
	/**
	 * Returns a comma separated list of user_ids for a given user's resellers.
	 *
	 * On failure, returns an integer of zero. Needed when used in a members loop to prevent SQL errors.
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *     @type int $user_id The user ID you want to check for resellers.
	 *     @type string $resell_type The resell type
	 * }
	 * @return string|int Comma-seperated string of user IDs on success. Integer zero on failure.
	 */
	function bp_get_reseller_ids( $args = '' ) {

		$r = wp_parse_args( $args, array(
			'user_id' => bp_displayed_user_id(),
		) );

		$ids = implode( ',', (array) bp_resell_get_resellers( array(
			'user_id' => $r['user_id'],
		) ) );

		$ids = empty( $ids ) ? 0 : $ids;

		return apply_filters( 'bp_get_reseller_ids', $ids, $r['user_id'] );
	}

/**
 * Output a comma-separated list of user_ids for a given user's reselling.
 *
 * @param array $args See bp_get_reselling_ids().
 */
function bp_reselling_ids( $args = '' ) {
	echo bp_get_reselling_ids( $args );
}
	/**
	 * Returns a comma separated list of IDs for a given user's reselling.
	 *
	 * On failure, returns integer zero. Needed when used in a members loop to prevent SQL errors.
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *     @type int $user_id The user ID to fetch reselling user IDs for.
	 *     @type string $resell_type The resell type
	 * }
	 * @return string|int Comma-seperated string of user IDs on success. Integer zero on failure.
	 */
	function bp_get_reselling_ids( $args = '' ) {

		$r = wp_parse_args( $args, array(
			'user_id'     => bp_displayed_user_id(),
			'resell_type' => '',
		) );

		$ids = implode( ',', (array) bp_resell_get_reselling( array(
			'user_id'     => $r['user_id'],
			'resell_type' => $r['resell_type'],
		) ) );

		$ids = empty( $ids ) ? 0 : $ids;

		return apply_filters( 'bp_get_reselling_ids', $ids, $r['user_id'], $r );
	}

/**
 * Get the total resellers and total reselling counts for a user.
 *
 * You shouldn't really use this function any more.
 *
 * @see bp_resell_get_the_reselling_count() To grab the reselling count.
 * @see bp_resell_get_the_resellers_count() To grab the resellers count.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int    $user_id     The user ID to grab resell counts for.
 *     @type string $resell_type The resell type. Default to '', which will query resell counts for users.
 *                               Passing a resell type such as 'blogs' will only return a 'reselling'
 *                               key and integer zero for the 'resellers' key since a user can only resell
 *                               blogs.
 * }
 * @return array [ resellers => int, reselling => int ]
 */
function bp_resell_total_resell_counts( $args = '' ) {
	$r = wp_parse_args( $args, array(
		'user_id'     => bp_loggedin_user_id(),
		'resell_type' => '',
	) );

	$retval = array();

	$retval['reselling'] = bp_resell_get_the_reselling_count( array(
		'user_id'     => $r['user_id'],
		'resell_type' => $r['resell_type'],
	) );

	/**
	 * Passing a resell type such as 'blogs' will only return a 'reselling'
	 * key and integer zero for the 'resellers' key since a user can only resell
	 * blogs.
	 */
	if ( ! empty( $r['resell_type'] ) ) {
		$retval['resellers'] = 0;
	} else {
		$retval['resellers'] = bp_resell_get_the_resellers_count( array(
			'user_id'     => $r['user_id'],
			'resell_type' => $r['resell_type'],
		) );
	}

	if ( empty( $r['resell_type'] ) ) {
		/**
		 * Filter the total resell counts for a user.
		 *
		 * @since 1.0.0
		 *
		 * @param array $retval  Array consisting of 'reselling' and 'resellers' counts.
		 * @param int   $user_id The user ID. Defaults to logged-in user ID.
		 */
		$retval = apply_filters( 'bp_resell_total_resell_counts', $retval, $r['user_id'] );
	} else {
		/**
		 * Filter the total resell counts for a user given a specific resell type.
		 *
		 * @since 1.3.0
		 *
		 * @param array $retval  Array consisting of 'reselling' and 'resellers' counts. Note: 'resellers'
		 *                       is always going to be 0, since a user can only resell a given resell type.
		 * @param int   $user_id The user ID. Defaults to logged-in user ID.
		 */
		$retval = apply_filters( 'bp_resell_total_resell_' . $r['resell_type'] . '_counts', $retval, $r['user_id'] );
	}

	return $retval;
}

/**
 * Get the reselling count for a particular item.
 *
 * Defaults to the number of users the logged-in user is reselling.
 *
 * @since 1.3.0
 *
 * @param  array $args See bp_resell_get_common_args().
 * @return int
 */
function bp_resell_get_the_reselling_count( $args = array() ) {
	$r = bp_resell_get_common_args( $args );

	// fetch cache.
	$retval = wp_cache_get( $r['object_id'], "bp_resell_{$r['object']}_reselling_count" );

	// query if necessary.
	if ( false === $retval ) {
		$retval = BP_Resell::get_reselling_count( $r['object_id'], $r['resell_type'] );
		wp_cache_set( $r['object_id'], $retval, "bp_resell_{$r['object']}_reselling_count" );
	}

	/**
	 * Dynamic filter for the reselling count.
	 *
	 * Defaults to 'bp_resell_get_user_reselling_count'.
	 *
	 * @since 1.3.0
	 *
	 * @param int $retval    The reselling count.
	 * @param int $object_id The object ID.  Defaults to logged-in user ID.
	 */
	return apply_filters( "bp_resell_get_{$r['object']}_reselling_count", $retval, $r['object_id'] );
}

/**
 * Get the resellers count for a particular item.
 *
 * Defaults to the number of users reselling the logged-in user.
 *
 * @since 1.3.0
 *
 * @param  array $args See bp_resell_get_common_args().
 * @return int
 */
function bp_resell_get_the_resellers_count( $args = array() ) {
	$r = bp_resell_get_common_args( $args );

	// fetch cache.
	$retval = wp_cache_get( $r['object_id'], "bp_resell_{$r['object']}_resellers_count" );

	// query if necessary.
	if ( false === $retval ) {
		$retval = BP_Resell::get_resellers_count( $r['object_id'], $r['resell_type'] );
		wp_cache_set( $r['object_id'], $retval, "bp_resell_{$r['object']}_resellers_count" );
	}

	/**
	 * Dynamic filter for the resellers count.
	 *
	 * Defaults to 'bp_resell_get_user_resellers_count'.
	 *
	 * @since 1.3.0
	 *
	 * @param int $retval    The resellers count.
	 * @param int $object_id The object ID.  Defaults to logged-in user ID.
	 */
	return apply_filters( "bp_resell_get_{$r['object']}_resellers_count", $retval, $r['object_id'] );
}

/**
 * Utility function to parse common arguments.
 *
 * Used quite a bit internally.
 *
 * @since 1.3.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int    $user_id     The user ID. Defaults to logged-in user ID.
 *     @type int    $object_id   The object ID. If filled in, this takes precedence over the $user_id
 *                               parameter. Handy when using a different $resell_type. Default: ''.
 *     @type string $resell_type The resell type. Leave blank to query for users. Default: ''.
 *     @type array  $query_args  Query arguments. Only used when querying.
 * }
 * @return array
 */
function bp_resell_get_common_args( $args = array() ) {
	$r = wp_parse_args( $args, array(
		'user_id'     => bp_loggedin_user_id(),
		'resell_type' => '',
		'object_id'   => '',
		'query_args'  => array(),
	) );

	// Set up our object. $object is used for cache keys and filter names.
	if ( ! empty( $r['resell_type'] ) ) {
		// Append 'user' to the $object if a user ID is passed.
		if ( ! empty( $r['user_id'] ) && empty( $r['object_id'] ) ) {
			$object = "user_{$r['resell_type']}";
		} else {
			$object = $r['resell_type'];
		}

	// Defaults to 'user'.
	} else {
		$object = 'user';
	}

	if ( ! empty( $r['object_id'] ) ) {
		$object_id = (int) $r['object_id'];
	} else {
		$object_id = (int) $r['user_id'];
	}

	return array(
		'object'      => $object,
		'object_id'   => $object_id,
		'resell_type' => $r['resell_type'],
		'query_args'  => $r['query_args'],
	);
}

/**
 * Is an AJAX request currently taking place?
 *
 * Since BP Resell still supports BP 1.5, we can't simply use the DOING_AJAX
 * constant because BP 1.5 doesn't use admin-ajax.php for AJAX requests.  A
 * workaround is checking the "HTTP_X_REQUESTED_WITH" server variable.
 *
 * Once BP Resell drops support for BP 1.5, we can use the DOING_AJAX constant
 * as intended.
 *
 * @since 1.3.0
 *
 * @return bool
 */
function bp_resell_is_doing_ajax() {
	return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' );
}

/** NOTIFICATIONS *******************************************************/

/**
 * Show a 'Resell' block on a user's "Settings > Email" page.
 *
 * Used internally only.
 *
 * @since 1.3.0
 */
function bp_resell_notification_settings_content() {
?>
	<table class="notification-settings" id="resell-notification-settings">
		<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php esc_html_e( 'Resell', 'buddypress-resellers' ); ?></th>
				<th class="yes"><?php esc_html_e( 'Yes', 'buddypress-resellers' ); ?></th>
				<th class="no"><?php esc_html_e( 'No', 'buddypress-resellers' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php do_action( 'bp_resell_screen_notification_settings' ); ?>
		</tbody>
	</table>
<?php
}

/**
 * Format on screen notifications into something readable by users.
 */
function bp_resell_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	$bp = $GLOBALS['bp'];

	do_action( 'bp_resell_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $format );

	switch ( $action ) {
		case 'new_resell':
			$text = false;
			$link = $text;

			if ( 1 === $total_items ) {
				$text = sprintf( __( '%s is now reselling you', 'buddypress-resellers' ), bp_core_get_user_displayname( $item_id ) );
				$link = add_query_arg( 'bpf_read', 1, bp_resell_get_user_url( $item_id ) );

			} else {
				$text = sprintf( __( '%d more users are now reselling you', 'buddypress-resellers' ), $total_items );

				if ( bp_is_active( 'notifications' ) ) {
					$link = bp_get_notifications_permalink();

					// filter notifications by 'new_resell' action.
					if ( version_compare( BP_VERSION, '2.0.9' ) >= 0 ) {
						$link = add_query_arg( 'action', $action, $link );
					}
				} else {
					$link = add_query_arg( 'new', 1, bp_resell_get_user_url( bp_loggedin_user_id(), array( $bp->resell->resellers->slug ) ) );
				}
			}

			break;

		default:
			$link = apply_filters( 'bp_resell_extend_notification_link', false, $action, $item_id, $secondary_item_id, $total_items );
			$text = apply_filters( 'bp_resell_extend_notification_text', false, $action, $item_id, $secondary_item_id, $total_items );
			break;
	}

	if ( ! $link || ! $text ) {
		return false;
	}

	if ( 'string' === $format ) {
		return apply_filters( 'bp_resell_new_resellers_notification', '<a href="' . $link . '">' . $text . '</a>', $total_items, $link, $text, $item_id, $secondary_item_id );

	} else {
		$array = array(
			'text' => $text,
			'link' => $link,
		);

		return apply_filters( 'bp_resell_new_resellers_return_notification', $array, $item_id, $secondary_item_id, $total_items );
	}
}
