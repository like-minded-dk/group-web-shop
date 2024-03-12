<?php
function get_button_args_x(
    $comp,
    $pid,
    $sg,
    $error,
    $id,
    $link_text,
    $chuck_array,
    $verify,
    $rel_id = 0,
    $mk = '_ba',
    $link_rel = 'remove',
    $block_self = true,
    $must_be_logged_in = true,
) {
    $class = $comp == 'friend' ? 'friendship-button' : 'engagement-button'; 
    error_log(json_encode(">>> {$error} {$id} {$mk}"));
    error_log(bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ));
    error_log(json_encode($verify));
    $text = __( "{$link_text} {$rel_id}", 'buddypress' );
    return array(
        'id'                => $id,
        'component'         => $comp == 'friend' ? 'friends' : 'engagements',
        'must_be_logged_in' => $must_be_logged_in,
        'block_self'        => $block_self,
        'wrapper_class'     => "{$class} {$id}",
        'wrapper_id'        => "{$class}-" . $pid,
        'link_href'         => wp_nonce_url( bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ), $verify),
        'link_text'         => $text,
        'link_title'        => $text,
        'link_id'           => $comp . '-' . $pid,
        'link_rel'          => $link_rel,
        'link_class'        => "{$class} {$id} requested",
    );
}


function ajax_add_relation($comp,  $user_id, $member_id,  $error = '', $note='') {
    error_log(json_encode($error));
    $add_fn = $comp == 'friend' ? 'friends_add_friend' : 'engagements_add_engagement';
    $btn_fn = $comp == 'friend' ? 'bp_get_add_friend_button' : 'bp_get_add_engagement_button';
    if ( ! $add_fn( $user_id, $member_id,  $error = '', $note='' ) ) {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship could not be requested -44.', 'buddypress' )
        );

        wp_send_json_error( $response );
    } else {
        wp_send_json_success( array( 'contents' => $btn_fn( $member_id ) ) );
    }
}

function ajax_withdraw($comp,  $user_id, $member_id,  $error = '', $note='') {
    error_log(json_encode($error));

    if ( $comp == 'engagement' ? engagements_withdraw_engagementship( $user_id, $member_id ) : friends_withdraw_friendship( $user_id, $member_id ) ) {
        wp_send_json_success( array( 'contents' => bp_get_add_engagement_button( $member_id ) ) );
    } else {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship request could not be cancelled.', 'buddypress' )
        );

        wp_send_json_error( $response );
    }
}


function ajax_remove_relation($comp, $user_id, $member_id, $error='', $note ='') {
    $remove_fn = $comp == 'friend' ? 'friends_remove_friend' : 'engagements_remove_engagement';
    $add_fn = $comp == 'friend' ? 'bp_get_add_friend_button' : 'bp_get_add_engagement_button';

    error_log($error . ': ' . $user_id . ' - ' . $member_id);
    if ( ! $remove_fn( $user_id, $member_id ) ) {
        $response['feedback'] = sprintf(
            '<div class="bp-feedback error">%s</div>',
            esc_html__( $comp . ' - Relationship could not be removed.', 'buddypress' )
        );

        wp_send_json_error( $response );
    } else {
        $is_user = bp_is_my_profile();

        if ( ! $is_user ) {
            $response = array( 'contents' => $add_fn( $member_id ) );
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
