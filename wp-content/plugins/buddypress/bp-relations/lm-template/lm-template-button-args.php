<?php
/*
    ETB 17->9-0 + ETB 9->17-conf_1 | FTB 17->9-0 + FTB 9->17-conf_1
    means Engagement Table in wordpress,  wp_bp_engagement table,  
    Has initiator 17 , receiver_user_id 9, confirmed is 1 
    And enagement table has initiator 9 , receiver_user_id 17, confirmed is 1
    or reversed condition:
    Friend Table in wordpress,  wp_bp_friend table,  
    Has initiator 17 , receiver_user_id 9, confirmed is 1 
    And friend table has initiator 9 , receiver_user_id 17, confirmed is 1
*/
function get_button_args ($pid, $comp) {
	$oppo = $comp == 'friend' ? 'engagement' : 'friend'; 
	$mk = $comp[0];

	list(
        $pid,
        $sg,
        $relation_id,
		$comp_list_sts,
		$oppo_list_sts,
        $from_reversed_table,
	) = get_template_vars($pid, $comp);
    error_log('$comp = ' . $comp . ', $oppo = ' . $oppo . ", {$comp}_st = " . $comp_list_sts . ", {$oppo}_st = " . $oppo_list_sts);

    $button_args = array();

    if (false) {
        return;   
    } elseif (strpos($comp_list_sts, 'confirmed_sts') !== false ) {
        if ($from_reversed_table) {
            $button_args =  relation_btn_args($comp, "remove_{$comp}_reversed", $pid, $sg, $relation_id);
        } else {
            $button_args =  relation_btn_args($comp, "remove_{$comp}", $pid, $sg, $relation_id);
        }
    } elseif (strpos($comp_list_sts, 'empty_sts') !== false ) {
        $button_args =  relation_btn_args($comp, "add_{$comp}", $pid, $sg, $relation_id);
    } elseif (strpos($comp_list_sts, 'pending_sts') !== false ) {
        $button_args =  relation_btn_args($comp, "pending_{$comp}", $pid, $sg, $relation_id);
    } elseif (strpos($comp_list_sts, 'awaiting_sts') !== false ) {
        $button_args =  relation_btn_args($comp, "awaiting_{$comp}", $pid, $sg, $relation_id);
    } else {
        ////////////////////// fallback buttons
    	error_log('|>>>>  els only ');
        $button_args =  relation_btn_args($comp, "add_{$comp}", $pid, $sg, $relation_id);
	}

	error_log('<<<<<<<<-: '.$mk);
	return $button_args;
}
function get_template_vars($pid, $comp) {
	// $user_id = bp_loggedin_user_id();
	$fst = bp_is_friend( $pid );
	$est = bp_is_engagement( $pid );
	$sg = $comp == 'friend' ? bp_get_friends_slug() : bp_get_engagements_slug() ;

	$is_relation_reversed = (int) is_oppsit_relation($comp);
	// $is_member = bp_current_component() == 'members';

	$ini_e_id = get_relation('engagement');
	$rev_e_id = get_relation('engagement', false);
	$ini_f_id = get_relation('friend');
	$rev_f_id = get_relation('friend', false);

	// $f_relation_id = get_friend_id($user_id, $pid);
	// $e_relation_id = get_engagement_id($user_id, $pid);

	// $ini_f = (int) is_initiator('friend');
	// $ini_e = (int) is_initiator('engagement');
	// $status = $ini_f > $ini_e ? $fst : $est ;

	// $ini_e_awa = is_initial_awating('engagement');
	// $rev_e_awa = is_receiver_awating('engagement');
	// $ini_f_awa = is_initial_awating('friend');
	// $rev_f_awa = is_receiver_awating('friend');

    $is_btn_reversed = $is_relation_reversed;
	$ini_id = ($comp == 'friend' ? $ini_f_id  : $ini_e_id) ;
	$rev_id = ($comp == 'friend' ? $rev_e_id  : $rev_f_id) ;

	$comp_list_sts = ($comp == 'friend' ? $fst  : $est);
	$oppo_list_sts = ($comp == 'friend' ? $est  : $fst);

    // $parsed_cls = explode($comp_list_sts, '_');
    $from_reversed_table = is_from_reverse($comp_list_sts);
	$relation_id = ($from_reversed_table ? $rev_id  : $ini_id);

	return [
		$pid,
        $sg,
        $relation_id,
		$comp_list_sts,
		$oppo_list_sts,
        $from_reversed_table,
	];
}



function relation_btn_args($comp, $status, $pid, $sg, $relation_id) {
    error_log('||> '.$comp.' btn_args, btn_status: '.$status);
    $is_f = $comp == 'friend' ;
    $oppo = $is_f ? 'engagement' : 'friend';

    $pending_comp = array(
        'act' => 'pending_' . $comp,
        'ver' => $comp . 's_withdraw_' . $comp,
        'text' => $is_f ? 'Cancel supply' : 'Cancel resell' ,
    );
    $awaiting_comp = array(
        'act' => 'awaiting_' . $comp,
        'ver' => '',
        'text' => $is_f ? 'Approve or Reject Supply' : 'Approve or Reject Resell', 
    );
    $remove_comp = array(
        'act' => 'remove_' . $comp,
        'ver' => $comp . 's_remove_' . $comp,
        'text' => $is_f ? 'Stop supplying' : 'Stop reselling', 
    );
    $remove_oppo = array(
        'act' => 'remove_' . $comp . '_reversed',
        'ver' => $oppo . 's_remove_' . $oppo,
        'text' => $is_f ? 'Stop supplying' : 'Stop reselling', 
    );
    $add_comp = array(
        'act' => 'add_' . $comp,
        'ver' => $comp . 's_add_' . $comp,
        'text' => $is_f ? 'Supply to' : 'Resell to', 
    );

    switch ( $status ) {
        case $pending_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'Err:',
                $relation_id, '_ba', 'remove',  true, true,
                $pending_comp['act'],
                $pending_comp['ver'],
                $pending_comp['text'],
                ['requests', array( 'cancel', $pid )],
            );
            break;

        case $awaiting_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'Err:',
                $relation_id, '_ba', 'remove',  true, true,
                $awaiting_comp['act'],
                $awaiting_comp['ver'],
                $awaiting_comp['text'],
                ['requests'],
            );
            break;

        case $remove_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'Err:',
                $relation_id, '_ba', 'remove',  true, false,
                $remove_comp['act'],
                $remove_comp['ver'],
                $remove_comp['text'],
                ['remove-' . $comp, array( $pid )],
            );
            break;

        case $add_comp['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'Err:',
                $relation_id, '_ba', 'add',  true, true,
                $add_comp['act'],
                $add_comp['ver'],
                $add_comp['text'],
                ['add-' . $comp, array( $pid )],
            );
        break;
            
        case $remove_oppo['act']:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'Err:',
                $relation_id, '_ba', 'remove',  true, false,
                $remove_oppo['act'],
                $remove_oppo['ver'],
                $remove_oppo['text'],
                ['remove-' . $comp, array( $pid )],
            );
            break;
        
        default:
            $button_args = get_button_args_wrapper(
                $comp, $pid, $sg, 'Err:',
                $relation_id, '_ba', 'add',  true, true,
                $add_comp['act'],
                $add_comp['ver'],
                $add_comp['text'],
                ['add-' . $comp, array( $pid )],
            );
            break;
    }

    return $button_args;
}
