<?php
function engagement_initiator_btn_args($engagementship_status, $potential_engagement_id, $engagements_slug) {
    error_log('');
    error_log('status e-i');
    error_log($engagementship_status);
    switch ( $engagementship_status ) {
        case 'pending_engagement':
            $button_args = array(
                'id'                => 'pending_engagement',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'engagementship-button pending_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'requests', array( 'cancel', $potential_engagement_id ) ) ) ),
                    'engagements_withdraw_engagementship'
                ),
                'link_text'         => __( "Cancel Resell Supplier Request e-i", 'buddypress' ),
                'link_title'        => __( "Cancel Resell Supplier Request e-i", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button pending_engagement requested',
            );
            break;

        case 'awaiting_response':
            $button_args = array(
                'id'                => 'awaiting_response',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'engagementship-button awaiting_response_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'requests' ) ) ),
                'link_text'         => __( "Resell Supplier Requested e-i", 'buddypress' ),
                'link_title'        => __( "Resell Supplier Requested e-i", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button awaiting_response_engagement requested',
            );
            break;

        case 'exist_initiator_engagement':
        case 'is_engagement':
            $button_args = array(
                'id'                => 'is_engagement',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'engagementship-button is_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'remove-engagement', array( $potential_engagement_id ) ) ) ),
                    'engagements_remove_engagement'
                ),
                'link_text'         => __( "Stop Resell Supplier e-i", 'buddypress' ),
                'link_title'        => __( "Stop Resell Supplier e-i", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button is_engagement remove',
            );
            break;

        case 'exist_more_friends':
            error_log(json_encode('>>default f-i'));
            $button_args = array(
                'id'                => 'is_engagement',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'engagementship-button is_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'remove-engagement', array( $potential_engagement_id ) ) ) ),
                    'engagements_remove_friends_from_engagements'
                ),
                'link_text'         => __( "Stop Supply Reseller f-i", 'buddypress' ),
                'link_title'        => __( "Stop Supply Reseller f-i", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button remove_friends_from_engagements remove',
            );
            break;
    
        default:
            error_log(json_encode('>>default e-i'));
            $button_args = array(
                'id'                => 'not_engagements',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'engagementship-button not_engagements',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'add-engagement', array( $potential_engagement_id ) ) ) ),
                    'engagements_add_engagement'
                ),
                'link_text'         => __( "Resell Supplier e-i", 'buddypress' ),
                'link_title'        => __( "Resell Supplier e-i", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'add',
                'link_class'        => 'engagementship-button not_engagements add',
            );
            break;
    }

    return $button_args;
}

function engagement_reciver_btn_args($engagementship_status, $potential_engagement_id, $engagements_slug) {
    error_log('');
    error_log('status e-r');
    error_log($engagementship_status);
    switch ( $engagementship_status ) {
        case 'pending_engagement':
            $button_args = array(
                'id'                => 'pending_engagement',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'engagementship-button pending_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'requests', array( 'cancel', $potential_engagement_id ) ) ) ),
                    'engagements_withdraw_engagementship'
                ),
                'link_text'         => __( "Cancel Supply Reseller Request e-r", 'buddypress' ),
                'link_title'        => __( "Cancel Supply Reseller Request e-r", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button pending_engagement requested',
            );
            break;

        case 'awaiting_response':
            $button_args = array(
                'id'                => 'awaiting_response',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'engagementship-button awaiting_response_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'requests' ) ) ),
                'link_text'         => __( "Supply Reseller Requested e-r", 'buddypress' ),
                'link_title'        => __( "Supply Reseller Requested e-r", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button awaiting_response_engagement requested',
            );
            break;

        case 'remove_initiator_engagement':
        case 'exist_initiator_engagement':
        case 'is_engagement':
            $button_args = array(
                'id'                => 'is_friend',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'friendship-button is_friend',
                'wrapper_id'        => 'friendship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'remove-friend', array( $potential_engagement_id ) ) ) ),
                    'engagements_remove_engagement'
                ),
                'link_text'         => __( "Stop Supply Reseller e-r", 'buddypress' ),
                'link_title'        => __( "Stop Supply Reseller e-r", 'buddypress' ),
                'link_id'           => 'friend-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'friendship-button is_engagement remove',
            );
            break;

        case 'remove_more_friends':
            $button_args = array(
                'id'                => 'remove_more_friends',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'engagementship-button is_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'remove-engagement', array( $potential_engagement_id ) ) ) ),
                    'engagements_remove_friends_from_engagements'
                ),
                'link_text'         => __( "Stop Supply Reseller e-r", 'buddypress' ),
                'link_title'        => __( "Stop Supply Reseller e-r", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button remove_friends_from_engagements remove',
            );
            break;

        case 'exist_more_engagements':
            $button_args = array(
                'id'                => 'exist_more_engagements',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => false,
                'wrapper_class'     => 'engagementship-button is_engagement',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'remove-engagement', array( $potential_engagement_id ) ) ) ),
                    'friends_remove_friends_from_engagements'
                ),
                'link_text'         => __( "Stop Supply Reseller e-r", 'buddypress' ),
                'link_title'        => __( "Stop Supply Reseller e-r", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'remove',
                'link_class'        => 'engagementship-button remove_friends_from_engagements remove',
            );
            break;
            
        case 'exist_initiator_engagement':
        default:
            error_log(json_encode('>>default e-r'));
            $button_args = array(
                'id'                => 'not_engagements',
                'component'         => 'engagements',
                'must_be_logged_in' => true,
                'block_self'        => true,
                'wrapper_class'     => 'engagementship-button not_engagements',
                'wrapper_id'        => 'engagementship-button-' . $potential_engagement_id,
                'link_href'         => wp_nonce_url(
                    bp_loggedin_user_url( bp_members_get_path_chunks( array( $engagements_slug, 'add-friend', array( $potential_engagement_id ) ) ) ),
                    'engagements_not_friends_from_engagements'
                ),
                'link_text'         => __( "Supply Reseller e-r", 'buddypress' ),
                'link_title'        => __( "Supply Reseller e-r", 'buddypress' ),
                'link_id'           => 'engagement-' . $potential_engagement_id,
                'link_rel'          => 'add',
                'link_class'        => 'engagementship-button not_friends_from_engagements add',
            );
            break;
    }

    return $button_args;
}
