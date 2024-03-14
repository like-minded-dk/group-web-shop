<?php
/*
    ETB 17->9-0 + ETB 9->17-conf_1 | FTB 17->9-0 + FTB 9->17-conf_1
    means Engagement Table in wordpress,  wp_bp_engagement table,  
    Has initiator 17 , engagement_user_id 9, confirmed is 1 
    And enagement table has initiator 9 , engagement_user_id 17, confirmed is 1
    or reversed condition:
    Friend Table in wordpress,  wp_bp_friend table,  
    Has initiator 17 , friend_user_id 9, confirmed is 1 
    And friend table has initiator 9 , friend_user_id 17, confirmed is 1
*/
function get_button_args ($pid, $comp) {
	$oppo = $comp == 'friend' ? 'engagement' : 'friend'; 
	$mk = $comp[0];

	list(
		$pid,
		$is_btn_reversed,
		$is_member,
		$comp_st,
		$oppo_st,
		$relation_btn,
	) = get_template_vars($pid, $comp);
    error_log('$comp = ' . $comp . ', $oppo = ' . $oppo . ", {$comp}_st = " . $comp_st . ", {$oppo}_st = " . $oppo_st);

    $cond_args = array();
    $button_args = array();
    $status = $comp_st;
    error_log('1 > '.json_encode((strpos($status, 'empty_status'))));
    if (false) {
        return;   
    } elseif (strpos($status, 'empty_status') !== false ) {
        $button_args = simple_cond_btn_args($relation_btn, "add_{$comp}");
    } elseif (strpos($status, 'confirmed_status') !== false ) {
        $button_args = simple_cond_btn_args($relation_btn, "remove_{$comp}");
    } elseif (strpos($status, 'pending_status') !== false ) {
        $button_args = simple_cond_btn_args($relation_btn, "pending_{$comp}",);
    } elseif (strpos($status, 'awaiting_status') !== false ) {
        $button_args = simple_cond_btn_args($relation_btn, "awaiting_{$comp}",);
    } else {
        ////////////////////// fallback buttons
    	error_log('|>>>>  els only ');
        $button_args = $relation_btn("not_{$comp}");
	}

	error_log('<<<<<<<<-: '.$mk);
	return $button_args;
}
function get_template_vars($pid, $comp) {
	$user_id = bp_loggedin_user_id();
	$fst = bp_is_friend( $pid );
	$est = bp_is_engagement( $pid );
	$sg = $comp == 'friend' ? bp_get_friends_slug() : bp_get_engagements_slug() ;

	$is_relation_reversed = (int) is_oppsit_relation($comp);
	$is_member = bp_current_component() == 'members';

	$ini_e_id = get_relation('engagement');
	$rev_e_id = get_relation('engagement', false);
	$ini_f_id = get_relation('friend');
	$rev_f_id = get_relation('friend', false);

	// $f_rel_id = get_friend_id($user_id, $pid);
	// $e_rel_id = get_engagement_id($user_id, $pid);
	
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

	$comp_st = ($comp == 'friend' ? $fst  : $est);
	$oppo_st = ($comp == 'friend' ? $est  : $fst);

	$rel_id = ($is_btn_reversed == '1' ? $rev_id  : $ini_id);
    $relation_btn = function ($status) use ($comp, $pid, $sg, $rel_id) { return relation_btn_args($comp, $status, $pid, $sg, $rel_id); };

	// error_log('gtv >rev_f_awa ini_f_awa rev_e_awa ini_e_awa: '.json_encode( $rev_f_awa . ', ' .$ini_f_awa . ', ' . $rev_e_awa . ', ' .$ini_e_awa ));
	// error_log('gtv ================================= $ini_e: '.$ini_e);
	// error_log('gtv ==================================$ini_f: '.$ini_f);
	// error_log('gtv ===========================friend_status: '.$fst);
	// error_log('gtv =======================engagement_status: '.$est);
	// error_log('gtv ======================= $is_btn_reversed: '.$is_btn_reversed);
	// error_log('gtv =============================$relation_f: '.$f_rel_id);	
	// error_log('gtv =============================$relation_e: '.$e_rel_id);	
	// error_log('gtv =====ini_f_id rev_f_id ini_e_id rev_e_id: '.$ini_f_id . ', ' . $rev_f_id . ', ' . $ini_e_id  . ', ' . $rev_e_id );
	// error_log('gtv =================================$status: '.$status);
	// error_log('gtv =====================================$sg: '.$sg)

	return [
		$pid,
		$is_btn_reversed,
		$is_member,
		$comp_st,
		$oppo_st,
		$relation_btn,
	];
}
