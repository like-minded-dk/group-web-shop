<?php
/**
 * engagements Ajax functions
 *
 * @since 3.0.0
 * @version 3.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_ajax_admin_init_action('friend');
add_ajax_admin_init_action('engagement');

/**
 * engagement/un-engagement a user via a POST request.
 *
 * @since 3.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_addremove_fn_friend() {
	lm_ajax_run_addremove_fn('friend');
}

function bp_nouveau_ajax_addremove_fn_engagement() {
	lm_ajax_run_addremove_fn('engagement');
}