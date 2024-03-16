<?php
/**
 * BuddyPress Friends Classes.
 *
 * @package BuddyPress
 * @subpackage Friends
 * @since 1.0.0
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress Friend object.
 *
 * @since 1.0.0
 */
#[AllowDynamicProperties]
class BP_Friends_Friendship extends BP_Relations_Relationship {
	private static $comp = 'friend';
    private static $receiver_id = 'friend_user_id';
    private static $bp_cachekey = 'bp_friends';
    private static $bp_cachekey_relation = 'bp_friends_relationships';
    private static $bp_cachekey_user = 'bp_friends_relationships_for_user';
    private static $bp_cachekey_request = 'bp_friends_requests';
    private static $component = 'friends';
    private static $component_request = 'friend_request';
    private static $relationship_accepted = 'friend_accepted';
    private static $filter_initiator_bs = 'friends_relationship_initiator_user_id_before_save';
    private static $filter_receiver_bs = 'friends_relationship_friend_user_id_before_save';
    private static $filter_confirmed_bs = 'friends_relationship_is_confirmed_before_save';
    private static $filter_limited_bs = 'friends_relationship_is_limited_before_save';
    private static $filter_dated_bs = 'friends_relationship_date_created_before_save';
    private static $action_bs = 'friends_relationship_before_save';
    private static $action_as = 'friends_relationship_after_save';
	
    private static $reverse_receiver_id = 'engagement_user_id';

	public function __construct($comp) {
		parent::__construct('friend');
		$this->comp = 'friend';
		$this->receiver_id = 'friend_user_id';
		$this->bp_cachekey = 'bp_friends';
		$this->bp_cachekey_relation = 'bp_friends_relationships';
		$this->bp_cachekey_user = 'bp_friends_relationships_for_user';
		$this->bp_cachekey_request = 'bp_friends_requests';
		$this->component = 'friends';
		$this->component_request = 'friend_request';
		$this->relationship_accepted = 'friend_accepted';
		$this->filter_initiator_bs = 'friends_relationship_initiator_user_id_before_save';
		$this->filter_receiver_bs = 'friends_relationship_friend_user_id_before_save';
		$this->filter_confirmed_bs = 'friends_relationship_is_confirmed_before_save';
		$this->filter_limited_bs = 'friends_relationship_is_limited_before_save';
		$this->filter_dated_bs = 'friends_relationship_date_created_before_save';
		$this->action_bs = 'friends_relationship_before_save';
		$this->action_as = 'friends_relationship_after_save';

		$this->reverse_receiver_id = 'engagement_user_id';
	}
}
