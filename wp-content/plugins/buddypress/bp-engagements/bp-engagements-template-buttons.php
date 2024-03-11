<?php
function engagement_btn_args($status, $pid, $sg, $rel_id) {
    error_log('engagement_btn_args , btn_status: '.$status);
    switch ( $status ) {
        case 'pending_engagement':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'pending_engagement',
                'Cancel Resell-S pe Eng _ba',
                ['requests', array( 'cancel', $pid )],
                'engagements_withdraw_engagementship',
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case 'awaiting_response_engagement':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'awaiting_response_engagement',
                'Approve Resell-S are Eng _ba',
                ['requests'],
                '',
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case 'remove_engagements':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_engagements',
                'Stop Resell-R rf Eng _ba',
                ['remove-engagement', array( $pid )],
                'engagements_remove_engagements',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case 'not_engagements':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'not_engagements',
                'Resell-R ne Eng _ba',
                ['add-engagement', array( $pid )],
                'engagements_add_engagements',
                $rel_id, '_ba', 'add',  true, true
            );
        break;

        case 'is_engagement':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'is_engagement',
                'Stop Resell-S ie Eng _ba',
                ['remove-friend', array( $pid )],
                'engagements_remove_engagements',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;

        case 'pending_friend':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'pending_friend',
                'Cancel Resell-S pf Eng _ba',
                ['requests', array( 'cancel', $pid )],
                'engagements_withdraw_engagementship',
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

        case 'awaiting_response_friend':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'awaiting_response_friend',
                'Approve Resell-S arf Eng _ba',
                ['requests'],
                '',
                $rel_id, '_ba', 'remove',  true, true
            );
            break;

            
        case 'remove_friends':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_friends',
                'Stop Supply-R re Eng _ba',
                ['remove-engagement', array( $pid )],
                'friends_remove_friends',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
            
        case 'not_friends':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'add_engagements_from_reciver',
                'Resell-S nf Eng _ba',
                ['add-friends', array( $pid )],
                'engagements_add_engagements_from_reciver',
                $rel_id, '_ba', 'add',  true, true
            );
            break;

        case 'remove_friends_from_reciver':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_friends_from_reciver',
                'Stop Resell-R rffr Eng _ba',
                ['remove-engagement', array( $pid )],
                'engagements_remove_engagements_from_reciver',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
    
        case 'remove_engagements_from_reciver':
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'remove_engagements_from_reciver',
                'Stop Resell-S refr Eng _ba',
                ['remove-engagement', array( $pid )],
                'engagements_remove_engagements_from_reciver',
                $rel_id, '_ba', 'remove',  true, false
            );
            break;
        
        case 'add_engagements_from_reciver':
        default:
            $button_args = get_button_args_x(
                'engagement', $pid, $sg, 'err:',
                'add_engagements_from_reciver',
                'Resell-S aefr Eng _ba',
                ['add-friends', array( $pid )],
                'engagements_add_engagements_from_reciver',
                $rel_id, '_ba', 'add',  true, true
            );
            break;
    }

    return $button_args;
}
