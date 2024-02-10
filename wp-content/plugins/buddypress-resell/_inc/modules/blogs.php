<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Resell Blogs Loader.
 *
 * @since 1.3.0
 */
function bp_resell_blogs_init() {
	$bp = $GLOBALS['bp'];

	$bp->resell->blogs = new BP_Resell_Blogs();

	do_action( 'bp_resell_blogs_loaded' );
}
add_action( 'bp_resell_loaded', 'bp_resell_blogs_init' );

/**
 * Resell Blogs module.
 *
 * @since 1.3.0
 */
class BP_Resell_Blogs {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// includes.
		$this->includes();

		// component hooks.
		add_action( 'bp_resell_setup_globals', array( $this, 'constants' ) );
		add_action( 'bp_resell_setup_globals', array( $this, 'setup_global_cachegroups' ) );
		add_action( 'bp_resell_setup_nav',     array( $this, 'setup_nav' ) );
		add_action( 'bp_activity_admin_nav',   array( $this, 'activity_admin_nav' ) );
		add_filter( 'bp_blogs_admin_nav',      array( $this, 'blogs_admin_nav' ) );

		// screen hooks.
		add_action( 'bp_after_member_blogs_content', 'BP_Resell_Blogs_Screens::user_blogs_inline_js' );
		add_action( 'bp_actions',                    'BP_Resell_Blogs_Screens::action_handler' );
		add_action( 'bp_actions',                    'BP_Resell_Blogs_Screens::rss_handler' );
		add_action( 'wp_ajax_bp_resell_blogs',       'BP_Resell_Blogs_Screens::ajax_handler' );

		// directory tabs.
		add_action( 'bp_before_activity_type_tab_favorites', array( $this, 'add_activity_directory_tab' ) );
		add_action( 'bp_blogs_directory_blog_types',         array( $this, 'add_blog_directory_tab' ) );

		// loop filtering.
		add_filter( 'bp_activity_set_resellblogs_scope_args', array( $this, 'filter_activity_scope' ), 10, 2 );
		add_filter( 'bp_ajax_querystring', array( $this, 'add_blogs_scope_filter' ),    20, 2 );
		add_filter( 'bp_has_blogs',        array( $this, 'bulk_inject_blog_resell_status' ) );

		// button injection.
		add_action( 'bp_directory_blogs_actions', array( $this, 'add_resell_button_to_loop' ),   20 );
		add_action( 'wp_footer',                  array( $this, 'add_resell_button_to_footer' ) );
		add_action( 'wp_enqueue_scripts',         array( $this, 'enqueue_script' ) );

		// blog deletion.
		add_action( 'bp_blogs_remove_blog', array( $this, 'on_blog_delete' ) );

		// cache invalidation.
		add_action( 'bp_resell_start_reselling_blogs', array( $this, 'clear_cache_on_resell' ) );
		add_action( 'bp_resell_stop_reselling_blogs',  array( $this, 'clear_cache_on_resell' ) );
		add_action( 'bp_resell_before_remove_data',    array( $this, 'clear_cache_on_user_delete' ) );

