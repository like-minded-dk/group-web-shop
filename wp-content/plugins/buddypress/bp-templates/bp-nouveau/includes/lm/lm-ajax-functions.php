<?php
function get_actions_array($comp) {
    return array(
		array(
			"{$comp}s_remove_{$comp}_as_receiver" => array(
				"function" => "bp_nouveau_ajax_addremove_fn_{$comp}",
				"nopriv"   => false,
			),
		),
		array(
			"{$comp}s_remove_{$comp}" => array(
				"function" => "bp_nouveau_ajax_addremove_fn_{$comp}",
				"nopriv"   => false,
			),
		),
		array(
			"{$comp}s_add_{$comp}" => array(
				"function" => "bp_nouveau_ajax_addremove_fn_{$comp}",
				"nopriv"   => false,
			),
		),
		array(
			"{$comp}s_withdraw_{$comp}" => array(
				"function" => "bp_nouveau_ajax_addremove_fn_{$comp}",
				"nopriv"   => false,
			),
		),
		array(
			"{$comp}s_accept_{$comp}" => array(
				"function" => "bp_nouveau_ajax_addremove_fn_{$comp}",
				"nopriv"   => false,
			),
		),
		array(
			"{$comp}s_reject_{$comp}" => array(
				"function" => "bp_nouveau_ajax_addremove_fn_{$comp}",
				"nopriv"   => false,
			),
		),
	);
}


function add_ajax_admin_init_action($comp) {
    add_action( 'admin_init', function() use ($comp) {
        $ajax_actions = get_actions_array($comp);
    
        foreach ( $ajax_actions as $ajax_action ) {
            $action = key( $ajax_action );
            add_action( 'wp_ajax_' . $action, $ajax_action[ $action ]['function'] );
    
            if ( ! empty( $ajax_action[ $action ]['nopriv'] ) ) {
                add_action( 'wp_ajax_nopriv_' . $action, $ajax_action[ $action ]['function']);
            }
        }
    }, 12 );
}

function ajax_add_relation($comp, $user_id, $item_id, $response, $error = '', $note='') {
    error_log('>>ajax_add_relation ' . $error . ' : ' . $user_id . ' - ' . $item_id);
    $call_fn = $comp == 'friend' ? 'friends_add_friend' : 'engagements_add_engagement_as_receiver';
    $back_btn_fn = $comp == 'friend' ? 'bp_get_add_friend_button' : 'bp_get_add_engagement_button';
    
    if ( ! $call_fn( $user_id, $item_id,  $error = '', $note='' ) ) {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship could not be requested.', 'buddypress' )
        );

        wp_send_json_error( $response );
        
    } else {
        wp_send_json_success( array( 'contents' => $back_btn_fn( $item_id ) ) );
    }
}

function ajax_withdraw_relation($comp, $user_id, $item_id, $response,  $error = '', $note='') {
    error_log('>>ajax_withdraw_relation ' . $error . ' : ' . $user_id . ' - ' . $item_id);
    $call_fn = $comp == 'friend' ? 'friends_withdraw_friend' : 'engagements_withdraw_engagement';
    $back_btn_fn = $comp == 'friend' ? 'bp_get_add_friend_button' : 'bp_get_add_engagement_button';

    if ( $call_fn( $user_id, $item_id ) ) {
        wp_send_json_success( array( 'contents' => $back_btn_fn( $item_id ) ) );
    } else {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship request could not be cancelled.', 'buddypress' )
        );

        wp_send_json_error( $response );
    }
}


function ajax_remove_relation($comp, $user_id, $item_id, $response, $error='', $note ='') {
    error_log('>>ajax_remove_relation ' . $error . ' : ' . $user_id . ' - ' . $item_id);
    $call_fn = $comp == 'friend' ? 'friends_remove_friend' : 'engagements_remove_engagement';
    $back_btn_fn = $comp == 'friend' ? 'bp_get_add_friend_button' : 'bp_get_add_engagement_button';

    if ( ! $call_fn( $user_id, $item_id ) ) {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship could not be removed.', 'buddypress' )
        );

        wp_send_json_error( $response );
    } else {
        $is_user = bp_is_my_profile();

        if ( ! $is_user ) {
            $response = array( 'contents' => $back_btn_fn( $item_id ) );
        } else {
            $response = array(
                'feedback' => sprintf(
                    '<div class="bp-feedback success">%s</div>',
                    esc_html__( $comp .  ' - Relationship cancelled.', 'buddypress' )
                ),
                'type'     => 'success',
                'is_user'  => $is_user,
            );
        }

        wp_send_json_success( $response );
    }
}


