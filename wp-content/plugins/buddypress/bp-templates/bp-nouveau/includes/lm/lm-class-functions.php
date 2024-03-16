<?php
function update_lm_relation_cache($comp, $user_id, $possible_member_ids, $bp_cache_key) {
	global $wpdb;

	$bp                  = buddypress();
	$user_id             = (int) $user_id;
	$possible_member_ids = wp_parse_id_list( $possible_member_ids );
	if ($comp === 'friend') {
		$comp_table = $bp->friends->table_name;
		$oppo_table = $bp->engagements->table_name;
		$comp_receiver_name = 'receiver_user_id';
		$oppo_receiver_name = 'receiver_user_id';
		$oppo = 'engagement';
	} else {
		$comp_table = $bp->engagements->table_name;
		$oppo_table = $bp->friends->table_name;
		$comp_receiver_name = 'receiver_user_id';
		$oppo_receiver_name = 'receiver_user_id';
		$oppo = 'friend';
	}

	$fetch = array();
	foreach ( $possible_member_ids as $member_id ) {
		// Check for cached items in both engagementship directions.
		if ( false === bp_core_get_incremented_cache( $user_id . ':' . $member_id, $bp_cache_key )
			|| false === bp_core_get_incremented_cache( $member_id . ':' . $user_id, $bp_cache_key ) ) {
			$fetch[] = $member_id;
		}
	}
	if ( empty( $fetch ) ) {
		return;
	}
	
	$member_ids_for_sql = implode( ',', array_unique( $fetch ) );
	// true means from component table
	$sql = $wpdb->prepare( <<<SQL
		SELECT id, initiator_user_id, {$comp_receiver_name}, is_confirmed, true as is_comp_table
		FROM {$comp_table} 
		WHERE (initiator_user_id = %d AND {$comp_receiver_name} IN ({$member_ids_for_sql}) ) 
	SQL,
	$user_id);
	$comp_relationships = $wpdb->get_results( $sql );

	// false means from opposit table
	$sql = $wpdb->prepare( <<<SQL
		SELECT id, initiator_user_id, {$oppo_receiver_name}, is_confirmed , false as is_comp_table
		FROM {$oppo_table} 
		WHERE ({$oppo_receiver_name} = %d AND initiator_user_id IN ({$member_ids_for_sql}) ) 
	SQL,
	$user_id);
	$oppo_relationships = $wpdb->get_results( $sql );
	$relationships = array_merge($comp_relationships, $oppo_relationships);
	$handled = array();
	$listName = $comp[0].'List';


	error_log(json_encode('-------->>> bp_cache_key:'. $bp_cache_key));
	// error_log('count rel: '.json_encode(count($relationships)));
	// error_log(json_encode($relationship));
	error_log(json_encode('-->>> comp_receiver_name: '. $comp_receiver_name . '  oppo_receiver_name: '. $oppo_receiver_name ));
	error_log(json_encode('---------->>> comp_table: '. $comp_table . '  oppo_table: '. $oppo_table));
	
	foreach ( $relationships as $relationship ) {
		$initiator_user_id = (int) $relationship->initiator_user_id;
		error_log(json_encode($relationship));
		if ( $relationship->is_comp_table ) {	
			$tableLetter = $comp[0];
			error_log(json_encode('---> has Ini record'));
			$receiver_user_id = (int) $relationship->$comp_receiver_name;
			$confirm_test = $initiator_user_id === $user_id;
		} else {
			$tableLetter = $oppo[0];
			error_log(json_encode('---> has Reverse record'));
			$receiver_user_id = (int) $relationship->$oppo_receiver_name;
			$confirm_test = $receiver_user_id === $user_id;
		}

		$suffix = "{$listName}_{$tableLetter}Table";
		if ($confirm_test) {
			if ((int) $relationship->is_confirmed === 1) {
				$status_initiator = "confirmed_sts_initial_{$suffix}";
				$status_receiver = "confirmed_sts_receiver_{$suffix}";
			} else {
				$status_initiator ="pending_sts_initial_{$suffix}";
				$status_receiver = "awaiting_sts_receiver_{$suffix}";
			}
		} else {
			$status_initiator = "empty_sts_initial_{$suffix}";
			$status_receiver = "empty_sts_receiver_{$suffix}";
		}
		
		
		error_log(json_encode('-------------->>> rel_id: '. $relationship->id . ' user_id: '. $user_id . ' '. $initiator_user_id . '->'. $receiver_user_id. ' comp: '. $comp ));
		error_log('--status_initiator-> ' . $status_initiator . ' --status_receiver-> ' . $status_receiver );
		bp_core_set_incremented_cache( $initiator_user_id . ':' . $receiver_user_id, $bp_cache_key, $status_initiator );
		bp_core_set_incremented_cache( $receiver_user_id . ':' . $initiator_user_id, $bp_cache_key, $status_receiver );

		$handled[] = ( $initiator_user_id === $user_id ) ? $receiver_user_id : $initiator_user_id;
	}

	// Set all those with no matching entry to "not engagements" status.
	$not_engagement = array_diff( $fetch, $handled );
	error_log('>>>fetched'.json_encode($fetch));
	error_log('>>>handled'.json_encode($handled));
	$empty_sts = "empty_sts_none_{$listName}";
	foreach ( $not_engagement as $not_engagement_id ) {
		error_log(json_encode(11111));
		bp_core_set_incremented_cache( $user_id . ':' . $not_engagement_id, $bp_cache_key, $empty_sts );
		bp_core_set_incremented_cache( $not_engagement_id . ':' . $user_id, $bp_cache_key, $empty_sts );
	}
};
