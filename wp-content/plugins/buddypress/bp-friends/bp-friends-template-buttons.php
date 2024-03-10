<?php
function friend_initiator_btn_args($status, $pid, $sg, $rel_id) {
    error_log('status f-i '.$status);
    switch ( $status ) {
        case 'pending_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'pending_friend',
                'Cancel Resell-S',
                ['requests', array( 'cancel', $pid )],
                'friends_remove_friends',
                $rel_id, 'f-i', 'remove',  true, true
            );
            break;

        case 'awaiting_response':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'awaiting_response_friend',
                'Approve Supply-R',
                ['requests'],
                '',
                $rel_id, 'f-i', 'remove',  true, true
            );
            break;

        case 'exist_initiator_friend':
        case 'is_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_friends',
                'Stop Supply-R',
                ['remove-friend', array( $pid )],
                'friends_remove_friends',
                $rel_id, 'f-i', 'remove',  true, false
            );

            break;

        case 'remove_friends':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_friends',
                'Cancel Resell-S',
                ['remove-friend', array( $pid )],
                'friends_remove_friends',
                $rel_id, 'f-i', 'remove',  true, false
            );

        case 'exist_more_engagements':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_engagements_from_friends',
                'Stop Supply-R',
                ['remove-friend', array( $pid )],
                'friends_remove_engagements_from_friends',
                $rel_id, 'f-i', 'remove',  true, false
            );

            break;

        case 'remove_initiator_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_friends_from_engagements',
                'Stop Resell-S',
                ['remove-friend', array( $pid )],
                'engagements_remove_friends_from_engagements',
                $rel_id, 'f-i', 'remove',  true, false
            );
            break;

        default:
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'not_friends',
                'Supply-R',
                ['add-friend', array( $pid )],
                'friends_add_friends',
                $rel_id, 'f-i', 'add',  true, true
            );

        break;
    }

    return $button_args;
}

function friend_reciver_btn_args($status, $pid, $sg, $rel_id) {
    error_log('status f-r '.$status);
    switch ( $status ) {
        case 'pending_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'pending_friend',
                'Cancel Supply-R',
                ['requests', array( 'cancel', $pid )],
                'friends_withdraw_friendship',
                $rel_id, 'f-r', 'remove',  true, true
            );

            break;

        case 'awaiting_response':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'awaiting_response_friend',
                'Approve Supply-R',
                ['requests'],
                '',
                $rel_id, 'f-r', 'remove',  true, true
            );

            break;

        case 'remove_initiator_friend':
        case 'exist_initiator_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'is_friend',
                'Stop Resell-S',
                ['remove-engagement', array( $pid )],
                'friends_remove_friends',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;

        case 'is_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_friends',
                'Stop Supply-R',
                ['remove-friend', array( $pid )],
                'friends_remove_friends',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;

        case 'remove_more_engagements':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_engagements_from_friends',
                'Stop Supply-R',
                ['remove-friend', array( $pid )],
                'friends_remove_engagements_from_friends',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;

        case 'remove_engagements':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_engagements',
                'Stop Supply-R',
                ['remove-friend', array( $pid )],
                'engagements_remove_engagements',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;

        case 'exist_more_friends':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_friends_from_engagements',
                'Stop Resell-S',
                ['remove-friend', array( $pid )],
                'engagements_remove_friends_from_engagements',
                $rel_id, 'f-r', 'remove',  true, false
            );
            break;
        
        case 'exist_initiator_friend':
        default:
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'not_engagements_from_friends',
                'Resell-S',
                ['add-engagements', array( $pid )],
                'friends_not_engagements_from_friends',
                $rel_id, 'f-r', 'add',  true, true
            );

            break;
    }

    return $button_args;
}
