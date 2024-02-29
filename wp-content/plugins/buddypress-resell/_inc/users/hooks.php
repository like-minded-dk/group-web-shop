<?php
/**
 * BP Resell Hooks
 *
 * Functions in this file allow this component to hook into BuddyPress so it
 * interacts seamlessly with the interface and existing core components.
 *
 * @package BP-Resell
 * @subpackage Hooks
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** USER NAV *************************************************************/

/**
 * Setup profile / BuddyBar navigation.
 *
 * This function was moved from {@link BP_Resell_Component} in v1.3.0 due
 * to the users module being toggleable.
 *
 * @since 1.3.0
 */
function bp_resell_user_setup_nav( $main_nav = array(), $sub_nav = array() ) {
	$bp = $GLOBALS['bp'];

	// If we're in the admin area and we're using the WP toolbar, we don't need
	// to run the rest of this method.
	if ( defined( 'WP_NETWORK_ADMIN' ) && bp_use_wp_admin_bar() ) {
		return;
	}

	// Need to change the user ID, so if we're not on a member page, $counts variable is still calculated.
	$user_id = bp_is_user() ? bp_displayed_user_id() : bp_loggedin_user_id();

	/** RESELLING NAV ************************************************/

	bp_core_new_nav_item(
		array(
			'name'                => sprintf(
				__( 'Reselling %s', 'buddypress-resellers' ),
				sprintf(
					'<span class="count">%s</span>',
					esc_html( bp_core_number_format( bp_resell_get_the_reselling_count( array( 'user_id' => $user_id ) ) ) )
				)
			),
			'slug'                => $bp->resell->reselling->slug,
			'position'            => $bp->resell->params['adminbar_myaccount_order'],
			'screen_function'     => 'bp_resell_screen_reselling',
			'default_subnav_slug' => 'reselling',
			'item_css_id'         => 'members-reselling',
		)
	);

	/** RESELLERS NAV ************************************************/

	bp_core_new_nav_item(
		array(
			'name'                => sprintf(
				__( 'Resellers %s', 'buddypress-resellers' ),
				sprintf(
					'<span class="count">%s</span>',
					esc_html( bp_core_number_format( bp_resell_get_the_resellers_count( array( 'user_id' => $user_id ) ) ) )
				)
			),
			'slug'                => $bp->resell->resellers->slug,
			'position'            => apply_filters( 'bp_resell_resellers_nav_position', 62 ),
			'screen_function'     => 'bp_resell_screen_resellers',
			'default_subnav_slug' => 'resellers',
			'item_css_id'         => 'members-resellers',
		)
	);

	/** ACTIVITY SUBNAV **********************************************/

	// Add activity sub nav item.
	if ( bp_is_active( 'activity' ) && apply_filters( 'bp_resell_show_activity_subnav', true ) ) {
		bp_core_new_subnav_item(
			array(
				'name'            => _x( 'Reselling', 'Activity subnav tab', 'buddypress-resellers' ),
				'slug'            => constant( 'BP_RESELLING_SLUG' ),
				'parent_url'      => bp_resell_get_user_url( $user_id, array( bp_get_activity_slug() ) ),
				'parent_slug'     => bp_get_activity_slug(),
				'screen_function' => 'bp_resell_screen_activity_reselling',
				'position'        => 21,
				'item_css_id'     => 'activity-reselling',
			)
		);
	}

	// BuddyBar compatibility.
	add_action( 'bp_adminbar_menus', 'bp_resell_group_buddybar_items' );
}
add_action( 'bp_resell_setup_nav', 'bp_resell_user_setup_nav', 10, 2 );

/**
 * Set up WP Toolbar / Admin Bar.
 *
 * This function was moved from {@link BP_Resell_Component} in v1.3.0 due
 * to the users module being toggleable.
 *
 * @since 1.3.0
 */
