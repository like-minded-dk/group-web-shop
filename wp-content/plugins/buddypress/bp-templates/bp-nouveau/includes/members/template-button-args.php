<?php
/*
    E_T 17->9-0 + E_T 9->17-conf_1 | F_T 17->9-0 + F_T 9->17-conf_1
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
	$button_args = array();
	list(
		$pid,
		$sg,
		$is_reversed,
		$status,
		$is_member,
		$button_func,
		$comp_st,
		$oppo_st,
		$rel_id,
	) = get_template_vars($pid, $comp);

	if (false) {
		return;
	} elseif ( $is_member ) {
		// one initiator
		$button_args = $button_func($status, $pid, $sg, $rel_id);

    error_log('$comp = ' . $comp . ', ' . '$oppo = ' . $oppo . ',  $comp_st = ' . $comp_st . ', $oppo_st = ' . $oppo_st);

    ////////////////////// E_T 17->9-conf_1 | F_T 17->9-conf_1
    } elseif  ( ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'not_friends')
             || ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_is_friend_ini') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_is_engagement_ini", $oppo_st = "not_friends"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "not_engagements", $oppo_st = "f_c1_is_friend_ini"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'not_friends') . '-' . 
          (int) ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_is_friend_ini');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 05: E_T 17->9-conf_1 LS-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 05-1');
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 05-2');
            $button_args = $button_func("not_{$comp}s", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c1_is_engagement_ini')
             || ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'not_engagements') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "not_friends", $oppo_st = "e_c1_is_engagement_ini"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_is_friend_ini", $oppo_st = "not_engagements"
        $cond_str = '' . 
          (int) ($comp_st == 'not_friends' && $oppo_st == 'e_c1_is_engagement_ini') . '-' . 
          (int) ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'not_engagements');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 06: E_T 17->9-conf_1 LS-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 06-1');
            $button_args = $button_func("not_{$oppo}s", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 06-2');
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'not_friends')
             || ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_is_reverse_engagement_rev') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_is_reverse_friend_rev", $oppo_st = "not_friends"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "not_engagements", $oppo_st = "f_c1_is_reverse_engagement_rev"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'not_friends') . '-' . 
          (int) ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_is_reverse_engagement_rev');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 07: E_T 17->9-conf_1 GD-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 07-1');
            $button_args = $button_func("add_{$comp}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 07-2');
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c1_is_reverse_friend_rev')
             || ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'not_engagements') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "not_friends", $oppo_st = "e_c1_is_reverse_friend_rev"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_is_reverse_engagement_rev", $oppo_st = "not_engagements"
        $cond_str = '' . 
          (int) ($comp_st == 'not_friends' && $oppo_st == 'e_c1_is_reverse_friend_rev') . '-' . 
          (int) ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'not_engagements');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 08: E_T 17->9-conf_1 GD-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 08-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 08-2');
            $button_args = $button_func("add_{$comp}s_from_reciver", $pid, $sg, $rel_id);
        }

    ////////////////////// E_T 17->9-conf_1 + F_T 17->9-0 | F_T 17->9-conf_1 + E_T 17->9-0
    } elseif  ( ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_pending_friend_ini')
             || ($comp_st == 'e_c1_pending_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_is_engagement_ini", $oppo_st = "f_c1_pending_friend_ini"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_pending_engagement_ini", $oppo_st = "f_c1_is_friend_ini"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_pending_friend_ini') . '-' . 
          (int) ($comp_st == 'e_c1_pending_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 09: E_T 17->9-conf_1 + F_T 17->9-0 LS-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 09-1'); 
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 09-2');
            $button_args = $button_func("pending_{$comp}", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'f_c1_pending_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini')
             || ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_pending_engagement_ini') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_pending_friend_ini", $oppo_st = "e_c1_is_engagement_ini"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_is_friend_ini", $oppo_st = "e_c1_pending_engagement_ini"
        $cond_str = '' . 
          (int) ($comp_st == 'f_c1_pending_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini') . '-' . 
          (int) ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_pending_engagement_ini');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 10: E_T 17->9-conf_1 + F_T 17->9-0 LS-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 10-1'); 
            $button_args = $button_func("pending_{$comp}", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 10-2');
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_awaiting_response_rev')
             || ($comp_st == 'e_c1_awaiting_response_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_is_reverse_friend_rev", $oppo_st = "f_c1_awaiting_response_rev"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_awaiting_response_rev", $oppo_st = "f_c1_is_reverse_engagement_rev"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_awaiting_response_rev') . '-' . 
          (int) ($comp_st == 'e_c1_awaiting_response_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 11: E_T 17->9-conf_1 + F_T 17->9-0 GD-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 11-1'); 
            $button_args = $button_func("awaiting_response_{$oppo}", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 11-2');
            $button_args = $button_func("awaiting_response_{$comp}", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'f_c1_awaiting_response_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev')
             || ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_awaiting_response_rev') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_awaiting_response_rev", $oppo_st = "e_c1_is_reverse_friend_rev"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_is_reverse_engagement_rev", $oppo_st = "e_c1_awaiting_response_rev"
        $cond_str = '' . 
          (int) ($comp_st == 'f_c1_awaiting_response_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev') . '-' . 
          (int) ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_awaiting_response_rev');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 12: E_T 17->9-conf_1 + F_T 17->9-0 GD-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 12-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 12-2');
            $button_args = $button_func("remove_{$comp}s_from_reciver", $pid, $sg, $rel_id);
        }


    // this condition always take 1st/Ed condition
    ////////////////////// E_T 17->9-conf_1 + F_T 17->9-conf_1 |  F_T 17->9-conf_1 + E_T 17->9-conf_1 
    } elseif  ( ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini')
             || ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_is_engagement_ini", $oppo_st = "f_c1_is_friend_ini"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "c1_is_engagement_ini", $oppo_st = "c1_is_friend_ini"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini') . '-' . 
          (int) ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 13: E_T 17->9-conf_1 + 17 f 1 LS-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 13-1'); 
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 13-2');
            $button_args = $button_func("remove_{$oppo}s", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini')
             || ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_is_friend_ini", $oppo_st = "e_c1_is_engagement_ini"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_is_friend_ini", $oppo_st = "e_c1_is_engagement_ini"
        $cond_str = '' . 
          (int) ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini') . '-' . 
          (int) ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 14: E_T 17->9-conf_1 + 17 f 1 LS-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 14-1'); 
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 14-2');
            $button_args = $button_func("remove_{$oppo}s", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev')
             || ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_is_reverse_friend_rev", $oppo_st = "f_c1_is_reverse_engagement_rev"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c1_is_reverse_friend_rev", $oppo_st = "f_c1_is_reverse_engagement_rev"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev') . '-' . 
          (int) ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 15: E_T 17->9-conf_1 + 17 f 1 GD-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 15-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 15-2');
            $button_args = $button_func("remove_{$comp}s_from_reciver", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev')
             || ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_is_reverse_engagement_rev", $oppo_st = "e_c1_is_reverse_friend_rev"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c1_is_reverse_engagement_rev", $oppo_st = "e_c1_is_reverse_friend_rev"
        $cond_str = '' . 
          (int) ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev') . '-' . 
          (int) ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 16: E_T 17->9-conf_1 + 17 f 1 GD-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 16-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 16-2');
            $button_args = $button_func("remove_{$comp}s_from_reciver", $pid, $sg, $rel_id);
        }



    ////////////////////// E_T 17->9-conf_1 + E_T 9->17-0 | F_T 17->9-conf_1 + F_T 9->17-0
    } elseif  ( ($comp_st == 'e_c2_fm1_is_engagement_ini' && $oppo_st == 'not_friends')
             || ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm1_is_friend_ini') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c2_fm1_is_engagement_ini", $oppo_st = "not_friends"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "not_engagements", $oppo_st = "f_c2_fm1_is_friend_ini"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c2_fm1_is_engagement_ini' && $oppo_st == 'not_friends') . '-' . 
          (int) ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm1_is_friend_ini');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 17: E_T 17->9-conf_1 + E_T 9->17-0 LS-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 17-1'); 
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 17-2');
            $button_args = $button_func("awaiting_response_{$oppo}", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm1_is_engagement_ini')
             || ($comp_st == 'f_c2_fm1_is_friend_ini' && $oppo_st == 'not_engagements') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "not_friends", $oppo_st = "e_c2_fm1_is_engagement_ini"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c2_fm1_is_friend_ini", $oppo_st = "not_engagements"
        $cond_str = '' . 
          (int) ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm1_is_engagement_ini') . '-' . 
          (int) ($comp_st == 'f_c2_fm1_is_friend_ini' && $oppo_st == 'not_engagements');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 18: E_T 17->9-conf_1 + E_T 9->17-0 LS-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 18-1'); 
            $button_args = $button_func("pending_{$oppo}", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 18-2');
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'e_c2_fm1_is_reverse_friend_rev' && $oppo_st == 'not_friends')
             || ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm1_is_friend_rev') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c2_fm1_is_reverse_friend_rev", $oppo_st = "not_friends"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "not_engagements", $oppo_st = "f_c2_fm1_is_friend_rev"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c2_fm1_is_reverse_friend_rev' && $oppo_st == 'not_friends') . '-' . 
          (int) ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm1_is_friend_rev');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 19: E_T 17->9-conf_1 + E_T 9->17-0 GD-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 19-1'); 
            $button_args = $button_func("awaiting_response_{$comp}", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 19-2');
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm1_is_reverse_friend_rev')
             || ($comp_st == 'f_c2_fm1_is_friend_rev' && $oppo_st == 'not_engagements') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "not_friends", $oppo_st = "e_c2_fm1_is_reverse_friend_rev"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c2_fm1_is_friend_rev", $oppo_st = "not_engagements"
        $cond_str = '' . 
          (int) ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm1_is_reverse_friend_rev') . '-' . 
          (int) ($comp_st == 'f_c2_fm1_is_friend_rev' && $oppo_st == 'not_engagements');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 20: E_T 17->9-conf_1 + E_T 9->17-0 GD-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 20-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 20-2');
            $button_args = $button_func("pending_{$comp}", $pid, $sg, $rel_id);
        }

    ////////////////////// E_T 17->9-conf_1 + E_T 9->17-conf_1 | F_T 17->9-conf_1 + F_T 9->17-conf_1
    } elseif  ( ($comp_st == 'e_c2_exist_both_engagements_v1_ini' && $oppo_st == 'not_friends')
             || ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_exist_both_engagements_v1_ini') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c2_exist_both_engagements_v1_ini", $oppo_st = "not_friends"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "not_engagements", $oppo_st = "f_c2_exist_both_engagements_v1_ini"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c2_exist_both_engagements_v1_ini' && $oppo_st == 'f_c1_is_reverse_engagement_rev') . '-' . 
          (int) ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_exist_both_engagements_v1_ini');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 21: E_T 17->9-conf_1 + E_T 9->17-conf_1 LS-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 21-1'); 
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 21-2');
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_exist_both_engagements_v1_ini')
             || ($comp_st == 'f_c2_exist_both_engagements_v1_ini' && $oppo_st == 'not_engagements') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "not_friends", $oppo_st = "e_c2_exist_both_engagements_v1_ini"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c2_exist_both_engagements_v1_ini", $oppo_st = "not_engagements"
        $cond_str = '' . 
          (int) ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c2_exist_both_engagements_v1_ini') . '-' . 
          (int) ($comp_st == 'f_c2_exist_both_engagements_v1_ini' && $oppo_st == 'not_engagements');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 22: E_T 17->9-conf_1 + E_T 9->17-conf_1 LS-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 22-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 22-2');
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'e_c2_exist_both_engagements_v1_rev' && $oppo_st == 'not_friends')
             || ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_exist_both_engagements_v1_rev') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c2_exist_both_engagements_v1_rev", $oppo_st = "not_friends"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "not_engagements", $oppo_st = "f_c2_exist_both_engagements_v1_rev"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c2_exist_both_engagements_v1_rev' && $oppo_st == 'not_friends') . '-' . 
          (int) ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_exist_both_engagements_v1_rev');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 23: E_T 17->9-conf_1 + E_T 9->17-conf_1 GD-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 23-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 23-2');
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_exist_both_engagements_v1_rev')
             || ($comp_st == 'f_c2_exist_both_engagements_v1_rev' && $oppo_st == 'not_engagements') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "not_friends", $oppo_st = "e_c2_exist_both_engagements_v1_rev"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c2_exist_both_engagements_v1_rev", $oppo_st = "not_engagements"
        $cond_str = '' . 
          (int) ($comp_st == 'not_friends' && $oppo_st == 'e_c2_exist_both_engagements_v1_rev') . '-' . 
          (int) ($comp_st == 'f_c2_exist_both_engagements_v1_rev' && $oppo_st == 'not_engagements');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 24: E_T 17->9-conf_1 + E_T 9->17-conf_1 GD-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 24-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 24-2');
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        }

    ////////////////////// E_T 17->9-0 + E_T 9->17-conf_1 | F_T 17->9-0 + F_T 9->17-conf_1
    } elseif  ( ($comp_st == 'e_c2_fm0_pending_engagement_ini' && $oppo_st == 'not_friends')
             || ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm0_pending_friend_ini') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c2_fm0_pending_engagement_ini", $oppo_st = "not_friends"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "not_engagements", $oppo_st = "f_c2_fm0_pending_friend_ini"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c2_fm0_pending_engagement_ini' && $oppo_st == 'not_friends') . '-' . 
          (int) ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm0_pending_friend_ini');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 25: E_T 17->9-0 + E_T 9->17-conf_1 LS-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 25-1'); 
            $button_args = $button_func("pending_{$comp}", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 25-2');
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm0_pending_engagement_ini')
             || ($comp_st == 'f_c2_fm0_pending_friend_ini' && $oppo_st == 'not_engagements') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "not_friends", $oppo_st = "e_c2_fm0_pending_engagement_ini"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c2_fm0_pending_friend_ini", $oppo_st = "not_engagements"
        $cond_str = '' . 
          (int) ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm0_pending_engagement_ini') . '-' . 
          (int) ($comp_st == 'f_c2_fm0_pending_friend_ini' && $oppo_st == 'not_engagements');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 26: E_T 17->9-0 + E_T 9->17-conf_1 LS-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 26-1'); 
            $button_args = $button_func("remove_{$oppo}s_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 26-2');
            $button_args = $button_func("pending_{$comp}", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'e_c2_fm0_awaiting_response_rev' && $oppo_st == 'not_friends')
             || ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm0_pending_friend_rev') ) {
        // E_cond $comp = "engagement", $oppo = "friend",     $comp_st = "e_c2_fm0_awaiting_response_rev", $oppo_st = "not_friends"
        // F_cond $comp = "engagement", $oppo = "friend",     $comp_st = "not_engagements", $oppo_st = "f_c2_fm0_pending_friend_rev"
        $cond_str = '' . 
          (int) ($comp_st == 'e_c2_fm0_awaiting_response_rev' && $oppo_st == 'not_friends') . '-' . 
          (int) ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm0_pending_friend_rev');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log('gba||> ' . $cond_str . ' - '  . $db . ' - condId: 27: E_T 17->9-0 + E_T 9->17-conf_1 GD-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 27-1'); 
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 27-2');
            $button_args = $button_func("awaiting_response_{$oppo}", $pid, $sg, $rel_id);
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm0_awaiting_response_rev')
             || ($comp_st == 'f_c2_fm0_pending_friend_rev' && $oppo_st == 'not_engagements') ) {
        // E_cond $comp = "friend",     $oppo = "engagement", $comp_st = "not_friends", $oppo_st = "e_c2_fm0_awaiting_response_rev"
        // F_cond $comp = "friend",     $oppo = "engagement", $comp_st = "f_c2_fm0_pending_friend_rev", $oppo_st = "not_engagements"
        $cond_str = '' . 
          (int) ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm0_awaiting_response_rev') . '-' . 
          (int) ($comp_st == 'f_c2_fm0_pending_friend_rev' && $oppo_st == 'not_engagements');
        $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
        error_log(('
        gba||> conds: ' . $cond_str . ' From_db: '  . $db . ' condition_Id: 28: E_T 17->9-0 + E_T 9->17-conf_1 GD-R_btn'));
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 28-1'); 
            $button_args = $button_func("awaiting_response_{$comp}", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: 28-2');
            $button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
        }


    ////////////////////// fallback buttons
	} elseif ($is_reversed == 1) {
		error_log('gba||>  rev only ');
		if (true) {
            error_log('gba||> btn_id: rev only-1'); 
            $button_args = $button_func("remove_{$oppo}_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: rev only-2');
            $button_args = $button_func("add_{$comp}s_from_reciver", $pid, $sg, $rel_id);
        }

	} else {

		error_log('gba||>  els only ');
		if (true) {
            error_log('gba||> btn_id: els only-1'); 
            $button_args = $button_func("remove_{$oppo}_from_reciver", $pid, $sg, $rel_id);
        } else {
            error_log('gba||> btn_id: els only-2');
            $button_args = $button_func("add_{$comp}s_from_reciver", $pid, $sg, $rel_id);
        }
	}

	error_log('<<<<<<<<-: '.$mk);
	error_log('');
	return $button_args;
}
function get_template_vars($pid, $comp) {
	$oppo = $comp == 'friend' ? 'engagement' : 'friend'; 
	$user_id = bp_loggedin_user_id();
	$fst = bp_is_friend( $pid );
	$est = bp_is_engagement( $pid );
	$sg = $comp == 'friend' ? bp_get_friends_slug() : bp_get_engagements_slug() ;

	$is_reversed = (int) is_oppsit_relation($comp);
	$is_member = bp_current_component() == 'members';

	$ini_e_id = get_relation('engagement');
	$rev_e_id = get_relation('engagement', false);
	$ini_f_id = get_relation('friend');
	$rev_f_id = get_relation('friend', false);

	$f_rel_id = get_friend_id($user_id, $pid);
	$e_rel_id = get_engagement_id($user_id, $pid);
	
	$ini_f = (int) is_initiator('friend');
	$ini_e = (int) is_initiator('engagement');
	$status = $ini_f > $ini_e ? $fst : $est ;

	$rev_e_awa = is_reciver_awating('engagement');
	$ini_e_awa = is_initial_awating('engagement');
	$rev_f_awa = is_reciver_awating('friend');
	$ini_f_awa = is_initial_awating('friend');

	$ini_id = ($comp == 'friend' ? $ini_f_id  : $ini_e_id) ?? $f_rel_id ;
	$rev_id = ($comp == 'friend' ? $rev_e_id  : $rev_f_id) ?? $e_rel_id ;

	$comp_st = ($comp == 'friend' ? $fst  : $est);
	$oppo_st = ($comp == 'friend' ? $est  : $fst);

	$rel_id = ($is_reversed == '1' ? $rev_id  : $ini_id);
    $button_func = $comp == 'friend' ? 'friend_btn_args' : 'engagement_btn_args';

	error_log('gtv ');
	error_log('gtv >rev_f_awa ini_f_awa rev_e_awa ini_e_awa: '.json_encode( $rev_f_awa . ', ' .$ini_f_awa . ', ' . $rev_e_awa . ', ' .$ini_e_awa ));
	error_log('gtv ================================= $ini_e: '.$ini_e);
	error_log('gtv ==================================$ini_f: '.$ini_f);
	error_log('gtv ===========================friend_status: '.$fst);
	error_log('gtv =======================engagement_status: '.$est);
	error_log('gtv ============================$is_reversed: '.$is_reversed);
	error_log('gtv =============================$relation_f: '.$f_rel_id);	
	error_log('gtv =============================$relation_e: '.$e_rel_id);	
	error_log('gtv =====ini_f_id rev_f_id ini_e_id rev_e_id: '.$ini_f_id . ', ' . $rev_f_id . ', ' . $ini_e_id  . ', ' . $rev_e_id );
	error_log('gtv =================================$status: '.$status);
	error_log('gtv =====================================$sg: '.$sg);
	error_log('gtv ============================$button_func: '.$button_func);
	error_log('gtv  ');

	return [
		$pid,
		$sg,
		$is_reversed,
		$status,
		$is_member,
		$button_func,
		$comp_st,
		$oppo_st,
		$rel_id,
	];
}
