<?php
/**
 * BuddyPress Relations Classes.
 *
 * @package BuddyPress
 * @subpackage Relations
 * @since 1.0.0
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress Relations object.
 *
 * @since 1.0.0
 */
#[AllowDynamicProperties]
class BP_Engagements_Engagementship extends BP_Relations_Relationship {
	private static $comp = 'engagement';
    private static $receiver_id = 'engagement_user_id';
    private static $bp_cachekey = 'bp_engagements';
    private static $bp_cachekey_relation = 'bp_engagements_relationships';
    private static $bp_cachekey_user = 'bp_engagements_relationships_for_user';
    private static $bp_cachekey_request = 'bp_engagements_requests';
    private static $component = 'engagements';
    private static $component_request = 'engagement_request';
    private static $relationship_accepted = 'engagement_accepted';
    private static $filter_initiator_bs = 'engagements_relationship_initiator_user_id_before_save';
    private static $filter_receiver_bs = 'engagements_relationship_engagement_user_id_before_save';
    private static $filter_confirmed_bs = 'engagements_relationship_is_confirmed_before_save';
    private static $filter_limited_bs = 'engagements_relationship_is_limited_before_save';
    private static $filter_dated_bs = 'engagements_relationship_date_created_before_save';
    private static $action_bs = 'engagements_relationship_before_save';
    private static $action_as = 'engagements_relationship_after_save';

    private static $reverse_receiver_id = 'friend_user_id';
	
	public function __construct($comp) {
		parent::__construct('engagement');
		$this->comp = 'engagement';
		$this->receiver_id = 'engagement_user_id';
		$this->bp_cachekey = 'bp_engagements';
		$this->bp_cachekey_relation = 'bp_engagements_relationships';
		$this->bp_cachekey_user = 'bp_engagements_relationships_for_user';
		$this->bp_cachekey_request = 'bp_engagements_requests';
		$this->component = 'engagements';
		$this->component_request = 'engagement_request';
		$this->relationship_accepted = 'engagement_accepted';
		$this->filter_initiator_bs = 'engagements_relationship_initiator_user_id_before_save';
		$this->filter_receiver_bs = 'engagements_relationship_engagement_user_id_before_save';
		$this->filter_confirmed_bs = 'engagements_relationship_is_confirmed_before_save';
		$this->filter_limited_bs = 'engagements_relationship_is_limited_before_save';
		$this->filter_dated_bs = 'engagements_relationship_date_created_before_save';
		$this->action_bs = 'engagements_relationship_before_save';
		$this->action_as = 'engagements_relationship_after_save';

		$this->reverse_receiver_id = 'friend_user_id';
	}
}
	