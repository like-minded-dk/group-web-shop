<?php
/**
 * BP Resell User Template Functions
 *
 * @package BP-Resell
 * @subpackage Template
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Output a resell / stop_resell button for a given user depending on the reseller status.
 *
 * @param mixed $args See bp_resell_get_add_resell_button() for full arguments.
 * @uses bp_resell_get_add_resell_button() Returns the resell / stop_resell button
 * @author r-a-y
 * @since 1.1
 */
function bp_resell_add_resell_button( $args = '' ) {
	echo bp_resell_get_add_resell_button( $args );
}
	/**
	 * Returns a resell / stop_resell button for a given user depending on the reseller status.
	 *
	 * Checks to see if the reseller is already reselling the leader.  If is reselling, returns
	 * "Stop reselling" button; if not reselling, returns "Resell" button.
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *     @type int $leader_id The user ID of the person we want to resell.
	 *     @type int $reseller_id The user ID initiating the resell request.
	 *     @type string $link_text The anchor text for the link.
	 *     @type string $link_title The title attribute for the link.
	 *     @type string $wrapper_class CSS class for the wrapper container.
	 *     @type string $link_class CSS class for the link.
	 *     @type string $wrapper The element for the wrapper container. Defaults to 'div'.
	 * }
	 * @return mixed String of the button on success.  Boolean false on failure.
	 * @uses bp_get_button() Renders a button using the BP Button API
	 * @author r-a-y
	 * @since 1.1
	 */
	function bp_resell_get_add_resell_button( $args = '' ) {
		global $members_template;

		$bp = $GLOBALS['bp'];

		$r = wp_parse_args( $args, array(
			'leader_id'     => bp_displayed_user_id(),
			'reseller_id'   => bp_loggedin_user_id(),
			'link_text'     => '',
			'link_title'    => '',
			'wrapper_class' => '',
			'link_class'    => '',
			'wrapper'       => 'div',
		) );

		if ( ! $r['leader_id'] || ! $r['reseller_id'] ) {
			return false;
		}

		// if we're checking during a members loop, then resell status is already
		// queried via bp_resell_inject_member_resell_status().
		if ( ! empty( $members_template->in_the_loop ) && $r['reseller_id'] === bp_loggedin_user_id() && $r['leader_id'] === bp_get_member_user_id() ) {
			$is_reselling = $members_template->member->is_reselling;

		// else we manually query the resell status
		} else {
			$is_reselling = bp_resell_is_reselling( array(
				'leader_id'   => $r['leader_id'],
				'reseller_id' => $r['reseller_id'],
			) );
		}

		$logged_user_id = bp_loggedin_user_id();

		// if the logged-in user is the leader, use already-queried variables.
		if ( $logged_user_id && $logged_user_id === $r['leader_id'] ) {
			$leader_fullname = bp_get_loggedin_user_fullname();

		// else we do a lookup for the user domain and display name of the leader.
		} else {
			$leader_fullname = bp_core_get_user_displayname( $r['leader_id'] );
		}

		// setup some variables.
		if ( $is_reselling ) {
			$id        = 'reselling';
			$action    = 'stop';
			$class     = 'stop_resell';
			$link_text = sprintf( _x( 'Stop-Resell', 'Button', 'buddypress-resellers' ), apply_filters( 'bp_resell_leader_name', bp_get_user_firstname( $leader_fullname ), $r['leader_id'] ) );

			if ( empty( $r['link_text'] ) ) {
				$r['link_text'] = $link_text;
			}

		} else {
			$id        = 'not-reselling';
			$action    = 'start';
			$class     = 'resell';
			$link_text = sprintf( _x( 'Resell', 'Button', 'buddypress-resellers' ), apply_filters( 'bp_resell_leader_name', bp_get_user_firstname( $leader_fullname ), $r['leader_id'] ) );

			if ( empty( $r['link_text'] ) ) {
				$r['link_text'] = $link_text;
			}

		}

		$wrapper_class = 'resell-button ' . $id;

		if ( ! empty( $r['wrapper_class'] ) ) {
			$wrapper_class .= ' ' . esc_attr( $r['wrapper_class'] );
		}

		$link_class = $class;

		if ( ! empty( $r['link_class'] ) ) {
			$link_class .= ' ' . esc_attr( $r['link_class'] );
		}

		// make sure we can view the button if a user is on their own page.
		$block_self = empty( $members_template->member ) ? true : false;

		// if we're using AJAX and a user is on their own profile, we need to set
		// block_self to false so the button shows up.
		if ( bp_resell_is_doing_ajax() && bp_is_my_profile() ) {
			$block_self = false;
		}

		// setup the button arguments.
		$button = array(
			'id'                => $id,
			'component'         => 'resell',
			'must_be_logged_in' => true,
			'block_self'        => $block_self,
			'wrapper_class'     => $wrapper_class,
			'wrapper_id'        => 'resell-button-' . (int) $r['leader_id'],
			'link_href'         => wp_nonce_url( bp_resell_get_user_url( $r['leader_id'], array( $bp->resell->resellers->slug, $action ) ), $action . '_reselling' ),
			'link_text'         => esc_attr( $r['link_text'] ),
			'link_title'        => esc_attr( $r['link_title'] ),
			'link_id'           => $class . '-' . (int) $r['leader_id'],
			'link_class'        => $link_class,
			'wrapper'           => ! empty( $r['wrapper'] ) ? esc_attr( $r['wrapper'] ) : false,
		);

		// BP Nouveau-specific button arguments.
		if ( function_exists( 'bp_nouveau' ) ) {
			if ( $button['wrapper'] && ! bp_is_group() ) {
				$button['parent_element'] = 'li';
			}
			$button['link_class']    .= ' button';
		}

		// Filter and return the HTML button.
		return bp_get_button( apply_filters( 'bp_resell_get_add_resell_button', $button, $r['leader_id'], $r['reseller_id'] ) );
	}
