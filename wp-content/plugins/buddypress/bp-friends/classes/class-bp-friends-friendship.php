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
	public $comp;
	public $friend_user_id;

	public function __construct() {
		$this->comp = 'friend';
		parent::__construct('friend');
	}

}
