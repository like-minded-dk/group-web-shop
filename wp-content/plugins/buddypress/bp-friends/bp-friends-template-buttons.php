<?php
function friend_initiator_btn_args($friendship_status, $potential_friend_id, $friends_slug) {
    error_log('');
    error_log('status f-i');
    error_log($friendship_status);
    switch ( $friendship_status ) {
        case 'pending_friend':
            $button_args = array(
                'id'                => 'pending_friend',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'friendship-button pending_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'requests', array( 'cancel', $potential_friend_id ) ) ) ),
                    'friends_withdraw_friendship'
                ),
                'link_text'         => __( "Cancel Supply Reseller Request f-i", 'buddypress' ),
                'link_title'        => __( "Cancel Supply Reseller Request f-i", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button pending_friend requested',
            );
            break;

        case 'awaiting_response':
            $button_args = array(
                'id'                => 'awaiting_response',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'friendship-button awaiting_response_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'requests' ) ) ),
                'link_text'         => __( "Supply Reseller Requested f-i", 'buddypress' ),
                'link_title'        => __( "Supply Reseller Requested f-i", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button awaiting_response_friend requested',
            );
            break;

        case 'exist_initiator_friend':
        case 'is_friend':
            $button_args = array(
                'id'                => 'is_friend',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'friendship-button is_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'remove-friend', array( $potential_friend_id ) ) ) ),
                    'friends_remove_friend'
                ),
                'link_text'         => __( "Stop Supply Reseller f-i", 'buddypress' ),
                'link_title'        => __( "Stop Supply Reseller f-i", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button is_friend remove',
            );
            break;

        case 'exist_more_engagements':
            error_log(json_encode('>>default f-i'));
            $button_args = array(
                'id'                => 'is_friend',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'friendship-button is_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'remove-friend', array( $potential_friend_id ) ) ) ),
                    'friends_remove_engagements_from_friends'
                ),
                'link_text'         => __( "Stop Supply Reseller f-i", 'buddypress' ),
                'link_title'        => __( "Stop Supply Reseller f-i", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button remove_engagements_from_friends remove',
            );
            break;

        default:
            error_log(json_encode('>>default f-i'));
            $button_args = array(
                'id'                => 'not_friends',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'friendship-button not_friends',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'add-friend', array( $potential_friend_id ) ) ) ),
                    'friends_add_friend'
                ),
                'link_text'         => __( "Supply Reseller f-i", 'buddypress' ),
                'link_title'        => __( "Supply Reseller f-i", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'add',
                'link_class'        => 'friendship-button not_friends add',
            );
            break;
    }

    return $button_args;
}

function friend_reciver_btn_args($friendship_status, $potential_friend_id, $friends_slug) {
    error_log('');
    error_log('status f-r');
    error_log($friendship_status);
    switch ( $friendship_status ) {
        case 'pending_friend':
            $button_args = array(
                'id'                => 'pending_friend',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'friendship-button pending_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'requests', array( 'cancel', $potential_friend_id ) ) ) ),
                    'friends_withdraw_friendship'
                ),
                'link_text'         => __( "Cancel Resell Supplier Request f-r", 'buddypress' ),
                'link_title'        => __( "Cancel Resell Supplier Request f-r", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button pending_friend requested',
            );
            break;

        case 'awaiting_response':
            $button_args = array(
                'id'                => 'awaiting_response',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'friendship-button awaiting_response_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'requests' ) ) ),
                'link_text'         => __( "Resell Supplier Requested f-r", 'buddypress' ),
                'link_title'        => __( "Resell Supplier Requested f-r", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button awaiting_response_friend requested',
            );
            break;

        case 'remove_initiator_friend':
        case 'exist_initiator_friend':
        case 'is_friend':
            $button_args = array(
                'id'                => 'is_engagement',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'engagementship-button is_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'remove-engagement', array( $potential_friend_id ) ) ) ),
                    'friends_remove_friend'
                ),
                'link_text'         => __( "Stop Resell Supplier f-r", 'buddypress' ),
                'link_title'        => __( "Stop Resell Supplier f-r", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button is_friend remove',
            );
            break;

        case 'remove_more_engagements':
            error_log(json_encode('>exist_more_friends -f-r'));
            $button_args = array(
                'id'                => 'remove_more_engagements',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'friendship-button is_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'remove-friend', array( $potential_friend_id ) ) ) ),
                    'friends_remove_engagements_from_friends'
                ),
                'link_text'         => __( "Stop Supply Reseller f-r", 'buddypress' ),
                'link_title'        => __( "Stop Supply Reseller f-r", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button remove_engagements_from_friends remove',
            );
            break;

        case 'exist_more_friends':
            error_log(json_encode('>exist_more_friends -f-r'));
            $button_args = array(
                'id'                => 'exist_more_friends',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'friendship-button is_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'remove-friend', array( $potential_friend_id ) ) ) ),
                    'engagements_remove_friends_from_engagements'
                ),
                'link_text'         => __( "Stop Resell Supplier f-r", 'buddypress' ),
                'link_title'        => __( "Stop Resell Supplier f-r", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button remove_friends_from_engagements remove',
            );
            break;
        
        case 'exist_initiator_friend':
        default:
            error_log(json_encode('>>default f-r'));
            $button_args = array(
                'id'                => 'not_friends',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'friendship-button not_friends',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'add-engagements', array( $potential_friend_id ) ) ) ),
                    'friends_not_engagements_from_friends'
                ),
                'link_text'         => __( "Resell Supplier f-r", 'buddypress' ),
                'link_title'        => __( "Resell Supplier f-r", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'add',
                'link_class'        => 'friendship-button not_engagements_from_friends add',
            );
            break;
    }

    return $button_args;
}
