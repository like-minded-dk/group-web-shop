<?php
/**
 * BuddyPress engagements Classes.
 *
 * @package BuddyPress
 * @subpackage engagementsengagementship
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress engagementship object.
 *
 * @since 1.0.0
 */
#[AllowDynamicProperties]
class BP_Engagements_Engagementship {

	/**
	 * ID of the engagementship.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id;

	/**
	 * User ID of the engagementship initiator.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $initiator_user_id;

	/**
	 * User ID of the 'engagement' - the one invited to the engagementship.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $engagement_user_id;

	/**
	 * Has the engagementship been confirmed/accepted?
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $is_confirmed;

	/**
	 * Is this a "limited" engagementship?
	 *
	 * Not currently used by BuddyPress.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $is_limited;

	/**
	 * Date the engagementship was created.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $date_created;

	/**
	 * Is this a request?
	 *
	 * Not currently used in BuddyPress.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $is_request;

	/**
	 * Should additional engagement details be queried?
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $populate_engagement_details;

	/**
	 * Details about the engagement.
	 *
	 * @since 1.0.0
	 * @var BP_Core_User
	 */
	public $engagement;

	/**
	 * Constructor method.
	 *
	 * @since 1.5.0
	 * @since 10.0.0 Updated to add deprecated notice for `$is_request`.
	 *
	 * @param int|null $id                      Optional. The ID of an existing engagementship.
	 * @param bool     $is_request              Deprecated.
	 * @param bool     $populate_engagement_details Optional. True if engagement details should be queried.
	 */
	public function __construct( $id = null, $is_request = false, $populate_engagement_details = true ) {

		if ( false !== $is_request ) {
			_deprecated_argument(
				__METHOD__,
				'1.5.0',
				sprintf(
					/* translators: 1: the name of the method. 2: the name of the file. */
					esc_html__( '%1$s no longer accepts $is_request. See the inline documentation at %2$s for more details.', 'buddypress' ),
					__METHOD__,
					__FILE__
				)
			);
		}

		$this->is_request = $is_request;

		if ( ! empty( $id ) ) {
			$this->id                      = (int) $id;
			$this->populate_engagement_details = $populate_engagement_details;
			$this->populate( $this->id );
		}
	}

	/**
	 * Set up data about the current engagementship.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 */
	public function populate() {
		global $wpdb;

		$bp = buddypress();

		// Check cache for engagementship data.
		$engagementship = wp_cache_get( $this->id, 'bp_engagements_engagementships' );

		// Cache missed, so query the DB.
		if ( false === $engagementship ) {
			$engagementship = $wpdb->get_row( $wpdb->prepare( <<<SQL
				SELECT * FROM {$bp->engagements->table_name} WHERE id = %d
			SQL
			, $this->id ) );

			wp_cache_set( $this->id, $engagementship, 'bp_engagements_engagementships' );
		}

		// No engagementship found so set the ID and bail.
		if ( empty( $engagementship ) || is_wp_error( $engagementship ) ) {
			$this->id = 0;
			return;
		}

		$this->initiator_user_id = (int) $engagementship->initiator_user_id;
		$this->engagement_user_id    = (int) $engagementship->engagement_user_id;
		$this->is_confirmed      = (int) $engagementship->is_confirmed;
		$this->is_limited        = (int) $engagementship->is_limited;
		$this->date_created      = $engagementship->date_created;

		if ( ! empty( $this->populate_engagement_details ) ) {
			if ( bp_displayed_user_id() === $this->engagement_user_id ) {
				$this->engagement = new BP_Core_User( $this->initiator_user_id );
			} else {
				$this->engagement = new BP_Core_User( $this->engagement_user_id );
			}
		}
	}

	/**
	 * Save the current engagementship to the database.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $wpdb;

		$bp = buddypress();

		$this->initiator_user_id = apply_filters( 'engagements_engagementship_initiator_user_id_before_save', $this->initiator_user_id, $this->id );
		$this->engagement_user_id    = apply_filters( 'engagements_engagementship_engagement_user_id_before_save', $this->engagement_user_id, $this->id );
		$this->is_confirmed      = apply_filters( 'engagements_engagementship_is_confirmed_before_save', $this->is_confirmed, $this->id );
		$this->is_limited        = apply_filters( 'engagements_engagementship_is_limited_before_save', $this->is_limited, $this->id );
		$this->date_created      = apply_filters( 'engagements_engagementship_date_created_before_save', $this->date_created, $this->id );

		/**
		 * Fires before processing and saving the current engagementship request.
		 *
		 * @since 1.0.0
		 *
		 * @param BP_Engagements_Engagementship $value Current engagementship object. Passed by reference.
		 */
		do_action_ref_array( 'engagements_engagementship_before_save', array( &$this ) );

