<?php
function engagement_initiator_btn_args($status, $pid, $sg, $rel_id) {
    error_log('status f-i '.$status);
    switch ( $status ) {
        case 'pending_engagement':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'pending_engagement',
                'Cancel Resell-S',
                ['requests', array( 'cancel', $pid )],
                'engagements_remove_engagements',
                $rel_id, 'f-i', 'remove',  true, true
            );
            break;

        case 'awaiting_response':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'awaiting_response_engagement',
                'Approve Supply-R',
                ['requests'],
                '',
                $rel_id, 'f-i', 'remove',  true, true
            );
            break;

        case 'stop_supply':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_engagements',
                'Stop Supply-R',
                ['remove-engagement', array( $pid )],
                'engagements_remove_engagements',
                $rel_id, 'f-i', 'remove',  true, false
            );

            break;

        case 'remove_engagements':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_engagements',
                'Cancel Resell-S',
                ['remove-engagement', array( $pid )],
                'engagements_remove_engagements',
                $rel_id, 'f-i', 'remove',  true, false
            );

        case 'exist_more_friends':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_friends_from_engagements',
                'Stop Supply-R',
                ['remove-engagement', array( $pid )],
                'engagements_remove_friends_from_engagements',
                $rel_id, 'f-i', 'remove',  true, false
            );

            break;

        case 'remove_initiator_engagement':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_engagements_from_friends',
                'Stop Resell-S',
                ['remove-engagement', array( $pid )],
                'friends_remove_engagements_from_friends',
                $rel_id, 'f-i', 'remove',  true, false
            );
            break;

        default:
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'not_engagements',
                'Supply-R',
                ['add-engagement', array( $pid )],
                'engagements_add_engagements',
                $rel_id, 'f-i', 'add',  true, true
            );

        break;
    }

    return $button_args;
}

function engagement_reciver_btn_args($status, $pid, $sg, $rel_id) {
    error_log('status f-r '.$status);
    switch ( $status ) {
        case 'pending_engagement':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'pending_engagement',
                'Cancel Supply-R',
                ['requests', array( 'cancel', $pid )],
                'engagements_withdraw_engagementship',
                $rel_id, 'f-r', 'remove',  true, true
            );

            break;

        case 'awaiting_response':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'awaiting_response_engagement',
                'Approve Supply-R',
                ['requests'],
                '',
                $rel_id, 'f-r', 'remove',  true, true
            );

            break;

        case 'remove_initiator_engagement':
        case 'exist_initiator_engagement':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'is_engagement',
                'Stop Resell-S',
                ['remove-friend', array( $pid )],
                'engagements_remove_engagements',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;

        case 'is_engagement':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_engagements',
                'Stop Supply-R',
                ['remove-engagement', array( $pid )],
                'engagements_remove_engagements',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;

        case 'remove_more_friends':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_friends_from_engagements',
                'Stop Supply-R',
                ['remove-engagement', array( $pid )],
                'engagements_remove_friends_from_engagements',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;

        case 'remove_friends':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_friends',
                'Stop Supply-R',
                ['remove-engagement', array( $pid )],
                'friends_remove_friends',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;

        case 'exist_more_engagements':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_engagements_from_friends',
                'Stop Resell-S',
                ['remove-engagement', array( $pid )],
                'friends_remove_engagements_from_friends',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;
        
        case 'exist_initiator_engagement':
        default:
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'not_friends_from_engagements',
                'Resell-S',
                ['add-friends', array( $pid )],
                'engagements_not_friends_from_engagements',
                $rel_id, 'f-r', 'add',  true, true
            );

            break;
    }

    return $button_args;
}
