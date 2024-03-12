<?php
require 'lm-ajax-functions.php';
function break_sql($error = '') {
    // @todo lm shortcut delete
    error_log($error ?? 'break call');
    throw new ErrorException($error);
}

function get_db_and_log( $cond_str ) {
    $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
    error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 01: ETB 17->9-conf_0 LS-L_btn');
    return $db;
}

function get_button_args_wrapper(
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
    // error_log('||> user_url:' . bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ));
    // error_log('||> verify:' . json_encode($verify));
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


function relation_btn_args($comp, $status, $pid, $sg, $rel_id) {
    error_log('||> '.$comp.' btn_args, btn_status: '.$status);

    if ($comp == 'friend') {
        $pending_comp = array('act' => 'pending_friend', 'ver' => 'friends_withdraw_friendship');
        $awaiting_comp = array('act' => 'awaiting_response_friend', 'ver' => '');
        $remove_comp = array('act' => 'remove_friends', 'ver' => 'friends_remove_friends');
        $not_comp = array('act' => 'not_friends', 'ver' => 'friends_add_friends');
        $is_comp = array('act' => 'is_friend', 'ver' => 'friends_remove_friends');

        $pending_oppo = array('act' => 'pending_engagement', 'ver' => 'engagements_withdraw_engagementship');
        $awaiting_oppo = array('act' => 'awaiting_response_engagement', 'ver' => '');
        $remove_oppo = array('act' => 'remove_engagements', 'ver' => 'engagements_remove_engagements');
        $not_oppo = array('act' => 'not_engagements', 'ver' => 'friends_add_friends_from_receiver');
        $remove_oppo_from_receiver = array('act' => 'remove_engagements_from_receiver', 'ver' => 'engagements_remove_engagements_from_receiver');
        $remove_receiver_comp = array('act' => 'remove_friends_from_receiver', 'ver' => 'friends_remove_friends_from_receiver');
        $add_comp_from_receiver = array('act' => 'add_friends_from_receiver', 'ver' => 'friends_add_friends_from_receiver');
    } else {
        $pending_comp = array('act' => 'pending_engagement', 'ver' => 'engagements_withdraw_engagementship');
        $awaiting_comp = array('act' => 'awaiting_response_engagement', 'ver' => '');
        $remove_comp = array('act' => 'remove_engagements', 'ver' => 'engagements_remove_engagements');
        $not_comp = array('act' => 'not_engagements', 'ver' => 'engagements_add_engagements');
        $is_comp = array('act' => 'is_engagement', 'ver' => 'engagements_remove_engagements');

        $pending_oppo = array('act' => 'pending_friend', 'ver' => 'friends_withdraw_friendship');
        $awaiting_oppo = array('act' => 'awaiting_response_friend', 'ver' => '');
        $remove_oppo = array('act' => 'remove_friends', 'ver' => 'friends_remove_friends');
        $not_oppo = array('act' => 'not_friends', 'ver' => 'engagements_add_engagements_from_receiver');
        $remove_oppo_from_receiver = array('act' => 'remove_friends_from_receiver', 'ver' => 'friends_remove_friends_from_receiver');
        $remove_receiver_comp = array('act' => 'remove_engagements_from_receiver', 'ver' => 'engagements_remove_engagements_from_receiver');
        $add_comp_from_receiver = array('act' => 'add_engagements_from_receiver', 'ver' => 'engagements_add_engagements_from_receiver');
    }

    error_log('||> friend_btn_args     , btn_status: '.$status);
    switch ( $status ) {
        case $pending_comp:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $pending_comp['act'],
                'Cancel Supply-R pf',
                ['requests', array( 'cancel', $pid )],
                $pending_comp['ver'],
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case $awaiting_comp:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $awaiting_comp['act'],
                'Approve Supply-S arf',
                ['requests'],
                $awaiting_comp['ver'],
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case $remove_comp:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $remove_comp['act'],
                'Stop Supply-R rf',
                ['remove-friend', array( $pid )],
                $remove_comp['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case $not_comp:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $not_comp['act'],
                'Supply-R nf',
                ['add-friend', array( $pid )],
                $not_comp['ver'],
                $rel_id, '_ba', 'add',  true, true
            );
        break;

        case $is_comp:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $is_comp['act'],
                'Stop Resell-S if',
                ['remove-engagement', array( $pid )],
                $is_comp['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case $pending_oppo:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $pending_oppo['act'],
                'Cancel Supply-R',
                ['requests', array( 'cancel', $pid )],
                $pending_oppo['ver'],
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case $awaiting_oppo:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $awaiting_oppo['act'],
                'Approve Supply-R are',
                ['requests'],
                $awaiting_oppo['ver'],
                $rel_id, '_ba', 'remove',  true, true
            );
            break;
            
        case $remove_oppo:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $remove_oppo['act'],
                'Stop Resell-R re',
                ['remove-friend', array( $pid )],
                $remove_oppo['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
            
        case $not_oppo:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $not_oppo['act'],
                'Supply-R ne',
                ['add-engagements', array( $pid )],
                $not_oppo['ver'],
                $rel_id, '_ba', 'add',  true, true
            );
            break;

        case $remove_oppo_from_receiver:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $remove_oppo_from_receiver['act'],
                'Stop Supply-R reff',
                ['remove-engagement', array( $pid )],
                $remove_oppo_from_receiver['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case $remove_receiver_comp:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $remove_receiver_comp['act'],
                'Stop Supply-S rffr',
                ['remove-friend', array( $pid )],
                $remove_receiver_comp['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
        
        case $add_comp_from_receiver:
        default:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $add_comp_from_receiver['act'],
                'Supply-R affr',
                ['add-engagements', array( $pid )],
                $add_comp_from_receiver['ver'],
                $rel_id, '_ba', 'add',  true, true
            );
            break;
    }

    return $button_args;
}
