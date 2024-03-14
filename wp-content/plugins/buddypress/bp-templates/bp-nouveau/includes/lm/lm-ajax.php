<?php
/**
 * engagements Ajax functions
 *
 * @since 3.0.0
 * @version 3.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function get_comp() {
	return strpos($_REQUEST['action'], 'friends_' ) ? 'friend' : 'engagement';
}

add_ajax_admin_init_action(get_comp());

/**
 * engagement/un-engagement a user via a POST request.
 *
 * @since 3.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_addremove_fn() {
	lm_ajax_run_addremove_fn(get_comp());
}