function ajax_reject_relation($comp, $item_id, $response, $error = '') {
    error_log('>>ajax_reject_relation ' . $error . ' : ' . $item_id);
    $call_fn = $comp == 'friend' ? 'friends_reject_friend' : 'engagements_reject_engagement';

    if ( ! $call_fn( $item_id ) ) {
        wp_send_json_error(
            array(
                'feedback' => sprintf(
                    '<div class="bp-feedback error">%s</div>',
                    esc_html__( 'There was a problem rejecting that request. Please try again.', 'buddypress' )
                ),
            )
        );
    } else {
        wp_send_json_success(
            array(
                'feedback' => sprintf(
                    '<div class="bp-feedback success">%s</div>',
                    esc_html__( 'Relationship rejected.', 'buddypress' )
                ),
                'type'     => 'success',
                'is_user'  => true,
            )
        );
    }

}

function ajax_accept_relation($comp, $item_id, $response, $error = '') {
    error_log('>>ajax_accept_relation ' . $error . ' -> ' . $item_id);
    $call_fn = $comp == 'friend' ? 'friends_accept_friend' : 'engagements_accept_engagement';

    if ( ! $call_fn( $item_id ) ) {
        wp_send_json_error(
            array(
                'feedback' => sprintf(
                    '<div class="bp-feedback error">%s</div>',
                    esc_html__( 'There was a problem accepting that request. Please try again.', 'buddypress' )
                ),
            )
        );
    } else {
        wp_send_json_success(
            array(
                'feedback' => sprintf(
                    '<div class="bp-feedback success">%s</div>',
                    esc_html__( 'Friendship accepted.', 'buddypress' )
                ),
                'type'     => 'success',
                'is_user'  => true,
            )
        );
    }
}


