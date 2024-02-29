<?php
/**
 * @group core
 */
class BP_Resell_Test_Core_Class extends BP_UnitTestCase {
	/**
	 * @group date_query
	 */
	public function test_date_query() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();
		$u4 = $this->factory->user->create();

		// resell all users at different dates
		bp_resell_start_reselling( array(
			'leader_id'     => $u2,
			'reseller_id'   => $u1,
		) );
		bp_resell_start_reselling( array(
			'leader_id'     => $u3,
			'reseller_id'   => $u1,
			'date_recorded' => '2001-01-01 12:00'
		) );
		bp_resell_start_reselling( array(
			'leader_id'     => $u4,
			'reseller_id'   => $u1,
			'date_recorded' => '2005-01-01 12:00'
		) );

		// 'date_query' before test
		$query = BP_Resell::get_reselling( $u1, '', array(
			'date_query' => array( array(
				'before' => array(
					'year'  => 2004,
					'month' => 1,
					'day'   => 1,
				),
			) )
		) );
		$this->assertEquals( array( $u3 ), $query );

		// 'date_query' range test
		$query = BP_Resell::get_reselling( $u1, '', array(
			'date_query' => array( array(
				'after'  => 'January 2nd, 2001',
				'before' => array(
					'year'  => 2013,
					'month' => 1,
					'day'   => 1,
				),
				'inclusive' => true,
			) )
		) );
		$this->assertEquals( array( $u4 ), $query );

		// 'date_query' after and relative test
		$query = BP_Resell::get_reselling( $u1, '', array(
			'date_query' => array( array(
 				'after' => '1 day ago'
			) )
		) );
		$this->assertEquals( array( $u2 ), $query );
	}

	/**
	 * @group null
	 */
	public function test_null_value_for_leader_id_should_return_no_results() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		bp_resell_start_reselling(
			array(
				'leader_id'   => $u2,
				'reseller_id' => $u1,
			)
		);

		$query = BP_Resell::get_resellers( NULL );

		$this->assertEmpty( $query );
	}

	/**
	 * @group null
	 */
	public function test_null_value_for_reseller_id_should_return_no_results() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		bp_resell_start_reselling(
			array(
				'leader_id'   => $u2,
				'reseller_id' => $u1,
			)
		);

		$query = BP_Resell::get_reselling( NULL );

		$this->assertEmpty( $query );
	}
}
