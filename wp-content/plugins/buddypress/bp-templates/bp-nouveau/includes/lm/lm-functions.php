<?php
require dirname( __FILE__ ) . '/lm-ajax-functions.php';
require dirname( __FILE__ ) . '/lm-class-functions.php';
require dirname( __FILE__ ) . '/lm-loader.php';

function break_sql($error = '') {
    // @todo lm shortcut delete
    error_log($error ?? 'break call');
    throw new ErrorException($error);
}

function get_button_args_wrapper(
    $comp,
    $pid,
    $sg,
    $error,
    $rel_id,
    $mk,
    $link_rel,
    $block_self,
    $must_be_logged_in,
    $action,
    $verify,
    $link_text,
    $chuck_array,
) {
    $class = $comp == 'friend' ? 'friendship-button' : 'engagement-button'; 
    // error_log("||> {$error} {$action} {$mk}");
    // error_log('||> user_url:' . bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ));
    // error_log('||> verify:' . json_encode($verify));
    $text = __( "{$link_text} {$rel_id}", 'buddypress' );
    return array(
        'id'                => $action,
        'component'         => $comp == 'friend' ? 'friends' : 'engagements',
        'must_be_logged_in' => $must_be_logged_in,
        'block_self'        => $block_self,
        'wrapper_class'     => "{$class} {$action}",
        'wrapper_id'        => "{$class}-" . $pid,
        'link_href'         => wp_nonce_url( bp_loggedin_user_url( bp_members_get_path_chunks( array_merge([$sg], $chuck_array) ) ), $verify),
        'link_text'         => $text,
        'link_title'        => $text,
        'link_id'           => $comp . '-' . $pid,
        'link_rel'          => $link_rel,
        'button_element'    => 'button',
        'link_class'        => "{$class} {$action} requested",
    );
}

function simple_cond_btn_args($relation_btn, $action) {
    $args = $relation_btn($action);
    error_log('|>>>> args_id: '.json_encode($args['id']));
    return $args;
}
 
// function get_db_and_log( $cond_str, $cond_note ) {
//     $db = $cond_str == '0-1' ? 'Fd' : 'Ed' ;
//     error_log('|>>>> ' . $cond_str . ' - '  . $db . ' - condId: ' . $cond_note);
//     return $db;
// }

// function cond_btn_args( $comp, $comp_st, $oppo_st, $relation_btn, $condId, $compE, $oppE, $caseE, $compF, $oppF, $caseF ) {
//     if (                  ($comp_st == $compE && $oppo_st == $oppE)   ||          ($comp_st == $compF && $oppo_st == $oppF)) {
//         $cond_str = (int) ($comp_st == $compE && $oppo_st == $oppE) . '-' . (int) ($comp_st == $compF && $oppo_st == $oppF);
//         $db = get_db_and_log($cond_str, $condId);
//         $args = '';
//         if ($db == 'Ed') {
//             error_log('|>>>> condId: '.$condId.' - ETB');
//             $args = $relation_btn($caseE);
//         } else {
//             error_log('|>>>> condId: '.$condId.' - FTB');
//             $args = $relation_btn($caseF);
//         }
        
//         error_log('>>args_id: '.json_encode($args['id']));
//         return $args;
//     } 
//  }
