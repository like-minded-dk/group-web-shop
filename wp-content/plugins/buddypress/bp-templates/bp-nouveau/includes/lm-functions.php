<?php
require 'lm-ajax-functions.php';
function break_sql($error = '') {
    // @todo lm shortcut delete
    error_log($error ?? 'break call');
    throw new ErrorException($error);
}

function get_db_and_log( $cond_str, $cond_note ) {
    $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
    error_log('|>>>> ' . $cond_str . ' - '  . $db . ' - condId: ' . $cond_note);
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
        $pending_comp = array(
            'act' => 'pending_friend',
            'ver' => 'friends_withdraw_friendship',
            'text' => 'Cancel Supply-R PDF',
        );
        $awaiting_comp = array(
            'act' => 'awaiting_response_friend',
            'ver' => '',
            'text' => 'Approve Supply-S AWF',
        );
        $remove_comp = array(
            'act' => 'remove_friends',
            'ver' => 'friends_remove_friends',
            'text' => 'Stop Supply-R RMF',
        );
        $not_comp = array(
            'act' => 'not_friends',
            'ver' => 'friends_add_friends',
            'text' => 'Supply-R NTF',
        );
        $is_comp = array(
            'act' => 'is_friend',
            'ver' => 'friends_remove_friends',
            'text' => 'Stop Resell-S ISF',
        );

        $pending_oppo = array(
            'act' => 'pending_engagement',
            'ver' => 'engagements_withdraw_engagementship',
            'text' => 'Cancel Supply-R PDF',
        );
        $awaiting_oppo = array(
            'act' => 'awaiting_response_engagement',
            'ver' => '',
            'text' => 'Approve Supply-R AWE',
        );
        $remove_oppo = array(
            'act' => 'remove_engagements',
            'ver' => 'engagements_remove_engagements',
            'text' => 'Stop Resell-R RME',
        );
        $not_oppo = array(
            'act' => 'not_engagements',
            'ver' => 'friends_add_friends_from_receiver',
            'text' => 'Supply-R NTE',
        );
        $remove_receiver_oppo = array(
            'act' => 'remove_engagements_from_receiver',
            'ver' => 'engagements_remove_engagements_from_receiver',
            'text' => 'Stop Supply-R RER',
        );
        $remove_receiver_comp = array(
            'act' => 'remove_friends_from_receiver',
            'ver' => 'friends_remove_friends_from_receiver',
            'text' => 'Stop Supply-S RFR',
        );
        $add_receiver_comp = array(
            'act' => 'add_friends_from_receiver',
            'ver' => 'friends_add_friends_from_receiver',
            'text' => 'Supply-R AFR',
        );
    } else {
        $pending_comp = array(
            'act' => 'pending_engagement',
            'ver' => 'engagements_withdraw_engagementship',
            'text' => 'Cancel Resell-S PDE',
        );
        $awaiting_comp = array(
            'act' => 'awaiting_response_engagement',
            'ver' => '',
            'text' => 'Approve Resell-S AWE',
        );
        $remove_comp = array(
            'act' => 'remove_engagements',
            'ver' => 'engagements_remove_engagements',
            'text' => 'Stop Resell-S RME',
        );
        $not_comp = array(
            'act' => 'not_engagements',
            'ver' => 'engagements_add_engagements',
            'text' => 'Resell-S NTE',
        );
        $is_comp = array(
            'act' => 'is_engagement',
            'ver' => 'engagements_remove_engagements',
            'text' => 'Stop Supply-R ISE',
        );

        $pending_oppo = array(
            'act' => 'pending_friend',
            'ver' => 'friends_withdraw_friendship',
            'text' => 'Cancel Resell-S PDF',
        );
        $awaiting_oppo = array(
            'act' => 'awaiting_response_friend',
            'ver' => '',
            'text' => 'Approve Resell-S AWF',
        );
        $remove_oppo = array(
            'act' => 'remove_friends',
            'ver' => 'friends_remove_friends',
            'text' => 'Stop Supply-R RMF',
        );
        $not_oppo = array(
            'act' => 'not_friends',
            'ver' => 'engagements_add_engagements_from_receiver',
            'text' => 'Resell-S NTF',
        );
        $remove_receiver_oppo = array(
            'act' => 'remove_friends_from_receiver',
            'ver' => 'friends_remove_friends_from_receiver',
            'text' => 'Stop Resell-S RFR',
        );
        $remove_receiver_comp = array(
            'act' => 'remove_engagements_from_receiver',
            'ver' => 'engagements_remove_engagements_from_receiver',
            'text' => 'Stop Resell-S RER',
        );
        $add_receiver_comp = array(
            'act' => 'add_engagements_from_receiver',
            'ver' => 'engagements_add_engagements_from_receiver',
            'text' => 'Resell-S AER',
        );
    }

    switch ( $status ) {
        case $pending_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $pending_comp['act'],
                $pending_comp['text'],
                ['requests', array( 'cancel', $pid )],
                $pending_comp['ver'],
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case $awaiting_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $awaiting_comp['act'],
                $awaiting_comp['text'],
                ['requests'],
                $awaiting_comp['ver'],
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case $remove_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $remove_comp['act'],
                $remove_comp['text'],
                ['remove-friend', array( $pid )],
                $remove_comp['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case $not_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $not_comp['act'],
                $not_comp['text'],
                ['add-friend', array( $pid )],
                $not_comp['ver'],
                $rel_id, '_ba', 'add',  true, true
            );
        break;

        case $is_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $is_comp['act'],
                $is_comp['text'],
                ['remove-engagement', array( $pid )],
                $is_comp['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case $pending_oppo['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $pending_oppo['act'],
                $pending_oppo['text'],
                ['requests', array( 'cancel', $pid )],
                $pending_oppo['ver'],
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case $awaiting_oppo['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $awaiting_oppo['act'],
                $awaiting_oppo['text'],
                ['requests'],
                $awaiting_oppo['ver'],
                $rel_id, '_ba', 'remove',  true, true
            );
            break;
            
        case $remove_oppo['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $remove_oppo['act'],
                $remove_oppo['text'],
                ['remove-friend', array( $pid )],
                $remove_oppo['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
            
        case $not_oppo['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $not_oppo['act'],
                $not_oppo['text'],
                ['add-engagements', array( $pid )],
                $not_oppo['ver'],
                $rel_id, '_ba', 'add',  true, true
            );
            break;

        case $remove_receiver_oppo['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $remove_receiver_oppo['act'],
                $remove_receiver_oppo['text'],
                ['remove-engagement', array( $pid )],
                $remove_receiver_oppo['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case $remove_receiver_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $remove_receiver_comp['act'],
                $remove_receiver_comp['text'],
                ['remove-friend', array( $pid )],
                $remove_receiver_comp['ver'],
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
        
        case $add_receiver_comp['act']:
        default:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'err:',
                $add_receiver_comp['act'],
                $add_receiver_comp['text'],
                ['add-engagements', array( $pid )],
                $add_receiver_comp['ver'],
                $rel_id, '_ba', 'add',  true, true
            );
            break;
    }

    return $button_args;
}

function cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, $condId, $compE, $oppE, $compF, $oppF, $caseE, $caseF ) {
    if (                  ($comp_st == $compE && $oppo_st == $oppE)   ||          ($comp_st == $compF && $oppo_st == $oppF)) {
        $cond_str = (int) ($comp_st == $compE && $oppo_st == $oppE) . '-' . (int) ($comp_st == $compF && $oppo_st == $oppF);
        $db = get_db_and_log($cond_str, $condId);
        $args = '';
        if ($db == 'Ed') {
            error_log('|>>>> condId: '.$condId.' - ETB');
            $args = $relation_btn($caseE);
        } else {
            error_log('|>>>> condId: '.$condId.' - FTB');
            $args = $relation_btn($caseF);
        }
        
        error_log('>>args_id: '.json_encode($args['id']));
        return $args;
    } 
 }