function bp_resell_user_setup_toolbar() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	global $wp_admin_bar, $bp;

	$reselling_url = bp_resell_get_user_url( bp_loggedin_user_id(), array( $bp->resell->reselling->slug ) );

	// "Resell" parent nav menu
	$wp_admin_nav[] = array(
		'parent' => $bp->my_account_menu_id,
		'id'     => 'my-account-' . $bp->resell->id,
		'title'  => _x( 'Resell', 'Adminbar main nav', 'buddypress-resellers' ),
		'href'   => $reselling_url,
	);

	// "Reselling" subnav item
	$wp_admin_nav[] = array(
		'parent' => 'my-account-' . $bp->resell->id,
		'id'     => 'my-account-' . $bp->resell->id . '-reselling',
		'title'  => _x( 'Reselling', 'Adminbar resell subnav', 'buddypress-resellers' ),
		'href'   => $reselling_url,
	);

	// "Resellers" subnav item
	$wp_admin_nav[] = array(
		'parent' => 'my-account-' . $bp->resell->id,
		'id'     => 'my-account-' . $bp->resell->id . '-resellers',
		'title'  => _x( 'Resellers', 'Adminbar resell subnav', 'buddypress-resellers' ),
		'href'   => bp_resell_get_user_url( bp_loggedin_user_id(), array( $bp->resell->resellers->slug ) ),
	);

	// Add each admin menu.
	foreach ( apply_filters( 'bp_resell_toolbar', $wp_admin_nav ) as $admin_menu ) {
		$wp_admin_bar->add_menu( $admin_menu );
	}
}
add_action( 'bp_resell_setup_admin_bar', 'bp_resell_user_setup_toolbar' );

/**
 * Inject "Reselling" nav item to WP adminbar's "Activity" main nav.
 *
 * This function was moved from {@link BP_Resell_Component} in v1.3.0 due
 * to the users module being toggleable.
 *
 * @param array $retval
 * @return array
 */
function bp_resell_user_activity_admin_nav_toolbar( $retval ) {

	if ( ! is_user_logged_in() ) {
		return $retval;
	}

	if ( bp_is_active( 'activity' ) && apply_filters( 'bp_resell_show_activity_subnav', true ) ) {
		$new_item = array(
			'parent' => 'my-account-activity',
			'id'     => 'my-account-activity-reselling',
			'title'  => _x( 'Reselling', 'Adminbar activity subnav', 'buddypress-resellers' ),
			'href'   => bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELLING_SLUG' ) ) ),
		);

		$inject = array();
		$offset = 3;

		$inject[ $offset ] = $new_item;
		$retval = array_merge(
			array_slice( $retval, 0, $offset, true ),
			$inject,
			array_slice( $retval, $offset, null, true )
		);
	}

	return $retval;
}
add_action( 'bp_activity_admin_nav', 'bp_resell_user_activity_admin_nav_toolbar' );

/**
 * Groups resell nav items together in the BuddyBar.
 *
 * For BP Resell, we use separate nav items for the "Reselling" and
 * "Resellers" pages, but for the BuddyBar, we want to group them together.
 *
 * Because of the way BuddyPress renders both the BuddyBar and profile nav
 * with the same code, to alter just the BuddyBar, you need to resort to
 * hacking the $bp global later on.
 *
 * This will probably break in future versions of BP, when that happens we'll
 * remove this entirely.
 *
 * If the WP Toolbar is in use, this method is skipped.
 *
 * This function was moved from {@link BP_Resell_Component} in v1.3.0 due
 * to the users module being toggleable.
 *
 * @since 1.3.0
 */
function bp_resell_group_buddybar_items() {
	// don't do this if we're using the WP Admin Bar / Toolbar.
	if ( ! defined( 'BP_USE_WP_ADMIN_BAR' ) ) {
		define( 'BP_USE_WP_ADMIN_BAR', false ); // Or true, based on your default preference
	}

	if ( defined( 'BP_USE_WP_ADMIN_BAR' ) && BP_USE_WP_ADMIN_BAR ) {
		return;
	}

	if ( ! bp_loggedin_user_id() ) {
		return;
	}

	$bp = $GLOBALS['bp'];

	// get resell nav positions.
	$reselling_position = $bp->resell->params['adminbar_myaccount_order'];
	$resellers_position = apply_filters( 'bp_resell_resellers_nav_position', 62 );

	// clobberin' time!
	unset( $bp->bp_nav[ $reselling_position ] );
	unset( $bp->bp_nav[ $resellers_position ] );
	unset( $bp->bp_options_nav['reselling'] );
	unset( $bp->bp_options_nav['resellers'] );

	$reselling_url = bp_resell_get_user_url( bp_loggedin_user_id(), array( $bp->resell->reselling->slug ) );

	// Add the "Resell" nav menu.
	$bp->bp_nav[ $reselling_position ] = array(
		'name'                    => _x( 'Resell', 'Adminbar main nav', 'buddypress-resellers' ),
		'link'                    => $reselling_url,
		'slug'                    => 'resell',
		'css_id'                  => 'resell',
		'position'                => $reselling_position,
		'show_for_displayed_user' => 1,
		'screen_function'         => 'bp_resell_screen_resellers',
	);

	// "Reselling" subnav item
	$bp->bp_options_nav['resell'][10] = array(
		'name'            => _x( 'Reselling', 'Adminbar resell subnav', 'buddypress-resellers' ),
		'link'            => $reselling_url,
		'slug'            => $bp->resell->reselling->slug,
		'css_id'          => 'reselling',
		'position'        => 10,
		'user_has_access' => 1,
		'screen_function' => 'bp_resell_screen_resellers',
	);

	// "Resellers" subnav item
	$bp->bp_options_nav['resell'][20] = array(
		'name'            => _x( 'Resellers', 'Adminbar resell subnav', 'buddypress-resellers' ),
		'link'            => bp_resell_get_user_url( bp_loggedin_user_id(), array( $bp->resell->resellers->slug ) ),
		'slug'            => $bp->resell->resellers->slug,
		'css_id'          => 'resellers',
		'position'        => 20,
		'user_has_access' => 1,
		'screen_function' => 'bp_resell_screen_resellers',
	);

	// Resort the nav items to account for the late change made above.
	ksort( $bp->bp_nav );
}

