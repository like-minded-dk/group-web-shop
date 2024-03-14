<?php
/**
 * Members template tags
 *
 * @since 3.0.0
 * @version 12.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
require 'template-buttons.php';
// todo: error_log('add_core_funciton')
function bp_is_user_templates() {
	return (bool) ( bp_is_user() && bp_is_templates_component() );
}

// todo: error_log('add_core_funciton')
function bp_is_templates_component() {
	return (bool) bp_is_current_component( 'engagements' );
}

/**
 * Template tag to wrap all Legacy actions that was used
 * before the members directory content
 *
 * @since 3.0.0
 */
function bp_nouveau_before_members_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0
	 */
	do_action( 'bp_before_directory_members_page' );

	/**
	 * Fires before the display of the members.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_directory_members' );

	/**
	 * Fires before the display of the members content.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_directory_members_content' );

	/**
	 * Fires before the display of the members list tabs.
	 *
	 * @since 1.8.0
	 */
	do_action( 'bp_before_directory_members_tabs' );
}

/**
 * Template tag to wrap all Legacy actions that was used
 * after the members directory content
 *
 * @since 3.0.0
 */
function bp_nouveau_after_members_directory_content() {
	/**
	 * Fires and displays the members content.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_directory_members_content' );

	/**
	 * Fires after the display of the members content.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_after_directory_members_content' );

	/**
	 * Fires after the display of the members.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_after_directory_members' );
}

/**
 * Fire specific hooks into the single members templates
 *
 * @since 3.0.0
 *
 * @param string $when   'before' or 'after'
 * @param string $suffix Use it to add terms at the end of the hook name
 */
function bp_nouveau_member_hook( $when = '', $suffix = '' ) {
	$hook = array( 'bp' );

	if ( $when ) {
		$hook[] = $when;
	}

	// It's a member hook
	$hook[] = 'member';

	if ( $suffix ) {
		$hook[] = $suffix;
	}

	bp_nouveau_hook( $hook );
}

/**
 * Template tag to wrap the notification settings hook
 *
 * @since 3.0.0
 */
function bp_nouveau_member_email_notice_settings() {
	/**
	 * Fires at the top of the member template notification settings form.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_notification_settings' );
}

/**
 * Output the action buttons for the displayed user profile
 *
 * @since 3.0.0
 *
 * @param array $args See bp_nouveau_wrapper() for the description of parameters.
 */
function bp_nouveau_member_header_buttons( $args = array() ) {
	$bp_nouveau = bp_nouveau();

	if ( bp_is_user() ) {
		$args['type'] = 'profile';
	} else {
		$args['type'] = 'header';// we have no real need for this 'type' on header actions
	}

	$output = join( ' ', bp_nouveau_get_members_buttons( $args ) );

	/**
	 * On the member's header we need to reset the group button's global
	 * once displayed as the friends component will use the member's loop
	 */
	if ( ! empty( $bp_nouveau->members->member_buttons ) ) {
		unset( $bp_nouveau->members->member_buttons );
	}

	ob_start();
	/**
	 * Fires in the member header actions section.
	 *
	 * @since 1.2.6
	 */
	do_action( 'bp_member_header_actions' );
	$output .= ob_get_clean();

	if ( ! $output ) {
		return;
	}

	if ( ! $args ) {
		$args = array(
			'id'      => 'item-buttons',
			'classes' => false,
		);
	}

	bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

/**
 * Output the action buttons in member loops
 *
 * @since 3.0.0
 *
 * @param array $args See bp_nouveau_wrapper() for the description of parameters.
 */
function bp_nouveau_members_loop_buttons( $args = array() ) {
	if ( empty( $GLOBALS['members_template'] ) ) {
		return;
	}

	$args['type'] = 'loop';
	$action       = 'bp_directory_members_actions';

	// Specific case for group members.
	if ( bp_is_active( 'groups' ) && bp_is_group_members() ) {
		$args['type'] = 'group_member';
		$action       = 'bp_group_members_list_item_action';

	} elseif ( bp_is_active( 'friends' ) && bp_is_user_friend_requests() ) {
		$args['type'] = 'friendship_request';
		$action       = 'bp_friend_requests_item_action';
	} elseif ( bp_is_active( 'engagements' ) && bp_is_user_engagement_requests() ) {
		// } elseif ( bp_is_active( 'engagements' ) && bp_is_user_engagement_requests() ) {
		$args['type'] = 'engagementship_request';
		$action       = 'bp_engagement_requests_item_action';
	}
	
	$output = join( ' ', bp_nouveau_get_members_buttons( $args ) );


	ob_start();
	/**
	 * Fires inside the members action HTML markup to display actions.
	 *
	 * @since 1.1.0
	 */
	do_action( $action );

	$output .= ob_get_clean();

	if ( ! $output ) {
		return;
	}

	bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

	/**
	 * Get the action buttons for the displayed user profile
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	function bp_nouveau_get_members_buttons( $args ) {
		error_log(json_encode('>>>>>> bp_nouveau_get_members_buttons'));
		$buttons = array();
		$type = ( ! empty( $args['type'] ) ) ? $args['type'] : '';

		// @todo Not really sure why BP Legacy needed to do this...
		if ( 'profile' === $type && is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return $buttons;
		}

		$user_id = bp_displayed_user_id();

		if ( 'loop' === $type || 'friendship_request' === $type ) {
			$user_id = bp_get_member_user_id();
		} elseif ( 'group_member' === $type ) {
			$user_id = bp_get_group_member_id();
		}

		if ( ! $user_id ) {
			return $buttons;
		}

		/*
		 * If the 'container' is set to 'ul'
		 * set a var $parent_element to li
		 * otherwise simply pass any value found in args
		 * or set var false.
		 */
		$parent_element = false;

		if ( ! empty( $args['container'] ) && 'ul' === $args['container']  ) {
			$parent_element = 'li';
		} elseif ( ! empty( $args['parent_element'] ) ) {
			$parent_element = $args['parent_element'];
		}

		if ( ! empty( $args['button_element'] ) ) {
			$button_element = $args['button_element'] ;
		} else {

			$button_element = 'button';
		}

		// If we pass through parent classes add them to $button array
		$parent_class = '';
		if ( ! empty( $args['parent_attr']['class'] ) ) {
			$parent_class = $args['parent_attr']['class'];
		}

		add_relation_button('engagement', $buttons, $user_id, $type, $parent_class, $button_element, $parent_element);
		add_relation_button('friend', $buttons, $user_id, $type, $parent_class, $button_element, $parent_element);
		add_profile_button($buttons, $type, $parent_class, $parent_element);

		/**
		 * Filter to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 3.0.0
		 * @since 9.0.0 Adds the `$args` parameter to the filter.
		 *
		 * @param array  $buttons The list of buttons.
		 * @param int    $user_id The displayed user ID.
		 * @param string $type    Whether we're displaying a members loop or a user's page
		 * @param array  $args    Button arguments.
		 */
		// note  bp_nouveau_get_members_buttons regester member buttons , there for registed all  other action , when action list include member
		$buttons_group = apply_filters( 'bp_nouveau_get_members_buttons', $buttons, $user_id, $type, $args );
		if ( ! $buttons_group ) {
			return array();
		}

		// It's the first entry of the loop, so build the Group and sort it
		if ( ! isset( bp_nouveau()->members->member_buttons ) || ! is_a( bp_nouveau()->members->member_buttons, 'BP_Buttons_Group' ) ) {
			$sort = true;
			bp_nouveau()->members->member_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first entry, the order is set, we simply need to update the Buttons Group
		} else {
			$sort = false;
			bp_nouveau()->members->member_buttons->update( $buttons_group );
		}

		$return = bp_nouveau()->members->member_buttons->get( $sort );

		if ( ! $return ) {
			return array();
		}

		/**
		 * Leave a chance to adjust the $return
		 *
		 * @since 3.0.0
		 *
		 * @param array  $return  The list of buttons ordered.
		 * @param int    $user_id The displayed user ID.
		 * @param string $type    Whether we're displaying a members loop or a user's page
		 */
		do_action_ref_array( 'bp_nouveau_return_members_buttons', array( &$return, $user_id, $type ) );
		error_log(json_encode('___________>>>> 2 end__ bp_nouveau_get_members_buttons'));
		return $return;
	}

/**
 * Does the member has meta.
 *
 * @since 3.0.0
 *
 * @return bool True if the member has meta. False otherwise.
 */
function bp_nouveau_member_has_meta() {
	return (bool) bp_nouveau_get_member_meta();
}

/**
 * Display the member meta.
 *
 * @since 3.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_member_meta() {
	echo join( "\n", bp_nouveau_get_member_meta() );
}

	/**
	 * Get the member meta.
	 *
	 * @since 3.0.0
	 *
	 * @return array The member meta.
	 */
	function bp_nouveau_get_member_meta() {
		$meta    = array();
		$is_loop = false;

		if ( ! empty( $GLOBALS['members_template']->member ) ) {
			$member  = $GLOBALS['members_template']->member;
			$is_loop = true;
		} else {
			$member = bp_get_displayed_user();
		}

		if ( empty( $member->id ) ) {
			return $meta;
		}

		if ( empty( $member->template_meta ) ) {
			// It's a single user's header
			if ( ! $is_loop ) {
				$meta['last_activity'] = sprintf(
					'<span class="activity">%s</span>',
					esc_html( bp_get_last_activity( bp_displayed_user_id() ) )
				);

			// We're in the members loop
			} else {
				$meta = array(
					'last_activity' => sprintf( '%s', bp_get_member_last_active() ),
				);
			}

			// Make sure to include hooked meta.
			$extra_meta = bp_nouveau_get_hooked_member_meta();

			if ( $extra_meta ) {
				$meta['extra'] = $extra_meta;
			}

			/**
			 * Filter to add/remove Member meta.
			 *
			 * @since 3.0.0
			 *
			 * @param array  $meta    The list of meta to output.
			 * @param object $member  The member object
			 * @param bool   $is_loop True if in the members loop. False otherwise.
			 */
			$member->template_meta = apply_filters( 'bp_nouveau_get_member_meta', $meta, $member, $is_loop );
		}

		return $member->template_meta;
	}

/**
 * Check if some extra content needs to be displayed into the members directory.
 *
 * @since 6.0.0
 *
 * @return bool True if some extra content needs to be displayed into the members directory.
 *              False otherwise.
 */
function bp_nouveau_member_has_extra_content() {
	/**
	 * Filter here to display the extra content not only into the Members directory.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $value True if on the Members directory page.
	 *                    False otherwise.
	 */
	$members_directory_only = (bool) apply_filters( 'bp_nouveau_member_extra_content_in_members_directory', bp_is_members_directory() );

	// Check if some extra content needs to be included into the item of the loop.
	$has_action = (bool) has_action( 'bp_directory_members_item' );

	return $members_directory_only && $has_action;
}

/**
 * Displays extra content for each item of a members loop.
 *
 * @since 6.0.0
 */
function bp_nouveau_member_extra_content() {
	/**
	 * Fires inside the display of a members loop member item.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_directory_members_item' );
}

/**
 * Load the appropriate content for the single member pages
 *
 * @since 3.0.0
 */
function bp_nouveau_member_template_part() {
	/**
	 * Fires before the display of member body content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_member_body' );

	if ( bp_is_user_front() ) {
		bp_displayed_user_front_template_part();

	} else {
		$template = 'plugins';

		if ( bp_is_user_activity() ) {
			$template = 'activity';
		} elseif ( bp_is_user_blogs() ) {
			$template = 'blogs';
		} elseif ( bp_is_user_friends() ) {
			$template = 'friends';
		} elseif ( bp_is_user_templates() ) {
			$template = 'engagements';
		} elseif ( bp_is_user_groups() ) {
			$template = 'groups';
		} elseif ( bp_is_user_messages() ) {
			$template = 'messages';
		} elseif ( bp_is_user_profile() ) {
			$template = 'profile';
		} elseif ( bp_is_user_notifications() ) {
			$template = 'notifications';
		} elseif ( bp_is_user_members_invitations() ) {
			$template = 'invitations';
		} elseif ( bp_is_user_settings() ) {
			$template = 'settings';
		}

		bp_nouveau_member_get_template_part( $template );
	}

	/**
	 * Fires after the display of member body content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_member_body' );
}

/**
 * Use the appropriate Member header and enjoy a template hierarchy
 *
 * @since 3.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_member_header_template_part() {
	$template = 'member-header';

	if ( bp_displayed_user_use_cover_image_header() ) {
		$template = 'cover-image-header';
	}

	/**
	 * Fires before the display of a member's header.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_member_header' );

	// Get the template part for the header
	bp_nouveau_member_get_template_part( $template );

	/**
	 * Fires after the display of a member's header.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_member_header' );

	bp_nouveau_template_notices();
}

/**
 * Get a link to set the Member's default front page and directly
 * reach the Customizer section where it's possible to do it.
 *
 * @since 3.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_members_get_customizer_option_link() {
	return bp_nouveau_get_customizer_link(
		array(
			'object'    => 'user',
			'autofocus' => 'bp_nouveau_user_front_page',
			'text'      => __( 'Members default front page', 'buddypress' ),
		)
	);
}

/**
 * Get a link to set the Member's front page widgets and directly
 * reach the Customizer section where it's possible to do it.
 *
 * @since 3.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_members_get_customizer_widgets_link() {
	return bp_nouveau_get_customizer_link(
		array(
			'object'    => 'user',
			'autofocus' => 'sidebar-widgets-sidebar-buddypress-members',
			'text'      => __( '(BuddyPress) Widgets', 'buddypress' ),
		)
	);
}

/**
 * Display the Member description making sure linefeeds are taking in account
 *
 * @since 3.0.0
 *
 * @param int $user_id Optional.
 *
 * @return string HTML output.
 */
function bp_nouveau_member_description( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = bp_loggedin_user_id();

		if ( bp_displayed_user_id() ) {
			$user_id = bp_displayed_user_id();
		}
	}

	// @todo This hack is too brittle.
	add_filter( 'the_author_description', 'make_clickable', 9 );
	add_filter( 'the_author_description', 'wpautop' );
	add_filter( 'the_author_description', 'wptexturize' );
	add_filter( 'the_author_description', 'convert_smilies' );
	add_filter( 'the_author_description', 'convert_chars' );
	add_filter( 'the_author_description', 'stripslashes' );

	the_author_meta( 'description', $user_id );

	remove_filter( 'the_author_description', 'make_clickable', 9 );
	remove_filter( 'the_author_description', 'wpautop' );
	remove_filter( 'the_author_description', 'wptexturize' );
	remove_filter( 'the_author_description', 'convert_smilies' );
	remove_filter( 'the_author_description', 'convert_chars' );
	remove_filter( 'the_author_description', 'stripslashes' );
}

/**
 * Display the Edit profile link (temporary).
 *
 * @since 3.0.0
 *
 * @todo replace with Ajax feature
 *
 * @return string HTML Output
 */
function bp_nouveau_member_description_edit_link() {
	echo bp_nouveau_member_get_description_edit_link();
}

	/**
	 * Get the Edit profile link (temporary)
	 * @todo  replace with Ajax featur
	 *
	 * @since 3.0.0
	 *
	 * @return string HTML Output
	 */
	function bp_nouveau_member_get_description_edit_link() {
		remove_filter( 'edit_profile_url', 'bp_members_edit_profile_url', 10, 3 );

		if ( is_multisite() && ! current_user_can( 'read' ) ) {
			$link = get_dashboard_url( bp_displayed_user_id(), 'profile.php' );
		} else {
			$link = get_edit_profile_url( bp_displayed_user_id() );
		}

		add_filter( 'edit_profile_url', 'bp_members_edit_profile_url', 10, 3 );
		$link .= '#description';

		return sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link ), esc_html__( 'Edit your bio', 'buddypress' ) );
	}


/** WP Profile tags **********************************************************/

/**
 * Template tag to wrap all Legacy actions that was used
 * before and after the WP User's Profile.
 *
 * @since 3.0.0
 */
function bp_nouveau_wp_profile_hooks( $type = 'before' ) {
	if ( 'before' === $type ) {
		/**
		 * Fires before the display of member profile loop content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_before_profile_loop_content' );

		/**
		 * Fires before the display of member profile field content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_before_profile_field_content' );
	} else {
		/**
		 * Fires after the display of member profile field content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_profile_field_content' );

		/**
		 * Fires and displays the profile field buttons.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_profile_field_buttons' );

		/**
		 * Fires after the display of member profile loop content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_after_profile_loop_content' );
	}
}

/**
 * Does the displayed user has WP profile fields?
 *
 * @since 3.0.0
 *
 * @return bool True if user has profile fields. False otherwise.
 */
function bp_nouveau_has_wp_profile_fields() {
	$user_id = bp_displayed_user_id();
	if ( ! $user_id ) {
		return false;
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return false;
	}

	$fields              = bp_nouveau_get_wp_profile_fields( $user );
	$user_profile_fields = array();

	foreach ( $fields as $key => $field ) {
		if ( empty( $user->{$key} ) ) {
			continue;
		}

		$user_profile_fields[] = (object) array(
			'id'    => 'wp_' . $key,
			'label' => $field,
			'data'  => $user->{$key},
		);
	}

	if ( ! $user_profile_fields ) {
		return false;
	}

	// Keep it for a later use.
	$bp_nouveau                            = bp_nouveau();
	$bp_nouveau->members->wp_profile       = $user_profile_fields;
	$bp_nouveau->members->wp_profile_index = 0;

	return true;
}

/**
 * Check if there are still profile fields to output.
 *
 * @since 3.0.0
 *
 * @return bool True if the profile field exists. False otherwise.
 */
function bp_nouveau_wp_profile_fields() {
	$bp_nouveau = bp_nouveau();

	if ( isset( $bp_nouveau->members->wp_profile[ $bp_nouveau->members->wp_profile_index ] ) ) {
		return true;
	}

	$bp_nouveau->members->wp_profile_index = 0;
	unset( $bp_nouveau->members->wp_profile_current );

	return false;
}

/**
 * Set the current profile field and iterate into the loop.
 *
 * @since 3.0.0
 */
function bp_nouveau_wp_profile_field() {
	$bp_nouveau = bp_nouveau();

	$bp_nouveau->members->wp_profile_current = $bp_nouveau->members->wp_profile[ $bp_nouveau->members->wp_profile_index ];
	$bp_nouveau->members->wp_profile_index  += 1;
}

/**
 * Output the WP profile field ID.
 *
 * @since 3.0.0
 */
function bp_nouveau_wp_profile_field_id() {
	echo esc_attr( bp_nouveau_get_wp_profile_field_id() );
}
	/**
	 * Get the WP profile field ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int the profile field ID.
	 */
	function bp_nouveau_get_wp_profile_field_id() {
		$field = bp_nouveau()->members->wp_profile_current;

		/**
		 * Filters the WP profile field ID used for BuddyPress Nouveau.
		 *
		 * @since 3.0.0
		 *
		 * @param string $id Field ID.
		 */
		return apply_filters( 'bp_nouveau_get_wp_profile_field_id', $field->id );
	}

/**
 * Output the WP profile field label.
 *
 * @since 3.0.0
 */
function bp_nouveau_wp_profile_field_label() {
	echo esc_html( bp_nouveau_get_wp_profile_field_label() );
}

	/**
	 * Get the WP profile label.
	 *
	 * @since 3.0.0
	 *
	 * @return string the profile field label.
	 */
	function bp_nouveau_get_wp_profile_field_label() {
		$field = bp_nouveau()->members->wp_profile_current;

		/**
		 * Filters the WP profile field label used for BuddyPress Nouveau.
		 *
		 * @since 3.0.0
		 *
		 * @param string $label Field label.
		 */
		return apply_filters( 'bp_nouveau_get_wp_profile_field_label', $field->label );
	}

/**
 * Output the WP profile field data.
 *
 * @since 3.0.0
 */
function bp_nouveau_wp_profile_field_data() {
	$data = bp_nouveau_get_wp_profile_field_data();
	$data = make_clickable( $data );

	echo wp_kses(
		/**
		 * Filters a WP profile field value.
		 *
		 * @since 3.0.0
		 *
		 * @param string $data The profile field data value.
		 */
		apply_filters( 'bp_nouveau_get_wp_profile_field_data', $data ),
		array(
			'a' => array(
				'href' => true,
				'rel'  => true,
			),
		)
	);
}

	/**
	 * Get the WP profile field data.
	 *
	 * @since 3.0.0
	 *
	 * @return string the profile field data.
	 */
	function bp_nouveau_get_wp_profile_field_data() {
		$field = bp_nouveau()->members->wp_profile_current;
		return $field->data;
	}

/**
 * Outputs the Invitations bulk actions dropdown list.
 *
 * @since 8.0.0
 */
function bp_nouveau_invitations_bulk_management_dropdown() {
	?>
	<div class="select-wrap">

		<label class="bp-screen-reader-text" for="invitation-select">
			<?php
			esc_html_e( 'Select Bulk Action', 'buddypress' );
			?>
		</label>

		<select name="invitation_bulk_action" id="invitation-select">
			<option value="" selected="selected"><?php esc_html_e( 'Bulk Actions', 'buddypress' ); ?></option>
			<option value="resend"><?php echo esc_html_x( 'Resend', 'button', 'buddypress' ); ?></option>
			<option value="cancel"><?php echo esc_html_x( 'Cancel', 'button', 'buddypress' ); ?></option>
		</select>

		<span class="select-arrow"></span>

	</div><!-- // .select-wrap -->

	<input type="submit" id="invitation-bulk-manage" class="button action" value="<?php echo esc_attr_x( 'Apply', 'button', 'buddypress' ); ?>">
	<?php
}

/**
 * Customize the way to output the Members' loop member latest activities.
 *
 * @since 12.0.0
 *
 * @param string $activity_content Formatted latest update for current member.
 * @param array  $args             Array of parsed arguments.
 * @param array  $latest_update    Array of the latest activity data.
 * @return string The formatted latest update for current member.
 */
function bp_nouveau_get_member_latest_update( $activity_content = '', $args = array(), $latest_update = array() ) {
	if ( ! isset( $latest_update['content'], $latest_update['excerpt'], $latest_update['permalink'] ) ) {
		return $activity_content;
	}

	if ( strlen( $latest_update['excerpt'] ) < strlen( $latest_update['content'] ) ) {
		return sprintf(
			'%1$s<span class="activity-read-more"><a href="%2$s" rel="nofollow">%3$s</a></span>',
			esc_html( $latest_update['excerpt'] ) . "\n",
			esc_url( $latest_update['permalink'] ),
			esc_html__( 'View full conversation', 'buddypress' )
		);
	}

	return esc_html( $latest_update['excerpt'] );
}
add_filter( 'bp_get_member_latest_update', 'bp_nouveau_get_member_latest_update', 10, 3 );
