<?php
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
		$ini_aw,
		$rev_aw,
		$ini_e_awa,
		$ini_f_awa,
		$rev_e_awa,
		$rev_f_awa,
		$ini_e_id,
		$rev_e_id,
		$ini_f_id,
		$rev_f_id,
	) = get_template_vars($pid, $comp);

	if (false) {
		return;
	} elseif ( $is_member ) {
		// one initiator
		error_log(json_encode("||||> is_member "). $button_func);
		$button_args = $button_func($status, $pid, $sg, $rel_id);

	// } elseif ($comp_st == "is_{$comp}" && $oppo_st == "is_{$oppo}" ) {
	// 	// one initiator
	// 	error_log(json_encode("||||> 1.1 isf ise"));
	// 	$button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

	// } elseif ($comp_st == "pending_{$comp}" && $oppo_st == "is_{$oppo}" ) {
	// 	// both relations are initiator
	// 	error_log(json_encode("||||> 1.1 pef ise"));
	// 	$button_args = $button_func("pending_{$comp}", $pid, $sg, $rel_id);

	// } elseif ($comp_st == "exist_initiator_{$comp}" && $oppo_st == "exist_initiator_{$oppo}") {
	// 	// both relations are reciver
	// 	error_log(json_encode("||||> 3.3 eif eie"));
	// 	$button_args = $button_func("remove_initiator_{$comp}", $pid, $sg, $rel_id);

	// } elseif ($comp_st == "awaiting_response" && $oppo_st == "exist_initiator_{$oppo}") {
	// 	// both relations are reciver
	// 	error_log(json_encode("||||> 3.3 awr eie"));
	// 	$button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);
	
	} elseif ($comp_st == "not_{$comp}s") {
		if ($oppo_st == "exist_more_{$oppo}s") {
			if (false) {
				return;
			} elseif ($rev_e_awa == "1") {
				error_log(json_encode("||||> rev_e_awa = 1 not ext more"));
				$button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);
			} elseif ($ini_e_awa == "1") {
				error_log(json_encode("||||> ini_e_awa = 1 not ext more"));
				$button_args = $button_func("remove_more_{$oppo}s", $pid, $sg, $rel_id);
			} else {
				// has init and rev in same table
				error_log(json_encode("||||> init rev same"));
				$button_args = $button_func("remove_more_{$oppo}s", $pid, $sg, $rel_id);
			}
		} elseif ($oppo_st == "pending_{$oppo}") {
			// stop existed in same table
			error_log(json_encode("||||> 17 e 0 + 9 e 1 LS F"));
			$button_args = $button_func("remove_{$oppo}s", $pid, $sg, $rel_id);	
		} elseif ($comp_st == "not_{$comp}s" && $oppo_st == "awaiting_response") {
			// stop existed in same table
			error_log(json_encode("||||> 17 e 0 + 9 e 1 GD F"));
			$button_args = $button_func('awaiting_response', $pid, $sg, $rel_id);
		} else {
			error_log(json_encode("||||> els only in not comp"));
			$button_args = $button_func($status, $pid, $sg, $rel_id);
		}
	} elseif ($oppo_st == "not_{$oppo}s") {
		if (false) {
			return;
		
		} elseif ($comp_st == "c1_is_{$comp}_ini") {
			error_log(json_encode("||||> 17 e 1 LS E"));
			$button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

		} elseif ($comp_st == "exist_initiator_{$comp}") {
			error_log(json_encode("||||> 3.0 nof exi"));
			$button_args = $button_func("remove_initiator_{$comp}", $pid, $sg, $rel_id);

		} elseif ($comp_st == "pending_{$comp}") {
			if ($is_reversed == "0" && $ini_e_awa == 1) {
				error_log(json_encode("||||> 17 e 0 + 9 e 1 LS E"));
				$button_args = $button_func("remove_{$comp}s", $pid, $sg, $rel_id);
			} elseif ($is_reversed == "0") {
				// initial
				error_log(json_encode("||||> nof pee rev0"));
				$button_args = $button_func("pending_{$comp}", $pid, $sg, $rel_id);
			} else {
				// reversed
				error_log(json_encode("||||> not pending rev1"));
				$button_args = $button_func("remove_more_{$comp}s", $pid, $sg, $rel_id);
			}
		} elseif ($comp_st == "awaiting_response") {
			
			if ($rev_e_awa == "1" && $mk == 'e' ) {
				// reversed
				error_log(json_encode("||||> 17 e 0 + 9 e 1 GD E"));
				$button_args = $button_func("remove_initiator_{$comp}", $pid, $sg, $ini_e_id);
			} elseif ($is_reversed == "1") {
				// reversed
				error_log(json_encode("||||> nof awr rev1 107"));
				$button_args = $button_func("exist_more_{$comp}s", $pid, $sg, $rel_id);
			} else {
				// initial
				error_log(json_encode("||||> nof awr rev0 110"));
				$button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);
			}

		} elseif ($comp_st == "exist_more_{$comp}s") {
			if ($ini_aw == "1") {
				// ini awating
				error_log(json_encode("||||> em oppo inia"));
				$button_args = $button_func("remove_more_{$comp}s", $pid, $sg, $rel_id);
			} elseif ($rev_aw == "1") {
				// rev awating
				error_log(json_encode("||||> 4.0 nof eme re"));
				$button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);
			} elseif ($is_reversed == "1") {
				// reversed
				error_log(json_encode("||||> exist_more_126 reversed"));
				$button_args = $button_func($status, $pid, $sg, $rel_id);
			} elseif($ini_e_awa == '1') {
				// initial
				error_log(json_encode("||||> exist_more_113 initial"));
				$button_args = $button_func("pending_{$comp}", $pid, $sg, $rel_id);
			} else {
				// initial
				error_log(json_encode("||||> exist_more_129 initial"));
				$button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);
			}
		}
	} elseif ($is_reversed == 1) {
		error_log(json_encode("||||> rev only "));
		$button_args = $button_func($status, $pid, $sg, $rel_id);

	} else {
		error_log(json_encode("||||> els only "));
		$button_args = $button_func($status, $pid, $sg, $rel_id);
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

	$ini_aw = ($comp == 'friend' ? $ini_e_awa  : $ini_f_awa);
	$rev_aw = ($comp == 'friend' ? $rev_e_awa  : $rev_f_awa);

	$comp_st = ($comp == 'friend' ? $fst  : $est);
	$oppo_st = ($comp == 'friend' ? $est  : $fst);

	$aw = ($is_reversed == '1' ? $rev_aw  : $ini_aw);
	$rel_id = ($is_reversed == '1' ? $rev_id  : $ini_id);

	//friend_initiator_btn_args
	//engagement_initiator_btn_args
	$initiator_func = "{$comp}_initiator_btn_args";
	$reciver_func   = "{$comp}_reciver_btn_args";
	$button_func = $is_reversed == '1' ? $reciver_func : $initiator_func;

	error_log('');
	error_log('----sts--comp_st--oppo_st');
	error_log('comp '.json_encode($comp) . ' oppo '.json_encode($oppo) . ' comp_st '.json_encode($comp_st) . ' oppo_st '.json_encode($oppo_st));
	error_log('>rev_f_awa ini_f_awa rev_e_awa ini_e_awa: '.json_encode( $rev_f_awa . ', ' .$ini_f_awa . ', ' . $rev_e_awa . ', ' .$ini_e_awa ));
	error_log('================================= $ini_e: '.$ini_e);
	error_log('==================================$ini_f: '.$ini_f);
	error_log('ssss ======================friend_status: '.$fst);
	error_log('ssss ==================engagement_status: '.$est);
	error_log('============================$is_reversed: '.$is_reversed);
	error_log('=============================$relation_f: '.$f_rel_id);	
	error_log('=============================$relation_e: '.$e_rel_id);	
	error_log('=====ini_f_id rev_f_id ini_e_id rev_e_id: '.$ini_f_id . ', ' . $rev_f_id . ', ' . $ini_e_id  . ', ' . $rev_e_id );
	error_log('=================================$status: '.$status);
	error_log('=====================================$sg: '.$sg);
	error_log('============================$button_func: '.$button_func);
	error_log(' ');

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
		$ini_aw,
		$rev_aw,
		$ini_e_awa,
		$ini_f_awa,
		$rev_e_awa,
		$rev_f_awa,
		$ini_e_id,
		$rev_e_id,
		$ini_f_id,
		$rev_f_id
	];
}