/** LOOP INJECTION *******************************************************/

/**
 * Inject $members_template global with resell status for each member in the
 * members loop.
 *
 * Once the members loop has queried and built a $members_template object,
 * fetch all of the member IDs in the object and bulk fetch the reselling
 * status for all the members in one query.
 *
 * This is significantly more efficient that querying for every member inside
 * of the loop.
 *
 * @since 1.0
 * @todo Use {@link BP_User_Query} introduced in BP 1.7 in a future version
 *
 * @global $members_template The members template object containing all fetched members in the loop
 * @uses BP_Resell::bulk_check_resell_status() Check the reselling status for more than one member
 * @param string $has_members Whether any members where actually returned in the loop.
 * @return $has_members Return the original $has_members param as this is a filter function.
 */
function bp_resell_inject_member_resell_status( $has_members ) {
	global $members_template;

	if ( empty( $has_members ) ) {
		return $has_members;
	}

	$user_ids = array();

	foreach ( (array) $members_template->members as $i => $member ) {
		if ( bp_loggedin_user_id() !== $member->id ) {
			$user_ids[] = $member->id;
		}

		$members_template->members[ $i ]->is_reselling = false;
	}

	if ( empty( $user_ids ) ) {
		return $has_members;
	}

	$reselling = BP_Resell::bulk_check_resell_status( $user_ids );

	if ( empty( $reselling ) ) {
		return $has_members;
	}

	foreach ( (array) $reselling as $is_reselling ) {
		foreach ( (array) $members_template->members as $i => $member ) {
			if ( $is_reselling->leader_id === $member->id ) {
				$members_template->members[ $i ]->is_reselling = true;
			}
		}
	}

	return $has_members;
}
add_filter( 'bp_has_members', 'bp_resell_inject_member_resell_status' );

/**
 * Inject $members_template global with resell status for each member in the
 * group members loop.
 *
 * Once the group members loop has queried and built a $members_template
 * object, fetch all of the member IDs in the object and bulk fetch the
 * reselling status for all the group members in one query.
 *
 * This is significantly more efficient that querying for every member inside
 * of the loop.
 *
 * @author r-a-y
 * @since 1.1
 *
 * @global $members_template The members template object containing all fetched members in the loop
 * @uses BP_Resell::bulk_check_resell_status() Check the reselling status for more than one member
 * @param string $has_members - Whether any members where actually returned in the loop.
 * @return $has_members - Return the original $has_members param as this is a filter function.
 */
function bp_resell_inject_group_member_resell_status( $has_members ) {
	global $members_template;

	if ( empty( $has_members ) ) {
		return $has_members;
	}

	$user_ids = array();

	foreach ( (array) $members_template->members as $i => $member ) {
		if ( bp_loggedin_user_id() !== $member->user_id ) {
			$user_ids[] = $member->user_id;
		}

		$members_template->members[ $i ]->is_reselling = false;
	}

	if ( empty( $user_ids ) ) {
		return $has_members;
	}

	$reselling = BP_Resell::bulk_check_resell_status( $user_ids );

	if ( empty( $reselling ) ) {
		return $has_members;
	}

	foreach ( (array) $reselling as $is_reselling ) {
		foreach ( (array) $members_template->members as $i => $member ) {
			if ( $is_reselling->leader_id === $member->user_id ) {
				$members_template->members[ $i ]->is_reselling = true;
			}
		}
	}

	return $has_members;
}
add_filter( 'bp_group_has_members', 'bp_resell_inject_group_member_resell_status' );

/** BUTTONS **************************************************************/

/**
 * Add a "Resell User/Stop Reselling" button to the profile header for a user.
 *
 * @uses bp_resell_is_reselling() Check the reselling status for a user
 * @uses bp_is_my_profile() Return true if you are looking at your own profile when logged in.
 * @uses is_user_logged_in() Return true if you are logged in.
 */