		// rss feed link.
		add_filter( 'bp_get_sitewide_activity_feed_link', array( $this, 'activity_feed_url' ) );
		add_filter( 'bp_dtheme_activity_feed_url',        array( $this, 'activity_feed_url' ) );
		add_filter( 'bp_legacy_theme_activity_feed_url',  array( $this, 'activity_feed_url' ) );
	}

	/**
	 * Includes.
	 */
	protected function includes() {
		$bp = $GLOBALS['bp'];

		if ( ! class_exists( 'BP_Activity_Query' ) ) {
			require( $bp->resell->path . '/modules/blogs-backpat.php' );
		}
	}

	/**
	 * Constants.
	 */
	public function constants() {
		// /members/admin/blogs/[RESELLING]
		if ( ! defined( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG' ) ) {
			define( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG', constant( 'BP_RESELLING_SLUG' ) );
		}

		// /members/admin/activity/[RESELLBLOGS]
		if ( ! defined( 'BP_RESELL_BLOGS_USER_ACTIVITY_SLUG' ) ) {
			define( 'BP_RESELL_BLOGS_USER_ACTIVITY_SLUG', 'resellblogs' );
		}
	}

	/**
	 * Set up global cachegroups.
	 */
	public function setup_global_cachegroups() {
		$bp = $GLOBALS['bp'];

		// blog counts.
		$bp->resell->global_cachegroups[] = 'bp_resell_user_blogs_reselling_count';
		$bp->resell->global_cachegroups[] = 'bp_resell_blogs_resellers_count';

		// blog data query.
		$bp->resell->global_cachegroups[] = 'bp_resell_user_blogs_reselling_query';
		$bp->resell->global_cachegroups[] = 'bp_resell_blogs_resellers_query';
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

		bp_core_new_subnav_item( array(
			'name'            => _x( 'Reselled Sites', 'Sites subnav tab', 'buddypress-resellers' ),
			'slug'            => constant( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG' ),
			'parent_url'      => bp_resell_get_user_url( $user_id, array( bp_get_blogs_slug() ) ),
			'parent_slug'     => bp_get_blogs_slug(),
			'screen_function' => 'BP_Resell_Blogs_Screens::user_blogs_screen',
			'position'        => 20,
			'item_css_id'     => 'blogs-reselling',
		) );

		// Add activity sub nav item
		if ( bp_is_active( 'activity' ) && apply_filters( 'bp_resell_blogs_show_activity_subnav', true ) ) {
			bp_core_new_subnav_item( array(
				'name'            => _x( 'Reselled Sites', 'Activity subnav tab', 'buddypress-resellers' ),
				'slug'            => constant( 'BP_RESELL_BLOGS_USER_ACTIVITY_SLUG' ),
				'parent_url'      => bp_resell_get_user_url( $user_id, array( bp_get_activity_slug() ) ),
				'parent_slug'     => bp_get_activity_slug(),
				'screen_function' => 'BP_Resell_Blogs_Screens::user_activity_screen',
				'position'        => 22,
				'item_css_id'     => 'activity-resellblogs',
			) );
		}
	}

	/**
	 * Inject "Reselled Sites" nav item to WP adminbar's "Activity" main nav.
	 *
	 * @param array $retval Return Value.
	 * @return array
	 */
	public function activity_admin_nav( $retval ) {
		if ( ! is_user_logged_in() ) {
			return $retval;
		}

		if ( bp_is_active( 'activity' ) && apply_filters( 'bp_resell_show_activity_subnav', true ) ) {
			$new_item = array(
				'parent' => 'my-account-activity',
				'id'     => 'my-account-activity-resellblogs',
				'title'  => _x( 'Reselled Sites', 'Adminbar activity subnav', 'buddypress-resellers' ),
				'href'   => bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELL_BLOGS_USER_ACTIVITY_SLUG' ) ) ),
			);

			$inject = array();
			$offset = 4;

			$inject[ $offset ] = $new_item;
			$retval = array_merge(
				array_slice( $retval, 0, $offset, true ),
				$inject,
				array_slice( $retval, $offset, NULL, true )
			);
		}

		return $retval;
	}

	/**
	 * Inject "Reselled Sites" nav item to WP adminbar's "Sites" main nav.
	 *
	 * @param array $retval Return Value.
	 * @return array
	 */
	public function blogs_admin_nav( $retval ) {
		if ( ! is_user_logged_in() ) {
			return $retval;
		}

		$new_item = array(
			'parent' => 'my-account-blogs',
			'id'     => 'my-account-blogs-reselling',
			'title'  => _x( 'Reselled Sites', 'Adminbar blogs subnav', 'buddypress-resellers' ),
			'href'   => bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_blogs_slug(), constant( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG' ) ) ),
		);

		$inject = array();
		$last   = end( $retval );

		// inject item in between "My Sites" and "Create a Site" subnav items.
		if ( 'my-account-blogs-create' === $last['id'] ) {
			$offset = key( $retval );

			$inject[ $offset ] = $new_item;

			$retval = array_merge( array_slice( $retval, 0, $offset, true ), $inject, array_slice( $retval, $offset, NULL, true ) );

		// "Create a Site" is disabled; just add nav item to the end
		} else {
			$inject = array();
			$inject[] = $new_item;
			$retval = array_merge( $retval, $inject );
		}

		return $retval;
	}

	/** DIRECTORY TABS ************************************************/

	/**
	 * Adds a "Reselled Sites (X)" tab to the activity directory.
	 *
	 * This is so the logged-in user can filter the activity stream to only sites
	 * that the current user is reselling.
	 */
	public function add_activity_directory_tab() {
		$counts = bp_resell_total_resell_counts( array(
			'user_id'     => bp_loggedin_user_id(),
			'resell_type' => 'blogs',
		) );

		/*
		if ( empty( $counts['reselling'] ) ) {
			return false;
		}
		*/

		$reselling_blog_url = bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_blogs_slug(), constant( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG' ) ) );
		?>
		<li id="activity-resellblogs"><a href="<?php echo esc_url( $reselling_blog_url ); ?>"><?php printf( esc_html__( 'Reselled Sites %s', 'buddypress-resellers' ), '<span>' . esc_html( bp_core_number_format( $counts['reselling'] ) ) . '</span>' ); ?></a></li><?php
	}


	/**
	 * Add a "Reselling (X)" tab to the sites directory.
	 *
	 * This is so the logged-in user can filter the site directory to only
	 * sites that the current user is reselling.
	 */
	function add_blog_directory_tab() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$counts = bp_resell_total_resell_counts( array(
			'user_id'     => bp_loggedin_user_id(),
			'resell_type' => 'blogs',
		) );

		if ( empty( $counts['reselling'] ) ) {
			return false;
		}

		$reselling_blog_url = bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_blogs_slug(), constant( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG' ) ) );
		?>
		<li id="blogs-reselling"><a href="<?php echo esc_url( $reselling_blog_url ); ?>"><?php printf( esc_html__( 'Reselling %s', 'buddypress-resellers' ), '<span>' . esc_html( bp_core_number_format( $counts['reselling'] ) ) . '</span>' ); ?></a></li><?php
	}

	/** LOOP-FILTERING ************************************************/

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
	function filter_activity_scope( $retval = array(), $filter = array() ) {
		$bp = $GLOBALS['bp'];

		// Determine the user_id.
		if ( ! empty( $filter['user_id'] ) ) {
			$user_id = $filter['user_id'];
		} else {
			$user_id = bp_displayed_user_id()
				? bp_displayed_user_id()
				: bp_loggedin_user_id();
		}

		// Get blogs that the user is reselling.
		$reselling_ids = bp_resell_get_reselling( array(
			'user_id'     => $user_id,
			'resell_type' => 'blogs',
		) );
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

		// support BP Groupblog.
		if ( function_exists( 'bp_groupblog_init' ) && array( 0 ) !== $reselling_ids ) {
			global $wpdb;

			$bp = $GLOBALS['bp'];

			// comma-delimit the blog IDs.
			$delimited_ids = implode( ',', $reselling_ids );
			$group_ids_connected_to_blogs = $wpdb->get_col( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'groupblog_blog_id' AND meta_value IN ( " . $delimited_ids . " )" );

			$clause = array(
				'relation' => 'OR',

				// general blog activity items.
				array(
					'relation' => 'AND',
					array(
						'column' => 'component',
						'value'  => $bp->blogs->id,
					),
					array(
						'column'  => 'item_id',
						'compare' => 'IN',
						'value'   => (array) $reselling_ids,
					),
				),

				// groupblog posts.
				array(
					'relation' => 'AND',
					array(
						'column' => 'component',
						'value'  => $bp->groups->id,
					),
					array(
						'column'  => 'item_id',
						'compare' => 'IN',
						'value'   => (array) $group_ids_connected_to_blogs,
					),
					array(
						'column'  => 'type',
						'value'   => 'new_groupblog_post',
					),
				),
			);

		// Regular resell blog clause
		} else {
			$clause = array(
				'relation' => 'AND',
				array(
					'column' => 'component',
					'value'  => $bp->blogs->id,
				),
				array(
					'column'  => 'item_id',
					'compare' => 'IN',
					'value'   => (array) $reselling_ids,
				),
			);
		}

		$retval = array(
			'relation' => 'AND',
			$clause,
			$show_hidden,

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

	/**
	 * Filter the blogs loop.
	 *
	 * Specifically, filter when we're on:
	 *  - a user's "Reselled Sites" page
	 *  - the Sites directory and clicking on the "Reselling" tab
	 *
	 * @param str $qs The querystring for the BP loop.
	 * @param str $object The current object for the querystring.
	 * @return str Modified querystring
	 */
	function add_blogs_scope_filter( $qs, $object ) {
		// not on the blogs object? stop now!
		if ( 'blogs' !== $object ) {
			return $qs;
		}

		// parse querystring into an array.
		$r = wp_parse_args( $qs );

		// set scope if a user is on a user's "Reselled Sites" page.
		if ( bp_is_user_blogs() && bp_is_current_action( constant( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG' ) ) ) {
			$r['scope'] = 'reselling';
		}

		if ( empty( $r['scope'] ) || 'reselling' !== $r['scope'] ) {
			return $qs;
		}

		// get blog IDs that the user is reselling.
		$reselling_ids = bp_get_reselling_ids( array(
			'user_id'     => bp_displayed_user_id() ? bp_displayed_user_id() : bp_loggedin_user_id(),
			'resell_type' => 'blogs',
		) );

		// if $reselling_ids is empty, pass the largest bigint(20) value to ensure
		// no blogs are matched.
		$reselling_ids = empty( $reselling_ids ) ? '18446744073709551615' : $reselling_ids;

		$args = array(
			'user_id'          => 0,
			'include_blog_ids' => $reselling_ids,
		);

		// make sure we add a separator if we have an existing querystring.
		if ( ! empty( $qs ) ) {
			$qs .= '&';
		}

		// add our resell parameters to the end of the querystring.
		$qs .= build_query( $args );

		return $qs;
	}

	/**
	 * Bulk-check the resell status of all blogs in a blogs loop.
	 *
	 * This is so we don't have query each resell blog status individually.
	 */
	public function bulk_inject_blog_resell_status( $has_blogs ) {
		global $blogs_template;

		if ( empty( $has_blogs ) ) {
			return $has_blogs;
		}

		if ( ! is_user_logged_in() ) {
			return $has_blogs;
		}

		$blog_ids = array();

		foreach ( (array) $blogs_template->blogs as $i => $blog ) {
			// add blog ID to array.
			$blog_ids[] = $blog->blog_id;

			// set default resell status to false.
			$blogs_template->blogs[ $i ]->is_reselling = false;
		}

		if ( empty( $blog_ids ) ) {
			return $has_blogs;
		}

		$reselling = BP_Resell::bulk_check_resell_status( $blog_ids, bp_loggedin_user_id(), 'blogs' );

		if ( empty( $reselling ) ) {
			return $has_blogs;
		}

		foreach ( (array) $reselling as $is_reselling ) {
			foreach ( (array) $blogs_template->blogs as $i => $blog ) {
				// set resell status to true if the logged-in user is reselling.
				if ( (int) $is_reselling->leader_id === (int) $blog->blog_id ) {
					$blogs_template->blogs[ $i ]->is_reselling = true;
				}
			}
		}

		return $has_blogs;
	}

	/** BUTTON ********************************************************/

	/**
	 * Registers and enqueues our JS.
	 */
	public function enqueue_script() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Register our script.
		wp_register_script( 'bp-resell-blogs', BP_RESELL_URL . '_inc/modules/blogs.js', [ 'jquery' ], false, true );

		// Nouveau requires early enqueuing on BP blog pages.
		if ( function_exists( 'bp_nouveau' ) && bp_is_blogs_component() ) {
			wp_enqueue_script( 'bp-resell-blogs' );
		}
	}

	/**
	 * Add a resell button to the blog loop.
	 */
	public function add_resell_button_to_loop() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		echo self::get_button();
	}

	/**
	 * Whether to show the blog footer buttons.
	 *
	 * @return bool Defaults to true. False when on BP root blog and not on a blog
	 *         page deemed by BuddyPress.
	 */
	public static function show_footer_button() {
		$retval = true;

		// @todo might need to tweak this a bit...
		if ( bp_is_root_blog() && ! bp_is_blog_page() ) {
			$retval = false;
		}

		// Bail if JSON request. Mostly for block widget render calls.
		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			$retval = false;
		}

		return apply_filters( 'bp_resell_blogs_show_footer_button', $retval );
	}

	/**
	 * Add a resell button to the footer.
	 *
	 * Also adds a "Home" link, which links to the activity directory's "Sites I
	 * Resell" tab.
	 *
	 * This UI mimics Tumblr's.
	 */
	public function add_resell_button_to_footer() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		// If blog is not recordable, do not show button.
		if ( ! bp_blogs_is_blog_recordable( get_current_blog_id(), bp_loggedin_user_id() ) ) {
			return;
		}

		// disable the footer button using this filter if needed.
		if ( false === self::show_footer_button() ) {
			return;
		}

		// remove inline CSS later... still testing.
	?>

		<style type="text/css">
			#bpf-blogs-ftr{
				position:fixed;
				bottom:5px;
				right: 5px;
				z-index:9999;
				text-align:right;
			}

			#bpf-blogs-ftr a {
				font: 600 12px/18px "Helvetica Neue","HelveticaNeue",Helvetica,Arial,sans-serif !important;
				color: #fff !important;
				text-decoration:none !important;
				background:rgba(0, 0, 0, 0.48);
				padding:2px 5px !important;
				border-radius: 4px;
			}
			#bpf-blogs-ftr a:hover {
				background:rgba(0, 0, 0, 0.42);
			}

			#bpf-blogs-ftr a:before {
				position: relative;
				top: 3px;
				font: normal 13px/1 'dashicons';
				padding-right:5px;
			}

			#bpf-blogs-ftr a.resell:before {
				content: "\f132";
			}

			#bpf-blogs-ftr a.stop_resell:before {
				content: "\f460";
			}

			#bpf-blogs-ftr a.home:before {
				content: "\f155";
				top: 2px;
			}
		</style>

		<div id="bpf-blogs-ftr">
			<?php echo self::get_button( array(
				'leader_id'     => get_current_blog_id(),
				'resell_text'   => _x( 'Resell Site', 'Button', 'buddypress-resellers' ),
				'stop_resell_text' => _x( 'Stop-Resell Site', 'Button', 'buddypress-resellers' ),
				'wrapper'       => false,
			) ); ?>

 			<?php
 				$btn_args = apply_filters( 'bp_resell_blogs_get_sites_button_args', array(
 					'class' => 'home',
 					'link' => bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_blogs_slug(), constant( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG' ) ) ),
 					'text' => _x( 'Reselled Sites', 'Footer button', 'buddypress-resellers' ),
 				) );

				if ( ! empty( $btn_args ) && is_array( $btn_args ) ) {
					echo '<a class=' . esc_attr( $btn_args['class'] ) . ' href=' . esc_url( $btn_args['link'] ) . '>';
					echo $btn_args['text'];
					echo '</a>';
				}
 			?>
		</div>

	<?php
	}

	/**
	 * Static method to generate a resell blogs button.
	 */
	public static function get_button( $args = '' ) {
		global $blogs_template;

		$r = wp_parse_args( $args, array(
			'leader_id'     => ! empty( $blogs_template->in_the_loop ) ? bp_get_blog_id() : get_current_blog_id(),
			'reseller_id'   => bp_loggedin_user_id(),
			'resell_text'   => _x( 'Resell', 'Button', 'buddypress-resellers' ),
			'stop_resell_text' => _x( 'Stop-Resell', 'Button', 'buddypress-resellers' ),
			'link_text'     => '',
			'link_title'    => '',
			'wrapper_class' => 'blog-button',
			'link_class'    => '',
			'button_attr'   => [],
			'wrapper'       => 'div',
		) );

		if ( ! $r['leader_id'] || ! $r['reseller_id'] ) {
			return false;
		}

		// Enqueue JS only if BP 2.7+.
		if ( class_exists( 'BP_Core_HTML_Element' ) ) {
			wp_enqueue_script( 'bp-resell-blogs' );
		}

		// if we're checking during a blog loop, then resell status is already
		// queried via bulk_inject_resell_blog_status()
		if ( ! empty( $blogs_template->in_the_loop ) && bp_loggedin_user_id() === $r['reseller_id'] && bp_get_blog_id() === $r['leader_id'] ) {
			$is_reselling = $blogs_template->blog->is_reselling;

		// else we manually query the resell status
		} else {
			$is_reselling = bp_resell_is_reselling( array(
				'leader_id'   => $r['leader_id'],
				'reseller_id' => $r['reseller_id'],
				'resell_type' => 'blogs',
			) );
		}

		$button_attr = [
			'data-resell-blog-id' => $r['leader_id'],
		];

		// setup some variables.
		if ( $is_reselling ) {
			$id        = 'reselling';
			$action    = 'stop_resell';

			if ( empty( $r['link_text'] ) ) {
				$r['link_text'] = $r['stop_resell_text'];
			}

		} else {
			$id        = 'not-reselling';
			$action    = 'resell';

			if ( empty( $r['link_text'] ) ) {
				$r['link_text'] = $r['resell_text'];
			}
		}

		$button_attr['data-resell-action'] = $action;
		$button_attr['data-resell-nonce']  = wp_create_nonce( "bp_resell_blog_{$action}" );
		$button_attr['data-resell-text']   = $r['resell_text'];
		$button_attr['data-stop_resell-text'] = $r['stop_resell_text'];

		$wrapper_class = 'resell-button ' . $id;

		if ( ! empty( $r['wrapper_class'] ) ) {
			$wrapper_class .= ' ' . esc_attr( $r['wrapper_class'] );
		}

		$link_class = $action;

		if ( ! empty( $r['link_class'] ) ) {
			$link_class .= ' ' . esc_attr( $r['link_class'] );
		}

		// setup the button arguments.
		$button = array(
			'id'                => $id,
			'component'         => 'resell',
			'must_be_logged_in' => true,
			'block_self'        => false,
			'wrapper_class'     => $wrapper_class,
			'wrapper_id'        => 'resell-button-' . (int) $r['leader_id'],
			'link_href'         => wp_nonce_url(
				add_query_arg( 'blog_id', $r['leader_id'], home_url( '/' ) ),
				"bp_resell_blog_{$action}",
				"bpfb-{$action}"
			),
			'link_text'         => esc_attr( $r['link_text'] ),
			'link_title'        => esc_attr( $r['link_title'] ),
			'link_id'           => $action . '-' . (int) $r['leader_id'],
			'link_class'        => $link_class,
			'wrapper'           => ! empty( $r['wrapper'] ) ? esc_attr( $r['wrapper'] ) : false,
			'button_attr'       => $button_attr
		);

		// BP Nouveau-specific button arguments.
		if ( function_exists( 'bp_nouveau' ) && ! empty( $blogs_template->in_the_loop ) ) {
			$button['parent_element'] = 'li';
			$button['wrapper_class']  = '';
			$button['link_class']    .= ' button';
		}

		// Filter and return the HTML button.
		return bp_get_button( apply_filters( 'bp_resell_blogs_get_resell_button', $button, $r, $is_reselling ) );
	}

	/** DELETION ***********************************************************/

	/**
	 * Do stuff when a blog is deleted.
	 *
	 * @param int $blog_id The ID of the blog being deleted.
	 */
	public function on_blog_delete( $blog_id ) {
		global $wpdb;

		$bp = $GLOBALS['bp'];

		$this->clear_cache_on_blog_delete( $blog_id );

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->resell->table_name} WHERE leader_id = %d AND resell_type = 'blogs'", $blog_id ) );
	}

	/**
	 * Save routine.
	 *
	 * @param array $r {
	 *     An array of arguments.
	 *     @type string $action  Type of resell action. Either 'resell' or 'stop_resell'.
	 *     @type int    $blog_id Blog ID to resell or stop_resell.
	 *     @type int    $user_id User ID initiating the resell request.
	 *     @type string $nonce   Nonce for the resell request.
	 * }
	 * @return bool|WP_Error Boolean true on success; WP_Error on failure.
	 */
	public static function save( $r = [] ) {
		if ( empty( $r['action'] ) || empty( $r['nonce'] ) || empty( $r['blog_id'] ) ) {
			return new WP_Error( 'empty', __( 'Missing required arguments', 'buddypress-resellers' ) );
		}

		$action  = 'resell';
		$save    = 'bp_resell_start_reselling';
		$blog_id = (int) $r['blog_id'];
		$user_id = ! empty( $r['user_id'] ) ? (int) $r['user_id'] : bp_loggedin_user_id();

		if ( empty( $user_id ) ) {
			return new WP_Error( 'no_user_id', __( 'Missing user ID', 'buddypress-resellers' ) );
		}

		if ( 'stop_resell' === $r['action'] ) {
			$action = 'stop_resell';
			$save   = 'bp_resell_stop_reselling';
		}

		if ( ! wp_verify_nonce( $r['nonce'], "bp_resell_blog_{$action}" ) ) {
			return new WP_Error( 'nonce', __( 'Nonce failure', 'buddypress-resellers' ) );
		}

		if ( ! $save( array(
			'leader_id'   => $blog_id,
			'reseller_id' => $user_id,
			'resell_type' => 'blogs',
		) ) ) {
			if ( 'resell' === $action ) {
				$error_code = 'already_reselling';
				$message    = __( 'You are already reselling that blog.', 'buddypress-resellers' );

				if ( (int) $user_id !== bp_loggedin_user_id() ) {
					$message = sprintf( __( '%s is already reselling that blog.', 'buddypress-resellers' ), bp_core_get_user_displayname( $user_id ) );
				}
			} else {
				$error_code = 'not_reselling';
				$message    = __( 'You are not reselling that blog.', 'buddypress-resellers' );

				if ( (int) $user_id !== bp_loggedin_user_id() ) {
					$message = sprintf( __( '%s is not reselling that blog.', 'buddypress-resellers' ), bp_core_get_user_displayname( $user_id ) );
				}
			}

			return new WP_Error( $error_code, $message );

		// success on resell action
		} else {
			$blog_name = bp_blogs_get_blogmeta( $blog_id, 'name' );

			// blog has never been recorded into BP; record it now.
			if ( '' === $blog_name && apply_filters( 'bp_resell_blogs_record_blog', true, $blog_id ) ) {
				// get the admin of the blog.
				$admin = get_users( array(
					'blog_id' => $blog_id,
					'role'    => 'administrator',
					'orderby' => 'ID',
					'number'  => 1,
					'fields'  => array( 'ID' ),
				) );

				// record the blog.
				bp_blogs_record_blog( $blog_id, $admin[0]->ID, true );
			}

			return true;
		}
	}

	/** CACHE **************************************************************/

	/**
	 * Clear count cache when a user resells / unfolows a blog.
	 *
	 * @param BP_Resell $resell
	 */
	public function clear_cache_on_resell( BP_Resell $resell ) {
		// clear resellers count for blog.
		wp_cache_delete( $resell->leader_id,   'bp_resell_blogs_resellers_count' );

		// clear reselling blogs count for user.
		wp_cache_delete( $resell->reseller_id, 'bp_resell_user_blogs_reselling_count' );

		// clear queried resellers / reselling.
		wp_cache_delete( $resell->leader_id,   'bp_resell_blogs_resellers_query' );
		wp_cache_delete( $resell->reseller_id, 'bp_resell_user_blogs_reselling_query' );

		// clear resell relationship.
		wp_cache_delete( "{$resell->leader_id}:{$resell->reseller_id}:blogs", 'bp_resell_data' );
	}

	/**
	 * Clear blog count cache when a user is deleted.
	 *
	 * @param int $user_id The user ID being deleted
	 */
	public function clear_cache_on_user_delete( $user_id = 0 ) {
		// delete user's blog resell count.
		wp_cache_delete( $user_id, 'bp_resell_user_blogs_reselling_count' );

		// delete queried blogs that user was reselling.
		wp_cache_delete( $user_id, 'bp_resell_user_blogs_reselling_query' );

		// delete each blog's resellers count that the user was reselling.
		$blogs = BP_Resell::get_reselling( $user_id, 'blogs' );
		if ( ! empty( $blogs ) ) {
			foreach ( $blogs as $blog_id ) {
				wp_cache_delete( $blog_id, 'bp_resell_blogs_resellers_count' );

				// clear resell relationship.
				wp_cache_delete( "{$blog_id}:{$user_id}:blogs", 'bp_resell_data' );
			}
		}
	}

	/**
	 * Clear blog count cache when a blog is deleted.
	 *
	 * @param int $blog_id The ID of the blog being deleted
	 */
	public function clear_cache_on_blog_delete( $blog_id ) {
		// clear resellers count for blog.
		wp_cache_delete( $blog_id, 'bp_resell_blogs_resellers_count' );

		// clear queried resellers for blog.
		wp_cache_delete( $blog_id, 'bp_resell_blogs_resellers_query' );

		// delete each user's blog reselling count for those that reselled the blog.
		$users = BP_Resell::get_resellers( $blog_id, 'blogs' );
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				wp_cache_delete( $user, 'bp_resell_user_blogs_reselling_count' );

				// clear resell relationship.
				wp_cache_delete( "{$blog_id}:{$user}:blogs", 'bp_resell_data' );
			}
		}
	}

	/** FEED URL ***********************************************************/

	/**
	 * Sets the "RSS" feed URL for the tab on the Sitewide Activity page.
	 *
	 * This occurs when the "Reselled Sites" tab is clicked on the Sitewide
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

		// this is done b/c we're filtering 'bp_get_sitewide_activity_feed_link' and
		// we only want to alter the feed link for the "RSS" tab.
		if ( ! defined( 'DOING_AJAX' ) && ! did_action( 'bp_before_directory_activity' ) ) {
			return $retval;
		}

		// get the activity scope.
		$scope = ! empty( $_COOKIE['bp-activity-scope'] ) ? $_COOKIE['bp-activity-scope'] : false;

		if ( 'resellblogs' === $scope && bp_loggedin_user_id() ) {
			$retval = bp_resell_get_user_url( bp_loggedin_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELL_BLOGS_USER_ACTIVITY_SLUG' ), array( 'feed' ) ) );
		}

		return $retval;
	}
}

/**
 * Screen loader class for BP Resell Blogs.
 *
 * @since 1.3.0
 */
