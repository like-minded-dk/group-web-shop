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
	public $comp;
	public $engagement_user_id;

	public function __construct() {
		$this->comp = 'engagement';
		parent::__construct('engagement');
	}
}
	