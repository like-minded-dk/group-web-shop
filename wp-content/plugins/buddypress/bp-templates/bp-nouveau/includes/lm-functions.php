<?php
function ajax_withdraw($comp,  $user_id, $member_id,  $error = '', $note='') {
    error_log(json_encode($error));

    if ( $comp == 'engagement' ? engagements_withdraw_engagementship( $user_id, $member_id ) : friends_withdraw_friendship( $user_id, $member_id ) ) {
        wp_send_json_success( array( 'contents' => bp_get_add_engagement_button( $member_id ) ) );
    } else {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . 'ship request could not be cancelled.', 'buddypress' )
        );

        wp_send_json_error( $response );
    }
}

function ajax_remove_friend($user_id, $friend_id, $error='', $note ='') {
    error_log($error . ': ' . $user_id . ' - ' . $friend_id);
    if ( ! friends_remove_friend( $user_id, $friend_id ) ) {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( 'Friendship could not be removed.', 'buddypress' )
        );

        wp_send_json_error( $response );
    } else {
        $is_user = bp_is_my_profile();

        if ( ! $is_user ) {
            $response = array( 'contents' => bp_get_add_friend_button( $friend_id ) );
        } else {
            $response = array(
                'feedback' => sprintf(
                    '<div class="bp-feedback success">%s</div>',
                    esc_html__( 'friendship cancelled.', 'buddypress' )
                ),
                'type'     => 'success',
                'is_user'  => $is_user,
            );
        }

        wp_send_json_success( $response );
    }
}

function ajax_remove_engagement($user_id, $engagement_id, $error='', $note ='') {
    error_log($error . ': ' . $user_id . ' - ' . $engagement_id);
    if ( ! engagements_remove_engagement( $user_id, $engagement_id ) ) {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( 'engagementship could not be removed.', 'buddypress' )
        );

        wp_send_json_error( $response );
    } else {
        $is_user = bp_is_my_profile();

        if ( ! $is_user ) {
            $response = array( 'contents' => bp_get_add_engagement_button( $engagement_id ) );
        } else {
            $response = array(
                'feedback' => sprintf(
                    '<div class="bp-feedback success">%s</div>',
                    esc_html__( 'engagementship cancelled.', 'buddypress' )
                ),
                'type'     => 'success',
                'is_user'  => $is_user,
            );
        }

        wp_send_json_success( $response );
    }
}
