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
			"{$comp}s_withdraw_{$comp}ship" => array(
				"function" => "bp_nouveau_ajax_addremove_fn_{$comp}",
				"nopriv"   => false,
			),
		),
		array(
			"{$comp}s_accept_{$comp}ship" => array(
				"function" => "bp_nouveau_ajax_addremove_fn_{$comp}",
				"nopriv"   => false,
			),
		),
		array(
			"{$comp}s_reject_{$comp}ship" => array(
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

function ajax_add_relation($comp, $user_id, $member_id, $response, $error = '', $note='') {
    error_log('>>ajax_add_relation ' . $error . ' : ' . $user_id . ' - ' . $member_id);
    $call_fn = $comp == 'friend' ? 'friends_add_friend' : 'engagements_add_engagement_as_receiver';
    $back_btn_fn = $comp == 'friend' ? 'bp_get_add_friend_button' : 'bp_get_add_engagement_button';
    
    if ( ! $call_fn( $user_id, $member_id,  $error = '', $note='' ) ) {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship could not be requested -44.', 'buddypress' )
        );

        wp_send_json_error( $response );
        
    } else {
        wp_send_json_success( array( 'contents' => $back_btn_fn( $member_id ) ) );
    }
}

function ajax_withdraw_relation($comp, $user_id, $member_id, $response,  $error = '', $note='') {
    error_log('>>ajax_withdraw_relation ' . $error . ' : ' . $user_id . ' - ' . $member_id);
    $call_fn = $comp == 'friend' ? 'friends_withdraw_friend' : 'engagements_withdraw_engagement';
    $back_btn_fn = $comp == 'friend' ? 'bp_get_add_friend_button' : 'bp_get_add_engagement_button';

    if ( $call_fn( $user_id, $member_id ) ) {
        wp_send_json_success( array( 'contents' => $back_btn_fn( $member_id ) ) );
    } else {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship request could not be cancelled.', 'buddypress' )
        );

        wp_send_json_error( $response );
    }
}