		// Update.
		if ( ! empty( $this->id ) ) {
			$result = $wpdb->query( $wpdb->prepare( <<<SQL
				UPDATE {$bp->engagements->table_name} 
				SET initiator_user_id = %d, 
					engagement_user_id = %d, 
					is_confirmed = %d, 
					is_limited = %d, 
					date_created = %s 
				WHERE id = %d
				SQL,
				$this->initiator_user_id,
				$this->engagement_user_id,
				$this->is_confirmed,
				$this->is_limited,
				$this->date_created,
				$this->id ) );
		// Save.
		} else {
			$result = $wpdb->query( $wpdb->prepare( <<<SQL
			INSERT INTO {$bp->engagements->table_name} 
				( initiator_user_id,
				engagement_user_id,
				is_confirmed,
				is_limited,
				date_created ) 
			VALUES ( %d, %d, %d, %d, %s )
			SQL,
			$this->initiator_user_id,
			$this->engagement_user_id,
			$this->is_confirmed,
			$this->is_limited,
			$this->date_created ) );
			$this->id = $wpdb->insert_id;
		}

		/**
		 * Fires after processing and saving the current engagementship request.
		 *
		 * @since 1.0.0
		 *
		 * @param BP_Engagements_Engagementship $value Current engagementship object. Passed by reference.
		 */
		do_action_ref_array( 'engagements_engagementship_after_save', array( &$this ) );