function bp_resell_add_profile_resell_button() {
	if ( bp_is_my_profile() ) {
		return;
	}

	bp_resell_add_resell_button();
}
add_action( 'bp_member_header_actions', 'bp_resell_add_profile_resell_button' );

/**
 * Add a "Resell User/Stop Reselling" button to each member shown in the
 * members loop.
 *
 * @global $members_template The members template object containing all fetched members in the loop
 * @uses is_user_logged_in() Return true if you are logged in.
 */
function bp_resell_add_listing_resell_button() {
	global $members_template;

	if ( bp_loggedin_user_id() === $members_template->member->id ) {
		return;
	}

	bp_resell_add_resell_button( 'leader_id=' . $members_template->member->id );
}
add_action( 'bp_directory_members_actions', 'bp_resell_add_listing_resell_button' );

/**
 * Add a "Resell User/Stop Reselling" button to each member shown in a group
 * members loop.
 *
 * @author r-a-y
 * @since 1.1
 *
 * @global $members_template The members template object containing all fetched members in the loop
 */
function bp_resell_add_group_member_resell_button() {
	global $members_template;

	if ( bp_loggedin_user_id() === $members_template->member->user_id || ! bp_loggedin_user_id() ) {
		return;
	}

	bp_resell_add_resell_button( 'leader_id=' . $members_template->member->user_id );
}
add_action( 'bp_group_members_list_item_action', 'bp_resell_add_group_member_resell_button' );

/** CACHE / DELETION ****************************************************/

/**
 * Set up global cachegroups for users module in BP Resell.
 *
 * @since 1.3.0
 */
function bp_resell_users_setup_global_cachegroups() {
	$bp = $GLOBALS['bp'];

	// user counts.
	$bp->resell->global_cachegroups[] = 'bp_resell_user_resellers_count';
	$bp->resell->global_cachegroups[] = 'bp_resell_user_reselling_count';

	// user data query.
	$bp->resell->global_cachegroups[] = 'bp_resell_resellers';
	$bp->resell->global_cachegroups[] = 'bp_resell_reselling';
}
add_action( 'bp_resell_setup_globals', 'bp_resell_users_setup_global_cachegroups' );

/**
 * Removes resell relationships for all users from a user who is deleted or spammed
 *
 * @since 1.0.0
 *
 * @uses BP_Resell::delete_all_for_user() Deletes user ID from all reselling / reseller records
 */
function bp_resell_remove_data( $user_id ) {
	do_action( 'bp_resell_before_remove_data', $user_id );

	BP_Resell::delete_all_for_user( $user_id );

	do_action( 'bp_resell_remove_data', $user_id );
}
add_action( 'wpmu_delete_user', 'bp_resell_remove_data' );
add_action( 'delete_user', 'bp_resell_remove_data' );
add_action( 'make_spam_user', 'bp_resell_remove_data' );

/**
 * Clear cache when a user resells / stop resell another user.
 *
 * @since 1.3.0
 *
 * @param BP_Resell $resell
 */
function bp_resell_clear_cache_on_resell( BP_Resell $resell ) {
	// clear resell cache.
	wp_cache_delete( $resell->leader_id,   'bp_resell_user_resellers_count' );
	wp_cache_delete( $resell->reseller_id, 'bp_resell_user_reselling_count' );
	wp_cache_delete( $resell->leader_id,   'bp_resell_user_resellers_query' );
	wp_cache_delete( $resell->reseller_id, 'bp_resell_user_reselling_query' );

	// clear resell relationship.
	wp_cache_delete( "{$resell->leader_id}:{$resell->reseller_id}:", 'bp_resell_data' );
}
add_action( 'bp_resell_start_reselling', 'bp_resell_clear_cache_on_resell' );
add_action( 'bp_resell_stop_reselling',  'bp_resell_clear_cache_on_resell' );

/**
 * Clear resell cache when a user is deleted.
 *
 * @since 1.3.0
 *
 * @param int $user_id The ID of the user being deleted.
 */
function bp_resell_clear_cache_on_user_delete( $user_id ) {
	// delete resell cache.
	wp_cache_delete( $user_id, 'bp_resell_user_reselling_count' );
	wp_cache_delete( $user_id, 'bp_resell_user_resellers_count' );
	wp_cache_delete( $user_id, 'bp_resell_user_reselling_query' );
	wp_cache_delete( $user_id, 'bp_resell_user_resellers_query' );

	// delete each user's resellers count that the user was reselling.
	$users = BP_Resell::get_reselling( $user_id );
	if ( ! empty( $users ) ) {
		foreach ( $users as $user ) {
			wp_cache_delete( $user, 'bp_resell_user_resellers_count' );

			// clear resell relationship.
			wp_cache_delete( "{$user_id}:{$user}:", 'bp_resell_data' );
		}
	}
}
add_action( 'bp_resell_before_remove_data', 'bp_resell_clear_cache_on_user_delete' );

