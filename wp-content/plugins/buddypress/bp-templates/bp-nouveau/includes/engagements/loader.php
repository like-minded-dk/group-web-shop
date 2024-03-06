<?php
/**
 * BP Nouveau Engagements
 *
 * @since 3.0.0
 * @version 12.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Engagements Loader class
 *
 * @since 3.0.0
 */
#[AllowDynamicProperties]
class BP_Nouveau_Engagements {
	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
		$this->setup_filters();
	}

	/**
	 * Globals
	 *
	 * @since 3.0.0
	 */
	protected function setup_globals() {
		$this->dir = trailingslashit( dirname( __FILE__ ) );
	}

	/**
	 * Include needed files
	 *
	 * @since 3.0.0
	 */
	protected function includes() {
		// Test suite requires the AJAX functions early.
		if ( function_exists( 'tests_add_filter' ) ) {
			require $this->dir . 'ajax.php';

		// Load AJAX code only on AJAX requests.
		} else {
			// error_log('>>> enagement includes');
			add_action( 'admin_init', function() {
				if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX && 0 === strpos( $_REQUEST['action'], 'engagements_' ) ) {
					require bp_nouveau()->engagements->dir . 'ajax.php';
				}
			} );
		}
	}

	/**
	 * Register do_action() hooks
	 *
	 * @since 3.0.0
	 */
	protected function setup_actions() {
		// Remove BuddyPress action for the members loop
		remove_action( 'bp_directory_members_actions', 'bp_member_add_engagement_button' );

		// Register the engagements Notifications filters
		add_action( 'bp_nouveau_notifications_init_filters', array( $this, 'notification_filters' ) );

		add_action( 'bp_init', array( $this, 'register_ajax_actions' ) );
	}

	/**
	 * Register add_filter() hooks
	 *
	 * @since 3.0.0
	 */
	protected function setup_filters() {
		$buttons = array(
			'engagements_pending_engagement',
			'engagements_is_engagement',
			'engagements_not_engagements',
			'engagements_not_friends_from_engagements',
			'engagements_remove_friends_from_engagements',
			'engagements_member_engagementship',
			'engagements_accept_engagementship',
			'engagements_reject_engagementship',
		);

		foreach ( $buttons as $button ) {
			add_filter( 'bp_button_' . $button, 'bp_nouveau_ajax_button', 10, 5 );
		}

		// The number formatting is done into the `bp_nouveau_nav_count()` template tag.
		remove_filter( 'engagements_get_total_engagement_count', 'bp_core_number_format' );
		remove_filter( 'bp_get_total_engagement_count',      'bp_core_number_format' );
	}

	/**
	 * Register notifications filters for the engagements component.
	 *
	 * @since 3.0.0
	 */
	public function notification_filters() {
		$notifications = array(
			array(
				'id'       => 'engagementship_accepted',
				'label'    => __( 'Accepted engagementship requests', 'buddypress' ),
				'position' => 35,
			),
			array(
				'id'       => 'engagementship_request',
				'label'    => __( 'Pending engagementship requests', 'buddypress' ),
				'position' => 45,
			),
		);

		foreach ( $notifications as $notification ) {
			bp_nouveau_notifications_register_filter( $notification );
		}
	}

	/**
	 * Register Engagements Ajax actions.
 	 *
	 * @since 12.0.0
	 */
	public function register_ajax_actions() {
		$ajax_actions = array( 'engagements_remove_friends_from_engagements', 'engagements_not_friends_from_engagements','engagements_remove_engagement', 'engagements_add_engagement', 'engagements_withdraw_engagementship', 'engagements_accept_engagementship', 'engagements_reject_engagementship' );

		foreach ( $ajax_actions as $ajax_action ) {
			bp_ajax_register_action( $ajax_action );
		}
	}
}

/**
 * Launch the Engagements loader class.
 *
 * @since 3.0.0
 */
function bp_nouveau_engagements( $bp_nouveau = null ) {
	if ( is_null( $bp_nouveau ) ) {
		return;
	}

	$bp_nouveau->engagements = new BP_Nouveau_Engagements();
}
add_action( 'bp_nouveau_includes', 'bp_nouveau_engagements', 10, 1 );
