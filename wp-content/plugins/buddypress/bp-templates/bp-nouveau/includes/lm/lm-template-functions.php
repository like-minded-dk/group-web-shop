<?php
function get_friend_id($user_id, $potential_engagement_id) {
	$friendship_id = BP_Friends_Friendship::get_relationship_id($user_id, $potential_engagement_id);
	if (!empty($friendship_id)) {
		$fri_rel = BP_Friends_Friendship::get_relationships_by_id($friendship_id)[0];
		if ( ! empty($fri_rel)) {
			$f_rel_id = $fri_rel->id;
		}
	}
	return $f_rel_id ?? 0 ;
}

function get_engagement_id($user_id, $potential_engagement_id) {
	$engagementship_id = BP_Engagements_Engagementship::get_relationship_id($user_id, $potential_engagement_id);
	if (!empty($engagementship_id)) {
		$eng_rel = BP_Engagements_Engagementship::get_relationships_by_id($engagementship_id)[0];
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
	$result = $wpdb->get_results( "SELECT * FROM wp_bp_{$table}s WHERE initiator_user_id = {$user_id} AND receiver_user_id = {$member_id} ",ARRAY_N );
	// return 1st id from database, otherwise return null
	return $result[0][0] ?? 0;
}

function is_oppsit_relation($comp) {
	$comp_table = $comp == 'friend' ? 'wp_bp_friends' : 'wp_bp_engagements';
	$oppo_table = $comp == 'friend' ? 'wp_bp_engagements' : 'wp_bp_friends';
	$user_id = bp_loggedin_user_id();
	$member_id = bp_get_member_user_id();

	global $wpdb;
	$initial_comp_relation = $wpdb->get_results( "SELECT * FROM {$comp_table} WHERE initiator_user_id = {$user_id} AND receiver_user_id = {$member_id}", OBJECT );
	$reverse_oppo_relation = $wpdb->get_results( "SELECT * FROM {$oppo_table} WHERE initiator_user_id = {$member_id} AND receiver_user_id = {$user_id}", OBJECT );
    $reverse_comp_relation = $wpdb->get_results( "SELECT * FROM {$comp_table} WHERE initiator_user_id = {$member_id} AND receiver_user_id = {$user_id}", OBJECT );

	if (count($initial_comp_relation)) {
		return 0;
	}

	if (count($reverse_oppo_relation)) {
		return 1;
	}
    
    if (count($reverse_comp_relation)) {
        return 1;
    }

	return 0;
}

function is_receiver_awating($table) {
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
	//error_log('>>is_receiver_awating  '.json_encode($result));
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
// 3  = receiver record only
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
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE receiver_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		if (!empty($relations1)) {
			$state += 1;
		} 
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE receiver_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		if (!empty($relations2)) {
			$state += 3;
		}
	}

	if ($component== 'engagement') {
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE receiver_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		if (!empty($relations1)) {
			$state += 1;
		} 
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE receiver_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		if (!empty($relations2)) {
			$state += 3;
		}
	}
	return $state;
}

function print_initiator($component = '') {
	// todo lm debug
	// return;
	$user_id = bp_loggedin_user_id();
	$user_name = bp_get_user_firstname();
	$member_id = bp_get_member_user_id();
	if (empty($component)) {
		$component = bp_current_component();
	}
	global $wpdb;

	$results='';
		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE receiver_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_engagements WHERE receiver_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		$relations = array_merge($relations1, $relations2);
		foreach ($relations as $relation) {
			$results .= '<br> '.$relation->id;
			if($relation->initiator_user_id === (string) $user_id) {
				$results .= "<br>{$user_name} in enga initiator. cofm:{$relation->is_confirmed}";
			 }else {
				$results .= "<br>{$user_name} in enga receiver. cofm:{$relation->is_confirmed}";
			}
		}

		$relations1 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE receiver_user_id = {$member_id} AND initiator_user_id = {$user_id}", OBJECT );
		$relations2 = $wpdb->get_results( "SELECT * FROM wp_bp_friends WHERE receiver_user_id = {$user_id} AND initiator_user_id = {$member_id}", OBJECT );
		$relations = array_merge($relations1, $relations2);
		foreach ($relations as $relation) {
			$results .= '<br> '.$relation->id;
			if($relation->initiator_user_id === (string) $user_id) {
				$results .= "<br>{$user_name} in frie initiator. cofm:{$relation->is_confirmed}";
			} else {
				$results .= "<br>{$user_name} in frie receiver. cofm:{$relation->is_confirmed}";
			}
		}
	
		
	return $results;
}
