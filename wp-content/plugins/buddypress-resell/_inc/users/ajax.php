<?php
/**
 * BP Resell AJAX Functions
 *
 * @package BP-Resell
 * @subpackage AJAX
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Registers the BP Resell Ajax actions.
 *
 * @since 1.3.0
 */
function bp_resell_register_ajax_action() {
	if ( ! function_exists( 'bp_ajax_register_action' ) ) {
		return;
	}

	bp_ajax_register_action( 'bp_resell' );
	bp_ajax_register_action( 'bp_stop_resell' );
}
add_action( 'bp_init', 'bp_resell_register_ajax_action' );

/**
 * AJAX callback when clicking on the "Resell" button to resell a user.
 *
 * @uses check_admin_referer() Checks to make sure the WP security nonce matches.
 * @uses bp_resell_start_reselling() Starts a user reselling another user.
 * @uses bp_resell_is_reselling() Checks to see if a user is reselling another user already.
 */
function bp_resell_ajax_action_start() {

	check_admin_referer( 'start_reselling' );

	$link_class = ! empty( $_POST['link_class'] ) ? str_replace( 'resell ', '', $_POST['link_class'] ) : false;

	// successful resell.
	if ( bp_resell_start_reselling( array(
		'leader_id' => $_POST['uid'],
		'reseller_id' => bp_loggedin_user_id(),
	) ) ) {
		// output stop_resell button.
		$output = bp_resell_get_add_resell_button( array(
			'leader_id'   => $_POST['uid'],
			'reseller_id' => bp_loggedin_user_id(),
			'wrapper'     => false,
			'link_class'  => $link_class,
		) );

	// failed resell
	} else {
		// output fallback invalid button.
		$args = array(
			'id'         => 'invalid',
			'link_href'  => 'javascript:;',
			'component'  => 'resell',
			'wrapper'    => false,
			'link_class' => $link_class,
		);

		if ( bp_resell_is_reselling( array(
			'leader_id' => $_POST['uid'],
			'reseller_id' => bp_loggedin_user_id(),
		) ) ) {
			$output = bp_get_button( array_merge(
				array(
					'link_text' => __( 'Already reselling', 'buddypress-resellers' ),
				),
				$args
			) );
		} else {
			$output = bp_get_button( array_merge(
				array(
					'link_text' => __( 'Error reselling user', 'buddypress-resellers' ),
				),
				$args
			) );
		}
	}

	/**
	 * Filter the JSON response for the AJAX start action.
	 *
	 * @since 1.3.0
	 *
	 * @param array $response {
	 *     An array of parameters. You can use this filter to add custom parameters as
	 *     array keys.
	 *     @type string $button The AJAX button to render after stop reselling a user.
	 * }
	 * @param int $leader_id The user ID of the person being reselled.
	 */
	$output = apply_filters( 'bp_resell_ajax_action_start_response', array(
		'button' => $output,
	), $_POST['uid'] );

	wp_send_json_success( $output );
}
add_action( 'wp_ajax_bp_resell', 'bp_resell_ajax_action_start' );

/**
 * AJAX callback when clicking on the "Stop-Resell" button to stop_resell a user.
 *
 * @uses check_admin_referer() Checks to make sure the WP security nonce matches.
 * @uses bp_resell_stop_reselling() Stops a user reselling another user.
 * @uses bp_resell_is_reselling() Checks to see if a user is reselling another user already.
 */
function bp_resell_ajax_action_stop() {

	check_admin_referer( 'stop_reselling' );

	$link_class = ! empty( $_POST['link_class'] ) ? str_replace( 'stop_resell ', '', $_POST['link_class'] ) : false;

	// successful stop_resell.
	if ( bp_resell_stop_reselling( array(
		'leader_id' => $_POST['uid'],
		'reseller_id' => bp_loggedin_user_id(),
	) ) ) {
		// output resell button.
		$output = bp_resell_get_add_resell_button( array(
			'leader_id'   => $_POST['uid'],
			'reseller_id' => bp_loggedin_user_id(),
			'wrapper'     => false,
			'link_class'  => $link_class,
		) );

	// failed stop_resell
	} else {
		// output fallback invalid button.
		$args = array(
			'id'         => 'invalid',
			'link_href'  => 'javascript:;',
			'component'  => 'resell',
			'wrapper'    => false,
			'link_class' => $link_class,
		);

		if ( ! bp_resell_is_reselling( array(
			'leader_id' => $_POST['uid'],
			'reseller_id' => bp_loggedin_user_id(),
		) ) ) {
			$output = bp_get_button( array_merge(
				array(
					'link_text' => __( 'Not reselling', 'buddypress-resellers' ),
				),
				$args
			) );

		} else {
			$output = bp_get_button( array_merge(
				array(
					'link_text' => __( 'Error stop reselling user', 'buddypress-resellers' ),
				),
				$args
			) );

		}
	}

	/**
	 * Filter the JSON response for the AJAX stop action.
	 *
	 * @since 1.3.0
	 *
	 * @param array $response {
	 *     An array of parameters. You can use this filter to add custom parameters as
	 *     array keys.
	 *     @type string $button The AJAX button to render after stop reselling a user.
	 * }
	 * @param int $leader_id The user ID of the person being stop_reselled.
	 */
	$output = apply_filters( 'bp_resell_ajax_action_stop_response', array(
		'button' => $output,
	), $_POST['uid'] );

	wp_send_json_success( $output );
}
add_action( 'wp_ajax_bp_stop_resell', 'bp_resell_ajax_action_stop' );
