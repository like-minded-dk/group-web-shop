<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Resell Activity Loader.
 *
 * @since 1.3.0
 */
function bp_resell_activity_init() {
	$bp = $GLOBALS['bp'];

	$bp->resell->activity = new BP_Resell_Activity_Core();

	// Default 'Resell Activity' to false during dev period
	// @todo Fill out other areas - notifications, etc.
	if ( true === (bool) apply_filters( 'bp_resell_enable_activity', false ) ) {
		$bp->resell->activity->module = new BP_Resell_Activity_Module();
	}

	do_action( 'bp_resell_activity_loaded' );
}
add_action( 'bp_resell_loaded', 'bp_resell_activity_init' );

/**
 * Resell Activity Core.
 *
 * @since 1.3.0
 */
class BP_Resell_Activity_Core {

	/**
	 * Resell Activity Module Class.
	 *
	 * @var BP_Resell_Activity_Module Resell Activity Module class.
	 */
	public $module;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// includes.
		$this->includes();

		// Activity API.
		add_filter( 'bp_activity_get_post_type_tracking_args', array( $this, 'set_resell_args_for_post_type' ) );
		add_filter( 'bp_activity_set_action', array( $this, 'set_resell_args' ), 999 );
		add_action( 'bp_actions', array( $this, 'action_listener' ) );
	}

	/**
	 * Includes.
	 */
	protected function includes() {
		$bp = $GLOBALS['bp'];

		require $bp->resell->path . '/modules/activity-functions.php';

		// Add dependant hooks for the 'activity' module.
		if ( true === (bool) apply_filters( 'bp_resell_enable_activity', false ) ) {
			require $bp->resell->path . '/modules/activity-module.php';
		}
	}

	/**
	 * Allow register_post_type() with 'bp_activity' to support resell arguments.
	 *
	 * See {@link bp_resell_activity_can_resell()} for more info on how to register.
	 *
	 * @param  object $retval Return Value.
	 * @return object
	 */
	public function set_resell_args_for_post_type( $retval ) {
		if ( isset( $retval->resell_button ) ) {
			$retval->contexts['resell_button'] = $retval->resell_button;
			unset( $retval->resell_button );
		}

		if ( isset( $retval->resell_type ) ) {
			$retval->contexts['resell_type'] = $retval->resell_type;
			unset( $retval->resell_type );
		}

		return $retval;
	}

	/**
	 * Hijack bp_activity_set_action() to support custom resell arguments.
	 *
	 * See {@link bp_resell_activity_can_resell()} for more info on how to register.
	 *
	 * bp_activity_set_action() is too limited. Fortunately, we work around this
	 * via array stuffing for the 'context' key.  Workaround-galore!
	 *
	 * @param  array $retval
	 * @return array
	 */
	public function set_resell_args( $retval ) {
		if ( isset( $retval['context']['resell_button'] ) ) {
			$retval['resell_button'] = $retval['context']['resell_button'];
			unset( $retval['context']['resell_button'] );
		}

		if ( isset( $retval['context']['resell_type'] ) ) {
			$retval['resell_type'] = $retval['context']['resell_type'];
			unset( $retval['context']['resell_type'] );
		}

		return $retval;
	}

	/**
	 * Action handler when a resell activity button is clicked.
	 */
	public function action_listener() {
		if ( ! bp_is_activity_component() ) {
			return;
		}

		if ( ! bp_is_current_action( 'resell' ) && ! bp_is_current_action( 'stop_resell' ) ) {
			return false;
		}

		if ( empty( $activity_id ) && bp_action_variable( 0 ) ) {
			$activity_id = (int) bp_action_variable( 0 );
		}

		// Not viewing a specific activity item.
		if ( empty( $activity_id ) ) {
			return;
		}

		$action = bp_is_current_action( 'resell' ) ? 'resell' : 'stop_resell';

		// Check the nonce.
		check_admin_referer( "bp_resell_activity_{$action}" );

		$save = bp_is_current_action( 'resell' ) ? 'bp_resell_start_reselling' : 'bp_resell_stop_reselling';
		$resell_type = bp_resell_activity_get_type( $activity_id );

		// Failure on action.
		if ( ! $save( array(
			'leader_id'   => $activity_id,
			'reseller_id' => bp_loggedin_user_id(),
			'resell_type' => $resell_type,
		) ) ) {
			$message_type = 'error';

			if ( 'resell' === $action ) {
				$message = __( 'You are already reselling that item.', 'buddypress-resellers' );
			} else {
				$message = __( 'You were not reselling that item.', 'buddypress-resellers' );
			}

		// Success!
		} else {
			$message_type = 'success';

			if ( 'resell' === $action ) {
				$message = __( 'You are now reselling that item.', 'buddypress-resellers' );
			} else {
				$message = __( 'You are no longer reselling that item.', 'buddypress-resellers' );
			}
		}

		/**
		 * Dynamic filter for the message displayed after the resell button is clicked.
		 *
		 * Default filter name is 'bp_resell_activity_message_activity'.
		 *
		 * Handy for plugin devs.
		 *
		 * @since 1.3.0
		 *
		 * @param string $message      Message that gets displayed after a resell action.
		 * @param string $action       Either 'resell' or 'stop_resell'.
		 * @param int    $activity_id  Activity ID.
		 * @param string $message_type Either 'success' or 'error'.
		 */
		$message = apply_filters( "bp_resell_activity_message_{$resell_type}", $message, $action, $activity_id, $message_type );
		bp_core_add_message( $message, $message_type );

		// Redirect.
		$redirect = wp_get_referer() ? wp_get_referer() : bp_get_activity_directory_permalink();
		bp_core_redirect( $redirect );
		die();
	}
}