/** DIRECTORIES **********************************************************/

/**
 * Adds a "Reselling (X)" tab to the activity directory.
 *
 * This is so the logged-in user can filter the activity stream to only users
 * that the current user is reselling.
 *
 * @uses bp_resell_total_resell_counts() Get the reselling/resellers counts for a user.
 */
function bp_resell_add_activity_tab() {

	$count = bp_resell_get_the_reselling_count();

	if ( empty( $count ) ) {
		return;
	}

	$resell_activity_url = bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELLING_SLUG' ) ) );
?>

	<li id="activity-reselling"><a href="<?php echo esc_url( $resell_activity_url ); ?>" title="<?php esc_html_e( 'The public activity for everyone you are reselling on this site.', 'buddypress-resellers' ) ?>"><?php printf( esc_html__( 'Reselling %s', 'buddypress-resellers' ), '<span>' . esc_html( bp_core_number_format( $count ) ) . '</span>' ); ?></a></li>

<?php
}
add_action( 'bp_before_activity_type_tab_engagements', 'bp_resell_add_activity_tab' );

/**
 * Add a "Reselling (X)" tab to the members directory.
 *
 * This is so the logged-in user can filter the members directory to only
 * users that the current user is reselling.
 *
 * @uses bp_resell_total_resell_counts() Get the reselling/resellers counts for a user.
 */
function bp_resell_add_reselling_tab() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$count = bp_resell_get_the_reselling_count();

	if ( empty( $count ) ) {
		return;
	}

	$reselling_url = bp_resell_get_user_url( bp_loggedin_user_id(), array( constant( 'BP_RESELLING_SLUG' ) ) );
?>

	<li id="members-reselling"><a href="<?php echo esc_url( $reselling_url ); ?>"><?php printf( esc_html__( 'Reselling %s', 'buddypress-resellers' ), '<span>' . esc_html( bp_core_number_format( $count ) ) . '</span>' ); ?></a></li>

<?php
}
add_action( 'bp_members_directory_member_types', 'bp_resell_add_reselling_tab' );

/** USER QUERY ***********************************************************/

/**
 * Override the BP User Query when our special resell type is in use.
 *
 * @since 1.3.0
 *
 * @param BP_User_Query $q
 */
function bp_resell_pre_user_query( $q ) {
	if ( 'oldest-resells' !== $q->query_vars['type'] && 'newest-resells' !== $q->query_vars['type'] ) {
		return;
	}

	$q->total_users = count( $q->query_vars['include'] );

	// oldest resells .
	if ( 'oldest-resells' === $q->query_vars['type'] ) {
		// flip the order.
		$q->query_vars['user_ids'] = array_reverse( wp_parse_id_list( $q->query_vars['include'] ) );

	// newest resells .
	} elseif ( 'newest-resells' === $q->query_vars['type'] ) {
		$q->query_vars['user_ids'] = $q->query_vars['include'];
	}

	// Manual pagination. Eek!
	if ( ! empty( $q->query_vars['page'] ) ) {
		$q->query_vars['user_ids'] = array_splice( $q->query_vars['user_ids'], $q->query_vars['per_page'] * ( $q->query_vars['page'] - 1 ), $q->query_vars['per_page'] );
	}
}
add_action( 'bp_pre_user_query_construct', 'bp_resell_pre_user_query' );

/** AJAX MANIPULATION ****************************************************/

/**
 * Set up activity arguments for use with the 'reselling' scope.
 *
 * For details on the syntax, see {@link BP_Activity_Query}.
 *
 * Only applicable to BuddyPress 2.2+.  Older BP installs uses the code
 * available in /backpat/activity-scope.php.
 *
 * @since 1.3.0
 *
 * @param array $retval Empty array by default.
 * @param array $filter Current activity arguments.
 * @return array
 */