function ajax_switch_each_action($comp, $action, $user_id, $item_id, $response) {
	$confirmed_sts_initiator = 'confirmed_sts_initiator';
	$confirmed_sts_receiver = 'confirmed_sts_receiver';
	$pending_sts_initiator = 'pending_sts_initiator';
	$awaiting_sts_receiver = 'awaiting_sts_receiver';
	$empty_sts_initiator = 'empty_sts_initiator';
	$empty_sts_receiver = 'empty_sts_receiver';

    if ($comp == 'friend') {
		$oppo = 'engagement';
		$check_relation_fn = 'BP_Friends_Friendship::check_is_relation';

		$accpt_action = 'friends_accept_friend';
		$reject_action = 'friends_reject_friend';
		$accpt_action_as_receiver = 'friends_accept_friend_as_receiver';
		$reject_action_as_receiver = 'friends_reject_friend_as_receiver';
		$add_action = 'friends_add_friend';
		$withdraw_action = 'friends_withdraw_friend';
		$remove_action = 'friends_remove_friend';
	} else {
		$oppo = 'friend';
		$check_relation_fn = 'BP_engagements_engagementship::check_is_relation';

		$accpt_action = 'engagements_accept_engagement';
		$reject_action = 'engagements_reject_engagement';
		$accpt_action_as_receiver = 'engagements_accept_engagement_as_receiver';
		$reject_action_as_receiver = 'engagements_reject_engagement_as_receiver';
		$add_action = 'engagements_add_engagement';
		$withdraw_action = 'engagements_withdraw_engagement';
		$remove_action = 'engagements_remove_engagements';
	}
	if( !strpos($action, 'accept') && !strpos($action, 'reject')){
		$relation_sts = $check_relation_fn( $user_id, $item_id );
	}

	if (false) {
		return;
	////////// Awaiting Request
	// Trying to accept awaiting relationship.
	} elseif ( ! empty( $action ) && $accpt_action === $action ) {
		ajax_accept_relation($comp, $item_id, $response, $comp . ' >> ajaxfile >> accept: ' . $comp . ' ' . $user_id . ' - ' . $item_id);
	
	// Trying to accept awaiting relationship - reverse.
	} elseif ( ! empty( $action ) && $accpt_action === $action ) {
		ajax_accept_relation($oppo, $item_id, $response, $comp . ' >> ajaxfile >> reject: ' . $comp . ' ' . $user_id . ' - ' . $item_id);
		
	// Trying to reject awaiting relationship.
	} elseif ( ! empty( $action ) && $reject_action === $action ) {
		ajax_reject_relation($comp, $item_id, $response, $comp . ' >> ajaxfile >> accept: ' . $comp . ' ' . $user_id . ' - ' . $item_id);

	// Trying to reject awaiting relationship - reverse.
	} elseif ( ! empty( $action ) && $reject_action === $action ) {
		ajax_reject_relation($oppo, $item_id, $response, $comp . ' >> ajaxfile >> reject: ' . $comp . ' ' . $user_id . ' - ' . $item_id);	
	
	////////////////// CRUD
	// Trying to add relationship.
	} elseif ( $relation_sts === $empty_sts_initiator && $action === $remove_action) {
		ajax_add_relation( $comp,  $user_id, $item_id, $response, ' >> ajaxfile >> remove-reverse: ' .  $comp . ' ' . $user_id . ' - ' . $item_id);
	
	// Trying to remove relationship.
	} elseif ( $relation_sts === $confirmed_sts_initiator ) {
		ajax_remove_relation( $comp,  $user_id, $item_id, $response, ' >> ajaxfile >> remove: ' . $comp . ' ' . $user_id . ' - ' . $item_id);	

	// Trying to withdraw pending relationship.
	} elseif ( $relation_sts === $pending_sts_initiator && $action === $withdraw_action ) {
		ajax_withdraw_relation($comp, $user_id, $item_id, $response, ' >> ajaxfile >> withdraw: ' . $comp . ' ' . $user_id . ' - ' . $item_id);

	// Request already pending.
	} else {
		$check_is_engagement = BP_engagements_engagementship::check_is_relation( $user_id, $item_id );
		$check_is_friend     = BP_Friends_Friendship::check_is_relation( $user_id, $item_id );
		
		error_log(' >>>>>> ajaxfile>comp and action: ' . $comp . ' - action: ' . $action );
		error_log(' >>>ajaxfile> relation_sts!: ' . $relation_sts);
		error_log(' >> ajaxfile>check_is_engagement: ' . $check_is_engagement);
		error_log(' >>>>>> ajaxfile>check_is_friend: ' . $check_is_friend);

		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Request Fallback Error - ' . $comp, 'buddypress' )
		);

		wp_send_json_error( $response );
	}
}

function lm_ajax_run_addremove_fn($comp) {
    $response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddypress' )
		),
	);

	$action = $_POST['action'];
	error_log(json_encode('ajaxfile nonce: ' . $_POST['nonce'] . ' action: ' . $action . ' - _wpnonce: '. $_POST['_wpnonce']));
	$user_id = bp_loggedin_user_id();
	$item_id = (int) $_POST['item_id'];
    ajax_check_nonce($comp, $response);
	// Cast fid as an integer.
	ajax_check_user_exist($comp, $item_id);
    ajax_switch_each_action($comp, $action, $user_id, $item_id, $response);
}

function ajax_check_nonce($comp, $error_response) {

    if ($comp == 'friend') {
		$default_check = 'bp_nouveau_friends';
	} else {
		$default_check = 'bp_nouveau_engagements';
	}

    // Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $error_response );
	}

	if ( empty( $_POST['nonce'] )
	|| empty( $_POST['item_id'] )
	|| ! bp_is_active( $comp.'s' ) ) {
		wp_send_json_error( $error_response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = $default_check;

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $action ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $action;
	}
	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		error_log(json_encode('ajaxfile verify nonce error!'));
		wp_send_json_error( $error_response );
	}
}

function ajax_check_user_exist($comp, $item_id) {
    if ($comp == 'friend') {
		$accpt_action = 'friends_accept_friend';
		$reject_action = 'friends_reject_friend';
	} else {
		$accpt_action = 'engagements_accept_engagement';
		$reject_action = 'engagements_reject_engagement';
	}

    // Check if the user exists only when the action is accpet or reject.
	if ( isset( $action ) && $action !== $accpt_action && $action !== $reject_action ) {
		$user = get_user_by( 'id', $item_id );
		if ( ! $user ) {
			wp_send_json_error(
				array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'No member found by that ID.', 'buddypress' )
					),
				)
			);
		}
	}
}
