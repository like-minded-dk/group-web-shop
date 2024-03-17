<?php
function break_sql($error = '') {
    // @todo lm shortcut delete
    error_log($error ?? 'break call');
    // throw new ErrorException($error);
}

function is_from_reverse($sts) {
    return (strpos($sts, 'eList') && strpos($sts, 'fTable')) || (strpos($sts, 'fList') && strpos($sts, 'eTable'));
}

function get_ajax_action_list($comp){
    $verbs = ['accept','reject','remove','add','pending','withdraw', 'member'];
    $actions = [];
    foreach (['', '_reversed'] as $suffix){
        foreach ($verbs as $verb){
            $actions[] = "{$comp}s_{$verb}_{$comp}{$suffix}";
        }
    }

    return $actions;
    // "{$comp}s_member_{$comp}ship",
    // "{$comp}s_accept_{$comp}_reversed",
    // "{$comp}s_reject_{$comp}_reversed",
    // "{$comp}s_remove_{$comp}_reversed",
    // "{$comp}s_accept_{$comp}",
    // "{$comp}s_reject_{$comp}",
    // "{$comp}s_remove_{$comp}",
    // "{$comp}s_add_{$comp}",
    // "{$comp}s_pending_{$comp}",
    // "{$comp}s_withdraw_{$comp}",
}

function get_button_args_wrapper(
    $comp,
    $pid,
    $sg,
    $error,
    $rel_id,
    $mk,
    $link_rel,
    $block_self,
    $must_be_logged_in,
    $action,
    $verify,
    $link_text,
    $chuck_array,
) {
    $class = $comp == 'friend' ? 'friendship-button' : 'engagement-button'; 
    // error_log("||> {$error} {$action} {$mk}");
    // error_log('||> user_url:' . bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ));
    // error_log('||> verify:' . json_encode($verify));
    $text = __( "{$link_text} {$rel_id}", 'buddypress' );
    return array(
        'id'                => $action,
        'component'         => $comp == 'friend' ? 'friends' : 'engagements',
        'must_be_logged_in' => $must_be_logged_in,
        'block_self'        => $block_self,
        'wrapper_class'     => "{$class} {$action}",
        'wrapper_id'        => "{$class}-" . $pid,
        'link_href'         => wp_nonce_url( bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ), $verify),
        'link_text'         => $text,
        'link_title'        => $text,
        'link_id'           => $comp . '-' . $pid,
        'link_rel'          => $link_rel,
        'button_element'    => 'button',
        'link_class'        => "{$class} {$action} requested",
    );
}


function get_awaiting_url($action, $comp){

    // $cf = array(
    //     'friend' => array(
    //         "accept" => array(
    //             "sg_fn" => 'bp_get_engagements_slug';
    //             "bp_filter" => 'bp_get_engagement_reject_request_link';
    //             "get_relation_fn" => 'engagements_get_relationship_id';
    //             "cache_prefix" => 'friendship_id_';
    //             "_wpnonce" => 'friends_reject_friend';
    //         ),
    //         "reject" => array(),
    //     ),
    //     'engagement' => array(),
    //         "accept" => array(),
    //         "reject" => array(),
    // );
    

    $action = "reject";
    $oppo = $comp == 'friend' ? 'engagement' : 'friend';
    $sg_fn = "bp_get_{$comp}s_slug";
    $bp_filter = "bp_get_{$comp}_{$action}_request_link";
    $cache_prefix = "{$oppo}ship_id_";
    $get_relation_fn = "{$comp}s_get_relationship_id";
    $_wpnonce = "{$oppo}s_{$action}_{$oppo}";

    error_log('--------------');
    error_log('action '.json_encode($action));
    error_log('sg_fn '.json_encode($sg_fn));
    error_log('bp_filter '.json_encode($bp_filter));
    error_log('cache_prefix '.json_encode($cache_prefix));
    error_log('_wpnonce '.json_encode($_wpnonce));
    error_log('get_relation_fn '.json_encode($get_relation_fn));
    global $members_template;
    if ( ! $relationship_id = wp_cache_get( $cache_prefix . $members_template->member->id . '_' . bp_loggedin_user_id(), 'bp' ) ) {
        $relationship_id     = $get_relation_fn( $members_template->member->id, bp_loggedin_user_id() );
        wp_cache_set( $cache_prefix . $members_template->member->id . '_' . bp_loggedin_user_id(), $relationship_id, 'bp' );
    }
    error_log(11);
    error_log($relationship_id);
    $url = wp_nonce_url(
        bp_loggedin_user_url( bp_members_get_path_chunks( array( $sg_fn(), 'requests', array( $action, $relationship_id ) ) ) ),
        $_wpnonce
    );
    
    error_log('relationship_id '.json_encode($relationship_id));
    /**
     * Filters the URL for accepting the current relationship request in the loop.
     *
     * @since 1.0.0
     * @since 2.6.0 Added the `$relationship_id` parameter.
     *
     * @param string $url           Accept-relationship URL.
     * @param int    $relationship_id ID of the relationship.
     */
    return apply_filters( $bp_filter, $url, $relationship_id );
}
