<?php
/**
 * Resell Activity Module.
 *
 * @since 1.3.0
 *
 * @package BP-Resell
 * @subpackage Activity Module
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Resell Activity module class.
 *
 * @since 1.3.0
 */
class BP_Resell_Activity_Module {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Component hooks.
		add_action( 'bp_resell_setup_globals', array( $this, 'constants' ) );
		add_action( 'bp_resell_setup_globals', array( $this, 'setup_global_cachegroups' ) );
		add_action( 'bp_resell_setup_nav',     array( $this, 'setup_nav' ) );
		add_action( 'bp_activity_admin_nav',   array( $this, 'activity_admin_nav' ) );

		// Loop filtering.
		add_action( 'bp_before_activity_type_tab_favorites', array( $this, 'add_activity_directory_tab' ) );
		add_filter( 'bp_activity_set_resell_scope_args', array( $this, 'filter_activity_scope' ), 10, 2 );
		add_filter( 'bp_has_activities',      array( $this, 'bulk_inject_resell_status' ) );
		add_action( 'bp_activity_entry_meta', array( $this, 'add_resell_button' ) );

		// Cache invalidation.
		add_action( 'bp_resell_start_reselling_activity', array( $this, 'clear_cache_on_resell' ) );
		add_action( 'bp_resell_stop_reselling_activity',  array( $this, 'clear_cache_on_resell' ) );
		add_action( 'bp_resell_before_remove_data',       array( $this, 'clear_cache_on_user_delete' ) );
		add_action( 'bp_activity_after_delete',           array( $this, 'on_activity_delete' ) );

