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
	public static $bp_cachekey_relation = 'bp_friends_relationships';

	// only used in static method
	public static $bp_cachekey = 'bp_friends';
	public static $bp_cachekey_user = 'bp_friends_relationships_for_user';

	// not used yet
	// private static $receiver_user_id = 'receiver_user_id';

	public function __construct($id = null, $is_request = false, $populate_relation_details = true ) {
		parent::__construct('friend', $id, $is_request, $populate_relation_details);
	}
}
