<?php

/**
 * @group cache
 */
class BP_Resell_Test_Cache extends BP_UnitTestCase {

	/**
	 * @group bp_resell_total_resell_counts
	 */
	public function test_bp_resell_total_resell_counts_cache() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		// create a resell relationship
		bp_resell_start_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u2,
		) );

		// stop_resell
		bp_resell_stop_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u2,
		) );

		// make sure cache is invalidated
		$this->assertEmpty( wp_cache_get( $u1, 'bp_resell_user_resellers_count' ) );
		$this->assertEmpty( wp_cache_get( $u2, 'bp_resell_user_reselling_count' ) );

		// get counts and assert
		$u1_counts = bp_resell_total_resell_counts( array(
			'user_id' => $u1,
		) );
		$this->assertSame( 0, $u1_counts['resellers'] );

		$u2_counts = bp_resell_total_resell_counts( array(
			'user_id' => $u2,
		) );
		$this->assertSame( 0, $u2_counts['reselling'] );
	}

	/**
	 * @group bp_resell_data
	 */
	public function test_bp_resell_data() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		$args = array(
			'leader_id'   => $u1,
			'reseller_id' => $u2,
		);

		// create a resell relationship
		bp_resell_start_reselling( $args );

		// check if user is reselling - this should generate cache
		bp_resell_is_reselling( $args );

		// assert that cache is there
		$key = "{$u1}:{$u2}:";
		$cache = wp_cache_get( $key, 'bp_resell_data' );
		$this->assertTrue( ! empty( $cache->id ), (bool) $cache->id );

		// delete the resell relationship
		bp_resell_stop_reselling( $args );

		// assert
		$this->assertEmpty( wp_cache_get( $key, 'bp_resell_data' ) );
	}

	/**
	 * @group bp_resell_get_reselling
	 */
	public function test_bp_resell_get_reselling() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();
		$u4 = $this->factory->user->create();

		// let user 1 resell everyone
		bp_resell_start_reselling( array(
			'leader_id'   => $u2,
			'reseller_id' => $u1,
		) );
		bp_resell_start_reselling( array(
			'leader_id'   => $u3,
			'reseller_id' => $u1,
		) );
		bp_resell_start_reselling( array(
			'leader_id'   => $u4,
			'reseller_id' => $u1,
		) );

		// get reselling for user 1
		bp_resell_get_reselling( array( 'user_id' => $u1 ) );

		// assert
		$this->assertEqualSets( array( $u2, $u3, $u4 ), wp_cache_get( $u1, 'bp_resell_user_reselling_query' ) );

		// stop reselling one user
		bp_resell_stop_reselling( array(
			'leader_id'   => $u4,
			'reseller_id' => $u1,
		) );

		// make sure cache is invalidated
		$this->assertEmpty( wp_cache_get( $u1, 'bp_resell_user_reselling_query' ) );
	}

	/**
	 * @group bp_resell_get_reselling
	 */
	public function test_bp_resell_get_reselling_no_cache() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();
		$u4 = $this->factory->user->create();

		// let user 1 resell everyone
		bp_resell_start_reselling( array(
			'leader_id'   => $u2,
			'reseller_id' => $u1,
		) );
		bp_resell_start_reselling( array(
			'leader_id'   => $u3,
			'reseller_id' => $u1,
		) );
		bp_resell_start_reselling( array(
			'leader_id'   => $u4,
			'reseller_id' => $u1,
		) );

		// get reselling for user 1
		bp_resell_get_reselling( array(
			'user_id' => $u1,

			// add query args
			'query_args' => array(
				'orderby' => 'id',
				'order'   => 'ASC',
			)
		) );

		// we do not cache reselling calls with query args at the moment
		$this->assertEmpty( wp_cache_get( $u1, 'bp_resell_user_reselling_query' ) );
	}

	/**
	 * @group bp_resell_get_resellers
	 */
	public function test_bp_resell_get_resellers() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();
		$u4 = $this->factory->user->create();

		// let user 1 be reselled by everyone
		bp_resell_start_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u2,
		) );
		bp_resell_start_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u3,
		) );
		bp_resell_start_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u4,
		) );

		// get resellers for user 1
		bp_resell_get_resellers( array( 'user_id' => $u1 ) );

		// assert
		$this->assertEqualSets( array( $u2, $u3, $u4 ), wp_cache_get( $u1, 'bp_resell_user_resellers_query' ) );

		// one user stops reselling user 1
		bp_resell_stop_reselling( array(
			'leader_id'   => $u1,
			'reseller_id' => $u4,
		) );

		// make sure cache is invalidated
		$this->assertEmpty( wp_cache_get( $u1, 'bp_resell_user_resellers_query' ) );
	}
}