function bp_resell_users_filter_activity_scope( $retval = array(), $filter = array() ) {
	$bp = $GLOBALS['bp'];

	// Determine the user_id.
	if ( ! empty( $filter['user_id'] ) ) {
		$user_id = $filter['user_id'];
	} else {
		$user_id = bp_displayed_user_id()
			? bp_displayed_user_id()
			: bp_loggedin_user_id();
	}

	// Determine engagements of user.
	$reselling_ids = bp_resell_get_reselling( array(
		'user_id' => $user_id,
	) );
	if ( empty( $reselling_ids ) ) {
		$reselling_ids = array( 0 );
	}

	/**
	 * Since BP Resell supports down to BP 1.5, BP 1.5 lacks the third parameter
	 * for the 'bp_has_activities' filter. So we must resort to this to mark that
	 * our 'reselling' scope is in effect
	 *
	 * Primarily used to alter the 'no activity found' text.
	 */
	$bp->resell->activity_scope_set = 1;

	$retval = array(
		'relation' => 'AND',
		array(
			'column'  => 'user_id',
			'compare' => 'IN',
			'value'   => (array) $reselling_ids,
		),

		// we should only be able to view sitewide activity content for those the user
		// is reselling.
		array(
			'column' => 'hide_sitewide',
			'value'  => 0,
		),

		// overrides.
		'override' => array(
			'filter'      => array(
				'user_id' => 0,
			),
			'show_hidden' => true,
		),
	);

	return $retval;
}
add_filter( 'bp_activity_set_reselling_scope_args', 'bp_resell_users_filter_activity_scope', 10, 2 );

/**
 * Filter the members loop on a resell page.
 *
 * This is done so we can return the users that:
 *   - the current user is reselling (on a user page or member directory); or
 *   - are reselling the displayed user on the displayed user's resellers page
 *
 * @author r-a-y
 * @since 1.2
 *
 * @param array|string $qs     The querystring for the BP loop.
 * @param str          $object The current object for the querystring.
 * @return array|string Modified querystring
 */
function bp_resell_add_member_scope_filter( $qs, $object ) {

	// not on the members object? stop now!
	if ( 'members' !== $object ) {
		return $qs;
	}

	// Parse querystring into array.
	$r = wp_parse_args( $qs );

	$set = false;

	// members directory
	// can't use bp_is_members_directory() yet since that's a BP 2.0 function.
	if ( ! bp_is_user() && bp_is_members_component() ) {
		// Check for existing scope.
		$scope = ! empty( $r['scope'] ) && 'reselling' === $r['scope'] ? true : false;

		// check if members scope is reselling before manipulating.
		if ( $scope || ( isset( $_COOKIE['bp-members-scope'] ) && 'reselling' === $_COOKIE['bp-members-scope'] ) ) {
			$set = true;
			$action = 'reselling';
		}

	// user page
	} elseif ( bp_is_user() ) {
		$set = true;
		$action = bp_current_action();
	}

	// not on a user page? stop now!
	if ( ! $set ) {
		return $qs;
	}

	// filter the members loop based on the current page.
	switch ( $action ) {
		case 'reselling':
			$r['include'] = bp_resell_get_reselling( array(
				'user_id' => bp_displayed_user_id() ? bp_displayed_user_id() : bp_loggedin_user_id(),
			) );

			break;

		case 'resellers':
			$r['include'] = bp_resell_get_resellers();

			break;
	}

	if ( in_array( $action, array( 'reselling', 'resellers' ), true ) && ! $r['include'] ) {
		$r['include'] = array( 0 );
	}

	/**
	 * Number of users to display on a user's Reselling or Resellers page.
	 *
	 * @since 1.2.2
	 *
	 * @param int $retval
	 */
	$r['per_page'] = apply_filters( 'bp_resell_per_page', 20 );

	return $r;
}
add_filter( 'bp_ajax_querystring', 'bp_resell_add_member_scope_filter', 20, 2 );

/**
 * Set pagination parameters when on a user Resell page for Nouveau.
 *
 * Nouveau has its own pagination routine...
 *
 * @since 1.3.0
 *
 * @param  array  $r    Current pagination arguments.
 * @param  string $type Pagination type.
 * @return array
 */
function bp_resell_set_pagination_for_nouveau( $r, $type ) {
	if ( $GLOBALS['bp']->resell->reselling->slug !== $type && $GLOBALS['bp']->resell->resellers->slug !== $type ) {
		return $r;
	}

	return array(
		'pag_count' => bp_get_members_pagination_count(),
		'pag_links' => bp_get_members_pagination_links(),
		'page_arg'  => $GLOBALS['members_template']->pag_arg
	);
}
add_filter( 'bp_nouveau_pagination_params', 'bp_resell_set_pagination_for_nouveau', 10, 2 );

/**
 * Set some default parameters for a member loop.
 *
 * If we're on a user's reselling or resellers page, set the member filter
 * so users are sorted by newest resells instead of last active.
 *
 * If we're on a user's engagements page or the members directory, reset the
 * members filter to last active.
 *
 * Only applicable for BuddyPress 1.7+.
 *
 * @since 1.3.0
 *
 * @see bp_resell_add_members_dropdown_filter()
 */
