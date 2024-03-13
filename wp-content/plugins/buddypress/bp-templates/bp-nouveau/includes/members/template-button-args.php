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
    error_log('// 00-XTB $comp = ' . $comp . ', $oppo = ' . $oppo . ', $comp_st = ' . $comp_st . ', $oppo_st = ' . $oppo_st);

    $cond_args = array();
    $button_args = array();

    if (false) {
        return;   
    } elseif (true) {  
        

        ////////////////////// ETB 17->9-conf_0 | FTB 17->9-conf_0
        // 01-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_pending_engagement_ini, $oppo_st = not_friends
        // 01-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c1_pending_friend_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '01',
            'e_c1_pending_engagement_ini',
            'not_friends',
            'not_engagements',
            'f_c1_pending_friend_ini',
            "pending_{$comp}",
            "not_{$comp}s",
        );
        if ($cond_args) { return $cond_args; }

    
        
        // 02-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c1_pending_engagement_ini
        // 02-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_pending_friend_ini, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '02',
            'not_friends',
            'e_c1_pending_engagement_ini',
            'f_c1_pending_friend_ini' ,
            'not_engagements',
            "not_{$comp}s",
            "pending_{$comp}",
        ) ;
        if ($cond_args) { return $cond_args; }    
        // 03-ETB  $comp = engagement, $oppo = friend, $comp_st = e_c1_awaiting_response_rev, $oppo_st = not_friends
        // 03-ETB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c1_awaiting_response_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '03',
            'e_c1_awaiting_response_rev',
            'not_friends',
            'not_engagements',
            'f_c1_awaiting_response_rev',
            "add_{$comp}s_from_receiver",
            "awaiting_response_{$oppo}",
        ) ;
        if ($cond_args) { return $cond_args;}

    
        // 04-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c1_awaiting_response_rev
        // 04-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_awaiting_response_rev, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '04',
            'not_friends',
            'e_c1_awaiting_response_rev',
            'f_c1_awaiting_response_rev' ,
            'not_engagements',
            "awaiting_response_{$oppo}",
            "add_{$comp}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}

    
        
        ////////////////////// ETB 17->9-conf_1 | FTB 17->9-conf_1
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_engagement_ini, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c1_is_friend_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '05',
            'e_c1_is_engagement_ini',
            'not_friends',
            'not_engagements',
            'f_c1_is_friend_ini',
            "remove_{$comp}s",
            "not_{$comp}s",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c1_is_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_friend_ini, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '06',
            'not_friends',
            'e_c1_is_engagement_ini',
            'f_c1_is_friend_ini' ,
            'not_engagements',
            "not_{$comp}s",
            "remove_{$comp}s",
        ) ;
        if ($cond_args) { return $cond_args;}
            
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_reverse_friend_rev, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c1_is_reverse_engagement_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '07',
            'e_c1_is_reverse_friend_rev',
            'not_friends',
            'not_engagements',
            'f_c1_is_reverse_engagement_rev',
            "add_{$comp}s_from_receiver",
            "remove_{$oppo}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c1_is_reverse_friend_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_reverse_engagement_rev, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '08',
            'not_friends',
            'e_c1_is_reverse_friend_rev',
            'f_c1_is_reverse_engagement_rev',
            'not_engagements',
            "remove_{$oppo}s_from_receiver",
            "add_{$comp}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}

    
        
        ////////////////////// ETB 17->9-conf_1 + FTB 17->9-conf_0 | FTB 17->9-conf_1 + ETB 17->9-conf_0
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_engagement_ini, $oppo_st = f_c1_pending_friend_ini
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_pending_engagement_ini, $oppo_st = f_c1_is_friend_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '09',
            'e_c1_is_engagement_ini',
            'f_c1_pending_friend_ini',
            'e_c1_pending_engagement_ini',
            'f_c1_is_friend_ini',
            "remove_{$comp}s",
            "pending_{$comp}",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_pending_friend_ini, $oppo_st = e_c1_is_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_friend_ini, $oppo_st = e_c1_pending_engagement_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '10',
            'f_c1_pending_friend_ini',
            'e_c1_is_engagement_ini',
            'f_c1_is_friend_ini' ,
            'e_c1_pending_engagement_ini',
            "pending_{$comp}",
            "remove_{$comp}s",
        ) ;
        if ($cond_args) { return $cond_args;}
            
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_reverse_friend_rev, $oppo_st = f_c1_awaiting_response_rev
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_awaiting_response_rev, $oppo_st = f_c1_is_reverse_engagement_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '11',
            'e_c1_is_reverse_friend_rev',
            'f_c1_awaiting_response_rev',
            'e_c1_awaiting_response_rev' ,
            'f_c1_is_reverse_engagement_rev',
            "awaiting_response_{$oppo}",
            "awaiting_response_{$comp}",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_awaiting_response_rev, $oppo_st = e_c1_is_reverse_friend_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_reverse_engagement_rev, $oppo_st = e_c1_awaiting_response_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '12',
            'f_c1_awaiting_response_rev',
            'e_c1_is_reverse_friend_rev',
            'f_c1_is_reverse_engagement_rev',
            'e_c1_awaiting_response_rev',
            "remove_{$oppo}s_from_receiver",
            "remove_{$comp}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}


        
        // this condition always take 1st/Ed condition
        ////////////////////// ETB 17->9-conf_1 + FTB 17->9-conf_1 | FTB 17->9-conf_1 + ETB 17->9-conf_1 
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_engagement_ini, $oppo_st = f_c1_is_friend_ini
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_engagement_ini, $oppo_st = f_c1_is_friend_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '13',
            'e_c1_is_engagement_ini',
            'f_c1_is_friend_ini',
            'e_c1_is_engagement_ini',
            'f_c1_is_friend_ini',
            "remove_{$comp}s",
            "remove_{$oppo}s",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_friend_ini, $oppo_st = e_c1_is_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_friend_ini, $oppo_st = e_c1_is_engagement_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '14',
            'f_c1_is_friend_ini',
            'e_c1_is_engagement_ini',
            'f_c1_is_friend_ini' ,
            'e_c1_is_engagement_ini',
            "remove_{$comp}s",
            "remove_{$oppo}s",
        ) ;
        if ($cond_args) { return $cond_args;}
            
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_reverse_friend_rev, $oppo_st = f_c1_is_reverse_engagement_rev
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_is_reverse_friend_rev, $oppo_st = f_c1_is_reverse_engagement_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '15',
            'e_c1_is_reverse_friend_rev',
            'f_c1_is_reverse_engagement_rev',
            'e_c1_is_reverse_friend_rev' ,
            'f_c1_is_reverse_engagement_rev',
            "remove_{$oppo}s_from_receiver",
            "remove_{$comp}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_reverse_engagement_rev, $oppo_st = e_c1_is_reverse_friend_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_is_reverse_engagement_rev, $oppo_st = e_c1_is_reverse_friend_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '16',
            'f_c1_is_reverse_engagement_rev',
            'e_c1_is_reverse_friend_rev',
            'f_c1_is_reverse_engagement_rev',
            'e_c1_is_reverse_friend_rev',
            "remove_{$oppo}s_from_receiver",
            "remove_{$comp}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}



        ////////////////////// ETB 17->9-conf_1 + ETB 9->17-conf_0 | FTB 17->9-conf_1 + FTB 9->17-conf_0
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_fm1_is_engagement_ini, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_fm1_is_friend_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '17',
            'e_c2_fm1_is_engagement_ini',
            'not_friends',
            'not_engagements',
            'f_c2_fm1_is_friend_ini',
            "remove_{$comp}s",
            "awaiting_response_{$oppo}",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_fm1_is_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_fm1_is_friend_ini, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '18',
            'not_friends',
            'e_c2_fm1_is_engagement_ini',
            'f_c2_fm1_is_friend_ini' ,
            'not_engagements',
            "awaiting_response_{$oppo}",
            "remove_{$comp}s",
        ) ;
        if ($cond_args) { return $cond_args;}
            
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_fm1_is_reverse_friend_rev, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_fm1_is_friend_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '19',
            'e_c2_fm1_is_reverse_friend_rev',
            'not_friends',
            'not_engagements',
            'f_c2_fm1_is_friend_rev',
            "pending_{$comp}",
            "remove_{$oppo}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_fm1_is_reverse_friend_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_fm1_is_friend_rev, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '20',
            'not_friends',
            'e_c2_fm1_is_reverse_friend_rev',
            'f_c2_fm1_is_friend_rev' ,
            'not_engagements',
            "remove_{$oppo}s_from_receiver",
            "pending_{$comp}",
        ) ;
        if ($cond_args) { return $cond_args;}

    

        ////////////////////// ETB 17->9-conf_1 + ETB 9->17-conf_1 | FTB 17->9-conf_1 + FTB 9->17-conf_1
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_exist_both_engagements_v1_ini, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_exist_both_engagements_v1_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '21',
            'e_c2_exist_both_engagements_v1_ini',
            'not_friends',
            'not_engagements',
            'f_c2_exist_both_engagements_v1_ini',
            "remove_{$comp}s",
            "remove_{$oppo}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_exist_both_engagements_v1_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_exist_both_engagements_v1_ini, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '22',
            'not_friends',
            'e_c2_exist_both_engagements_v1_ini',
            'f_c2_exist_both_engagements_v1_ini' ,
            'not_engagements',
            "remove_{$oppo}s_from_receiver",
            "remove_{$comp}s",
        ) ;
        if ($cond_args) { return $cond_args;}
            
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_exist_both_engagements_v1_rev, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_exist_both_engagements_v1_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '23',
            'e_c2_exist_both_engagements_v1_rev',
            'not_friends',
            'not_engagements',
            'f_c2_exist_both_engagements_v1_rev',
            "remove_{$comp}s",
            "remove_{$oppo}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_exist_both_engagements_v1_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_exist_both_engagements_v1_rev, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '24',
            'not_friends',
            'e_c2_exist_both_engagements_v1_rev',
            'f_c2_exist_both_engagements_v1_rev' ,
            'not_engagements',
            "remove_{$oppo}s_from_receiver",
            "remove_{$comp}s",
        ) ;
        if ($cond_args) { return $cond_args;}

    
        
        ////////////////////// ETB 17->9-conf_0 + ETB 9->17-conf_1 | FTB 17->9-conf_0 + FTB 9->17-conf_1
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_fm0_pending_engagement_ini, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_fm0_pending_friend_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '25',
            'e_c2_fm0_pending_engagement_ini',
            'not_friends',
            'not_engagements',
            'f_c2_fm0_pending_friend_ini',
            "pending_{$comp}",
            "remove_{$oppo}s_from_receiver",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $oppo = engagement, $comp_st = not_friends, $oppo_st = e_c2_fm0_pending_engagement_ini
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_fm0_pending_friend_ini, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '26',
            'not_friends',
            'e_c2_fm0_pending_engagement_ini',
            'f_c2_fm0_pending_friend_ini' ,
            'not_engagements',
            "remove_{$oppo}s_from_receiver",
            "pending_{$comp}",
        ) ;
        if ($cond_args) { return $cond_args;}
            
        // 00-ETB $comp = engagement, $oppo = friend, $comp_st = e_c2_fm0_awaiting_response_rev, $oppo_st = not_friends
        // 00-FTB $comp = engagement, $oppo = friend, $comp_st = not_engagements, $oppo_st = f_c2_fm0_pending_friend_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '27',
            'e_c2_fm0_awaiting_response_rev',
            'not_friends',
            'not_engagements',
            'f_c2_fm0_pending_friend_rev',
            "remove_{$comp}s",
            "awaiting_response_{$oppo}",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 00-ETB $comp = friend, $opment, $comp_st = not_friends, $oppo_st = e_c2_fm0_awaiting_response_rev
        // 00-FTB $comp = friend, $oppo = engagement, $comp_st = f_c2_fm0_pending_friend_rev, $oppo_st = not_engagements
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '28',
            'not_friends',
            'e_c2_fm0_awaiting_response_rev',
            'f_c2_fm0_pending_friend_rev' ,
            'not_engagements',
            "awaiting_response_{$comp}",
            "remove_{$comp}s",
        ) ;
        if ($cond_args) { return $cond_args;}



        // this condition always take 1st/Ed condition 
        ////////////////////// ETB 17->9-conf_0 + ETB 17->9-conf_0 | FTB 9->17-conf_0 + FTB 9->17-conf_0
        // 29-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_pending_engagement_ini, $oppo_st = f_c1_pending_friend_ini
        // 29-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_pending_engagement_ini, $oppo_st = f_c1_pending_friend_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '29',
            'e_c1_pending_engagement_ini',
            'f_c1_pending_friend_ini',
            'e_c1_pending_engagement_ini',
            'f_c1_pending_friend_ini',
            "pending_{$comp}",
            "pending_{$comp}",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 30-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_pending_friend_ini, $oppo_st = e_c1_pending_engagement_ini
        // 30-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_pending_friend_ini, $oppo_st = e_c1_pending_engagement_ini
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '30',
            'f_c1_pending_friend_ini',
            'e_c1_pending_engagement_ini',
            'f_c1_pending_friend_ini' ,
            'e_c1_pending_engagement_ini',
            "pending_{$oppo}",
            "pending_{$oppo}",
        ) ;
        if ($cond_args) { return $cond_args;}
            
        // 31-ETB $comp = engagement, $oppo = friend, $comp_st = e_c1_awaiting_response_rev, $oppo_st = f_c1_awaiting_response_rev
        // 31-FTB $comp = engagement, $oppo = friend, $comp_st = e_c1_awaiting_response_rev, $oppo_st = f_c1_awaiting_response_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '31',
            'e_c1_awaiting_response_rev',
            'f_c1_awaiting_response_rev',
            'e_c1_awaiting_response_rev' ,
            'f_c1_awaiting_response_rev',
            "awaiting_response_{$oppo}",
            "awaiting_response_{$oppo}",
        ) ;
        if ($cond_args) { return $cond_args;}

        // 32-ETB $comp = friend, $oppo = engagement, $comp_st = f_c1_awaiting_response_rev, $oppo_st = e_c1_awaiting_response_rev
        // 32-FTB $comp = friend, $oppo = engagement, $comp_st = f_c1_awaiting_response_rev, $oppo_st = e_c1_awaiting_response_rev
        $cond_args = cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, '32',
            'f_c1_awaiting_response_rev',
            'e_c1_awaiting_response_rev',
            'f_c1_awaiting_response_rev' ,
            'e_c1_awaiting_response_rev',
            "awaiting_response_{$comp}",
            "awaiting_response_{$comp}",
        ) ;
        if ($cond_args) { return $cond_args;}

    
    
        ////////////////////// fallback buttons
    } else {
    	error_log('|>>>>  els only ');
		if (true) {
            error_log('|>>>> btn_id: el-ETB');
            $button_args = $relation_btn("remove_{$oppo}_from_receiver");
        } else {
            error_log('|>>>> btn_id: el-FTB');
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

	// $f_rel_id = get_friend_id($user_id, $pid);
	// $e_rel_id = get_engagement_id($user_id, $pid);
	
	$ini_f = (int) is_initiator('friend');
	$ini_e = (int) is_initiator('engagement');
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

	error_log('gtv ');
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
	// error_log('gtv =====================================$sg: '.$sg);
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
