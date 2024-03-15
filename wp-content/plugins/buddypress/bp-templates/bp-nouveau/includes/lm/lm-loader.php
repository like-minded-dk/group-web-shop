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
class BP_Nouveau_Lm_Relations {
	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct($comp) {
        $this->comp = $comp;
        $this->isf = $comp === 'friend';
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
			require $this->dir . 'lm-ajax.php';

		// Load AJAX code only on AJAX requests.
		} else {
			// error_log('>>> enagement includes');  e.g search 'friends_' in $_REQUEST['action']
			add_action( 'admin_init', function() {
				if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX && 0 === strpos( $_REQUEST['action'], $this->comp . 's_' ) ) {
					require 'lm-ajax.php';
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
        $remove_action = $this->isf ? 'bp_member_add_friend_button' : 'bp_member_add_engagement_button';
		// Remove BuddyPress action for the members loop
		remove_action( 'bp_directory_members_actions', $remove_action );

		// Register the relations Notifications filters
		add_action( 'bp_nouveau_notifications_init_filters', array( $this, 'notification_filters' ) );

		add_action( 'bp_init', array( $this, 'register_ajax_actions' ) );
	}

	/**
	 * Register add_filter() hooks
	 *
	 * @since 3.0.0
	 */
	protected function setup_filters() {
		// related to template-tags.php:296 
		// https://github.com/like-minded-dk/group-web-shop/blob/3b2a9013818cbd6c8d8097f447b0e83a3e103b0f/wp-content/plugins/buddypress/bp-templates/bp-nouveau/includes/members/template-tags.php#L296

		$buttons = $this->isf == 'friend' ? array(
			'engagements_accept_engagement_as_receiver',
			'engagements_reject_engagement_as_receiver',
			'engagements_add_engagement',
			'engagements_remove_engagement',
			'engagements_pending_engagement',
			'engagements_withdraw_engagement',
			'engagements_member_engagementship',
			'engagements_accept_engagement',
			'engagements_reject_engagement',
		) : array(
			'friends_accept_friend_as_receiver',
			'friends_accerejectend_as_receiver',
			'friends_add_friend',
			'friends_remove_friend',
			'friends_pending_friend',
			'friends_withdraw_friend',
			'friends_member_friendship',
			'friends_accept_friend',
			'friends_reject_friend',
		);
        
		foreach ( $buttons as $button ) {
			add_filter( 'bp_button_' . $button, 'bp_nouveau_ajax_button', 10, 5 );
		}

        $comp_count = $this->isf ? 'friends_get_total_friend_count' : 'engagements_get_total_engagement_count';
        $bp_count   = $this->isf ? 'bp_get_total_friend_count' : 'bp_get_total_engagement_count';

		// The number formatting is done into the `bp_nouveau_nav_count()` template tag.
		remove_filter( $comp_count, 'bp_core_number_format' );
		remove_filter( $bp_count, 'bp_core_number_format');
	}

	/**
	 * Register notifications filters for the engagements component.
	 *
	 * @since 3.0.0
	 */
	public function notification_filters() {
		$notifications = $this->isf ? array(
            array(
				'id'       => 'friendship_accepted',
				'label'    => __( 'Accepted friendship requests', 'buddypress' ),
				'position' => 35,
			),
			array(
				'id'       => 'friendship_request',
				'label'    => __( 'Pending friendship requests', 'buddypress' ),
				'position' => 45,
			),
		) : array(
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
		$ajax_actions = $this->isf = 'friend' ? array(
            'friends_accept_friend_as_receiver',
			'friends_accerejectend_as_receiver',
            'friends_remove_friend',
            'friends_add_friend',
            'friends_await_friend',
            'friends_pending_friend',
            'friends_withdraw_friend',
            'friends_accept_friend',
            'friends_reject_friend'
		) : array (
            'engagements_accept_engagement_as_receiver',
			'engagements_reject_engagement_as_receiver',
            'engagements_remove_engagement',
            'engagements_add_engagement',
            'engagements_await_engagement',
            'engagements_pending_engagement',
            'engagements_withdraw_engagement',
            'engagements_accept_engagement',
            'engagements_reject_engagement'
       );

		foreach ( $ajax_actions as $ajax_action ) {
			bp_ajax_register_action( $ajax_action );
		}
	}
}

/**
 * Launch the Relations loader class.
 *
 * @since 3.0.0
 */
function bp_nouveau_lm_relations( $bp_nouveau = null ) {
	if ( is_null( $bp_nouveau ) ) {
		return;
	}

	$bp_nouveau->friends = new BP_Nouveau_Lm_Relations('friend');
	$bp_nouveau->engagements = new BP_Nouveau_Lm_Relations('engagement');
}
add_action( 'bp_nouveau_includes', 'bp_nouveau_lm_relations', 10, 1 );
