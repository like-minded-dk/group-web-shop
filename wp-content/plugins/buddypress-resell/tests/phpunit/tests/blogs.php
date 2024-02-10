<?php

/**
 * @group blogs
 */
class BP_Resell_Test_Blogs extends BP_UnitTestCase {

	/**
	 * @group delete
	 */
	public function test_resell_and_delete_blog() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped();
		}

		// create user and blog
		$u = $this->factory->user->create();
		$b = $this->factory->blog->create( array(
			'title' => 'The Foo Bar Blog',
			'user_id' => $u,
		) );

		// make blog creator resell own blog
		$f = bp_resell_start_reselling( array(
			'leader_id'   => $b,
			'reseller_id' => $u,
			'resell_type' => 'blogs',
		) );

		// assert that resell relationship worked
		$this->assertTrue( $f );

		// prime cache
		new BP_Resell( $b, $u, 'blogs' );
		bp_resell_get_the_reselling_count( array(
			'user_id' => $u,
			'resell_type' => 'blogs',
		) );
		bp_resell_get_the_resellers_count( array(
			'object_id'   => $b,
			'resell_type' => 'blogs'
		) );

		// now delete blog
		wpmu_delete_blog( $b );

		// check if cache was deleted
		$this->assertEmpty( wp_cache_get( "{$b}:{$u}:blogs", 'bp_resell_data' ) );
		$this->assertEmpty( wp_cache_get( $u, 'bp_resell_user_blogs_reselling_count' ) );
		$this->assertEmpty( wp_cache_get( $b, 'bp_resell_blogs_resellers_count' ) );
	}

	/**
	 * @group groupblog
	 */
	public function test_resell_blog_and_groupblog() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped();
		}

		// save the current user and override logged-in user
		$old_user = get_current_user_id();
		$u = $this->factory->user->create();
		$this->set_current_user( $u );

		// create some blogs
		$b = $this->factory->blog->create( array(
			'title' => 'Groupblog',
			'user_id' => $u,
		) );
		$b2 = $this->factory->blog->create( array(
			'title' => 'Test blog 1',
			'user_id' => $u,
		) );
		$b3 = $this->factory->blog->create( array(
			'title' => 'Test blog 2',
			'user_id' => $u,
		) );

		// create a group and connect a blog
		$g = $this->factory->group->create( array(
			'creator_id' => $u,
		) );
		groups_update_groupmeta( $g, 'groupblog_blog_id', $b );

		// resell the groupblog
		$f = bp_resell_start_reselling( array(
			'leader_id'   => $b,
			'reseller_id' => $u,
			'resell_type' => 'blogs',
		) );

		// resell a regular blog
		$f2 = bp_resell_start_reselling( array(
			'leader_id'   => $b2,
			'reseller_id' => $u,
			'resell_type' => 'blogs',
		) );

		// add some activity items
		$a = $this->factory->activity->create( array(
			'component' => buddypress()->groups->id,
			'type' => 'new_groupblog_post',
			'user_id' => $u,
			'item_id' => $g,
			'secondary_item_id' => 1,
		) );
		$a2 = $this->factory->activity->create( array(
			'component' => buddypress()->blogs->id,
			'type' => 'new_blog_post',
			'user_id' => $u,
			'item_id' => $b3,
			'secondary_item_id' => 1,
		) );
		$a3 = $this->factory->activity->create( array(
			'component' => buddypress()->blogs->id,
			'type' => 'new_blog_post',
			'user_id' => $u,
			'item_id' => $b2,
			'secondary_item_id' => 1,
		) );

		// fake that we're on a user's "Activity > Reselled Sites" page
		add_filter( 'bp_ajax_querystring', array( $this, 'add_activity_scope_filter' ) );

		// fake that BP groupblog is installed so groupblog filter will kick in
		if ( ! function_exists( 'bp_groupblog_init' ) ) {
			function bp_groupblog_init() {}
		}

		// run the activity loop
		global $activities_template;
		bp_has_activities( bp_ajax_querystring( 'activity' ) );

		// grab the activity IDs from the loop
		$ids = wp_list_pluck( $activities_template->activities, 'id' );

		// assert!
		$this->assertEquals( array( $a, $a3 ), $ids );

		// reset everything
		$activities_template = null;
		$this->set_current_user( $old_user );
		remove_filter( 'bp_ajax_querystring', array( $this, 'add_activity_scope_filter' ) );
	}

	/**
	 * Filter to force activity loop scope to "resellblogs".
	 */
	public function add_activity_scope_filter( $qs ) {
		return 'scope=resellblogs';
	}
}
