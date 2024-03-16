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
	public static $comp = 'engagement';
	public static $component = 'engagements';
	public static $bp_cachekey_relation = 'bp_engagements_relationships';
	public static $receiver_id = 'engagement_user_id';

	public static $filter_initiator_bs = 'engagements_relationship_initiator_user_id_before_save';
	public static $filter_receiver_bs = 'engagements_relationship_engagement_user_id_before_save';
	public static $filter_confirmed_bs = 'engagements_relationship_is_confirmed_before_save';
	public static $filter_limited_bs = 'engagements_relationship_is_limited_before_save';
	public static $filter_dated_bs = 'engagements_relationship_date_created_before_save';
	public static $action_bs = 'engagements_relationship_before_save';
	public static $action_as = 'engagements_relationship_after_save';

	public static $bp_cachekey = 'bp_engagements';
	public static $bp_cachekey_user = 'bp_engagements_relationships_for_user';
	public static $bp_cachekey_request = 'bp_engagements_requests';
	public static $relationship_accepted = 'engagement_accepted';
	public static $reverse_receiver_id = 'friend_user_id';
	public static $component_request = 'engagement_request';

	public static $engagement_user_id = 'engagement_user_id';

	public function __construct() {
		parent::__construct('engagement');
	}
}
