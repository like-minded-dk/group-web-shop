<?php
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
