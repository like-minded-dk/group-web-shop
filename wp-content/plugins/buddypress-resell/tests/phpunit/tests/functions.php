<?php

/**
 * @group functions
 */
class BP_Resell_Functions extends BP_UnitTestCase {

	public function test_resell_start_reselling() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		$resell = bp_resell_start_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u2,
		) );

		$this->assertTrue( $resell );
	}

	public function test_resell_start_reselling_already_exists() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		$f1 = bp_resell_start_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u2,
		) );

		$f2 = bp_resell_start_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u2,
		) );

		$this->assertFalse( $f2 );
	}

	/**
	 * Test two resell relationships with the same leader_id and reseller_id.
	 *
	 * But, set the resell_type for the second relationship to 'blogs'. This is to
	 * determine if there are any conflicts with setting the same leader and
	 * reseller IDs.
	 *
	 * @group blogs
	 */
	public function test_resell_start_reselling_user_blog_with_same_leader_reseller_id() {
		// add a user relationship
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$f1 = bp_resell_start_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u2,
		) );

		// now add a blog relationship
		// use the exact same leader_id and reseller_id, but set different type
		$f2 = bp_resell_start_reselling( array(
			// this is meant to be the blog ID
			// we pretend that a blog ID of $u1 exists
			'leader_id'   => $u1,

			// this is the same user ID as above
			'reseller_id' => $u2,

			// different resell type
			'resell_type' => 'blogs',
		) );

		$this->assertTrue( $f2 );
	}

	/**
	 * Check stop reselling function when resell ID doesn't exist.
	 *
	 * @group bp_resell_stop_reselling
	 */
	public function test_stop_reselling_when_resell_id_does_not_exist() {
		$f1 = bp_resell_stop_reselling( array(
			'leader_id'   => get_current_user_id(),
			'reseller_id' => 9999,
		) );


		$this->assertFalse( $f1 );
	}
}