function bp_resell_set_members_scope_default() {
	// don't do this for older versions of BP.
	if ( ! class_exists( 'BP_User_Query' ) ) {
		return;
	}

	// set default members filter to 'newest-resells' on member resell pages.
	if ( bp_is_user() && ( bp_is_current_action( 'reselling' ) || bp_is_current_action( 'resellers' ) ) ) {
		// set the members filter to 'newest-resells' by faking an ajax request (loophole!)
		$_POST['cookie'] = 'bp-members-filter%3Dnewest-resells';

		// reset the dropdown menu to 'Newest Resells'.
		@setcookie( 'bp-members-filter', 'newest-resells', 0, '/' );

	// reset members filter on the user engagements and members directory page
	// this is done b/c the 'newest-resells' filter does not apply on these pages.
	} elseif ( bp_is_user_engagements() || ( ! bp_is_user() && bp_is_members_component() ) ) {
		// set the members filter to 'newest' by faking an ajax request (loophole!).
		$_POST['cookie'] = 'bp-members-filter%3Dactive';

		// reset the dropdown menu to 'Last Active'.
		@setcookie( 'bp-members-filter', 'active', 0, '/' );
	}
}
add_action( 'bp_screens', 'bp_resell_set_members_scope_default' );

/**
 * Sets the "RSS" feed URL for the tab on the Sitewide Activity page.
 *
 * This occurs when the "Reselling" tab is clicked on the Sitewide Activity
 * page or when the activity scope is already set to "reselling".
 *
 * Only do this for BuddyPress 1.8+.
 *
 * @since 1.2.1
 *
 * @author r-a-y
 * @param string $retval The feed URL.
 * @return string The feed URL.
 */
function bp_resell_alter_activity_feed_url( $retval ) {
	// only available in BP 1.8+
	if ( ! class_exists( 'BP_Activity_Feed' ) ) {
		return $retval;
	}

	// this is done b/c we're filtering 'bp_get_sitewide_activity_feed_link' and
	// we only want to alter the feed link for the "RSS" tab.
	if ( ! defined( 'DOING_AJAX' ) && ! did_action( 'bp_before_directory_activity' ) ) {
		return $retval;
	}

	// get the activity scope.
	$scope = ! empty( $_COOKIE['bp-activity-scope'] ) ? $_COOKIE['bp-activity-scope'] : false;

	if ( 'reselling' === $scope && bp_loggedin_user_id() ) {
		$retval = bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELLING_SLUG' ), array( 'feed' ) ) );
	}

	return $retval;
}
add_filter( 'bp_get_sitewide_activity_feed_link', 'bp_resell_alter_activity_feed_url' );
add_filter( 'bp_dtheme_activity_feed_url',        'bp_resell_alter_activity_feed_url' );
add_filter( 'bp_legacy_theme_activity_feed_url',  'bp_resell_alter_activity_feed_url' );

/** GETTEXT **************************************************************/

/**
 * Add gettext filter when no activities are found and when using resell scope.
 *
 * @since 1.2.1
 *
 * @author r-a-y
 * @param bool $has_activities Whether the current activity loop has activities.
 * @return bool
 */
function bp_resell_has_activities( $has_activities ) {
	$bp = $GLOBALS['bp'];

	if ( ! empty( $bp->resell->activity_scope_set ) && ! $has_activities ) {
		add_filter( 'gettext', 'bp_resell_no_activity_text', 10, 2 );
	}

	return $has_activities;
}
add_filter( 'bp_has_activities', 'bp_resell_has_activities', 10, 2 );

/**
 * Modifies 'no activity found' text to be more specific to resell scope.
 *
 * @since 1.2.1
 *
 * @author r-a-y
 * @see bp_resell_has_activities()
 * @param string $translated_text The translated text.
 * @param string $untranslated_text The unmodified text.
 * @return string
 */
function bp_resell_no_activity_text( $translated_text, $untranslated_text ) {
	if ( 'Sorry, there was no activity found. Please try a different filter.' === $untranslated_text ) {
		if ( ! bp_is_user() || bp_is_my_profile() ) {
			$resell_counts = bp_resell_total_resell_counts( array(
				'user_id' => bp_loggedin_user_id(),
			) );

			if ( $resell_counts['reselling'] ) {
				return __( "You are reselling some users, but they haven't posted yet.", 'buddypress-resellers' );
			} else {
				return __( "You are not reselling anyone yet.", 'buddypress-resellers' );
			}
		} else {
			$resell_counts = bp_resell_total_resell_counts( array(
				'user_id' => bp_displayed_user_id(),
			) );

			if ( ! empty( $resell_counts['reselling'] ) ) {
				return __( "This user is reselling some users, but they haven't posted yet.", 'buddypress-resellers' );
			} else {
				return __( "This user isn't reselling anyone yet.", 'buddypress-resellers' );
			}
		}
	}

	return $translated_text;
}