class BP_Resell_Blogs_Screens {

	/** SCREENS *******************************************************/

	/**
	 * Sets up the user blogs screen.
	 */
	public static function user_blogs_screen() {
		add_action( 'bp_template_content', array( __CLASS__, 'user_blogs_screen_content' ) );

		// this is for bp-default themes.
		bp_core_load_template( 'members/single/home' );
	}

	/**
	 * Content for the user blogs screen.
	 */
	public static function user_blogs_screen_content() {
		do_action( 'bp_before_member_blogs_content' );
	?>

		<div class="blogs resell-blogs" role="main">
			<?php bp_get_template_part( 'blogs/blogs-loop' ); ?>
		</div><!-- .blogs.resell-blogs -->

	<?php
		do_action( 'bp_after_member_blogs_content' );
	}

	/**
	 * Inline JS when on a user blogs page.
	 *
	 * We need to:
	 *  - Disable AJAX when clicking on a blogs subnav item (this is a BP bug)
	 *  - Add a reselling scope when AJAX is submitted
	 */
	public static function user_blogs_inline_js() {
		//jQuery("#blogs-personal-li").attr('id','blogs-reselling-personal-li');
	?>

		<script type="text/javascript">
		jQuery('#subnav a').on( 'click', function(event) {
			event.stopImmediatePropagation();
		});
		</script>

	<?php
	}

