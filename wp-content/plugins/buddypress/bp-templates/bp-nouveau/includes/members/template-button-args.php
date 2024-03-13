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
	$button_args = array();
	list(
		$pid,
		$is_btn_reversed,
		$is_member,
		$comp_st,
		$oppo_st,
		$relation_btn,
	) = get_template_vars($pid, $comp);
    error_log('// 00-XTB $comp = ' . $comp . ', $oppo = ' . $oppo . ', $comp_st = ' . $comp_st . ', $oppo_st = ' . $oppo_st);
	
    if (false) {
		return;
	// } elseif ( $is_member ) {
	// 	// one initiator
	// 	$button_args = $relation_btn($status);

    ////////////////////// ETB 17->9-conf_0 | FTB 17->9-conf_0
    } elseif  ( ($comp_st == 'e_c1_pending_engagement_ini' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_pending_friend_ini') ) {
        $cond_str = '' . 
        (int)   ($comp_st == 'e_c1_pending_engagement_ini' && $oppo_st == 'not_friends') . '-' . 
        (int)   ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_pending_friend_ini');
        // 01-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_pending_engagement_ini, $oppo_st = not_friends
        // 01-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c1_pending_friend_ini
        
        $db = get_db_and_log($cond_str, '01: ETB 17->9-conf_0 LS-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 01-ETB');
            $button_args = $relation_btn("pending_{$comp}");
        } else {
            error_log('gba||> btn_id: 01-FTB');
            $button_args = $relation_btn("not_{$comp}s");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c1_pending_engagement_ini')
            ||  ($comp_st == 'f_c1_pending_friend_ini' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)   ($comp_st == 'not_friends' && $oppo_st == 'e_c1_pending_engagement_ini') . '-' . 
        (int)   ($comp_st == 'f_c1_pending_friend_ini' && $oppo_st == 'not_engagements');
        // 02-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c1_pending_engagement_ini
        // 02-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_pending_friend_ini, $oppo_st = not_engagements
        
        $db = get_db_and_log($cond_str, '02: ETB 17->9-conf_0 LS-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 02-ETB');
            $button_args = $relation_btn("not_{$comp}s");
        } else {
            error_log('gba||> btn_id: 02-FTB');
            $button_args = $relation_btn("pending_{$comp}");
        }

    } elseif  ( ($comp_st == 'e_c1_awaiting_response_rev' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_awaiting_response_rev') ) {
        $cond_str = '' . 
        (int)   ($comp_st == 'e_c1_awaiting_response_rev' && $oppo_st == 'not_friends') . '-' . 
        (int)   ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_awaiting_response_rev');
        // 03-ETB  $comp = engagement, $oppo = friend, $comp_st = e_c1_awaiting_response_rev, $oppo_st = not_friends
        // 03-ETB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c1_awaiting_response_rev
        $db = get_db_and_log($cond_str, '03: ETB 17->9-conf_0 GD-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 03-ETB');
            $button_args = $relation_btn("add_{$comp}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 03-FTB');
            $button_args = $relation_btn("awaiting_response_{$oppo}");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c1_awaiting_response_rev')
            ||  ($comp_st == 'f_c1_awaiting_response_rev' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)   ($comp_st == 'not_friends' && $oppo_st == 'e_c1_awaiting_response_rev') . '-' . 
        (int)   ($comp_st == 'f_c1_awaiting_response_rev' && $oppo_st == 'not_engagements');
        // 04-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c1_awaiting_response_rev
        // 04-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_awaiting_response_rev, $oppo_st = not_engagements
        $db = get_db_and_log($cond_str, '04: ETB 17->9-conf_0 GD-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 04-ETB');
            $button_args = $relation_btn("awaiting_response_{$oppo}");
        } else {
            error_log('gba||> btn_id: 04-FTB');
            $button_args = $relation_btn("add_{$comp}s_from_receiver");
        }


    ////////////////////// ETB 17->9-conf_1 | FTB 17->9-conf_1
    } elseif  ( ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_is_friend_ini') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'not_friends') . '-' . 
        (int)     ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_is_friend_ini');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_engagement_ini, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c1_is_friend_ini
        
        $db = get_db_and_log($cond_str, '05: ETB 17->9-conf_1 LS-L_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 05-ETB');
            $button_args = $relation_btn("remove_{$comp}s");
        } else {
            error_log('gba||> btn_id: 05-FTB');
            $button_args = $relation_btn("not_{$comp}s");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c1_is_engagement_ini')
            ||  ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'not_friends' && $oppo_st == 'e_c1_is_engagement_ini') . '-' . 
        (int)     ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'not_engagements');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c1_is_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_friend_ini, $oppo_st = not_engagements
        
        $db = get_db_and_log($cond_str, '06: ETB 17->9-conf_1 LS-R_btn');
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 06-ETB');
            $button_args = $relation_btn("not_{$comp}s");
        } else {
            error_log('gba||> btn_id: 06-FTB');
            $button_args = $relation_btn("remove_{$comp}s");
        }

    } elseif  ( ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_is_reverse_engagement_rev') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'not_friends') . '-' . 
        (int)     ($comp_st == 'not_engagements' && $oppo_st == 'f_c1_is_reverse_engagement_rev');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_reverse_friend_rev, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c1_is_reverse_engagement_rev
        $db = get_db_and_log($cond_str, '07: ETB 17->9-conf_1 GD-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 07-ETB');
            $button_args = $relation_btn("add_{$comp}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 07-FTB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c1_is_reverse_friend_rev')
            ||  ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'not_friends' && $oppo_st == 'e_c1_is_reverse_friend_rev') . '-' . 
        (int)     ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'not_engagements');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c1_is_reverse_friend_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_reverse_engagement_rev, $oppo_st = not_engagements
        $db = get_db_and_log($cond_str, '08: ETB 17->9-conf_1 GD-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 08-ETB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 08-FTB');
            $button_args = $relation_btn("add_{$comp}s_from_receiver");
        }

    ////////////////////// ETB 17->9-conf_1 + FTB 17->9-0 | FTB 17->9-conf_1 + ETB 17->9-0
    } elseif  ( ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_pending_friend_ini')
            ||  ($comp_st == 'e_c1_pending_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_pending_friend_ini') . '-' . 
        (int)     ($comp_st == 'e_c1_pending_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_engagement_ini, $oppo_st = f_c1_pending_friend_ini
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_pending_engagement_ini, $oppo_st = f_c1_is_friend_ini
        $db = get_db_and_log($cond_str, '09: ETB 17->9-conf_1 + FTB 17->9-0 LS-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 09-ETB');
            $button_args = $relation_btn("remove_{$comp}s");
        } else {
            error_log('gba||> btn_id: 09-FTB');
            $button_args = $relation_btn("pending_{$comp}");
        }

    } elseif  ( ($comp_st == 'f_c1_pending_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini')
            ||  ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_pending_engagement_ini') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'f_c1_pending_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini') . '-' . 
        (int)     ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_pending_engagement_ini');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_pending_friend_ini, $oppo_st = e_c1_is_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_friend_ini, $oppo_st = e_c1_pending_engagement_ini
        $db = get_db_and_log($cond_str, '10: ETB 17->9-conf_1 + FTB 17->9-0 LS-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 10-ETB');
            $button_args = $relation_btn("pending_{$comp}");
        } else {
            error_log('gba||> btn_id: 10-FTB');
            $button_args = $relation_btn("remove_{$comp}s");
        }

    } elseif  ( ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_awaiting_response_rev')
            ||  ($comp_st == 'e_c1_awaiting_response_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_awaiting_response_rev') . '-' . 
        (int)     ($comp_st == 'e_c1_awaiting_response_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_reverse_friend_rev, $oppo_st = f_c1_awaiting_response_rev
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_awaiting_response_rev, $oppo_st = f_c1_is_reverse_engagement_rev
        $db = get_db_and_log($cond_str, '11: ETB 17->9-conf_1 + FTB 17->9-0 GD-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 11-ETB');
            $button_args = $relation_btn("awaiting_response_{$oppo}");
        } else {
            error_log('gba||> btn_id: 11-FTB');
            $button_args = $relation_btn("awaiting_response_{$comp}");
        }

    } elseif  ( ($comp_st == 'f_c1_awaiting_response_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev')
            ||  ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_awaiting_response_rev') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'f_c1_awaiting_response_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev') . '-' . 
        (int)     ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_awaiting_response_rev');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_awaiting_response_rev, $oppo_st = e_c1_is_reverse_friend_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_reverse_engagement_rev, $oppo_st = e_c1_awaiting_response_rev
        $db = get_db_and_log($cond_str, '12: ETB 17->9-conf_1 + FTB 17->9-0 GD-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 12-ETB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 12-FTB');
            $button_args = $relation_btn("remove_{$comp}s_from_receiver");
        }


    // this condition always take 1st/Ed condition
    ////////////////////// ETB 17->9-conf_1 + FTB 17->9-conf_1 |  FTB 17->9-conf_1 + ETB 17->9-conf_1 
    } elseif  ( ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini')
            ||  ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini') . '-' . 
        (int)     ($comp_st == 'e_c1_is_engagement_ini' && $oppo_st == 'f_c1_is_friend_ini');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_engagement_ini, $oppo_st = f_c1_is_friend_ini
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_engagement_ini, $oppo_st = f_c1_is_friend_ini
        $db = get_db_and_log($cond_str, '13: ETB 17->9-conf_1 + 17 f 1 LS-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 13-ETB');
            $button_args = $relation_btn("remove_{$comp}s");
        } else {
            error_log('gba||> btn_id: 13-FTB');
            $button_args = $relation_btn("remove_{$oppo}s");
        }

    } elseif  ( ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini')
            ||  ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini') . '-' . 
        (int)     ($comp_st == 'f_c1_is_friend_ini' && $oppo_st == 'e_c1_is_engagement_ini');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_friend_ini, $oppo_st = e_c1_is_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_friend_ini, $oppo_st = e_c1_is_engagement_ini
        $db = get_db_and_log($cond_str, '14: ETB 17->9-conf_1 + 17 f 1 LS-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 14-ETB');
            $button_args = $relation_btn("remove_{$comp}s");
        } else {
            error_log('gba||> btn_id: 14-FTB');
            $button_args = $relation_btn("remove_{$oppo}s");
        }

    } elseif  ( ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev')
            ||  ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev') . '-' . 
        (int)     ($comp_st == 'e_c1_is_reverse_friend_rev' && $oppo_st == 'f_c1_is_reverse_engagement_rev');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_reverse_friend_rev, $oppo_st = f_c1_is_reverse_engagement_rev
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_reverse_friend_rev, $oppo_st = f_c1_is_reverse_engagement_rev
        $db = get_db_and_log($cond_str, '15: ETB 17->9-conf_1 + 17 f 1 GD-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 15-ETB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 15-FTB');
            $button_args = $relation_btn("remove_{$comp}s_from_receiver");
        }

    } elseif  ( ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev')
            ||  ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev') . '-' . 
        (int)     ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c1_is_reverse_friend_rev');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_reverse_engagement_rev, $oppo_st = e_c1_is_reverse_friend_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_reverse_engagement_rev, $oppo_st = e_c1_is_reverse_friend_rev
        $db = get_db_and_log($cond_str, '16: ETB 17->9-conf_1 + 17 f 1 GD-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 16-ETB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 16-FTB');
            $button_args = $relation_btn("remove_{$comp}s_from_receiver");
        }



    ////////////////////// ETB 17->9-conf_1 + ETB 9->17-0 | FTB 17->9-conf_1 + FTB 9->17-0
    } elseif  ( ($comp_st == 'e_c2_fm1_is_engagement_ini' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm1_is_friend_ini') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c2_fm1_is_engagement_ini' && $oppo_st == 'not_friends') . '-' . 
        (int)     ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm1_is_friend_ini');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_fm1_is_engagement_ini, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_fm1_is_friend_ini
        $db = get_db_and_log($cond_str, '17: ETB 17->9-conf_1 + ETB 9->17-0 LS-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 17-ETB');
            $button_args = $relation_btn("remove_{$comp}s");
        } else {
            error_log('gba||> btn_id: 17-FTB');
            $button_args = $relation_btn("awaiting_response_{$oppo}");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm1_is_engagement_ini')
            ||  ($comp_st == 'f_c2_fm1_is_friend_ini' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm1_is_engagement_ini') . '-' . 
        (int)     ($comp_st == 'f_c2_fm1_is_friend_ini' && $oppo_st == 'not_engagements');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_fm1_is_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_fm1_is_friend_ini, $oppo_st = not_engagements
        $db = get_db_and_log($cond_str, '18: ETB 17->9-conf_1 + ETB 9->17-0 LS-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 18-ETB');
            $button_args = $relation_btn("awaiting_response_{$oppo}");
        } else {
            error_log('gba||> btn_id: 18-FTB');
            $button_args = $relation_btn("remove_{$comp}s");
        }

    } elseif  ( ($comp_st == 'e_c2_fm1_is_reverse_friend_rev' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm1_is_friend_rev') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c2_fm1_is_reverse_friend_rev' && $oppo_st == 'not_friends') . '-' . 
        (int)     ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm1_is_friend_rev');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_fm1_is_reverse_friend_rev, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_fm1_is_friend_rev
        $db = get_db_and_log($cond_str, '19: ETB 17->9-conf_1 + ETB 9->17-0 GD-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 19-ETB');
            $button_args = $relation_btn("pending_{$comp}");
        } else {
            error_log('gba||> btn_id: 19-FTB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm1_is_reverse_friend_rev')
            ||  ($comp_st == 'f_c2_fm1_is_friend_rev' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm1_is_reverse_friend_rev') . '-' . 
        (int)     ($comp_st == 'f_c2_fm1_is_friend_rev' && $oppo_st == 'not_engagements');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_fm1_is_reverse_friend_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_fm1_is_friend_rev, $oppo_st = not_engagements
        $db = get_db_and_log($cond_str, '20: ETB 17->9-conf_1 + ETB 9->17-0 GD-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 20-ETB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 20-FTB');
            $button_args = $relation_btn("pending_{$comp}");
        }

    ////////////////////// ETB 17->9-conf_1 + ETB 9->17-conf_1 | FTB 17->9-conf_1 + FTB 9->17-conf_1
    } elseif  ( ($comp_st == 'e_c2_exist_both_engagements_v1_ini' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_exist_both_engagements_v1_ini') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c2_exist_both_engagements_v1_ini' && $oppo_st == 'f_c1_is_reverse_engagement_rev') . '-' . 
        (int)     ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_exist_both_engagements_v1_ini');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_exist_both_engagements_v1_ini, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_exist_both_engagements_v1_ini
        $db = get_db_and_log($cond_str, '21: ETB 17->9-conf_1 + ETB 9->17-conf_1 LS-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 21-ETB');
            $button_args = $relation_btn("remove_{$comp}s");
        } else {
            error_log('gba||> btn_id: 21-FTB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_exist_both_engagements_v1_ini')
            ||  ($comp_st == 'f_c2_exist_both_engagements_v1_ini' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'f_c1_is_reverse_engagement_rev' && $oppo_st == 'e_c2_exist_both_engagements_v1_ini') . '-' . 
        (int)     ($comp_st == 'f_c2_exist_both_engagements_v1_ini' && $oppo_st == 'not_engagements');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_exist_both_engagements_v1_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_exist_both_engagements_v1_ini, $oppo_st = not_engagements
        $db = get_db_and_log($cond_str, '22: ETB 17->9-conf_1 + ETB 9->17-conf_1 LS-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 22-ETB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 22-FTB');
            $button_args = $relation_btn("remove_{$comp}s");
        }

    } elseif  ( ($comp_st == 'e_c2_exist_both_engagements_v1_rev' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_exist_both_engagements_v1_rev') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c2_exist_both_engagements_v1_rev' && $oppo_st == 'not_friends') . '-' . 
        (int)     ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_exist_both_engagements_v1_rev');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_exist_both_engagements_v1_rev, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_exist_both_engagements_v1_rev
        $db = get_db_and_log($cond_str, '23: ETB 17->9-conf_1 + ETB 9->17-conf_1 GD-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 23-ETB');
            $button_args = $relation_btn("remove_{$comp}s");
        } else {
            error_log('gba||> btn_id: 23-FTB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_exist_both_engagements_v1_rev')
            ||  ($comp_st == 'f_c2_exist_both_engagements_v1_rev' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'not_friends' && $oppo_st == 'e_c2_exist_both_engagements_v1_rev') . '-' . 
        (int)     ($comp_st == 'f_c2_exist_both_engagements_v1_rev' && $oppo_st == 'not_engagements');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_exist_both_engagements_v1_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_exist_both_engagements_v1_rev, $oppo_st = not_engagements
        $db = get_db_and_log($cond_str, '24: ETB 17->9-conf_1 + ETB 9->17-conf_1 GD-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 24-ETB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 24-FTB');
            $button_args = $relation_btn("remove_{$comp}s");
        }

    ////////////////////// ETB 17->9-0 + ETB 9->17-conf_1 | FTB 17->9-0 + FTB 9->17-conf_1
    } elseif  ( ($comp_st == 'e_c2_fm0_pending_engagement_ini' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm0_pending_friend_ini') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c2_fm0_pending_engagement_ini' && $oppo_st == 'not_friends') . '-' . 
        (int)     ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm0_pending_friend_ini');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_fm0_pending_engagement_ini, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_fm0_pending_friend_ini
        $db = get_db_and_log($cond_str, '25: ETB 17->9-0 + ETB 9->17-conf_1 LS-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 25-ETB');
            $button_args = $relation_btn("pending_{$comp}");
        } else {
            error_log('gba||> btn_id: 25-FTB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm0_pending_engagement_ini')
            ||  ($comp_st == 'f_c2_fm0_pending_friend_ini' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm0_pending_engagement_ini') . '-' . 
        (int)     ($comp_st == 'f_c2_fm0_pending_friend_ini' && $oppo_st == 'not_engagements');
        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_fm0_pending_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_fm0_pending_friend_ini, $oppo_st = not_engagements
        $db = get_db_and_log($cond_str, '26: ETB 17->9-0 + ETB 9->17-conf_1 LS-R_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 26-ETB');
            $button_args = $relation_btn("remove_{$oppo}s_from_receiver");
        } else {
            error_log('gba||> btn_id: 26-FTB');
            $button_args = $relation_btn("pending_{$comp}");
        }

    } elseif  ( ($comp_st == 'e_c2_fm0_awaiting_response_rev' && $oppo_st == 'not_friends')
            ||  ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm0_pending_friend_rev') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'e_c2_fm0_awaiting_response_rev' && $oppo_st == 'not_friends') . '-' . 
        (int)     ($comp_st == 'not_engagements' && $oppo_st == 'f_c2_fm0_pending_friend_rev');
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_fm0_awaiting_response_rev, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_fm0_pending_friend_rev
        $db = get_db_and_log($cond_str, '27: ETB 17->9-0 + ETB 9->17-conf_1 GD-L_btn');
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 27-ETB');
            $button_args = $relation_btn("remove_{$comp}s");
        } else {
            error_log('gba||> btn_id: 27-FTB');
            $button_args = $relation_btn("awaiting_response_{$oppo}");
        }

    } elseif  ( ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm0_awaiting_response_rev')
            ||  ($comp_st == 'f_c2_fm0_pending_friend_rev' && $oppo_st == 'not_engagements') ) {
        $cond_str = '' . 
        (int)     ($comp_st == 'not_friends' && $oppo_st == 'e_c2_fm0_awaiting_response_rev') . '-' . 
        (int)     ($comp_st == 'f_c2_fm0_pending_friend_rev' && $oppo_st == 'not_engagements');
        // 00-ETB $comp = friend, $opment, $comp_st = not_friends, $oppo_st = e_c2_fm0_awaiting_response_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_fm0_pending_friend_rev, $oppo_st = not_engagements
        $db = get_db_and_log($cond_str, '28: ETB 17->9-0 + ETB 9->17-conf_1 GD-R_btn');
        
        
        if ($db == 'Ed') {
            error_log('gba||> btn_id: 28-ETB');
            $button_args = $relation_btn("awaiting_response_{$comp}");
        } else {
            error_log('gba||> btn_id: 28-FTB');
            $button_args = $relation_btn("remove_{$comp}s");
        }


    ////////////////////// fallback buttons
	} elseif ($is_btn_reversed == 1) {
		error_log('gba||>  rev only ');
		if (true) {
            error_log('gba||> btn_id: re-ETB');
            $button_args = $relation_btn("remove_{$oppo}_from_receiver");
        } else {
            error_log('gba||> btn_id: re-FTB');
            $button_args = $relation_btn("add_{$comp}s_from_receiver");
        }

	} else {

		error_log('gba||>  els only ');
		if (true) {
            error_log('gba||> btn_id: el-ETB');
            $button_args = $relation_btn("remove_{$oppo}_from_receiver");
        } else {
            error_log('gba||> btn_id: el-FTB');
            $button_args = $relation_btn("add_{$comp}s_from_receiver");
        }
	}

	error_log('<<<<<<<<-: '.$mk);
	error_log('');
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

	$f_rel_id = get_friend_id($user_id, $pid);
	$e_rel_id = get_engagement_id($user_id, $pid);
	
	$ini_f = (int) is_initiator('friend');
	$ini_e = (int) is_initiator('engagement');
	$status = $ini_f > $ini_e ? $fst : $est ;

	$ini_e_awa = is_initial_awating('engagement');
	$rev_e_awa = is_receiver_awating('engagement');
	$ini_f_awa = is_initial_awating('friend');
	$rev_f_awa = is_receiver_awating('friend');

    $is_btn_reversed = $is_relation_reversed;
	$ini_id = ($comp == 'friend' ? $ini_f_id  : $ini_e_id) ;
	$rev_id = ($comp == 'friend' ? $rev_e_id  : $rev_f_id) ;

	$comp_st = ($comp == 'friend' ? $fst  : $est);
	$oppo_st = ($comp == 'friend' ? $est  : $fst);

	$rel_id = ($is_btn_reversed == '1' ? $rev_id  : $ini_id);
    $relation_btn = function ($status) use ($comp, $pid, $sg, $rel_id) { return relation_btn_args($comp, $status, $pid, $sg, $rel_id); };

	error_log('gtv ');
	error_log('gtv >rev_f_awa ini_f_awa rev_e_awa ini_e_awa: '.json_encode( $rev_f_awa . ', ' .$ini_f_awa . ', ' . $rev_e_awa . ', ' .$ini_e_awa ));
	error_log('gtv ================================= $ini_e: '.$ini_e);
	error_log('gtv ==================================$ini_f: '.$ini_f);
	error_log('gtv ===========================friend_status: '.$fst);
	error_log('gtv =======================engagement_status: '.$est);
	error_log('gtv ======================= $is_btn_reversed: '.$is_btn_reversed);
	error_log('gtv =============================$relation_f: '.$f_rel_id);	
	error_log('gtv =============================$relation_e: '.$e_rel_id);	
	error_log('gtv =====ini_f_id rev_f_id ini_e_id rev_e_id: '.$ini_f_id . ', ' . $rev_f_id . ', ' . $ini_e_id  . ', ' . $rev_e_id );
	error_log('gtv =================================$status: '.$status);
	error_log('gtv =====================================$sg: '.$sg);
	error_log('gtv  ');

	return [
		$pid,
		$is_btn_reversed,
		$is_member,
		$comp_st,
		$oppo_st,
		$relation_btn,
	];
}