/**
 * Removes custom gettext filter when using resell scope.
 *
 * @since 1.2.1
 *
 * @author r-a-y
 * @see bp_resell_has_activities()
 */
function bp_resell_after_activity_loop() {
	$bp = $GLOBALS['bp'];

	if ( ! empty( $bp->resell->activity_scope_set ) ) {
		remove_filter( 'gettext', 'bp_resell_no_activity_text', 10, 2 );
		unset( $bp->resell->activity_scope_set );
	}
}
add_action( 'bp_after_activity_loop', 'bp_resell_after_activity_loop' );

/** SUGGESTIONS *********************************************************/

/**
 * Override BP's engagement suggestions with resellers.
 *
 * This takes effect for private messages currently. Available in BP 2.1+.
 *
 * @since 1.3.0
 *
 * @param array $retval Parameters for the user query.
 */
function bp_resell_user_suggestions_args( $retval ) {
	$bp = $GLOBALS['bp'];

	// if only engagements, override with resellers instead.
	if ( true === (bool) $retval['only_engagements'] ) {
		// set marker.
		$bp->resell->only_engagements_override = 1;

		// we set 'only_engagements' to 0 to bypass engagements component check.
		$retval['only_engagements'] = 0;

		// add our user query filter.
		add_filter( 'bp_members_suggestions_query_args', 'bp_resell_user_resell_suggestions' );
	}

	return $retval;
}
add_filter( 'bp_members_suggestions_args', 'bp_resell_user_suggestions_args' );

/**
 * Filters the user suggestions query to limit by resellers only.
 *
 * Only available in BP 2.1+.
 *
 * @since 1.3.0
 *
 * @see bp_resell_user_suggestions_args()
 * @param array $user_query User query arguments. See {@link BP_User_Query}.
 */
function bp_resell_user_resell_suggestions( $user_query ) {
	$bp = $GLOBALS['bp'];

	if ( isset( $bp->resell->only_engagements_override ) ) {
		unset( $bp->resell->only_engagements_override );

		// limit suggestions to resellers.
		$user_query['include'] = bp_resell_get_resellers( array(
			'user_id' => bp_loggedin_user_id(),
		) );

		// No resellers, so don't return any suggestions.
		if ( empty( $user_query['include'] ) && false === is_super_admin( bp_loggedin_user_id() ) ) {
			$user_query['include'] = (array) 0;
		}
	}

	return $user_query;
}

/**
 * Remove at-mention primed results for the engagements component.
 *
 * We'll use a list of members the logged-in user is reselling instead.
 *
 * @see bp_resell_prime_mentions_results()
 */
remove_action( 'bp_activity_mentions_prime_results', 'bp_engagements_prime_mentions_results' );

/**
 * Set up a list of members the current user is reselling for at-mention use.
 *
 * This is intended to speed up at-mention lookups for a majority of use cases.
 *
 * @since 1.3.0
 *
 * @see bp_activity_mentions_script()
 */
function bp_resell_prime_mentions_results() {
	if ( ! bp_activity_maybe_load_mentions_scripts() ) {
		return;
	}

	// Bail out if the site has a ton of users.
	if ( is_multisite() && wp_is_large_network( 'users' ) ) {
		return;
	}

	$reselling = bp_resell_get_reselling( array(
		'user_id' => bp_loggedin_user_id(),
	) );

	if ( empty( $reselling ) ) {
		return;
	}

	$resellers_query = new BP_User_Query( array(
		'count_total'     => '', // Prevents total count.
		'populate_extras' => false,
		'type'            => 'alphabetical',
		'include'         => $reselling,
	) );
	$results = array();

	foreach ( $resellers_query->results as $user ) {
		$result        = new stdClass();
		$result->ID    = $user->user_nicename;
		$result->name  = bp_core_get_user_displayname( $user->ID );
		$result->image = bp_core_fetch_avatar( array(
			'html' => false,
			'item_id' => $user->ID,
		) );

		$results[] = $result;
	}

	wp_localize_script( 'bp-mentions', 'BP_Suggestions', array(
		'engagements' => $results,
	) );
}
add_action( 'bp_activity_mentions_prime_results', 'bp_resell_prime_mentions_results' );
