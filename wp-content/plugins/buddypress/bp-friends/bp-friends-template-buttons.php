<?php
function friend_initiator_btn_args($friendship_status, $potential_friend_id, $friends_slug) {
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
                'link_text'         => __( "Cancel Supply Reseller Request", 'buddypress' ),
                'link_title'        => __( "Cancel Supply Reseller Request", 'buddypress' ),
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
                'link_text'         => __( "Supply Reseller Requested", 'buddypress' ),
                'link_title'        => __( "Supply Reseller Requested", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button awaiting_response_friend requested',
            );
            break;

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
                'link_text'         => __( "Stop Supply Reseller", 'buddypress' ),
                'link_title'        => __( "Stop Supply Reseller", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button is_friend remove',
            );
            break;

        default:
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
                'link_text'         => __( "Supply Reseller", 'buddypress' ),
                'link_title'        => __( "Supply Reseller", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'add',
                'link_class'        => 'friendship-button not_friends add',
            );
            break;
    }

    return $button_args;
}

function friend_reciver_btn_args($friendship_status, $potential_friend_id, $friends_slug) {
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
                'link_text'         => __( "Cancel Resell Supplier Request", 'buddypress' ),
                'link_title'        => __( "Cancel Resell Supplier Request", 'buddypress' ),
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
                'link_text'         => __( "Resell Supplier Requested", 'buddypress' ),
                'link_title'        => __( "Resell Supplier Requested", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button awaiting_response_friend requested',
            );
            break;

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
                'link_text'         => __( "Stop Resell Supplier", 'buddypress' ),
                'link_title'        => __( "Stop Resell Supplier", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button is_friend remove',
            );
            break;

        default:
            $button_args = array(
                'id'                => 'not_friends',
                'component'         => 'friends',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'friendship-button not_friends',
                'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $friends_slug, 'add-engagment', array( $potential_friend_id ) ) ) ),
                    'engagements_add_engagement'
                ),
                'link_text'         => __( "Resell Supplier", 'buddypress' ),
                'link_title'        => __( "Resell Supplier", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_friend_id,
                'link_rel'          => 'add',
                'link_class'        => 'friendship-button not_friends add',
            );
            break;
    }

    return $button_args;
}