		// RSS.
		add_action( 'bp_actions', array( $this, 'rss_handler' ) );
		add_filter( 'bp_get_sitewide_activity_feed_link', array( $this, 'activity_feed_url' ) );
		add_filter( 'bp_dtheme_activity_feed_url',        array( $this, 'activity_feed_url' ) );
		add_filter( 'bp_legacy_theme_activity_feed_url',  array( $this, 'activity_feed_url' ) );
		add_filter( 'bp_get_activities_member_rss_link',  array( $this, 'activity_feed_url' ) );
	}

	/** COMPONENT HOOKS ******************************************************/

	/**
	 * Constants.
	 */
	public function constants() {
		// /members/admin/activity/[RESELL]
		if ( ! defined( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ) ) {
			define( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG', 'resell' );
		}
	}

	/**
	 * Set up global cachegroups.
	 */
	public function setup_global_cachegroups() {
		$bp = $GLOBALS['bp'];

		// Counts.
		$bp->resell->global_cachegroups[] = 'bp_resell_user_activity_reselling_count';
		$bp->resell->global_cachegroups[] = 'bp_resell_activity_resellers_count';

		// Query.
		$bp->resell->global_cachegroups[] = 'bp_resell_user_activity_reselling_query';
		$bp->resell->global_cachegroups[] = 'bp_resell_activity_resellers_query';
	}

	/**
	 * Setup profile nav.
	 */
	public function setup_nav() {
		// Determine user to use.
		if ( bp_displayed_user_domain() ) {
			$user_id = bp_displayed_user_id();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_id = bp_loggedin_user_id();
		} else {
			return;
		}

		// Add activity sub nav item.
		if ( bp_is_active( 'activity' ) && apply_filters( 'bp_resell_activity_show_activity_subnav', true ) ) {
			bp_core_new_subnav_item( array(
				'name'            => _x( 'Reselled Activity', 'Activity subnav tab', 'buddypress-resellers' ),
				'slug'            => constant( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ),
				'parent_url'      => bp_resell_get_user_url( $user_id, array( bp_get_activity_slug() ) ),
				'parent_slug'     => bp_get_activity_slug(),
				'screen_function' => 'bp_activity_screen_my_activity',
				'position'        => 21,
				'item_css_id'     => 'activity-resell',
			) );
		}
	}

	/**
	 * Inject "Reselled Sites" nav item to WP adminbar's "Activity" main nav.
	 *
	 * @param array $retval
	 * @return array
	 */
	public function activity_admin_nav( $retval ) {
		if ( ! is_user_logged_in() ) {
			return $retval;
		}

		if ( bp_is_active( 'activity' ) && apply_filters( 'bp_resell_show_activity_subnav', true ) ) {
			$new_item = array(
				'parent' => 'my-account-activity',
				'id'     => 'my-account-activity-resellactivity',
				'title'  => _x( 'Reselled Activity', 'Adminbar activity subnav', 'buddypress-resellers' ),
				'href'   => bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ) ) ),
			);

			$inject = array();
			$offset = 4;

			$inject[ $offset ] = $new_item;
			$retval = array_merge(
				array_slice( $retval, 0, $offset, true ),
				$inject,
				array_slice( $retval, $offset, null, true )
			);
		}

		return $retval;
	}

	/** LOOP FILTERING *******************************************************/

	/**
	 * Adds a "Reselled Sites (X)" tab to the activity directory.
	 *
	 * This is so the logged-in user can filter the activity stream to only sites
	 * that the current user is reselling.
	 */
	public function add_activity_directory_tab() {
		/*
		 * Adding a count is confusing when you can resell comments of activity items...
		 * $count = bp_resell_get_the_reselling_count( array(
		 *	'user_id'     => bp_loggedin_user_id(),
		 *	'resell_type' => 'activity',
		 * ) );
		 *
		 * if ( empty( $count ) ) {
		 *	return;
		 * }
		 */
		$activity_resell_url =  bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ) ) );
		?>
		<li id="activity-resell"><a href="<?php echo esc_url( $activity_resell_url ); ?>"><?php esc_html_e( 'My Reselled Activity', 'buddypress-resellers' ); ?></a></li><?php
	}

	public function bulk_inject_resell_status( $retval ) {
		global $activities_template;

		if ( empty( $retval ) ) {
			return $retval;
		}

		if ( ! is_user_logged_in() ) {
			return $retval;
		}

		$activity_ids = array();

		foreach ( (array) $activities_template->activities as $i => $activity ) {
			// add blog ID to array.
			$activity_ids[] = $activity->id;

			// set default resell status to false.
			$activities_template->activities[ $i ]->is_reselling = false;
		}

		if ( empty( $activity_ids ) ) {
			return $retval;
		}

		$reselling = BP_Resell::bulk_check_resell_status( $activity_ids, bp_loggedin_user_id(), 'activity' );

		if ( empty( $reselling ) ) {
			return $retval;
		}

		foreach ( (array) $reselling as $is_reselling ) {
			foreach ( (array) $activities_template->activities as $i => $activity ) {
				// set resell status to true if the logged-in user is reselling.
				if ( $is_reselling->leader_id == $activity->id ) {
					$activities_template->activities[$i]->is_reselling = true;
				}
			}
		}

		return $retval;
	}

	/**
	 * Set up activity arguments for use with the 'resellblogs' scope.
	 *
	 * For details on the syntax, see {@link BP_Activity_Query}.
	 *
	 * Only applicable to BuddyPress 2.2+.  Older BP installs uses the code
	 * available in /modules/blogs-backpat.php.
	 *
	 * @since 1.3.0
	 *
	 * @param array $retval Empty array by default.
	 * @param array $filter Current activity arguments.
	 * @return array
	 */
	public function filter_activity_scope( $retval = array(), $filter = array() ) {
		// Determine the user_id.
		if ( ! empty( $filter['user_id'] ) ) {
			$user_id = $filter['user_id'];
		} else {
			$user_id = bp_displayed_user_id()
				? bp_displayed_user_id()
				: bp_loggedin_user_id();
		}

		// Get activity IDs that the user is reselling.
		$reselling_ids = bp_resell_get_reselling( array(
			'user_id'     => $user_id,
			'resell_type' => 'activity',
		) );

		// If no activity, pass largest int value to denote no blogs... sigh.
		if ( empty( $reselling_ids ) ) {
			$reselling_ids = array( 0 );
		}

		// Should we show all items regardless of sitewide visibility?
		$show_hidden = array();
		if ( ! empty( $user_id ) && ( bp_loggedin_user_id() !== $user_id ) ) {
			$show_hidden = array(
				'column' => 'hide_sitewide',
				'value'  => 0,
			);
		}

		$clause = array(
			'relation' => 'OR',

			// general blog activity items.
			array(
				'column'  => 'id',
				'compare' => 'IN',
				'value'   => $reselling_ids,
			),

			// groupblog posts.
			array(
				'relation' => 'AND',
				array(
					'column' => 'type',
					'value'  => 'activity_comment',
				),
				array(
					'column'  => 'item_id',
					'compare' => 'IN',
					'value'   => $reselling_ids,
				),
			),
		);

		$retval = array(
			'relation' => 'AND',
			$clause,
			$show_hidden,

			// overrides.
			'override' => array(
				'display_comments' => 'stream',
				'filter'      => array(
					'user_id' => 0,
				),
				'show_hidden' => true,
			),
		);

		return $retval;
	}

	/**
	 * Add 'Resell' button in activity loop.
	 */
	public function add_resell_button() {
		if ( false === bp_resell_activity_can_resell() ) {
			return;
		}

		bp_resell_activity_button();
	}

	/** CACHE **************************************************************/

	/**
	 * Clear count cache when a user resells / unfolows an activity item.
	 *
	 * @param BP_Resell $resell
	 */
	public function clear_cache_on_resell( BP_Resell $resell ) {
		// clear resellers count for activity.
		wp_cache_delete( $resell->leader_id,   'bp_resell_activity_resellers_count' );

		// clear reselling activity count for user.
		wp_cache_delete( $resell->reseller_id, 'bp_resell_user_activity_reselling_count' );

		// clear queried resellers / reselling.
		wp_cache_delete( $resell->leader_id,   'bp_resell_activity_resellers_query' );
		wp_cache_delete( $resell->reseller_id, 'bp_resell_user_activity_reselling_query' );
	}

	/**
	 * Clear activity cache when a user is deleted.
	 *
	 * @param int $user_id The user ID being deleted.
	 */
	public function clear_cache_on_user_delete( $user_id = 0 ) {
		// delete user's blog resell count.
		wp_cache_delete( $user_id, 'bp_resell_user_activity_reselling_count' );

		// delete queried blogs that user was reselling.
		wp_cache_delete( $user_id, 'bp_resell_user_activity_reselling_query' );

		// delete each blog's resellers count that the user was reselling.
		$aids = BP_Resell::get_reselling( $user_id, 'activity' );
		if ( ! empty( $aids ) ) {
			foreach ( $aids as $aid ) {
				wp_cache_delete( $aid, 'bp_resell_activity_resellers_count' );
			}
		}
	}

	/**
	 * Clear cache when activity item is deleted.
	 *
	 * @param array $activities An array of activities objects.
	 */
	public function on_activity_delete( $activities ) {
		$bp = $GLOBALS['bp'];

		// Pluck the activity IDs out of the $activities array.
		$activity_ids = wp_parse_id_list( wp_list_pluck( $activities, 'id' ) );

		// See if any of the deleted activity IDs were being reselled.
		$sql  = 'SELECT leader_id, reseller_id FROM ' . esc_sql( $bp->resell->table_name ) . ' ';
		$sql .= 'WHERE leader_id IN (' . implode( ',', wp_parse_id_list( $activity_ids ) ) . ') ';
		$sql .= "AND resell_type = 'activity'";

		$reselled_ids = $GLOBALS['wpdb']->get_results( $sql );

		foreach ( $reselled_ids as $activity ) {
			// clear resellers count for activity item.
			wp_cache_delete( $activity->leader_id, 'bp_resell_activity_resellers_count' );

			// clear queried resellers for activity item.
			wp_cache_delete( $activity->leader_id, 'bp_resell_activity_resellers_query' );

			// delete user's activity resell count.
			wp_cache_delete( $activity->reseller_id, 'bp_resell_user_activity_reselling_count' );

			// delete queried activity that user was reselling.
			wp_cache_delete( $activity->reseller_id, 'bp_resell_user_activity_reselling_query' );

			// Delete the resell entry
			// @todo Need a mass bulk-delete method.
			bp_resell_stop_reselling( array(
				'leader_id'   => $activity->leader_id,
				'reseller_id' => $activity->reseller_id,
				'resell_type' => 'activity',
			) );
		}
	}

	/** RSS ******************************************************************/

	/**
	 * Sets the "RSS" feed URL for the tab on the Sitewide Activity page.
	 *
	 * This occurs when the "Reselled Activity" tab is clicked on the Sitewide
	 * Activity page or when the activity scope is already set to "resellblogs".
	 *
	 * Only do this for BuddyPress 1.8+.
	 *
	 * @param string $retval The feed URL.
	 * @return string The feed URL.
	 */
	public function activity_feed_url( $retval ) {
		// only available in BP 1.8+
		if ( ! class_exists( 'BP_Activity_Feed' ) ) {
			return $retval;
		}

		// This filters the RSS link when on a user's "Activity > Papers" page.
		if ( 'bp_get_activities_member_rss_link' === current_filter() && '' == $retval && bp_is_current_action( constant( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ) ) ) {
			return esc_url( bp_resell_get_user_url( bp_displayed_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ), array( 'feed' ) ) ) );
		}

		// this is done b/c we're filtering 'bp_get_sitewide_activity_feed_link' and
		// we only want to alter the feed link for the "RSS" tab
		if ( ! defined( 'DOING_AJAX' ) && ! did_action( 'bp_before_directory_activity' ) ) {
			return $retval;
		}

		// get the activity scope.
		$scope = ! empty( $_COOKIE['bp-activity-scope'] ) ? $_COOKIE['bp-activity-scope'] : false;

		if ( 'resell' === $scope && bp_loggedin_user_id() ) {
			$retval = bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ), array( 'feed' ) ) );
		}

		return esc_url( $retval );
	}

	/**
	 * RSS handler for a user's reselled sites.
	 *
	 * When a user lands on /members/USERNAME/activity/resellblogs/feed/, this
	 * method generates the RSS feed for their reselled sites.
	 */
	public function rss_handler() {
		// only available in BP 1.8+
		if ( ! class_exists( 'BP_Activity_Feed' ) ) {
			return;
		}

		if ( ! bp_is_user_activity() || ! bp_is_current_action( constant( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ) ) || ! bp_is_action_variable( 'feed', 0 ) ) {
			return;
		}

		$bp = $GLOBALS['bp'];

		$args = array(
			'user_id' => bp_displayed_user_id(),
			'scope'   => 'resell',
		);

		// setup the feed.
		$bp->activity->feed = new BP_Activity_Feed( array(
			'id'            => 'reselledactivity',

			/* translators: User's reselling activity RSS title - "[Site Name] | [User Display Name] | Reselled Activity" */
			'title'         => sprintf( __( '%1$s | %2$s | Reselled Activity', 'buddypress-resellers' ), bp_get_site_name(), bp_get_displayed_user_fullname() ),

			'link'          => esc_url( bp_resell_get_user_url( bp_displayed_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELL_ACTIVITY_USER_ACTIVITY_SLUG' ) ) ) ),
			'description'   => sprintf( __( "Feed for activity that %s is reselling.", 'buddypress' ), bp_get_displayed_user_fullname() ),
			'activity_args' => $args,
		) );
	}
}
