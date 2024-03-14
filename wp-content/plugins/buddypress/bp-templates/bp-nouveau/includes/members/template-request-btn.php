<?php
function get_request_btn($comp, $parent_element, $button_element, $parent_class ) {
    if ($comp == 'friend') {
        $accept_key = 'accept_friendship';
        $reject_key = 'reject_friendship';
        $component = 'friends';
    } else {
        $accept_key = 'accept_engagementship';
        $reject_key = 'reject_engagementship';
        $component = 'engagements';
    }
    return array (
        $accept_key => array(
            'id'                => $accept_key,
            'position'          => 5,
            'component'         => $component,
            'must_be_logged_in' => true,
            'parent_element'    => $parent_element,
            'link_text'         => _x( 'Accept', 'button', 'buddypress' ),
            'parent_attr'       => array(
                'id'    => '',
                'class' => $parent_class ,
            ),
            'button_element'    => $button_element,
            'button_attr'       => array(
                'class'           => 'button accept',
                'rel'             => '',
            ),
        ), $reject_key => array(
            'id'                => $reject_key,
            'position'          => 15,
            'component'         => $component,
            'must_be_logged_in' => true,
            'parent_element'    => $parent_element,
            'link_text'         => _x( 'Reject', 'button', 'buddypress' ),
            'parent_attr'       => array(
                'id'    => '',
                'class' => $parent_class,
            ),
            'button_element'    => $button_element,
            'button_attr'       => array (
                'class'           => 'button reject',
                'rel'             => '',
            ),
        ),
    );
}
