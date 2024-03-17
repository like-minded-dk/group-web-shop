<?php
function add_request_button($isf, &$btns, $comp, $user_id, $css_args) {
	[ $parent_class, $button_element, $parent_element] = $css_args;
	$conf = array(
        // todo lm change accept action id
        'friend' => array(
            'isf' => true,
            'comp' => $comp,
            'accept_key' => 'accept_friend',
            'reject_key' => 'reject_friend',
            'component' => 'friends',
			'accept_link_fn' => 'bp_get_friend_accept_request_link',
			'reject_link_fn' => 'bp_get_friend_reject_request_link',
			'get_btn_args_fn' => 'bp_get_add_friend_button_args',
        ),
        'engagement' => array(
            'isf' => false,
            'comp' => $comp,
            'accept_key' => 'accept_engagement',
            'reject_key' => 'reject_engagement',
            'component' => 'engagements',
			'accept_link_fn' => 'bp_get_engagement_accept_request_link',
			'reject_link_fn' => 'bp_get_engagement_reject_request_link',
			'get_btn_args_fn' => 'bp_get_add_engagement_button_args',
        )
    );
	$cf = $conf[$comp];
	get_request_btn_args($cf, $btns, $css_args);

	$btn_args = $cf['get_btn_args_fn']( $user_id );
	error_log('[btn_args] '.json_encode($btn_args));
	// If button element set add nonce link to data attr
	if ( 'button' === $button_element ) {
		$btns[$cf['accept_key']]['button_attr']['data-bp-nonce'] = $cf['accept_link_fn']();
		$btns[$cf['reject_key']]['button_attr']['data-bp-nonce'] = $cf['reject_link_fn']();
	} else {
		$btns[$cf['accept_key']]['button_attr']['href'] = $cf['accept_link_fn']();
		$btns[$cf['reject_key']]['button_attr']['href'] = $cf['reject_link_fn']();
	}
}

function get_request_btn_args($cf, &$btns, $css_args) {
    [ $parent_class, $button_element, $parent_element] = $css_args;
    $btn_suffix = $cf['isf'] ? 'to supply' : 'to resell';
    $btns[$cf['accept_key']] = array(
        'id'                => $cf['accept_key'],
        'position'          => 5,
        'component'         => $cf['component'],
        'must_be_logged_in' => true,
        'parent_element'    => $parent_element,
        'link_text'         => _x( "Accept {$btn_suffix}", 'button', 'buddypress' ),
        'parent_attr'       => array(
            'id'    => '',
            'class' => $parent_class ,
        ),
        'button_element'    => $button_element,
        'button_attr'       => array(
            'class'           => 'button accept',
            'rel'             => '',
        ),
    );
    $btns[$cf['reject_key']] = array(
        'id'                => $cf['reject_key'],
        'position'          => 5,
        'component'         => $cf['component'],
        'must_be_logged_in' => true,
        'parent_element'    => $parent_element,
        'link_text'         => _x( "Reject {$btn_suffix}", 'button', 'buddypress' ),
        'parent_attr'       => array(
            'id'    => '',
            'class' => $parent_class,
        ),
        'button_element'    => $button_element,
        'button_attr'       => array (
            'class'           => 'button reject',
            'rel'             => '',
        )
    );
}