	/**
	 * Sets up the user activity screen.
	 *
	 * eg. /members/admin/activity/resellblogs/
	 */
	public static function user_activity_screen() {
		do_action( 'bp_resell_blogs_screen_user_activity' );

		// this is for bp-default themes.
		bp_core_load_template( 'members/single/home' );
	}

	/** ACTIONS *******************************************************/

	/**
	 * RSS handler for a user's reselled sites.
	 *
	 * When a user lands on /members/USERNAME/activity/resellblogs/feed/, this
	 * method generates the RSS feed for their reselled sites.
	 */
	public static function rss_handler() {
		// only available in BP 1.8+
		if ( ! class_exists( 'BP_Activity_Feed' ) ) {
			return;
		}

		if ( ! bp_is_user_activity() || ! bp_is_current_action( constant( 'BP_RESELL_BLOGS_USER_ACTIVITY_SLUG' ) ) || ! bp_is_action_variable( 'feed', 0 ) ) {
			return;
		}

		$bp = $GLOBALS['bp'];

		// get blog IDs that the user is reselling.
		$reselling_ids = bp_get_reselling_ids( array(
			'resell_type' => 'blogs',
		) );

		// if $reselling_ids is empty, pass a negative number so no blogs can be found.
		$reselling_ids = empty( $reselling_ids ) ? -1 : $reselling_ids;

		$args = array(
			'user_id'    => 0,
			'object'     => 'blogs',
			'primary_id' => $reselling_ids,
		);

		// setup the feed.
		$bp->activity->feed = new BP_Activity_Feed( array(
			'id'            => 'reselledsites',

			/* translators: User's reselling activity RSS title - "[Site Name] | [User Display Name] | Reselled Site Activity" */
			'title'         => sprintf( __( '%1$s | %2$s | Reselled Site Activity', 'buddypress-resellers' ), bp_get_site_name(), bp_get_displayed_user_fullname() ),

			'link'          => bp_resell_get_user_url( bp_displayed_user_id(), array( bp_get_activity_slug(), constant( 'BP_RESELL_BLOGS_USER_ACTIVITY_SLUG' ) ) ),
			'description'   => sprintf( __( "Activity feed for sites that %s is reselling.", 'buddypress' ), bp_get_displayed_user_fullname() ),
			'activity_args' => $args,
		) );
	}

