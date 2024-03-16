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
	// both static and instanc used
	public static $comp = 'friend';
	public static $component = 'friends';
	public static $receiver_id = 'friend_user_id';
	public static $reverse_receiver_id = 'engagement_user_id';
	public static $bp_cachekey_relation = 'bp_friends_relationships';

	// only used in static method
	public static $bp_cachekey = 'bp_friends';
	public static $bp_cachekey_user = 'bp_friends_relationships_for_user';

	// not used yet
	// private static $friend_user_id = 'friend_user_id';

	public function __construct() {
		parent::__construct('friend');
	}
}