function ajax_remove_relation($comp, $user_id, $member_id, $response, $error='', $note ='') {
    error_log('>>ajax_remove_relation ' . $error . ' : ' . $user_id . ' - ' . $member_id);
    $call_fn = $comp == 'friend' ? 'friends_remove_friend' : 'engagements_remove_engagement';
    $back_btn_fn = $comp == 'friend' ? 'bp_get_add_friend_button' : 'bp_get_add_engagement_button';

    if ( ! $call_fn( $user_id, $member_id ) ) {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship could not be removed.', 'buddypress' )
        );

        wp_send_json_error( $response );
    } else {
        $is_user = bp_is_my_profile();

        if ( ! $is_user ) {
            $response = array( 'contents' => $back_btn_fn( $member_id ) );
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


function ajax_reject_relation($comp, $member_id, $response, $error = '') {
    error_log('>>ajax_reject_relation ' . $error . ' : ' . $member_id);
    $call_fn = $comp == 'friend' ? 'friends_reject_friend' : 'engagments_reject_engagmentship';

    if ( ! $call_fn( $member_id ) ) {
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

function ajax_accept_relation($comp, $member_id, $response, $error = '') {
    error_log('>>ajax_accept_relation ' . $error . ' : ' . $member_id);
    $call_fn = $comp == 'friend' ? 'friends_accept_friend' : 'engagement_accept_engagement';

    if ( ! $call_fn( $member_id ) ) {
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


function ajax_switch_each_action($comp, $action, $user_id, $member_id, $response) {
    if ($comp == 'friend') {
		$check_relation_fn = 'BP_Friends_Friendship::check_is_relation';
		$accpt_action = 'friends_accept_friend';
		$reject_action = 'friends_reject_friend';
		$add_action = 'friends_add_friend';
		$receiver_add_action = 'friends_add_friend_as_receiver';
		$withdraw_action = 'friends_withdraw_friend';
		$remove_action = 'friends_remove_friend';
		$receiver_remove_action = 'friends_remove_friend_as_receiver';

		$check_is_comp = 'is_friend';
		$check_is_comp_c1i = 'f_c1_is_friend_ini';
		$check_reverse_comp = 'f_c1_is_reverse_friend_rev';
		$check_reverse_oppo_c1r = 'f_c1_is_reverse_engagement_rev';
		$check_is_comp_c2i = 'f_c2_fm1_is_friend_ini';
        $check_reverse_oppo_c2r = 'f_c2_fm1_is_reverse_engagement_rev';
		$check_is_comp_c2r = 'f_c2_fm1_is_friend_rev';
		$check_pending_comp_c1i = 'f_c1_pending_friend_ini';
        $check_awaiting_c1r = 'f_c1_awaiting_response_rev';
		$check_not_comp = 'not_friend';
        $check_both_comp_v1r = 'f_c2_exist_both_friends_v1_rev';
        $check_both_oppo_v1i = 'f_c2_exist_both_friends_v1_ini'; 
        $check_pending_comp_c2i = 'f_c2_fm0_pending_friend_ini';
        $check_awaiting_c2r = 'f_c2_fm0_awaiting_response_rev';

	} else {
		$check_relation_fn = 'BP_engagements_engagementship::check_is_relation';
		$accpt_action = 'engagements_accept_engagement';
		$reject_action = 'engagements_reject_engagement';
		$add_action = 'engagements_add_engagement_as_receivers';
		$receiver_add_action = 'engagements_add_engagement_as_receiver';
		$withdraw_action = 'engagements_withdraw_engagement';
		$remove_action = 'engagements_remove_engagements';
		$receiver_remove_action = 'engagements_remove_engagements_as_receiver';

		$check_is_comp = 'is_engagement';
		$check_is_comp_c1i = 'e_c1_is_engagement_ini';
		$check_reverse_comp = 'e_c1_is_reverse_engagement_rev';
		$check_reverse_oppo_c1r = 'e_c1_is_reverse_friend_rev';
		$check_is_comp_c2i = 'e_c2_fm1_is_engagement_ini';
        $check_reverse_oppo_c2r = 'e_c2_fm1_is_reverse_friend_rev';
		$check_is_comp_c2r = 'e_c2_fm1_is_engagement_rev';
		$check_pending_comp_c1i = 'e_c1_pending_engagement_ini';
		$check_awaiting_c1r = 'e_c1_awaiting_response_rev';
		$check_not_comp = 'not_engagement';
        $check_both_comp_v1r = 'e_c2_exist_both_engagements_v1_rev';
        $check_both_oppo_v1i = 'e_c2_exist_both_engagements_v1_ini';
        $check_pending_comp_c2i = 'e_c2_fm0_pending_engagement_ini';
        $check_awaiting_c2r = 'e_c2_fm0_awaiting_response_rev';
	}

    $check_is_relation = $check_relation_fn( $user_id, $member_id );
    $check_is_engagement = BP_engagements_engagementship::check_is_relation( $user_id, $member_id );
	$check_is_friend     = BP_Friends_Friendship::check_is_relation( $user_id, $member_id );
	error_log('--- check both ' . json_encode($check_is_engagement . ' ' . $check_is_friend));

	error_log('ajaxfile >>> check: ' . $check_is_relation . ' - act: ' . $action );

	if ( ! empty( $action ) && $accpt_action === $action ) {
		ajax_accept_relation($comp, $member_id, $response);

	// Rejecting a relationship.
	} elseif ( ! empty( $action ) && $reject_action === $action ) {
		ajax_reject_relation($comp, $member_id, $response);

	// Trying to remove relationship.
	} elseif ( $check_is_relation === $check_is_comp ) {
		ajax_remove_relation( $comp,  $user_id, $member_id , $response, 'ajaxfile>> 107');
	
	// Trying to remove relationship.
	} elseif ( $check_is_relation === $check_is_comp_c1i && $action === $remove_action) {
		ajax_remove_relation( $comp,  $user_id, $member_id , $response, 'ajaxfile>> 111x');

	// Trying to remove relationship.
	} elseif ( $check_is_relation === $check_reverse_comp && $action === $remove_action) {
		ajax_remove_relation( $comp,  $member_id , $user_id, $response, 'ajaxfile>> 115');

    // Trying to remove relationship.
	} elseif ( $check_is_relation === $check_reverse_oppo_c1r && $action === $receiver_remove_action) {
		ajax_remove_relation( $comp, $member_id, $user_id, $response, 'ajaxfile>> 118x');

    // Trying to remove receiver relation from current button RER RFR.
	} elseif ( $check_is_relation === $check_reverse_oppo_c2r && $action === $receiver_remove_action) {
		ajax_remove_relation( $comp, $member_id, $user_id, $response, 'ajaxfile>> 278x');
    
    // Trying to remove receiver relation from current button RER RFR.
	} elseif ( $check_is_relation === $check_both_comp_v1r && $action === $receiver_remove_action) {
		ajax_remove_relation( $comp, $member_id, $user_id, $response, 'ajaxfile>> 286x');
    
    // Trying to remove initial relation RME RMF.
	} elseif ( $check_is_relation === $check_both_comp_v1r && $action === $remove_action) {
		ajax_remove_relation( $comp, $user_id, $member_id, $response, 'ajaxfile>> 290x');

	// Trying to stop relation RME RMF.
	} elseif ( $check_is_relation === $check_is_comp_c2i && $action === $remove_action) {
		ajax_remove_relation( $comp, $user_id, $member_id, $response, 'ajaxfile>> 122x');

	// Trying to stop friendship from engagement.
	} elseif ( $check_is_relation === $check_is_comp_c2r && $action === $receiver_remove_action ) {
		ajax_remove_relation( $comp, $member_id, $user_id, $response, 'ajaxfile>> 126');

	// Trying to cancel pending request.
	} elseif ( $check_is_relation === $check_is_comp_c1i && $action === $remove_action ) {
		ajax_remove_relation($comp, $user_id, $member_id, $response, 'ajaxfile>> 130');
	
    // Trying to cancel pending request RME RMF.
	} elseif ( $check_is_relation === $check_both_oppo_v1i && $action === $remove_action ) {
		ajax_remove_relation($comp, $user_id, $member_id, $response, 'ajaxfile>> 302x');
    
    // Trying to cancel pending request RME RMF.
	} elseif ( $check_is_relation === $check_awaiting_c2r && $action === $remove_action ) {
		ajax_remove_relation($comp, $user_id, $member_id, $response, 'ajaxfile>> 315x');

    // Trying to remove reversed relation RER RFR.
	} elseif ( $check_is_relation === $check_both_oppo_v1i && $action === $receiver_remove_action ) {
		ajax_remove_relation($comp, $member_id, $user_id, $response, 'ajaxfile>> 306x');
    
    // Trying to remove reversed relation RER RFR.
	} elseif ( $check_is_relation === $check_pending_comp_c2i && $action === $receiver_remove_action ) {
		ajax_remove_relation($comp, $member_id, $user_id, $response, 'ajaxfile>> 317x');

	// Trying to stop awaiting friendship.
	} elseif ( $check_is_relation === $check_is_comp_c2r && $action === $withdraw_action ) {
		ajax_withdraw_relation($comp, $user_id, $member_id, $response, 'ajaxfile>> 197');

	// Trying to cancel pending request.
	} elseif ( $check_is_relation === $check_pending_comp_c1i && $action === $withdraw_action) {
		ajax_withdraw_relation($comp, $user_id, $member_id, $response, 'ajaxfile>> 138');
	
    // Trying to cancel pending request PDE PDF.
	} elseif ( $check_is_relation === $check_pending_comp_c2i && $action === $withdraw_action) {
		ajax_withdraw_relation($comp, $user_id, $member_id, $response, 'ajaxfile>> 325x');

	// Trying to withdraw reversed initial PDE PDF
	} elseif ( $check_is_relation === $check_reverse_oppo_c2r && $action === $withdraw_action) {
		ajax_withdraw_relation($comp, $user_id, $member_id, $response, 'ajaxfile>> 339x');
	
    // Trying to add revers action.
	} elseif ( $check_is_relation === $check_awaiting_c1r && $action === $receiver_add_action) {
		ajax_add_relation($comp, $member_id, $user_id, $response, 'ajaxfile>> 343');
    
    // Trying to add revers action AER AFR.
	} elseif ( $check_is_relation === $check_both_comp_v1r && $action === $receiver_add_action) {
		ajax_add_relation($comp, $user_id, $member_id, $response, 'ajaxfile>> 347');

	// Trying to request friendship.
	} elseif ( $check_is_relation === $check_reverse_oppo_c1r && $action = $receiver_add_action ) {
		ajax_add_relation($comp, $user_id, $member_id, $response, 'ajaxfile >> 142x: ' . $comp . ' ' . $user_id . ' - ' . $member_id);

	// Trying to request friendship.
	} elseif ( $check_is_relation === $check_not_comp && $action = $add_action ) {
		ajax_add_relation($comp, $user_id, $member_id, $response, 'ajaxfile >> 146x: ' . $comp . ' ' . $member_id);

	// Request already pending.
	} else {
        error_log($check_is_relation . ' - ' . $action );
		error_log('ajaxfile >>> default Request Pending - comp: ' . $comp);
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Request Fallback Error - ' . $comp, 'buddypress' )
		);

		wp_send_json_error( $response );
	}
}

function lm_ajax_run_addremove_fn($comp) {
	error_log(json_encode('------lm_ajax_run_addremove_fn'));
    $response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddypress' )
		),
	);

	$action = $_POST['action'];
	error_log(json_encode('ajaxfile nonce: ' . $_POST['nonce'] . ' action: ' . $action . ' - _wpnonce: '. $_POST['_wpnonce']));
	$user_id = bp_loggedin_user_id();

    ajax_check_nonce($comp, $response);

	// Cast fid as an integer.
	$member_id = (int) $_POST['item_id'];

	ajax_check_user_exist($comp, $member_id);

    ajax_switch_each_action($comp, $action, $user_id, $member_id, $response);
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
	|| ! bp_is_active( "{$comp}" ) ) {
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

function ajax_check_user_exist($comp, $member_id) {
    if ($comp == 'friend') {
		$accpt_action = 'friends_accept_friend';
		$reject_action = 'friends_reject_friend';
	} else {
		$accpt_action = 'engagements_accept_engagement';
		$reject_action = 'engagements_reject_engagement';
	}

    // Check if the user exists only when the action is accpet or reject.
	if ( isset( $action ) && $action !== $accpt_action && $action !== $reject_action ) {
		$user = get_user_by( 'id', $member_id );
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
