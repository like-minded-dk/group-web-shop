<?php
function friend_btn_args($status, $pid, $sg, $rel_id) {
    error_log('||> friend_btn_args     , btn_status: '.$status);
    switch ( $status ) {
        case 'pending_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'pending_friend',
                'Cancel Supply-R pf Fri _ba',
                ['requests', array( 'cancel', $pid )],
                'friends_withdraw_friendship',
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case 'awaiting_response_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'awaiting_response_friend',
                'Approve Supply-S arf Fri _ba',
                ['requests'],
                '',
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case 'remove_friends':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_friends',
                'Stop Supply-R rf Fri _ba',
                ['remove-friend', array( $pid )],
                'friends_remove_friends',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case 'not_friends':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'not_friends',
                'Supply-R nf Fri _ba',
                ['add-friend', array( $pid )],
                'friends_add_friends',
                $rel_id, '_ba', 'add',  true, true
            );
        break;

        case 'is_friend':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'is_friend',
                'Stop Resell-S if Fri _ba',
                ['remove-engagement', array( $pid )],
                'friends_remove_friends',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case 'pending_engagement':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'pending_engagement',
                'Cancel Supply-R Fri _ba',
                ['requests', array( 'cancel', $pid )],
                'engagements_withdraw_engagementship',
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case 'awaiting_response_engagement':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'awaiting_response_engagement',
                'Approve Supply-R are Fri _ba',
                ['requests'],
                '',
                $rel_id, '_ba', 'remove',  true, true
            );
            break;
            
        case 'remove_engagements':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_engagements',
                'Stop Resell-R re Fri _ba',
                ['remove-friend', array( $pid )],
                'engagements_remove_engagements',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
            
        case 'not_engagements':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'add_friends_from_reciver',
                'Supply-R ne Fri _ba',
                ['add-engagements', array( $pid )],
                'friends_add_friends_from_reciver',
                $rel_id, '_ba', 'add',  true, true
            );
            break;

        case 'remove_engagements_from_reciver':
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'remove_engagements_from_reciver',
                'Stop Supply-R reff Fri _ba',
                ['remove-engagement', array( $pid )],
                'engagements_remove_engagements_from_reciver',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case 'remove_friends_from_reciver':
            $button_args = get_button_args_x(
                'engagements', $pid, $sg, 'err:',
                'remove_friends_from_reciver',
                'Stop Supply-S rffr Fri _ba',
                ['remove-friend', array( $pid )],
                'friends_remove_friends_from_reciver',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
        
        case 'add_friends_from_reciver':
        default:
            $button_args = get_button_args_x(
                'friend', $pid, $sg, 'err:',
                'add_friends_from_reciver',
                'Supply-R affr Fri _ba',
                ['add-engagements', array( $pid )],
                'friends_add_friends_from_reciver',
                $rel_id, '_ba', 'add',  true, true
            );
            break;
    }

    return $button_args;
}