	/**
	 * Action handler when a resell blogs button is clicked.
	 *
	 * Handles both reselling and stop reselling a blog.
	 */
	public static function action_handler() {
		if ( empty( $_GET['blog_id'] ) || ! is_user_logged_in() ) {
			return;
		}

		$action = false;

		if ( ! empty( $_GET['bpfb-resell'] ) || ! empty( $_GET['bpfb-stop_resell'] ) ) {
			$nonce   = ! empty( $_GET['bpfb-resell'] ) ? $_GET['bpfb-resell'] : $_GET['bpfb-stop_resell'];
			$action  = ! empty( $_GET['bpfb-resell'] ) ? 'resell' : 'stop_resell';
		}

		if ( ! $action ) {
			return;
		}

		$save = BP_Resell_Blogs::save( [
			'action' => $action,
			'nonce'  => $nonce,
			'blog_id' => (int) $_GET['blog_id']
		] );

		if ( is_wp_error( $save ) ) {
			if ( 'already_reselling' === $save->get_error_code() || 'not_reselling' === $save->get_error_code() ) {
				bp_core_add_message( $save->get_error_message(), 'error' );
			} else {
				return;
			}

		} else {
			$blog_name = bp_blogs_get_blogmeta( (int) $_GET['blog_id'], 'name' );

			if ( 'resell' === $action ) {
				if ( ! empty( $blog_name ) ) {
					$message = sprintf( __( 'You are now reselling the site, %s.', 'buddypress-resellers' ), $blog_name );
				} else {
					$message = __( 'You are now reselling that site.', 'buddypress-resellers' );
				}
			} else {
				if ( ! empty( $blog_name ) ) {
					$message = sprintf( __( 'You are no longer reselling the site, %s.', 'buddypress-resellers' ), $blog_name );
				} else {
					$message = __( 'You are no longer reselling that site.', 'buddypress-resellers' );
				}
			}

			bp_core_add_message( $message );
		}

		// it's possible that wp_get_referer() returns false, so let's fallback to the displayed user's page.
		$redirect = wp_get_referer() ? wp_get_referer() : bp_resell_get_user_url( bp_displayed_user_id(), array( bp_get_blogs_slug(), constant( 'BP_RESELL_BLOGS_USER_RESELLING_SLUG' ) ) );
		bp_core_redirect( $redirect );
	}

	/**
	 * AJAX handler.
	 */
	public static function ajax_handler() {
		$data = json_decode( stripslashes( $_POST['resellData'] ) );
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$save = BP_Resell_Blogs::save( [
			'action'  => $data->resellAction,
			'nonce'   => $data->resellNonce,
			'blog_id' => $data->resellBlogId
		] );

		// Error during resell action. Render invalid button for AJAX response.
		if ( is_wp_error( $save ) ) {
			$button = bp_get_button( [
				'id'        => 'invalid',
				'link_href' => 'javascript:;',
				'component' => 'resell',
				'wrapper'   => false,
				'link_text' => esc_html__( 'Error', 'buddypress-resellers' )
			] );

		// Success! Render resell button for AJAX response.
		} else {
			$button = BP_Resell_Blogs::get_button( [
				'leader_id'     => $data->resellBlogId,
				'resell_text'   => $data->resellText,
				'stop_resell_text' => $data->stop_resellText,
				'wrapper'       => false
			] );
		}

		wp_send_json_success( [ 'button' => $button ] );
	}
}
