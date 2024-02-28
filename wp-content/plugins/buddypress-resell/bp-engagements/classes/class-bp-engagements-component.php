<?php
/**
 * BuddyPress engagements Streams Loader.
 *
 * The engagements component is for users to create relationships with each other.
 *
 * @package BuddyPress
 * @subpackage engagementsComponent
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the BuddyPress engagements Component.
 *
 * @since 1.5.0
 */
#[AllowDynamicProperties]
class BP_Engagements_Component extends BP_Component {

	/**
	 * Start the engagements component creation process.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {
		
		parent::start(
			'engagements',
			_x( 'engagement Connections', 'engagements screen page <title>', 'buddypress' ),
			buddypress()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 60,
			)
		);
		$this->path = constant( 'BP_RESELL_DIR' ). '/';
	}

	/**
	 * Include bp-engagements files.
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

		// engagements.
		// Authenticated actions.
		if ( is_user_logged_in() && bp_current_action() == 'add-engagement') {
			require_once $this->path . 'bp-engagements/actions/add-engagement.php';
		}

		if ( is_user_logged_in() && bp_current_action() == 'remove-engagement') {
			require_once $this->path . 'bp-engagements/actions/remove-engagement.php';
		}

		// User nav.
		require_once $this->path . 'bp-engagements/screens/my-engagements.php';
		if ( is_user_logged_in() && bp_is_user_engagement_requests() ) {
			require_once $this->path . 'bp-engagements/screens/requests.php';
		}
	}

	/**
	 * Set up bp-engagements global settings.
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

		// Global tables for the engagements component.
		$global_tables = array(
			'table_name'      => $bp->table_prefix . 'bp_engagements',
			'table_name_meta' => $bp->table_prefix . 'bp_engagements_meta',
		);

		// All globals for the engagements component.
		// Note that global_tables is included in this array.
		$args = array(
			'slug'                  => $default_slug,
			'has_directory'         => false,
			'search_string'         => __( 'Search engagements...', 'buddypress' ),
			'notification_callback' => 'engagements_format_notifications',
			'global_tables'         => $global_tables,
			'block_globals'         => array(
				'bp/engagements' => array(
					'widget_classnames' => array( 'widget_bp_core_engagements_widget', 'buddypress' ),
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
	 * @param array $main_nav Optional. See `BP_Component::register_nav()` for
	 *                        description.
	 * @param array $sub_nav  Optional. See `BP_Component::register_nav()` for
	 *                        description.
	 */
	public function register_nav( $main_nav = array(), $sub_nav = array() ) {
		$slug   = bp_get_engagements_slug();

		$main_nav = array(
			'name'                => __( 'Engagements', 'buddypress' ),
			'slug'                => $slug,
			'position'            => 60,
			'screen_function'     => 'engagements_screen_my_engagements',
			'default_subnav_slug' => 'my-engagements',
			'item_css_id'         => $this->id,
		);

		// Add the subnav items to the engagements nav item.
		$sub_nav[] = array(
			'name'            => _x( 'Engagementships', 'engagements screen sub nav', 'buddypress' ),
			'slug'            => 'my-engagements',
			'parent_slug'     => $slug,
			'screen_function' => 'engagements_screen_my_engagements',
			'position'        => 10,
			'item_css_id'     => 'engagements-my-engagements',
		);

		$sub_nav[] = array(
			'name'                     => _x( 'Requests', 'engagements screen sub nav', 'buddypress' ),
			'slug'                     => 'requests',
			'parent_slug'              => $slug,
			'screen_function'          => 'engagements_screen_requests',
			'position'                 => 20,
			'user_has_access'          => false,
			'user_has_access_callback' => 'bp_core_can_edit_settings',
		);

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
		// Only grab count if we're on a user page.
		if ( bp_is_user() && isset( $this->main_nav['name'] ) ) {
			// Add 'engagements' to the main navigation.
			$count                  = (int) engagements_get_total_engagement_count();
			$class                  = ( 0 === $count ) ? 'no-count' : 'count';
			$this->main_nav['name'] = sprintf(
				/* translators: %s: engagement count for the current user */
				__( 'Engagements %s', 'buddypress' ),
				sprintf(
					'<span class="%s">%s</span>',
					esc_attr( $class ),
					esc_html( $count )
				)
			);
		}

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up bp-engagements integration with the WordPress admin bar.
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

			// Setup the logged in user variables.
			$engagements_slug = bp_get_engagements_slug();

			// Pending engagement requests.
			$count = count( engagements_get_engagementship_request_user_ids( bp_loggedin_user_id() ) );

			if ( ! empty( $count ) ) {
				$title = sprintf(
					/* translators: %s: Pending engagement request count for the current user */
					_x( 'Engagements %s', 'My Account engagements menu', 'buddypress' ),
					'<span class="count">' . bp_core_number_format( $count ) . '</span>'
				);
				$pending = sprintf(
					/* translators: %s: Pending engagement request count for the current user */
					_x( 'Pending Requests %s', 'My Account engagements menu sub nav', 'buddypress' ),
					'<span class="count">' . bp_core_number_format( $count ) . '</span>'
				);
			} else {
				$title   = _x( 'Engagements', 'My Account engagements menu', 'buddypress' );
				$pending = _x( 'No Pending Requests', 'My Account engagements menu sub nav', 'buddypress' );
			}

			// Add the "My Account" sub menus.
			$wp_admin_nav[] = array(
				'parent' => buddypress()->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => $title,
				'href'   => bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug ) ) ),
			);

			// My engagements.
			$wp_admin_nav[] = array(
				'parent'   => 'my-account-' . $this->id,
				'id'       => 'my-account-' . $this->id . '-engagementships',
				'title'    => _x( 'engagementships', 'My Account engagements menu sub nav', 'buddypress' ),
				'href'     => bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'my-engagements' ) ) ),
				'position' => 10,
			);

			// Requests.
			$wp_admin_nav[] = array(
				'parent'   => 'my-account-' . $this->id,
				'id'       => 'my-account-' . $this->id . '-requests',
				'title'    => $pending,
				'href'     => bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'requests' ) ) ),
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

		// Adjust title.
		if ( bp_is_engagements_component() ) {
			$bp = buddypress();

			if ( bp_is_my_profile() ) {
				$bp->bp_options_title = __( 'Engagementships', 'buddypress' );
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
		wp_cache_add_global_groups( array(
			'bp_engagement_requests',
			'bp_engagement_engagementships', // Individual engagementship objects are cached here by ID.
			'bp_engagement_engagementships_for_user' // All engagementship IDs for a single user.
		) );

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
		parent::rest_api_init( array( 'BP_REST_Engagement_Endpoint' ) );
	}

	/**
	 * Register the BP engagements Blocks.
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
				'bp/engagements' => array(
					'metadata'        => trailingslashit( constant( 'BP_RESELL_DIR' ) ) . 'bp-engagements/blocks/dynamic-engagements',
					'render_callback' => 'bp_engagements_render_engagements_block',
				),
			)
		);
	}
}