		return $result;
	}

	/**
	 * Delete the current engagementship from the database.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @return bool|int
	 */
	public function delete() {
		global $wpdb;

		$bp = buddypress();

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->engagements->table_name} WHERE id = %d", $this->id ) );
	}

	/** Static Methods ********************************************************/

	/**
	 * Get the engagementships for a given user.
	 *
	 * @since 2.6.0
	 *
	 * @param int    $user_id  ID of the user whose engagements are being retrieved.
	 * @param array  $args     {
	 *        Optional. Filter parameters.
	 *        @type int    $id                ID of specific engagementship to retrieve.
	 *        @type int    $initiator_user_id ID of engagementship initiator.
	 *        @type int    $engagement_user_id    ID of specific engagementship to retrieve.
	 *        @type int    $is_confirmed      Whether the engagementship has been accepted.
	 *        @type int    $is_limited        Whether the engagementship is limited.
	 *        @type string $order_by          Column name to order by.
	 *        @type string $sort_order        Optional. ASC or DESC. Default: 'DESC'.
	 * }
	 * @param string $operator Optional. Operator to use in `wp_list_filter()`.
	 *
	 * @return array $engagementships Array of engagementship objects.
	 */
	public static function get_engagementships( $user_id, $args = array(), $operator = 'AND' ) {
		if ( empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		$engagementships = array();
		$operator    = strtoupper( $operator );

		if ( ! in_array( $operator, array( 'AND', 'OR', 'NOT' ), true ) ) {
			return $engagementships;
		}

		$r = bp_parse_args(
			$args,
			array(
				'id'                => null,
				'initiator_user_id' => null,
				'engagement_user_id'    => null,
				'is_confirmed'      => null,
				'is_limited'        => null,
				'order_by'          => 'date_created',
				'sort_order'        => 'DESC',
				'page'              => null,
				'per_page'          => null,
			),
			'bp_get_user_engagementships'
		);

		// First, we get all engagementships that involve the user.

		
		$engagementship_ids = wp_cache_get( $user_id, 'bp_engagements_engagementships_for_user' );
		if ( false === $engagementship_ids ) {
			$engagementship_ids = self::get_engagementship_ids_for_user( $user_id );
			wp_cache_set( $user_id, $engagementship_ids, 'bp_engagements_engagementships_for_user' );
		}

		// Prime the membership cache.
		$uncached_engagementship_ids = bp_get_non_cached_ids( $engagementship_ids, 'bp_engagements_engagementships' );
		if ( ! empty( $uncached_engagementship_ids ) ) {
			$uncached_engagementships = self::get_engagementships_by_id( $uncached_engagementship_ids );

			foreach ( $uncached_engagementships as $uncached_engagementship ) {
				wp_cache_set( $uncached_engagementship->id, $uncached_engagementship, 'bp_engagements_engagementships' );
			}
		}

		$int_keys  = array( 'id', 'initiator_user_id', 'engagement_user_id' );
		$bool_keys = array( 'is_confirmed', 'is_limited' );

		// Assemble filter array.
		$filters = wp_array_slice_assoc( $r, array( 'id', 'initiator_user_id', 'engagement_user_id', 'is_confirmed', 'is_limited' ) );
		foreach ( $filters as $filter_name => $filter_value ) {
			if ( is_null( $filter_value ) ) {
				unset( $filters[ $filter_name ] );
			} elseif ( in_array( $filter_name, $int_keys, true ) ) {
				$filters[ $filter_name ] = (int) $filter_value;
			} else {
				$filters[ $filter_name ] = (bool) $filter_value;
			}
		}

		// Populate engagementship array from cache, and normalize.
		foreach ( $engagementship_ids as $engagementship_id ) {
			// Create a limited BP_Engagements_Engagementship object (don't fetch the user details).
			$engagementship = new BP_Engagements_Engagementship( $engagementship_id, false, false );

			// Sanity check.
			if ( ! isset( $engagementship->id ) ) {
				continue;
			}

			// Integer values.
			foreach ( $int_keys as $index ) {
				$engagementship->{$index} = intval( $engagementship->{$index} );
			}

			// Boolean values.
			foreach ( $bool_keys as $index ) {
				$engagementship->{$index} = (bool) $engagementship->{$index};
			}

			// We need to support the same operators as wp_list_filter().
			if ( 'OR' === $operator || 'NOT' === $operator ) {
				$matched = 0;

				foreach ( $filters as $filter_name => $filter_value ) {
					if ( isset( $engagementship->{$filter_name} ) && $filter_value === $engagementship->{$filter_name} ) {
						$matched++;
					}
				}

				if ( ( 'OR' === $operator && $matched > 0 )
				  || ( 'NOT' === $operator && 0 === $matched ) ) {
					$engagementships[ $engagementship->id ] = $engagementship;
				}

			} else {
				/*
				 * This is the more typical 'AND' style of filter.
				 * If any of the filters miss, we move on.
				 */
				foreach ( $filters as $filter_name => $filter_value ) {
					if ( ! isset( $engagementship->{$filter_name} ) || $filter_value !== $engagementship->{$filter_name} ) {
						continue 2;
					}
				}
				$engagementships[ $engagementship->id ] = $engagementship;
			}

		}

		// Sort the results on a column name.
		if ( in_array( $r['order_by'], array( 'id', 'initiator_user_id', 'engagement_user_id' ) ) ) {
			$engagementships = bp_sort_by_key( $engagementships, $r['order_by'], 'num', true );
		}

		// Adjust the sort direction of the results.
		if ( 'ASC' === bp_esc_sql_order( $r['sort_order'] ) ) {
			// `true` to preserve keys.
			$engagementships = array_reverse( $engagementships, true );
		}

		// Paginate the results.
		if ( $r['per_page'] && $r['page'] ) {
			$start       = ( $r['page'] - 1 ) * ( $r['per_page'] );
			$engagementships = array_slice( $engagementships, $start, $r['per_page'] );
		}
		return $engagementships;
	}

	/**
	 * Get all engagementship IDs for a user.
	 *
	 * @since 2.7.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $user_id ID of the user.
	 * @return array
	 */
	public static function get_engagementship_ids_for_user( $user_id ) {
		global $wpdb;

		$bp = buddypress();
		$engagementship_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$bp->engagements->table_name} WHERE (initiator_user_id = %d OR engagement_user_id = %d) ORDER BY date_created DESC", $user_id, $user_id ) );
		//$engagementship_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$bp->engagements->table_name} WHERE (engagement_user_id = %d) ORDER BY date_created DESC", $user_id, $user_id ) );

		return $engagementship_ids;
	}

	/**
	 * Get the IDs of a given user's engagements.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $user_id              ID of the user whose engagements are being retrieved.
	 * @param bool $engagement_requests_only Optional. Whether to fetch
	 *                                   unaccepted requests only. Default: false.
	 * @param bool $assoc_arr            Optional. True to receive an array of arrays
	 *                                   keyed as 'user_id' => $user_id; false to get a one-dimensional
	 *                                   array of user IDs. Default: false.
	 * @return array $fids IDs of engagements for provided user.
	 */
	public static function get_engagement_user_ids( $user_id, $engagement_requests_only = false, $assoc_arr = false ) {

		if ( ! empty( $engagement_requests_only ) ) {
			$args = array(
				'is_confirmed'   => 0,
				'engagement_user_id' => $user_id,
			);
		} else {
			if (bp_current_component() == 'engagements') {
				$args = array(
					'initiator_user_id' => $user_id,
					'is_confirmed' => 1,
				);
			} 
			if (bp_current_component() == 'friends') {
				$args = array(
					'engagement_user_id' => $user_id,
					'is_confirmed' => 1,
				);
			}
		}

		$engagementships = self::get_engagementships( $user_id, $args );
		$user_id     = (int) $user_id;

		$fids = array();
		foreach ( $engagementships as $engagementship ) {
			$engagement_id = $engagementship->engagement_user_id;
			if ( $engagementship->engagement_user_id === $user_id ) {
				$engagement_id = $engagementship->initiator_user_id;
			}

			if ( ! empty( $assoc_arr ) ) {
				$fids[] = array( 'user_id' => $engagement_id );
			} else {
				$fids[] = $engagement_id;
			}
		}

		return array_map( 'intval', $fids );
	}

	/**
	 * Get the ID of the engagementship object, if any, between a pair of users.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   The ID of the first user.
	 * @param int $engagement_id The ID of the second user.
	 * @return int|null The ID of the engagementship object if found, otherwise null.
	 */
	public static function get_engagementship_id( $user_id, $engagement_id ) {
		$engagementship_id = null;

		// Can't engagement yourself.
		if ( $user_id === $engagement_id ) {
			return $engagementship_id;
		}

		/*
		 * Find engagementships where the possible_engagement_userid is the
		 * initiator or engagement.
		 */
		$args = array(
			'initiator_user_id' => $engagement_id,
			'engagement_user_id'    => $engagement_id,
		);

		$result = self::get_engagementships( $user_id, $args, 'OR' );
		if ( $result ) {
			$engagementship_id = current( $result )->id;
		}
		return $engagementship_id;
	}

	/**
	 * Get a list of IDs of users who have requested engagementship of a given user.
	 *
	 * @since 1.2.0
	 *
	 * @param int $user_id The ID of the user who has received the
	 *                     engagementship requests.
	 * @return array|bool An array of user IDs or false if none are found.
	 */
	public static function get_engagementship_request_user_ids( $user_id ) {
		$engagement_requests = wp_cache_get( $user_id, 'bp_engagements_requests' );

		if ( false === $engagement_requests ) {
			$engagement_requests = self::get_engagement_user_ids( $user_id, true );

			wp_cache_set( $user_id, $engagement_requests, 'bp_engagements_requests' );
		}

		// Integer casting.
		if ( ! empty( $engagement_requests ) ) {
			$engagement_requests = array_map( 'intval', $engagement_requests );
		}
		return $engagement_requests;
	}

	/**
	 * Get a total engagement count for a given user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id Optional. ID of the user whose engagementships you
	 *                     are counting. Default: displayed user (if any), otherwise
	 *                     logged-in user.
	 * @return int engagement count for the user.
	 */
	public static function total_engagement_count( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
		}

		/*
		 * This is stored in 'total_engagement_count' usermeta.
		 * This function will recalculate, update and return.
		 */

		$args        = array( 'is_confirmed' => 1 );
		$engagementships = self::get_engagementships( $user_id, $args );
		$count       = count( $engagementships );

		// Do not update meta if user has never had engagements.
		if ( ! $count && ! bp_get_user_meta( $user_id, 'total_engagement_count', true ) ) {
			return 0;
		}

		bp_update_user_meta( $user_id, 'total_engagement_count', (int) $count );

		return absint( $count );
	}

	/**
	 * Search the engagements of a user by a search string.
	 *
	 * @todo Optimize this function.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param string   $filter  The search string, matched against xprofile
	 *                        fields (if available), or usermeta 'nickname' field.
	 * @param int      $user_id ID of the user whose engagements are being searched.
	 * @param int|null $limit   Optional. Max number of engagements to return.
	 * @param int|null $page    Optional. The page of results to return. Default:
	 *                          null (no pagination - return all results).
	 * @return array|bool On success, an array: {
	 *     @type array $engagements IDs of engagements returned by the query.
	 *     @type int   $count   Total number of engagements (disregarding
	 *                          pagination) who match the search.
	 * }. Returns false on failure.
	 */
	public static function search_engagements( $filter, $user_id, $limit = null, $page = null ) {
		global $wpdb;

		$bp = buddypress();

		if ( empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		// Only search for matching strings at the beginning of the
		// name (@todo - figure out why this restriction).
		$search_terms_like = bp_esc_like( $filter ) . '%';

		$pag_sql = '';
		if ( ! empty( $limit ) && ! empty( $page ) ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
		}

		$engagement_ids = self::get_engagement_user_ids( $user_id );
		if ( ! $engagement_ids ) {
			return false;
		}

		// Get all the user ids for the current user's engagements.
		$fids = implode( ',', wp_parse_id_list( $engagement_ids ) );

		if ( empty( $fids ) ) {
			return false;
		}

		// Filter the user_ids based on the search criteria.
		if ( bp_is_active( 'xprofile' ) ) {
			$sql       = $wpdb->prepare( "SELECT DISTINCT user_id FROM {$bp->profile->table_name_data} WHERE user_id IN ({$fids}) AND value LIKE %s {$pag_sql}", $search_terms_like );
			$total_sql = $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$bp->profile->table_name_data} WHERE user_id IN ({$fids}) AND value LIKE %s", $search_terms_like );
		} else {
			$sql       = $wpdb->prepare( "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE user_id IN ({$fids}) AND meta_key = 'nickname' AND meta_value LIKE %s {$pag_sql}", $search_terms_like );
			$total_sql = $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE user_id IN ({$fids}) AND meta_key = 'nickname' AND meta_value LIKE %s", $search_terms_like );
		}

		$filtered_engagement_ids = $wpdb->get_col( $sql );
		$total_engagement_ids    = $wpdb->get_var( $total_sql );

		if ( empty( $filtered_engagement_ids ) ) {
			return false;
		}

		return array(
			'engagements' => array_map( 'intval', $filtered_engagement_ids ),
			'total'   => (int) $total_engagement_ids,
		);
	}

	/**
	 * Check engagementship status between two users.
	 *
	 * Note that 'pending_engagement' means that $initiator_userid has sent a engagement
	 * request to $possible_engagement_userid that has not yet been approved,
	 * while 'awaiting_response' is the other way around ($possible_engagement_userid
	 * sent the initial request).
	 *
	 * @since 1.0.0
	 *
	 * @param int $initiator_userid       The ID of the user who is the initiator
	 *                                    of the potential engagementship/request.
	 * @param int $possible_engagement_userid The ID of the user who is the
	 *                                    recipient of the potential engagementship/request.
	 * @return string|false $value The engagementship status, from among 'not_engagements',
	 *                             'is_engagement', 'pending_engagement', and 'awaiting_response'.
	 */
	public static function check_is_engagement( $initiator_userid, $possible_engagement_userid ) {

		if ( empty( $initiator_userid ) || empty( $possible_engagement_userid ) ) {
			return false;
		}

		// Can't engagement yourself.
		if ( (int) $initiator_userid === (int) $possible_engagement_userid ) {
			return 'not_engagements';
		}

		self::update_bp_engagements_cache( $initiator_userid, $possible_engagement_userid );

		return bp_core_get_incremented_cache( $initiator_userid . ':' . $possible_engagement_userid, 'bp_engagements' );
	}

	/**
	 * Find uncached engagementships between a user and one or more other users and cache them.
	 *
	 * @since 3.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int              $user_id             The ID of the primary user for whom we want
	 *                                              to check engagementships statuses.
	 * @param int|array|string $possible_engagement_ids The IDs of the one or more users
	 *                                              to check engagementship status with primary user.
	 */
	public static function update_bp_engagements_cache( $user_id, $possible_engagement_ids ) {
		global $wpdb;

		$bp                  = buddypress();
		$user_id             = (int) $user_id;
		$possible_engagement_ids = wp_parse_id_list( $possible_engagement_ids );

		$fetch = array();
		foreach ( $possible_engagement_ids as $engagement_id ) {
			// Check for cached items in both engagementship directions.
			if ( false === bp_core_get_incremented_cache( $user_id . ':' . $engagement_id, 'bp_engagements' )
				|| false === bp_core_get_incremented_cache( $engagement_id . ':' . $user_id, 'bp_engagements' ) ) {
				$fetch[] = $engagement_id;
			}
		}

		if ( empty( $fetch ) ) {
			return;
		}

		$engagement_ids_sql = implode( ',', array_unique( $fetch ) );
		$sql = $wpdb->prepare( <<<SQL
			SELECT initiator_user_id, engagement_user_id, is_confirmed 
			FROM {$bp->engagements->table_name} 
			WHERE (initiator_user_id = %d AND engagement_user_id IN ({$engagement_ids_sql}) ) 
				OR (initiator_user_id IN ({$engagement_ids_sql}) AND engagement_user_id = %d )
		SQL, $user_id, $user_id );
		$engagementships = $wpdb->get_results( $sql );

		// Use $handled to keep track of all of the $possible_engagement_ids we've matched.
		$handled = array();
		foreach ( $engagementships as $engagementship ) {
			$initiator_user_id = (int) $engagementship->initiator_user_id;
			$engagement_user_id    = (int) $engagementship->engagement_user_id;
			if ( 1 === (int) $engagementship->is_confirmed) {
				if ($initiator_user_id === $user_id && bp_current_component() === 'engagements') {
					$status_initiator = $status_engagement = 'is_engagement';
				} else {
					$status_initiator = $status_engagement = 'exist_engagement';
				}
			} else {
				$status_initiator = 'pending_engagement';
				$status_engagement    = 'awaiting_response';
			}
			bp_core_set_incremented_cache( $initiator_user_id . ':' . $engagement_user_id, 'bp_engagements', $status_initiator );
			bp_core_set_incremented_cache( $engagement_user_id . ':' . $initiator_user_id, 'bp_engagements', $status_engagement );

			$handled[] = ( $initiator_user_id === $user_id ) ? $engagement_user_id : $initiator_user_id;
		}

		// Set all those with no matching entry to "not engagements" status.
		$not_engagements = array_diff( $fetch, $handled );

		foreach ( $not_engagements as $not_engagement_id ) {
			bp_core_set_incremented_cache( $user_id . ':' . $not_engagement_id, 'bp_engagements', 'not_engagements' );
			bp_core_set_incremented_cache( $not_engagement_id . ':' . $user_id, 'bp_engagements', 'not_engagements' );
		}
	}

	/**
	 * Get the last active date of many users at once.
	 *
	 * @todo Why is this in the engagements component?
	 *
	 * @since 1.0.0
	 *
	 * @param array $user_ids IDs of users whose last_active meta is
	 *                        being queried.
	 * @return array $retval Array of last_active values + user_ids.
	 */
	public static function get_bulk_last_active( $user_ids ) {
		$last_activities = BP_Core_User::get_last_activity( $user_ids );

		// Sort and structure as expected in legacy function.
		usort( $last_activities, function( $a, $b ) {
			if ( $a['date_recorded'] === $b['date_recorded'] ) {
				return 0;
			}

			return ( strtotime( $a['date_recorded'] ) < strtotime( $b['date_recorded'] ) ) ? 1 : -1;
		} );

		$retval = array();
		foreach ( $last_activities as $last_activity ) {
			$u                = new stdClass();
			$u->last_activity = $last_activity['date_recorded'];
			$u->user_id       = $last_activity['user_id'];

			$retval[] = $u;
		}

		return $retval;
	}

	/**
	 * Mark a engagementship as accepted.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $engagementship_id ID of the engagementship to be accepted.
	 * @return int Number of database rows updated.
	 */
	public static function accept( $engagementship_id ) {
		global $wpdb;

		$bp = buddypress();

		return $wpdb->query( $wpdb->prepare( "UPDATE {$bp->engagements->table_name} SET is_confirmed = 1, date_created = %s WHERE id = %d AND engagement_user_id = %d", bp_core_current_time(), $engagementship_id, bp_loggedin_user_id() ) );
	}

	/**
	 * Remove a engagementship or a engagementship request INITIATED BY the logged-in user.
	 *
	 * @since 1.6.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $engagementship_id ID of the engagementship to be withdrawn.
	 * @return int Number of database rows deleted.
	 */
	public static function withdraw( $engagementship_id ) {
		global $wpdb;

		$bp = buddypress();

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->engagements->table_name} WHERE id = %d AND initiator_user_id = %d", $engagementship_id, bp_loggedin_user_id() ) );
	}

	/**
	 * Remove a engagementship or a engagementship request MADE OF the logged-in user.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $engagementship_id ID of the engagementship to be rejected.
	 * @return int Number of database rows deleted.
	 */
	public static function reject( $engagementship_id ) {
		global $wpdb;

		$bp = buddypress();

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->engagements->table_name} WHERE id = %d AND engagement_user_id = %d", $engagementship_id, bp_loggedin_user_id() ) );
	}

	/**
	 * Search users.
	 *
	 * @todo Why does this exist, and why is it in bp-engagements?
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param string   $filter  String to search by.
	 * @param int      $user_id A user ID param that is unused.
	 * @param int|null $limit   Optional. Max number of records to return.
	 * @param int|null $page    Optional. Number of the page to return. Default:
	 *                          false (no pagination - return all results).
	 * @return array $filtered_ids IDs of users who match the query.
	 */
	public static function search_users( $filter, $user_id, $limit = null, $page = null ) {
		global $wpdb;

		// Only search for matching strings at the beginning of the
		// name (@todo - figure out why this restriction).
		$search_terms_like = bp_esc_like( $filter ) . '%';

		$usermeta_table = $wpdb->base_prefix . 'usermeta';
		$users_table    = $wpdb->base_prefix . 'users';

		$pag_sql = '';
		if ( ! empty( $limit ) && ! empty( $page ) ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * intval( $limit ) ), intval( $limit ) );
		}

		$bp = buddypress();

		// Filter the user_ids based on the search criteria.
		if ( bp_is_active( 'xprofile' ) ) {
			$sql = $wpdb->prepare( "SELECT DISTINCT d.user_id as id FROM {$bp->profile->table_name_data} d, {$users_table} u WHERE d.user_id = u.id AND d.value LIKE %s ORDER BY d.value DESC {$pag_sql}", $search_terms_like );
		} else {
			$sql = $wpdb->prepare( "SELECT DISTINCT user_id as id FROM {$usermeta_table} WHERE meta_value LIKE %s ORDER BY d.value DESC {$pag_sql}", $search_terms_like );
		}

		$filtered_fids = $wpdb->get_col( $sql );

		if ( empty( $filtered_fids ) ) {
			return false;
		}

		return $filtered_fids;
	}

	/**
	 * Get a count of users who match a search term.
	 *
	 * @todo Why does this exist, and why is it in bp-engagements?
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param string $filter Search term.
	 * @return int Count of users matching the search term.
	 */
	public static function search_users_count( $filter ) {
		global $wpdb;

		// Only search for matching strings at the beginning of the
		// name (@todo - figure out why this restriction).
		$search_terms_like = bp_esc_like( $filter ) . '%';

		$usermeta_table = $wpdb->prefix . 'usermeta';
		$users_table    = $wpdb->base_prefix . 'users';

		$bp = buddypress();

		// Filter the user_ids based on the search criteria.
		if ( bp_is_active( 'xprofile' ) ) {
			$sql = $wpdb->prepare( "SELECT COUNT(DISTINCT d.user_id) FROM {$bp->profile->table_name_data} d, {$users_table} u WHERE d.user_id = u.id AND d.value LIKE %s", $search_terms_like );
		} else {
			$sql = $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$usermeta_table} WHERE meta_value LIKE %s", $search_terms_like );
		}

		$user_count = $wpdb->get_col( $sql );

		if ( empty( $user_count ) ) {
			return false;
		}

		return $user_count[0];
	}

	/**
	 * Sort a list of user IDs by their display names.
	 *
	 * @todo Why does this exist, and why is it in bp-engagements?
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param array $user_ids Array of user IDs.
	 * @return array|bool User IDs, sorted by the associated display names.
	 *                    False if XProfile component is not active.
	 */
	public static function sort_by_name( $user_ids ) {
		global $wpdb;

		if ( ! bp_is_active( 'xprofile' ) ) {
			return false;
		}

		$bp = buddypress();

		$user_ids = implode( ',', wp_parse_id_list( $user_ids ) );

		return $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM {$bp->profile->table_name_data} pd, {$bp->profile->table_name_fields} pf WHERE pf.id = pd.field_id AND pf.name = %s AND pd.user_id IN ( {$user_ids} ) ORDER BY pd.value ASC", bp_xprofile_fullname_field_name() ) );
	}

	/**
	 * Get a list of random engagement IDs.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $user_id       ID of the user whose engagements are being retrieved.
	 * @param int $total_engagements Optional. Number of random engagements to get.
	 *                           Default: 5.
	 * @return array|false An array of random engagement user IDs on success;
	 *                     false if none are found.
	 */
	public static function get_random_engagements( $user_id, $total_engagements = 5 ) {
		global $wpdb;

		$bp      = buddypress();
		$fids    = array();
		$sql     = $wpdb->prepare( "SELECT engagement_user_id, initiator_user_id FROM {$bp->engagements->table_name} WHERE (engagement_user_id = %d || initiator_user_id = %d) && is_confirmed = 1 ORDER BY rand() LIMIT %d", $user_id, $user_id, $total_engagements );
		$results = $wpdb->get_results( $sql );
		$user_id = (int) $user_id;

		for ( $i = 0, $count = count( $results ); $i < $count; ++$i ) {
			$engagement_user_id    = (int) $results[ $i ]->engagement_user_id;
			$initiator_user_id = (int) $results[ $i ]->initiator_user_id;

			if ( $engagement_user_id === $user_id ) {
				$fids[] = $initiator_user_id;
			} else {
				$fids[] = $engagement_user_id;
			}
		}

		// Remove duplicates.
		if ( count( $fids ) > 0 ) {
			return array_flip( array_flip( $fids ) );
		}

		return false;
	}

	/**
	 * Get a count of a user's engagements who can be invited to a given group.
	 *
	 * Users can invite any of their engagements except:
	 *
	 * - users who are already in the group
	 * - users who have a pending invite to the group
	 * - users who have been banned from the group
	 *
	 * @todo Need to do a group component check before using group functions.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id  ID of the user whose engagements are being counted.
	 * @param int $group_id ID of the group engagements are being invited to.
	 * @return bool|int False if group component is not active, and engagement count.
	 */
	public static function get_invitable_engagement_count( $user_id, $group_id ) {

		if ( ! bp_is_active( 'group' ) ) {
			return false;
		}

		// Setup some data we'll use below.
		$is_group_admin  = groups_is_user_admin( $user_id, $group_id );
		$engagement_ids      = self::get_engagement_user_ids( $user_id );
		$invitable_count = 0;

		for ( $i = 0, $count = count( $engagement_ids ); $i < $count; ++$i ) {

			// If already a member, they cannot be invited again.
			if ( groups_is_user_member( (int) $engagement_ids[ $i ], $group_id ) ) {
				continue;
			}

			// If user already has invite, they cannot be added.
			if ( groups_check_user_has_invite( (int) $engagement_ids[ $i ], $group_id ) ) {
				continue;
			}

			// If user is not group admin and engagement is banned, they cannot be invited.
			if ( ( false === $is_group_admin ) && groups_is_user_banned( (int) $engagement_ids[ $i ], $group_id ) ) {
				continue;
			}

			$invitable_count++;
		}

		return $invitable_count;
	}

	/**
	 * Get engagementship objects by ID (or an array of IDs).
	 *
	 * @since 2.7.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int|string|array $engagementship_ids Single engagementship ID or comma-separated/array list of engagementship IDs.
	 * @return array
	 */
	public static function get_engagementships_by_id( $engagementship_ids ) {
		global $wpdb;

		$bp = buddypress();

		$engagementship_ids = implode( ',', wp_parse_id_list( $engagementship_ids ) );
		return $wpdb->get_results( "SELECT * FROM {$bp->engagements->table_name} WHERE id IN ({$engagementship_ids})" );
	}

	/**
	 * Get the engagement user IDs for a given engagementship.
	 *
	 * @since 1.0.0
	 *
	 * @param int $engagementship_id ID of the engagementship.
	 * @return null|stdClass
	 */
	public static function get_user_ids_for_engagementship( $engagementship_id ) {
		$engagementship = new BP_Engagements_Engagementship( $engagementship_id, false, false );

		if ( empty( $engagementship->id ) ) {
			return null;
		}

		$retval                    = new StdClass();
		$retval->engagement_user_id    = $engagementship->engagement_user_id;
		$retval->initiator_user_id = $engagementship->initiator_user_id;

		return $retval;
	}

	/**
	 * Delete all engagementships and engagement notifications related to a user.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $user_id ID of the user being expunged.
	 */
	public static function delete_all_for_user( $user_id ) {
		global $wpdb;

		$bp      = buddypress();
		$user_id = (int) $user_id;

		// Get all engagementships, of any status, for the user.
		$engagementships    = self::get_engagementships( $user_id );
		$engagement_ids     = array();
		$engagementship_ids = array();
		foreach ( $engagementships as $engagementship ) {
			$engagementship_ids[] = $engagementship->id;
			if ( $engagementship->is_confirmed ) {
				if ( $engagementship->engagement_user_id === $user_id ) {
					$engagement_ids[] = $engagementship->initiator_user_id;
				} else {
					$engagement_ids[] = $engagementship->engagement_user_id;
				}
			}
		}

		// Delete the engagementships from the database.
		if ( $engagementship_ids ) {
			$engagementship_ids_sql = implode( ',', wp_parse_id_list( $engagementship_ids ) );
			$wpdb->query( "DELETE FROM {$bp->engagements->table_name} WHERE id IN ({$engagementship_ids_sql})" );
		}

		// Delete engagement request notifications for members who have a
		// notification from this user.
		if ( bp_is_active( 'notifications' ) ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->notifications->table_name} WHERE component_name = 'engagements' AND ( component_action = 'engagementship_request' OR component_action = 'engagementship_accepted' ) AND item_id = %d", $user_id ) );
		}

		// Clean up the engagementships cache.
		foreach ( $engagementship_ids as $engagementship_id ) {
			wp_cache_delete( $engagementship_id, 'bp_engagements_engagementships' );
		}

		// Loop through engagement_ids to scrub user caches and update total count metas.
		foreach ( (array) $engagement_ids as $engagement_id ) {
			// Delete cached engagementships.
			wp_cache_delete( $engagement_id, 'bp_engagements_engagementships_for_user' );

			self::total_engagement_count( $engagement_id );
		}

		// Delete cached engagementships.
		wp_cache_delete( $user_id, 'bp_engagements_engagementships_for_user' );
	}
}
