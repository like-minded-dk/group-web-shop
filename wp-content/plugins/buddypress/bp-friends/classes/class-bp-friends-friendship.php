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
	public static $comp = 'friend';
	public static $component = 'friends';
	public static $bp_cachekey_relation = 'bp_friends_relationships';
	public static $receiver_id = 'friend_user_id';

	public static $filter_initiator_bs = 'friends_relationship_initiator_user_id_before_save';
	public static $filter_receiver_bs = 'friends_relationship_friend_user_id_before_save';
	public static $filter_confirmed_bs = 'friends_relationship_is_confirmed_before_save';
	public static $filter_limited_bs = 'friends_relationship_is_limited_before_save';
	public static $filter_dated_bs = 'friends_relationship_date_created_before_save';
	public static $action_bs = 'friends_relationship_before_save';
	public static $action_as = 'friends_relationship_after_save';

	public static $bp_cachekey = 'bp_friends';
	public static $bp_cachekey_user = 'bp_friends_relationships_for_user';
	public static $bp_cachekey_request = 'bp_friends_requests';
	public static $relationship_accepted = 'friend_accepted';
	public static $reverse_receiver_id = 'engagement_user_id';
	public static $component_request = 'friend_request';

	public static $friend_user_id = 'friend_user_id';

	public function __construct() {
		parent::__construct('friend');
	}
}
