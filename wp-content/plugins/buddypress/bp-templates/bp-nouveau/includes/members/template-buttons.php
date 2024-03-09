<?php
function get_button_args ($pid, $comp) {
	$oppo = $comp == 'friend' ? 'engagement' : 'friend'; 
	//friend_initiator_btn_args
	$initiator_func = "{$comp}_initiator_btn_args";
	//friend_reciver_btn_args
	$reciver_func   = "{$comp}_reciver_btn_args";
	$mk = $comp[0];
	$button_args = array();
	list(
		$ini_e,
		$ini_f,
		$fst,
		$est,
		$pid,
		$sg,
		$f_rel_id,
		$e_rel_id,
		$is_reversed,
		$status,
		$rev_e_awa,
		$rev_f_awa,
		$ini_e_awa,
		$ini_f_awa,
		$ini_f_id,
		$rev_f_id,
		$ini_e_id,
		$rev_e_id,
		$is_member
	) = get_template_vars($pid, $comp);

	$ini_id = ($comp == 'friend' ? $ini_f_id  : $ini_e_id) ?? $f_rel_id ;
	$rev_id = ($comp == 'friend' ? $rev_e_id  : $rev_f_id) ?? $e_rel_id ;

	$ini_aw = ($comp == 'friend' ? $ini_e_awa  : $ini_f_awa);
	$rev_aw = ($comp == 'friend' ? $rev_e_awa  : $rev_f_awa);

	$comp_st = ($comp == 'friend' ? $fst  : $est);
	$oppo_st = ($comp == 'friend' ? $est  : $fst);

	$status = ($comp == 'friend' ? $fst  : $est);

	$button_func = $is_reversed == '1' ? $reciver_func : $initiator_func;
	// error_log('----sts--comp_st--oppo_st');
	// error_log(json_encode($comp_st));
	// error_log(json_encode($oppo_st));
	
	if (false) {
		return;
	} elseif ($is_member ) {
		// one initiator
		error_log(json_encode("is_member "). $button_func);
		$button_args = $button_func($status, $pid, $sg, $ini_id ?? $rev_id);

	} elseif ($comp_st == "is_{$comp}" && $oppo_st == "is_{$oppo}" ) {
		// one initiator
		error_log(json_encode("1.1 isf ise"));
		$button_args = $button_func("is_{$comp}", $pid, $sg, $ini_id);

	} elseif ($comp_st == "pending_{$comp}" && $oppo_st == "is_{$oppo}" ) {
		// both relations are initiator
		error_log(json_encode("1.1 pef ise"));
		$button_args = $button_func("pending_{$comp}", $pid, $sg, $ini_id);

	} elseif ($comp_st == "not_{$comp}s" && $oppo_st == "exist_initiator_{$oppo}") {
		// one reciver
		error_log(json_encode("3.0 nof exi"));
		$button_args = $button_func("remove_initiator_{$oppo}", $pid, $sg, $rev_id);

	} elseif ($comp_st == "exist_initiator_{$comp}" && $oppo_st == "exist_initiator_{$oppo}") {
		// both relations are reciver
		error_log(json_encode("3.3 eif eie"));
		$button_args = $button_func("remove_initiator_{$comp}", $pid, $sg, $rev_id);

	} elseif ($comp_st == "awaiting_response" && $oppo_st == "exist_initiator_{$oppo}") {
		// both relations are reciver
		error_log(json_encode("3.3 awr eie"));
		$button_args = $button_func("awaiting_response", $pid, $sg, $rev_id);
		
	} elseif ($comp_st == "not_{$comp}s" && $oppo_st == "exist_more_{$oppo}s" && $rev_aw == "1" ) {
		// 17 e 1 + 9 e 0						
		error_log(json_encode("4.0 nof eme re"));
		$button_args = $button_func("awaiting_response", $pid, $sg, $rev_id);

	} elseif ($comp_st == "not_{$comp}s" && $oppo_st == "exist_more_{$oppo}s" && $ini_aw == "1" ) {
		// 17 e 1 + 9 e 0						
		error_log(json_encode("4.0 not_ exist_more_ ia=1"));
		$button_args = $button_func("remove_more_engagements", $pid, $sg, $rev_id);

	} elseif ($status === "exist_more_{$comp}s") {
		error_log(json_encode("em comp"));
		$button_args = $button_func("is_{$comp}", $pid, $sg, $ini_id);

	} elseif ($status === "exist_initiator_{$comp}") {
		error_log(json_encode("eif"));
		$button_args = $button_func("remove_initiator_{$comp}", $pid, $sg, $rev_id);

	} elseif ($comp_st == "not_{$comp}s" && $oppo_st == "pending_{$oppo}" && $is_reversed == "0") {
		error_log(json_encode("nof pee rev0"));
		$button_args = $button_func("pending_{$comp}", $pid, $sg, $rev_id);

	} elseif ($comp_st == "not_{$comp}s" && $oppo_st == "pending_{$oppo}" && $is_reversed == "1") {
		error_log(json_encode("not pending rev1"));
		$button_args = $button_func("remove_more_{$oppo}s", $pid, $sg, $rev_id);

	} elseif ($comp_st == "not_{$comp}s" && $oppo_st == "awaiting_response" && $is_reversed == "1") {
		error_log(json_encode("nof awr rev1"));
		$button_args = $button_func("exist_more_{$oppo}s", $pid, $sg, $ini_id);

	} elseif ($comp_st == "not_{$comp}s" && $oppo_st == "awaiting_response" && $is_reversed == "0") {
		error_log(json_encode("nof awr rev0"));
		$button_args = $button_func("awaiting_response", $pid, $sg, $rev_id);

	} elseif ($comp_st == "not_{$comp}s" && $oppo_st== "exist_more_{$oppo}s" && $ini_aw == "1" ) {
		error_log(json_encode("em oppo inia"));
		$button_args = $button_func("remove_more_{$oppo}s", $pid, $sg, $rev_id);
	
	} elseif ($comp_st == "not_{$comp}s" && $oppo_st== "exist_more_{$oppo}s" && $rev_aw == "1" ) {
		error_log(json_encode("em oppo reva"));
		$button_args = $button_func("remove_more_{$oppo}s", $pid, $sg, $rev_id);

	} elseif ($is_reversed == 1) {
		error_log(json_encode("rev only "));
		$button_args = $button_func($status, $pid, $sg, $rev_id);

	} else {
		error_log(json_encode("els only "));
		$button_args = $button_func($status, $pid, $sg, $ini_id);
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
		
		error_log('');
		error_log('>>await rfa ifa rea iea: '.json_encode( $rev_f_awa . ', ' .$ini_f_awa . ', ' . $rev_e_awa . ', ' .$ini_e_awa ));
		error_log('>>>>>>>>>>>>>>>e $ini_e: '.$ini_e);
		error_log('=================$ini_f: '.$ini_f);
		error_log('==========friend_status: '.$fst);
		error_log('======engagement_status: '.$est);
		error_log('===========$is_reversed: '.$is_reversed);
		// error_log('===============$user_id: '.$user_id);
		// error_log('===================$pid: '.$pid);
		error_log('============$relation_f: '.$f_rel_id);	
		error_log('============$relation_e: '.$e_rel_id);	
		error_log('========ifi rfi iei rei: '.$ini_f_id . ', ' . $rev_f_id . ', ' . $ini_e_id  . ', ' . $rev_e_id );
		error_log('================$status: '.$status);
		error_log('====================$sg: '.$sg);
		error_log('==$bp_current_component: '.bp_current_component());

		return [
			$ini_e,
			$ini_f,
			$fst,
			$est,
			$pid,
			$sg,
			$f_rel_id,
			$e_rel_id,
			$is_reversed,
			$status,
			$rev_e_awa,
			$rev_f_awa,
			$ini_e_awa,
			$ini_f_awa,
			$ini_f_id,
			$rev_f_id,
			$ini_e_id,
			$rev_e_id,
			$is_member
		];
}

function get_friend_id($user_id, $potential_engagement_id) {
	$friendship_id = BP_Friends_Friendship::get_friendship_id($user_id, $potential_engagement_id);
	if (!empty($friendship_id)) {
		$fri_rel = BP_Friends_Friendship::get_friendships_by_id($friendship_id)[0];
		if ( ! empty($fri_rel)) {
			$f_rel_id = $fri_rel->id;
		}
	}
	return $f_rel_id ?? 0 ;
}

function get_engagement_id($user_id, $potential_engagement_id) {
	$engagementship_id = BP_Engagements_Engagementship::get_engagementship_id($user_id, $potential_engagement_id);
	if (!empty($engagementship_id)) {
		$eng_rel = BP_Engagements_Engagementship::get_engagementships_by_id($engagementship_id)[0];
		if ( ! empty($eng_rel)) {
			$e_rel_id = $eng_rel->id;
		}
	}
	return $e_rel_id ?? 0 ;
}

function get_relation($table, $initiator = 1) {
	$user_id = bp_loggedin_user_id();
	$member_id = bp_get_member_user_id();
	if (empty($initiator)) {
		[$user_id , $member_id] = [$member_id , $user_id];
	}
	global $wpdb;
	$result = $wpdb->get_results( "SELECT * FROM wp_bp_{$table}s WHERE initiator_user_id = {$user_id} AND {$table}_user_id = {$member_id} ",ARRAY_N );
	// return 1st id from database, otherwise return null
	return $result[0][0] ?? 0;
}

function is_oppsit_relation($table) {
	$oppsite = $table == 'friend' ? 'engagement' : 'friend';
	$user_id = bp_loggedin_user_id();
	$member_id = bp_get_member_user_id();

	global $wpdb;
	$initial_comp_relation = $wpdb->get_results( "SELECT * FROM wp_bp_{$table}s WHERE initiator_user_id = {$user_id} AND {$table}_user_id = {$member_id}", OBJECT );
	$reverse_oppo_relation = $wpdb->get_results( "SELECT * FROM wp_bp_{$oppsite}s WHERE initiator_user_id = {$member_id} AND {$oppsite}_user_id = {$user_id}", OBJECT );
	// error_log('1111111- '.json_encode($table). '  '. $user_id . ' '. $member_id . ' '. count($initial_comp_relation) . ' ' . count($reverse_oppo_relation));
	if (count($initial_comp_relation)) {
		return 0;
	}
	if (count($reverse_oppo_relation)) {
		return 1;
	}
	return 0;
}

function is_reciver_awating($table) {
	$user_id = bp_loggedin_user_id();
	$member_id = bp_get_member_user_id();
	global $wpdb;

	$result= $wpdb->get_results(<<<SQL
		SELECT id
		FROM wp_bp_{$table}s
		WHERE is_confirmed = 0 AND
			({$table}_user_id = {$user_id}
			AND initiator_user_id = {$member_id})
	SQL, OBJECT );
	//error_log('>>is_reciver_awating  '.json_encode($result));
	return (string) count($result);
}

function is_initial_awating($table) {
	$user_id = bp_loggedin_user_id();
	$member_id = bp_get_member_user_id();
	global $wpdb;
	$result= $wpdb->get_results(<<<SQL
		SELECT id
		FROM wp_bp_{$table}s
		WHERE is_confirmed = 0 AND
			({$table}_user_id = {$member_id}
			AND initiator_user_id = {$user_id})
	SQL, OBJECT );
	// error_log('>>is_initial_awating  '.json_encode($result));
	return (string) count($result);
}

// $state could be 0, 1, 3, 4
// 0  = no record
// 1  = initiator record only
// 3  = reciver record only
// 4  = both record
function is_initiator($component = '') {
	$user_id = bp_loggedin_user_id();
	// $user_name = bp_get_user_firstname();

	$member_id = bp_get_member_user_id();
	if (empty($component)) {
		$component = bp_current_component();
	}
	global $wpdb;
	
	$state = 0;
	if ($component== 'friend') {
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE friend_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		if (!empty($relations1)) {
			$state += 1;
		} 
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE friend_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		if (!empty($relations2)) {
			$state += 3;
		}
	}

	if ($component== 'engagement') {
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE engagement_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		if (!empty($relations1)) {
			$state += 1;
		} 
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE engagement_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		if (!empty($relations2)) {
			$state += 3;
		}
	}
	return $state;
}

function print_initiator($component = '') {
	$user_id = bp_loggedin_user_id();
	$user_name = bp_get_user_firstname();
	$member_id = bp_get_member_user_id();
	if (empty($component)) {
		$component = bp_current_component();
	}
	global $wpdb;

	$results='';
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE friend_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE friend_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		$relations = array_merge($relations1, $relations2);
		foreach ($relations as $relation) {
			$results .= '<br> '.$relation->id;
			if($relation->initiator_user_id === (string) $user_id) {
				$results .= "<br> (resell) {$user_name} has frie initiator. ";
			} else {
				$results .= "<br> (resell) {$user_name} has frie receiver. ";
			}
		}
	
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE engagement_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE engagement_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		$relations = array_merge($relations1, $relations2);
		foreach ($relations as $relation) {
			$results .= '<br> '.$relation->id;
			if($relation->initiator_user_id === (string) $user_id) {
				$results .= "<br> (supply) {$user_name} has enga initiator. ";
			 }else {
				$results .= "<br> (supply) {$user_name} has enga receiver. ";
			}
		}
	return $results;
}

function add_engagement_button(&$buttons, $user_id, $type, $parent_class, $button_element, $parent_element) {
	if (  bp_is_active( 'engagements' ) ) {
			// It's the member's friendship requests screen
			if ( 'engagementship_request' === $type ) {
				$buttons = array(
					'accept_engagementship' => array(
						'id'                => 'accept_engagementship',
						'position'          => 5,
						'component'         => 'engagements',
						'must_be_logged_in' => true,
						'parent_element'    => $parent_element,
						'link_text'         => _x( 'Accept', 'button', 'buddypress' ),
						'parent_attr'       => array(
							'id'    => '',
							'class' => $parent_class ,
						),
						'button_element'    => $button_element,
						'button_attr'       => array(
							'class'           => 'button accept',
							'rel'             => '',
						),
					), 'reject_engagementship' => array(
						'id'                => 'reject_engagementship',
						'position'          => 15,
						'component'         => 'engagements',
						'must_be_logged_in' => true,
						'parent_element'    => $parent_element,
						'link_text'         => _x( 'Reject', 'button', 'buddypress' ),
						'parent_attr'       => array(
							'id'    => '',
							'class' => $parent_class,
						),
						'button_element'    => $button_element,
						'button_attr'       => array (
							'class'           => 'button reject',
							'rel'             => '',
						),
					),
				);

				// If button element set add nonce link to data attr
				if ( 'button' === $button_element ) {
					$buttons['accept_engagementship']['button_attr']['data-bp-nonce'] = bp_get_engagement_accept_request_link();
					$buttons['reject_engagementship']['button_attr']['data-bp-nonce'] = bp_get_engagement_reject_request_link();
				} else {
					$buttons['accept_engagementship']['button_attr']['href'] = bp_get_engagement_accept_request_link();
					$buttons['reject_engagementship']['button_attr']['href'] = bp_get_engagement_reject_request_link();
				}
			// It's any other members screen
			} else {
				$button_args = bp_get_add_engagement_button_args( $user_id );

				if ( array_filter( $button_args ) ) {
					$buttons['member_engagementship'] = array(
						'id'                => 'member_engagementship',
						'position'          => 5,
						'component'         => $button_args['component'],
						'must_be_logged_in' => $button_args['must_be_logged_in'],
						'block_self'        => $button_args['block_self'],
						'parent_element'    => $parent_element,
						'link_text'         => $button_args['link_text'],
						'link_title'        => $button_args['link_title'],
						'parent_attr'       => array(
							'id'    => $button_args['wrapper_id'],
							'class' => $parent_class . ' ' . $button_args['wrapper_class'],
						),
						'button_element'    => $button_element,
						'button_attr'       => array(
							'id'    => $button_args['link_id'],
							'class' => $button_args['link_class'],
							'rel'   => $button_args['link_rel'],
							'title' => '',
						),
					);

					// If button element set add nonce link to data attr
					if ( 'button' === $button_element && 'awaiting_response' !== $button_args['id'] ) {
						$buttons['member_engagementship']['button_attr']['data-bp-nonce'] = $button_args['link_href'];
					} else {
						$buttons['member_engagementship']['button_element'] = 'a';
						$buttons['member_engagementship']['button_attr']['href'] = $button_args['link_href'];
					}
				}
			}
			
		}
	return $buttons;
}

function add_friend_button(&$buttons, $user_id, $type, $parent_class, $button_element, $parent_element) {
    if ( bp_is_active( 'friends' ) ) {
        // It's the member's friendship requests screen
        if ( 'friendship_request' === $type ) {
            $buttons = array(
                'accept_friendship' => array(
                    'id'                => 'accept_friendship',
                    'position'          => 5,
                    'component'         => 'friends',
                    'must_be_logged_in' => true,
                    'parent_element'    => $parent_element,
                    'link_text'         => _x( 'Accept', 'button', 'buddypress' ),
                    'parent_attr'       => array(
                        'id'    => '',
                        'class' => $parent_class ,
                    ),
                    'button_element'    => $button_element,
                    'button_attr'       => array(
                        'class'           => 'button accept',
                        'rel'             => '',
                    ),
                ), 'reject_friendship' => array(
                    'id'                => 'reject_friendship',
                    'position'          => 15,
                    'component'         => 'friends',
                    'must_be_logged_in' => true,
                    'parent_element'    => $parent_element,
                    'link_text'         => _x( 'Reject', 'button', 'buddypress' ),
                    'parent_attr'       => array(
                        'id'    => '',
                        'class' => $parent_class,
                    ),
                    'button_element'    => $button_element,
                    'button_attr'       => array (
                        'class'           => 'button reject',
                        'rel'             => '',
                    ),
                ),
            );
    
            // If button element set add nonce link to data attr
            if ( 'button' === $button_element ) {
                $buttons['accept_friendship']['button_attr']['data-bp-nonce'] = bp_get_friend_accept_request_link();
                $buttons['reject_friendship']['button_attr']['data-bp-nonce'] = bp_get_friend_reject_request_link();
            } else {
                $buttons['accept_friendship']['button_attr']['href'] = bp_get_friend_accept_request_link();
                $buttons['reject_friendship']['button_attr']['href'] = bp_get_friend_reject_request_link();
            }
    
        // It's any other members screen
        } else {
            $button_args = bp_get_add_friend_button_args( $user_id );
    
            if ( array_filter( $button_args ) ) {
                $buttons['member_friendship'] = array(
                    'id'                => 'member_friendship',
                    'position'          => 5,
                    'component'         => $button_args['component'],
                    'must_be_logged_in' => $button_args['must_be_logged_in'],
                    'block_self'        => $button_args['block_self'],
                    'parent_element'    => $parent_element,
                    'link_text'         => $button_args['link_text'],
                    'link_title'        => $button_args['link_title'],
                    'parent_attr'       => array(
                        'id'    => $button_args['wrapper_id'],
                        'class' => $parent_class . ' ' . $button_args['wrapper_class'],
                    ),
                    'button_element'    => $button_element,
                    'button_attr'       => array(
                        'id'    => $button_args['link_id'],
                        'class' => $button_args['link_class'],
                        'rel'   => $button_args['link_rel'],
                        'title' => '',
                    ),
                );
    
                // If button element set add nonce link to data attr
                if ( 'button' === $button_element && 'awaiting_response' !== $button_args['id'] ) {
                    $buttons['member_friendship']['button_attr']['data-bp-nonce'] = $button_args['link_href'];
                } else {
                    $buttons['member_friendship']['button_element'] = 'a';
                    $buttons['member_friendship']['button_attr']['href'] = $button_args['link_href'];
                }
            }
        }
    }
}

function add_profile_button(&$buttons, $type, $parent_class, $parent_element) {
	// Only add The public and private messages when not in a loop
	if ( 'profile' === $type ) {
		if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) {
			$button_args = bp_activity_get_public_message_button_args();

			if ( array_filter( $button_args ) ) {
				/*
				* This button should remain as an anchor link.
				* Hardcode the use of anchor elements if button arg passed in for other elements.
				*/
				$buttons['public_message'] = array(
					'id'                => $button_args['id'],
					'position'          => 15,
					'component'         => $button_args['component'],
					'must_be_logged_in' => $button_args['must_be_logged_in'],
					'block_self'        => $button_args['block_self'],
					'parent_element'    => $parent_element,
					'button_element'    => 'a',
					'link_text'         => $button_args['link_text'],
					'link_title'        => $button_args['link_title'],
					'parent_attr'       => array(
						'id'    => $button_args['wrapper_id'],
						'class' => $parent_class,
					),
					'button_attr'       => array(
						'href'             => $button_args['link_href'],
						'id'               => '',
						'class'            => $button_args['link_class'],
					),
				);
			}
		}

		if ( bp_is_active( 'messages' ) ) {
			$button_args = bp_get_send_message_button_args();

			if ( array_filter( $button_args ) ) {
				/*
				* This button should remain as an anchor link.
				* Hardcode the use of anchor elements if button arg passed in for other elements.
				*/
				$buttons['private_message'] = array(
					'id'                => $button_args['id'],
					'position'          => 25,
					'component'         => $button_args['component'],
					'must_be_logged_in' => $button_args['must_be_logged_in'],
					'block_self'        => $button_args['block_self'],
					'parent_element'    => $parent_element,
					'button_element'    => 'a',
					'link_text'         => $button_args['link_text'],
					'link_title'        => $button_args['link_title'],
					'parent_attr'       => array(
						'id'    => $button_args['wrapper_id'],
						'class' => $parent_class,
					),
					'button_attr'       => array(
						'href'  => bp_get_send_private_message_link(),
						'id'    => false,
						'class' => $button_args['link_class'],
						'rel'   => '',
						'title' => '',
					),
				);
			}
		}
	}
}
