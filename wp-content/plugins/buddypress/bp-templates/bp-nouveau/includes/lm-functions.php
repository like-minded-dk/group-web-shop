<?php
require 'lm-ajax-functions.php';
function break_sql($error = '') {
    // @todo lm shortcut delete
    error_log($error ?? 'break call');
    throw new ErrorException($error);
}

function get_button_args_x(
    $comp,
    $pid,
    $sg,
    $error,
    $id,
    $link_text,
    $chuck_array,
    $verify,
    $rel_id = 0,
    $mk = '_mk',
    $link_rel = 'remove',
    $block_self = true,
    $must_be_logged_in = true,
) {
    $class = $comp == 'friend' ? 'friendship-button' : 'engagement-button'; 
    error_log("||> {$error} {$id} {$mk}");
    error_log('>> user_url:' . bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ));
    error_log('>> verify:' . json_encode($verify));
    $text = __( "{$link_text} {$rel_id}", 'buddypress' );
    return array(
        'id'                => $id,
        'component'         => $comp == 'friend' ? 'friends' : 'engagements',
        'must_be_logged_in' => $must_be_logged_in,
        'block_self'        => $block_self,
        'wrapper_class'     => "{$class} {$id}",
        'wrapper_id'        => "{$class}-" . $pid,
        'link_href'         => wp_nonce_url( bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ), $verify),
        'link_text'         => $text,
        'link_title'        => $text,
        'link_id'           => $comp . '-' . $pid,
        'link_rel'          => $link_rel,
        'button_element'    => 'button',
        'link_class'        => "{$class} {$id} requested",
    );
}
