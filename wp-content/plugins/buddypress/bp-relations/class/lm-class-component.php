<?php
/**
 * BuddyPress relations Streams Loader.
 *
 * The relations component is for users to create relationships with each other.
 *
 * @package BuddyPress
 * @subpackage relationsComponent
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the BuddyPress relations Component.
 *
 * @since 1.5.0
 */
#[AllowDynamicProperties]
class BP_Relations_Component extends BP_Component {

	/**
	 * Start the {$this->comps} component creation process.
	 *
	 * @since 1.5.0
	 */
	public function __construct($comp) {
		$this->isf = $comp == 'friend';
		$this->bpComps = $this->isf ? 'bp-friends' : 'bp-engagements';
		$this->bpTable = $this->isf ? 'bp_friends' : 'bp_engagements';
		$this->bpTableMeta = $this->isf ? 'bp_friends_meta' : 'bp_engagements_meta';
		$this->bpPaths = $this->isf ? 'bp/friends' : 'bp/engagements';
		$this->fe_name = $this->isf ? 'Resellers' : 'Suppliers';
		// $this->comp = $this->isf ? 'friend' : 'engagement';
		$this->comps = $this->isf ? 'friends' : 'engagements';
		$this->Comps = $this->isf ? 'Friends' : 'Engagements';
		$this->myComps = $this->isf ? 'my-friends' : 'my-engagements';
		$this->sg_fn = $this->isf ? 'bp_get_friends_slug' : 'bp_get_engagements_slug';

		parent::start(
			$this->comps,
			_x( $this->comps . ' Connections', $this->comps . 'screen page <title>', 'buddypress' ),
			buddypress()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 60,
			)
		);
	}

	/**
	 * Include bp-relationss files.
	 *
	 * @since 1.5.0
	 *
	 * @see BP_Component::includes() for description of parameters.
	 *
	 * @param array $includes See {@link BP_Component::includes()}.
	 */
	public function includes( $includes = array() ) {
		$includes = array(
			'cssjs',
			'cache',
			'filters',
			'template',
			'functions',
			'blocks',
		);

		// Conditional includes.
		if ( bp_is_active( 'activity' ) ) {
			$includes[] = 'activity';
		}
		if ( bp_is_active( 'notifications' ) ) {
			$includes[] = 'notifications';
		}

		parent::includes( $includes );
	}

	/**
	 * Late includes method.
	 *
	 * Only load up certain code when on specific pages.
	 *
	 * @since 3.0.0
	 */
	public function late_includes() {
		// Bail if PHPUnit is running.
		if ( defined( 'BP_TESTS_DIR' ) ) {
			return;
		}

		$addComp = $this->isf ? 'add-friend' : 'add-engagement';
		$removeComp = $this->isf ? 'remove-friend' : 'remove-engagement';
		$addCompFile = $this->isf ? "bp-friends/actions/add-friend.php" : "bp-engagements/actions/add-engagement.php";
		$removeCompFile = $this->isf ? "bp-friends/actions/remove-friend.php" : "bp-engagements/actions/remove-engagement.php";
		$myCompFile = $this->isf ? "bp-friends/screens/my-friends.php" : "bp-engagements/screens/my-engagements.php";
		$requestsFile = $this->isf ? "bp-friends/screens/requests.php" : "bp-engagements/screens/requests.php";
		$is_user_request = $this->isf ? 'bp_is_user_friend_requests' : 'bp_is_user_engagement_requests';

		// relationss.
		// Authenticated actions.
		if ( is_user_logged_in() && bp_current_action() == $addComp) {
			require_once $this->path . $addCompFile;
		}

		if ( is_user_logged_in() && bp_current_action() ==  $removeComp) {
			require_once $this->path . $removeCompFile;
		}

		// User nav.
		require_once $this->path . $myCompFile;
		if ( is_user_logged_in() && $is_user_request() ) {
			require_once $this->path . $requestsFile;
		}
	}

	/**
	 * Set up {$this->bpComps} global settings.
	 *
	 * The BP_ENGAGEMENTS_SLUG constant is deprecated.
	 *
	 * @since 1.5.0
	 *
	 * @see BP_Component::setup_globals() for description of parameters.
	 *
	 * @param array $args See {@link BP_Component::setup_globals()}.
	 */
	public function setup_globals( $args = array() ) {
		$bp           = buddypress();
		$default_slug = $this->id;
		
		// // @deprecated.
		// if ( defined( 'BP_Engagement_DB_VERSION' ) ) {
		// 	_doing_it_wrong( 'BP_Engagement_DB_VERSION', esc_html__( 'This constants is not used anymore.', 'buddypress' ), 'BuddyPress 12.0.0' );
		// }

		// // @deprecated.
		// if ( !defined( 'BP_ENGAGEMENTS_SLUG' ) ) {
		// 	define( 'BP_ENGAGEMENTS_SLUG' , false ) ;
		// }
		// if ( defined( 'BP_ENGAGEMENTS_SLUG' ) ) {
		// 	_doing_it_wrong( 'BP_ENGAGEMENTS_SLUG', esc_html__( 'Slug constants are deprecated.', 'buddypress' ), 'BuddyPress 12.0.0' );
		// 	$default_slug = BP_ENGAGEMENTS_SLUG;
		// }

		// Global tables for the {$this->comps} component.
		$global_tables = array(
			'table_name'      => $bp->table_prefix . $this->bpTable,
			'table_name_meta' => $bp->table_prefix . $this->bpTableMeta,
		);

		$notification_fn = $this->isf ? "friends_format_notifications" : "engagements_format_notifications" ;
		$widget_classnames = $this->isf ? "widget_bp_core_friends_widget" : "widget_bp_core_engagements_widget" ;
		// All globals for the $this->comps component.
		// Note that global_tables is included in this array.
		$args = array(
			'slug'                  => $default_slug,
			'has_directory'         => false,
			'search_string'         => __( "Search {$this->comps}...", 'buddypress' ),
			'notification_callback' => $notification_fn,
			'global_tables'         => $global_tables,
			'block_globals'         => array(
				$this->bpPaths => array(
					'widget_classnames' => array( $widget_classnames, 'buddypress' ),
				)
			),
		);

		parent::setup_globals( $args );
	}

	/**
	 * Register component navigation.
	 *
	 * @since 12.0.0
	 *
	 * @see `BP_Component::register_nav()` for a description of arguments.
	 *
	 * @param array $rg_nav Optional. See `BP_Component::register_nav()` for
	 *                        description.
	 * @param array $sub_nav  Optional. See `BP_Component::register_nav()` for
	 *                        description.
	 */
	public function register_nav( $main_nav = array(), $sub_nav = array() ) {
		$screen_my = $this->isf ? 'friends_screen_my_friends' : 'engagements_screen_my_engagements';
		$my_id = $this->isf ? 'friends-my-friends' : 'engagements-my-engagements';
		$screen_requests = $this->isf ? 'friends_screen_requests' : 'engagements_screen_requests';
		$sg_fn = $this->sg_fn;
		$slug   = $sg_fn();
		$main_nav = array(
			'name'                => __( $this->Comps, 'buddypress' ),
			'slug'                => $slug,
			'position'            => 60,
			'screen_function'     => $screen_my,
			'default_subnav_slug' => $this->myComps,
			'item_css_id'         => $this->id,
		);

		// Add the subnav items to the {$this->comps} nav item.
		$sub_nav[] = array(
			'name'            => _x( $this->fe_name, "{$this->comps} screen sub nav", 'buddypress' ),
			'slug'            => $this->myComps,
			'parent_slug'     => $slug,
			'screen_function' => $screen_my,
			'position'        => 10,
			'item_css_id'     => $my_id,
		);

		// $sub_nav[] = array(
		// 	'name'                     => _x( 'Requests', "{$this->comps} screen sub nav", 'buddypress' ),
		// 	'slug'                     => 'requests',
		// 	'parent_slug'              => $slug,
		// 	'screen_function'          => $screen_requests,
		// 	'position'                 => 20,
		// 	'user_has_access'          => false,
		// 	'user_has_access_callback' => 'bp_core_can_edit_settings',
		// );

		parent::register_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up component navigation.
	 *
	 * @since 1.5.0
	 *
	 * @see `BP_Component::setup_nav()` for a description of arguments.
	 *
	 * @param array $main_nav Optional. See `BP_Component::setup_nav()` for
	 *                        description.
	 * @param array $sub_nav  Optional. See `BP_Component::setup_nav()` for
	 *                        description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		$count_fn = $this->isf ? 'friends_get_total_friend_count' : 'engagements_get_total_engagement_count';
		// Only grab count if we're on a user page.
		if ( bp_is_user() && isset( $this->main_nav['name'] ) ) {
			// Add $this->comps to the main navigation.
			$count                  = (int) $count_fn();
			$class                  = ( 0 === $count ) ? '0' : 'count';
			$this->main_nav['name'] = sprintf(
				/* translators: %s: {$this->comp} count for the current user */
				sprintf(
					__( "{$this->fe_name} %s", 'buddypress' ),
					esc_attr( $class ),
					esc_html( $count )
				)
			);
		}

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up {$this->bpComps} integration with the WordPress admin bar.
	 *
	 * @since 1.5.0
	 *
	 * @see BP_Component::setup_admin_bar() for a description of arguments.
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar()
	 *                            for description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {

		// Menus for logged in user.
		if ( is_user_logged_in() ) {
			$get_relation_user_ids = $this->isf ? 'friends_get_relationship_request_user_ids' : 'engagements_get_relationship_request_user_ids';
			$relationships = $this->isf ? 'friendships' : 'engagementships';
			$sg_fn = $this->sg_fn;
			// Setup the logged in user variables.
			$relations_slug = $sg_fn();

			// Pending relation requests.
			$count = count( $get_relation_user_ids( bp_loggedin_user_id() ) );

			if ( ! empty( $count ) ) {
				$title = sprintf(
					/* translators: %s: Pending relation request count for the current user */
					_x( "{$this->Comps} %s", "My Account {$this->comps} menu", 'buddypress' ),
					'<span class="count">' . bp_core_number_format( $count ) . '</span>'
				);
				$pending = sprintf(
					/* translators: %s: Pending relation request count for the current user */
					_x( 'Pending Requests %s', "My Account {$this->comps} menu sub nav", 'buddypress' ),
					'<span class="count">' . bp_core_number_format( $count ) . '</span>'
				);
			} else {
				$title   = _x( $this->Comps, "My Account {$this->comps} menu", 'buddypress' );
				$pending = _x( 'No Pending Requests', "My Account {$this->comps} menu sub nav", 'buddypress' );
			}

			// Add the "My Account" sub menus.
			$wp_admin_nav[] = array(
				'parent' => buddypress()->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => $title,
				'href'   => bp_loggedin_user_url( bp_members_get_path_chunks( array( $relations_slug ) ) ),
			);

			// My {$this->MyComps}.
			$wp_admin_nav[] = array(
				'parent'   => 'my-account-' . $this->id,
				'id'       => 'my-account-' . $this->id . "-{$relationships}",
				'title'    => _x( $relationships, "My Account {$this->comps} menu sub nav", 'buddypress' ),
				'href'     => bp_loggedin_user_url( bp_members_get_path_chunks( array( $relations_slug, $this->myComps ) ) ),
				'position' => 10,
			);

			// Requests.
			$wp_admin_nav[] = array(
				'parent'   => 'my-account-' . $this->id,
				'id'       => 'my-account-' . $this->id . '-requests',
				'title'    => $pending,
				'href'     => bp_loggedin_user_url( bp_members_get_path_chunks( array( $relations_slug, 'requests' ) ) ),
				'position' => 20,
			);
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Set up the title for pages and <title>.
	 *
	 * @since 1.5.0
	 */
	public function setup_title() {
		$isComps = $this->isf ? 'bp_is_friends_component' : 'bp_is_engagements_component';
		// Adjust title.
		if ( $isComps() ) {
			$bp = buddypress();

			if ( bp_is_my_profile() ) {
				$bp->bp_options_title = __( $this->fe_name, 'buddypress' );
			} else {
				$bp->bp_options_avatar = bp_core_fetch_avatar( array(
					'item_id' => bp_displayed_user_id(),
					'type'    => 'thumb',
					'alt'     => sprintf(
						/* translators: %s: member name */
						__( 'Profile picture of %s', 'buddypress' ),
						bp_get_displayed_user_fullname()
					),
				) );
				$bp->bp_options_title = bp_get_displayed_user_fullname();
			}
		}

		parent::setup_title();
	}

	/**
	 * Setup cache groups.
	 *
	 * @since 2.2.0
	 */
	public function setup_cache_groups() {

		// Global groups.
		wp_cache_add_global_groups( $this->isf ? array(
				'bp_friend_requests',
				'bp_friend_friendships',
				'bp_friend_friendships_for_user'
			) : array(
				'bp_engagement_requests',
				'bp_engagement_engagementships', // Individual relationship objects are cached here by ID.
				'bp_engagement_engagementships_for_user' // All relationship IDs for a single user.
			)
	 	);

		parent::setup_cache_groups();
	}

	/**
	 * Init the BP REST API.
	 *
	 * @since 6.0.0
	 *
	 * @param array $controllers Optional. See BP_Component::rest_api_init() for
	 *                           description.
	 */
	public function rest_api_init( $controllers = array() ) {
		$endpoint = $this->isf ? 'BP_REST_Friend_Endpoint' : 'BP_REST_Engagement_Endpoint';
		parent::rest_api_init( array( $endpoint ) );
	}

	/**
	 * Register the BP {$this->comps} Blocks.
	 *
	 * @since 9.0.0
	 * @since 12.0.0 Use the WP Blocks API v2.
	 *
	 * @param array $blocks Optional. See BP_Component::blocks_init() for
	 *                      description.
	 */
	public function blocks_init( $blocks = array() ) {
		parent::blocks_init(
			array(
				$this->bpPaths => array(
					'metadata'        => trailingslashit( buddypress()->plugin_dir ) . "{$this->bpComps}/blocks/dynamic-{$this->comps}",
					'render_callback' => "{$this->bpComps}_render_{$this->comps}_block",
				),
			)
		);
	}
}
