<?php
/**
 * BuddyPress Engagements Classes.
 *
 * @package BuddyPress
 * @subpackage Engagements
 * @since 1.0.0
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress Engagements object.
 *
 * @since 1.0.0
 */
#[AllowDynamicProperties]
class BP_Engagements_Engagementship extends BP_Relations_Relationship {
	// both static and instanc used
	public static $comp = 'engagement';
	public static $component = 'engagements';
	public static $receiver_id = 'engagement_user_id';
	public static $reverse_receiver_id = 'friend_user_id';
	public static $bp_cachekey_relation = 'bp_engagements_relationships';

	// only used in static method
	public static $bp_cachekey = 'bp_engagements';
	public static $bp_cachekey_user = 'bp_engagements_relationships_for_user';

	// not used yet
	// private static $engagement_user_id = 'engagement_user_id';

	public function __construct() {
		parent::__construct('engagement');
	}
}
