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

    
    ////////////////////// E 17-9-1 | F 17-9-1
    } elseif  ( ($comp_st == "e_c1_is_engagement_ini" && $oppo_st == "not_friends")
             || ($comp_st == "not_engagements" && $oppo_st == "f_c1_is_friend_ini") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_is_engagement_ini", $oppo_st : "not_friends"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "not_engagements", $oppo_st : "f_c1_is_friend_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c1_is_engagement_ini" && $oppo_st == "not_friends") . ' - ' . (int) ($comp_st == "not_engagements" && $oppo_st == "f_c1_is_friend_ini") );
        error_log(json_encode("||||>05: E 17-9-1 LS-L"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "not_friends" && $oppo_st == "e_c1_is_engagement_ini")
             || ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "not_engagements") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "not_friends", $oppo_st : "e_c1_is_engagement_ini"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_friend_ini", $oppo_st : "not_engagements"
        error_log('||||> conds: ' . (int) ($comp_st == "not_friends" && $oppo_st == "e_c1_is_engagement_ini") . ' - ' . (int) ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "not_engagements") );
        error_log(json_encode("||||>06: E 17-9-1 LS-R"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "e_c1_is_reverse_friend_rev" && $oppo_st == "not_friends")
             || ($comp_st == "not_engagements" && $oppo_st == "f_c1_is_reverse_engagement_rev") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_is_reverse_friend_rev", $oppo_st : "not_friends"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "not_engagements", $oppo_st : "f_c1_is_reverse_engagement_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c1_is_reverse_friend_rev" && $oppo_st == "not_friends") . ' - ' . (int) ($comp_st == "not_engagements" && $oppo_st == "f_c1_is_reverse_engagement_rev") );
        error_log(json_encode("||||>07: E 17-9-1 GD-L"));
        $button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "not_friends" && $oppo_st == "e_c1_is_reverse_friend_rev")
             || ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "not_engagements") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "not_friends", $oppo_st : "e_c1_is_reverse_friend_rev"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_reverse_engagement_rev", $oppo_st : "not_engagements"
        error_log('||||> conds: ' . (int) ($comp_st == "not_friends" && $oppo_st == "e_c1_is_reverse_friend_rev") . ' - ' . (int) ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "not_engagements") );
        error_log(json_encode("||||>08: E 17-9-1 GD-R"));
        $button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);


    ////////////////////// E 17-9-1 + F 17-9-0 | F 17-9-1 + E 17-9-0
    } elseif  ( ($comp_st == "e_c1_is_engagement_ini" && $oppo_st == "f_c1_pending_friend_ini")
             || ($comp_st == "e_c1_pending_engagement_ini" && $oppo_st == "f_c1_is_friend_ini") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_is_engagement_ini", $oppo_st : "f_c1_pending_friend_ini"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_pending_engagement_ini", $oppo_st : "f_c1_is_friend_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c1_is_engagement_ini" && $oppo_st == "f_c1_pending_friend_ini") . ' - ' . (int) ($comp_st == "e_c1_pending_engagement_ini" && $oppo_st == "f_c1_is_friend_ini") );
        error_log(json_encode("||||>09: E 17-9-1 + F 17-9-0 LS-L"));
        $button_args = $button_func("remove_{$oppo}s", $pid, $sg, $rel_id);	

    } elseif  ( ($comp_st == "f_c1_pending_friend_ini" && $oppo_st == "e_c1_is_engagement_ini")
             || ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c1_pending_engagement_ini") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_pending_friend_ini", $oppo_st : "e_c1_is_engagement_ini"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_friend_ini", $oppo_st : "e_c1_pending_engagement_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "f_c1_pending_friend_ini" && $oppo_st == "e_c1_is_engagement_ini") . ' - ' . (int) ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c1_pending_engagement_ini") );
        error_log(json_encode("||||>10: E 17-9-1 + F 17-9-0 LS-R"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "e_c1_is_reverse_friend_rev" && $oppo_st == "f_c1_awaiting_response_rev")
             || ($comp_st == "e_c1_awaiting_response_rev" && $oppo_st == "f_c1_is_reverse_engagement_rev") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_is_reverse_friend_rev", $oppo_st : "f_c1_awaiting_response_rev"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_awaiting_response_rev", $oppo_st : "f_c1_is_reverse_engagement_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c1_is_reverse_friend_rev" && $oppo_st == "f_c1_awaiting_response_rev") . ' - ' . (int) ($comp_st == "e_c1_awaiting_response_rev" && $oppo_st == "f_c1_is_reverse_engagement_rev") );
        error_log(json_encode("||||>11: E 17-9-1 + F 17-9-0 GD-L"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "f_c1_awaiting_response_rev" && $oppo_st == "e_c1_is_reverse_friend_rev")
             || ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c1_awaiting_response_rev") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_awaiting_response_rev", $oppo_st : "e_c1_is_reverse_friend_rev"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_reverse_engagement_rev", $oppo_st : "e_c1_awaiting_response_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "f_c1_awaiting_response_rev" && $oppo_st == "e_c1_is_reverse_friend_rev") . ' - ' . (int) ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c1_awaiting_response_rev") );
        error_log(json_encode("||||>12: E 17-9-1 + F 17-9-0 GD-R"));
        $button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);



    ////////////////////// E 17-9-1 + F 17-9-1 |  F 17-9-1 + E 17-9-1
    } elseif  ( ($comp_st == "e_c1_is_engagement_ini" && $oppo_st == "f_c1_is_friend_ini")
             || ($comp_st == "e_c1_is_engagement_ini" && $oppo_st == "f_c1_is_friend_ini") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_is_engagement_ini", $oppo_st : "f_c1_is_friend_ini"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "c1_is_engagement_ini", $oppo_st : "c1_is_friend_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c1_is_engagement_ini" && $oppo_st == "f_c1_is_friend_ini") . ' - ' . (int) ($comp_st == "e_c1_is_engagement_ini" && $oppo_st == "f_c1_is_friend_ini") );
        error_log(json_encode("||||>13: E 17-9-1 + 17 f 1 LS-L"));
        $button_args = $button_func("remove_{$oppo}s", $pid, $sg, $rel_id);	

    } elseif  ( ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c1_is_engagement_ini")
             || ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c1_is_engagement_ini") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_friend_ini", $oppo_st : "e_c1_is_engagement_ini"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_friend_ini", $oppo_st : "e_c1_is_engagement_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c1_is_engagement_ini") . ' - ' . (int) ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c1_is_engagement_ini") );
        error_log(json_encode("||||>14: E 17-9-1 + 17 f 1 LS-R"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "e_c1_is_reverse_friend_rev" && $oppo_st == "f_c1_is_reverse_engagement_rev")
             || ($comp_st == "e_c1_is_reverse_friend_rev" && $oppo_st == "f_c1_is_reverse_engagement_rev") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_is_reverse_friend_rev", $oppo_st : "f_c1_is_reverse_engagement_rev"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_is_reverse_friend_rev", $oppo_st : "f_c1_is_reverse_engagement_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c1_is_reverse_friend_rev" && $oppo_st == "f_c1_is_reverse_engagement_rev") . ' - ' . (int) ($comp_st == "e_c1_is_reverse_friend_rev" && $oppo_st == "f_c1_is_reverse_engagement_rev") );
        error_log(json_encode("||||>15: E 17-9-1 + 17 f 1 GD-L"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c1_is_reverse_friend_rev")
             || ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c1_is_reverse_friend_rev") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_reverse_engagement_rev", $oppo_st : "e_c1_is_reverse_friend_rev"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_reverse_engagement_rev", $oppo_st : "e_c1_is_reverse_friend_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c1_is_reverse_friend_rev") . ' - ' . (int) ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c1_is_reverse_friend_rev") );
        error_log(json_encode("||||>16: E 17-9-1 + 17 f 1 GD-R"));
        $button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);



    ////////////////////// E 17-9-1 + E 9-17-0 | F 17-9-1 + F 9-17-0
    } elseif  ( ($comp_st == "e_c2_fm1_is_engagement_ini" && $oppo_st == "not_friends")
             || ($comp_st == "not_engagements" && $oppo_st == "f_c2_fm1_is_friend_ini") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c2_fm1_is_engagement_ini", $oppo_st : "not_friends"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "not_engagements", $oppo_st : "f_c2_fm1_is_friend_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c2_fm1_is_engagement_ini" && $oppo_st == "not_friends") . ' - ' . (int) ($comp_st == "not_engagements" && $oppo_st == "f_c2_fm1_is_friend_ini") );
        error_log(json_encode("||||>17: E 17-9-1 + E 9-17-0 LS L"));
        $button_args = $button_func("remove_{$oppo}s", $pid, $sg, $rel_id);	

    } elseif  ( ($comp_st == "not_friends" && $oppo_st == "e_c2_fm1_is_engagement_ini")
             || ($comp_st == "f_c2_fm1_is_friend_ini" && $oppo_st == "not_engagements") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "not_friends", $oppo_st : "e_c2_fm1_is_engagement_ini"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c2_fm1_is_friend_ini", $oppo_st : "not_engagements"
        error_log('||||> conds: ' . (int) ($comp_st == "not_friends" && $oppo_st == "e_c2_fm1_is_engagement_ini") . ' - ' . (int) ($comp_st == "f_c2_fm1_is_friend_ini" && $oppo_st == "not_engagements") );
        error_log(json_encode("||||>18: E 17-9-1 + E 9-17-0 LS R"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "e_c2_fm1_is_reverse_friend_rev" && $oppo_st == "not_friends")
             || ($comp_st == "not_engagements" && $oppo_st == "f_c2_fm1_is_friend_rev") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c2_fm1_is_reverse_friend_rev", $oppo_st : "not_friends"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "not_engagements", $oppo_st : "f_c2_fm1_is_friend_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c2_fm1_is_reverse_friend_rev" && $oppo_st == "not_friends") . ' - ' . (int) ($comp_st == "not_engagements" && $oppo_st == "f_c2_fm1_is_friend_rev") );
        error_log(json_encode("||||>19: E 17-9-1 + E 9-17-0 GD L"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "not_friends" && $oppo_st == "e_c2_fm1_is_reverse_friend_rev")
             || ($comp_st == "f_c2_fm1_is_friend_rev" && $oppo_st == "not_engagements") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "not_friends", $oppo_st : "e_c2_fm1_is_reverse_friend_rev"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c2_fm1_is_friend_rev", $oppo_st : "not_engagements"
        error_log('||||> conds: ' . (int) ($comp_st == "not_friends" && $oppo_st == "e_c2_fm1_is_reverse_friend_rev") . ' - ' . (int) ($comp_st == "f_c2_fm1_is_friend_rev" && $oppo_st == "not_engagements") );
        error_log(json_encode("||||>20: E 17-9-1 + E 9-17-0 GD R"));
        $button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);



    ////////////////////// E 17-9-1 + E 9-17-1 | F 17-9-1 + F 9-17-1
    } elseif  ( ($comp_st == "e_c2_exist_both_engagements_v1_ini" && $oppo_st == "f_c1_is_reverse_engagement_rev")
             || ($comp_st == "not_engagements" && $oppo_st == "f_c2_exist_both_engagements_v1_ini") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c2_exist_both_engagements_v1_ini", $oppo_st : "f_c1_is_reverse_engagement_rev"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "not_engagements", $oppo_st : "f_c2_exist_both_engagements_v1_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c2_exist_both_engagements_v1_ini" && $oppo_st == "f_c1_is_reverse_engagement_rev") . ' - ' . (int) ($comp_st == "not_engagements" && $oppo_st == "f_c2_exist_both_engagements_v1_ini") );
        error_log(json_encode("||||>21: E 17-9-1 + E 9-17-1 LS-L"));
        $button_args = $button_func("remove_{$oppo}s", $pid, $sg, $rel_id);	

    } elseif  ( ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c2_exist_both_engagements_v1_ini")
             || ($comp_st == "f_c2_exist_both_engagements_v1_ini" && $oppo_st == "not_engagements") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_reverse_engagement_rev", $oppo_st : "e_c2_exist_both_engagements_v1_ini"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c2_exist_both_engagements_v1_ini", $oppo_st : "not_engagements"
        error_log('||||> conds: ' . (int) ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c2_exist_both_engagements_v1_ini") . ' - ' . (int) ($comp_st == "f_c2_exist_both_engagements_v1_ini" && $oppo_st == "not_engagements") );
        error_log(json_encode("||||>22: E 17-9-1 + E 9-17-1 LS-R"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "e_c2_exist_both_engagements_v1_rev" && $oppo_st == "f_c1_is_friend_ini")
             || ($comp_st == "not_engagements" && $oppo_st == "f_c2_exist_both_engagements_v1_rev") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c2_exist_both_engagements_v1_rev", $oppo_st : "f_c1_is_friend_ini"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "not_engagements", $oppo_st : "f_c2_exist_both_engagements_v1_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c2_exist_both_engagements_v1_rev" && $oppo_st == "f_c1_is_friend_ini") . ' - ' . (int) ($comp_st == "not_engagements" && $oppo_st == "f_c2_exist_both_engagements_v1_rev") );
        error_log(json_encode("||||>23: E 17-9-1 + E 9-17-1 GD-L"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c2_exist_both_engagements_v1_rev")
             || ($comp_st == "f_c2_exist_both_engagements_v1_rev" && $oppo_st == "not_engagements") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_friend_ini", $oppo_st : "e_c2_exist_both_engagements_v1_rev"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c2_exist_both_engagements_v1_rev", $oppo_st : "not_engagements"
        error_log('||||> conds: ' . (int) ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c2_exist_both_engagements_v1_rev") . ' - ' . (int) ($comp_st == "f_c2_exist_both_engagements_v1_rev" && $oppo_st == "not_engagements") );
        error_log(json_encode("||||>24: E 17-9-1 + E 9-17-1 GD-R"));
        $button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);



    ////////////////////// E 17-9-0 + E 9-17-1 | F 17-9-0 + F 9-17-1
    } elseif  ( ($comp_st == "e_c2_fm1_is_engagement_ini" && $oppo_st == "f_c1_is_reverse_engagement_rev")
             || ($comp_st == "e_c1_awaiting_response_rev" && $oppo_st == "f_c2_fm0_pending_friend_ini") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c2_fm1_is_engagement_ini", $oppo_st : "f_c1_is_reverse_engagement_rev"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_awaiting_response_rev", $oppo_st : "f_c2_fm0_pending_friend_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c2_fm1_is_engagement_ini" && $oppo_st == "f_c1_is_reverse_engagement_rev") . ' - ' . (int) ($comp_st == "e_c1_awaiting_response_rev" && $oppo_st == "f_c2_fm0_pending_friend_ini") );
        error_log(json_encode("||||>25: E 17-9-0 + E 9-17-1 LS-L"));
        $button_args = $button_func("remove_{$oppo}s", $pid, $sg, $rel_id);	

    } elseif  ( ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c2_fm1_is_engagement_ini")
             || ($comp_st == "f_c2_fm0_pending_friend_ini" && $oppo_st == "e_c1_awaiting_response_rev") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_reverse_engagement_rev", $oppo_st : "e_c2_fm1_is_engagement_ini"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c2_fm0_pending_friend_ini", $oppo_st : "e_c1_awaiting_response_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "f_c1_is_reverse_engagement_rev" && $oppo_st == "e_c2_fm1_is_engagement_ini") . ' - ' . (int) ($comp_st == "f_c2_fm0_pending_friend_ini" && $oppo_st == "e_c1_awaiting_response_rev") );
        error_log(json_encode("||||>26: E 17-9-0 + E 9-17-1 LS-R"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "e_c2_fm1_is_reverse_friend_rev" && $oppo_st == "f_c1_is_friend_ini")
             || ($comp_st == "e_c1_pending_engagement_ini" && $oppo_st == "f_c2_fm0_pending_friend_rev") ) {
        // E_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c2_fm1_is_reverse_friend_rev", $oppo_st : "f_c1_is_friend_ini"
        // F_condition {$comp} : "engagement", {$oppo} : "friend", $comp_st : "e_c1_pending_engagement_ini", $oppo_st : "f_c2_fm0_pending_friend_rev"
        error_log('||||> conds: ' . (int) ($comp_st == "e_c2_fm1_is_reverse_friend_rev" && $oppo_st == "f_c1_is_friend_ini") . ' - ' . (int) ($comp_st == "e_c1_pending_engagement_ini" && $oppo_st == "f_c2_fm0_pending_friend_rev") );
        error_log(json_encode("||||>27: E 17-9-0 + E 9-17-1 GD-L"));
        $button_args = $button_func("is_{$comp}", $pid, $sg, $rel_id);

    } elseif  ( ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c2_fm1_is_reverse_friend_rev")
             || ($comp_st == "f_c2_fm0_pending_friend_rev" && $oppo_st == "e_c1_pending_engagement_ini") ) {
        // E_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c1_is_friend_ini", $oppo_st : "e_c2_fm1_is_reverse_friend_rev"
        // F_condition {$comp} : "friend", {$oppo} : "engagement", $comp_st : "f_c2_fm0_pending_friend_rev", $oppo_st : "e_c1_pending_engagement_ini"
        error_log('||||> conds: ' . (int) ($comp_st == "f_c1_is_friend_ini" && $oppo_st == "e_c2_fm1_is_reverse_friend_rev") . ' - ' . (int) ($comp_st == "f_c2_fm0_pending_friend_rev" && $oppo_st == "e_c1_pending_engagement_ini") );
        error_log(json_encode("||||>28: E 17-9-0 + E 9-17-1 GD-R"));
        $button_args = $button_func("awaiting_response", $pid, $sg, $rel_id);


    ////////////////////// fallback buttons
	} elseif ($is_reversed == 1) {
        // error_log(json_encode( $oppo_st . ' - ' . "c2_exist_both_{$oppo}s_v1_ini" . ' -' . $comp_st . '-' . "not_{$comp}s" ));
        // error_log(json_encode( $oppo_st == "c2_exist_both_{$oppo}s_v1_ini" ));
        // error_log(json_encode( $comp_st == "not_{$comp}s" ));

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
	error_log('||||> {$comp} : '.json_encode($comp) . ', {$oppo} : '.json_encode($oppo) . ', $comp_st : '.json_encode($comp_st) . ', $oppo_st : '.json_encode($oppo_st));
	error_log('>rev_f_awa ini_f_awa rev_e_awa ini_e_awa: '.json_encode( $rev_f_awa . ', ' .$ini_f_awa . ', ' . $rev_e_awa . ', ' .$ini_e_awa ));
	error_log('================================= $ini_e: '.$ini_e);
	error_log('==================================$ini_f: '.$ini_f);
	error_log('===========================friend_status: '.$fst);
	error_log('=======================engagement_status: '.$est);
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
