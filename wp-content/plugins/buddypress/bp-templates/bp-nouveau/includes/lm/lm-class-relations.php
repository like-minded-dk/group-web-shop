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
class BP_Relations_Relationship {
	public static $comp;
	public static $component;
	public static $bp_cachekey_relation;
	public static $reverse_receiver_id;

	public static $filter_initiator_bs;
	public static $filter_receiver_bs;
	public static $filter_confirmed_bs;
	public static $filter_limited_bs;
	public static $filter_dated_bs;
	public static $action_bs;
	public static $action_as;

	public static $receiver_id;
	public static $bp_cachekey;
	public static $bp_cachekey_user;
	public static $bp_cachekey_request;
	public static $relationship_accepted;
	public static $component_request;


	public static $var_list = array(
		'comp',
		'component',
		'bp_cachekey_relation',
		'reverse_receiver_id',

		'filter_initiator_bs',
		'filter_receiver_bs',
		'filter_confirmed_bs',
		'filter_limited_bs',
		'filter_dated_bs',
		'action_bs',
		'action_as',

		'receiver_id',
		'bp_cachekey',
		'bp_cachekey_user',
		'bp_cachekey_request',
		'relationship_accepted',
		'component_request',
	);

	/**
	 * ID of the relationship.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id;

	/**
	 * User ID of the relationship initiator.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $initiator_user_id;

	/**
	 * User ID of the 'relation' - the one invited to the relationship.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	// public $receiver_id;

	/**
	 * Has the relationship been confirmed/accepted?
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $is_confirmed;

	/**
	 * Is this a "limited" relationship?
	 *
	 * Not currently used by BuddyPress.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $is_limited;

	/**
	 * Date the relationship was created.
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
	 * Should additional relation details be queried?
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $populate_relation_details;

	/**
	 * Details about the relation.
	 *
	 * @since 1.0.0
	 * @var BP_Core_User
	 */
	public $relation;
    
	/**
	 * Constructor method.
	 *
	 * @since 1.5.0
	 * @since 10.0.0 Updated to add deprecated notice for `$is_request`.
	 *
	 * @param int|null $id                      Optional. The ID of an existing relationship.
	 * @param bool     $is_request              Deprecated.
	 * @param bool     $populate_relation_details Optional. True if relation details should be queried.
	 */
	public function __construct($comp, $id = null, $is_request = false, $populate_relation_details = true ) {
		$className = 'BP_Engagements_Engagementship';

        foreach (static::$var_list as $key) {
			error_log('===test=== key: '. $key . ' BEEV: '.BP_Engagements_Engagementship::$$key);
			// Ensure property exists and is accessible
			$key_ins = $key . '_ins';
			$this->$key_ins = $className::${$key};
		}

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
			$this->populate_relation_details = $populate_relation_details;
			$this->populate( $this->id );
		}
	}

	/**
	 * Set up data about the current relationship.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 */
	public function populate() {
		global $wpdb;

		$bp = buddypress();
		$receiver_id = $this->receiver_id_ins;
		// Check cache for relationship data.
		$relationship = wp_cache_get( $this->id, $this->bp_cachekey_relation_ins );
		$ccc = $this->comp_ins == 'friend' ? BP_Engagements_Engagementship::$comp : BP_Friends_Friendship::$comp;
		error_log(json_encode('========test===== ' . $ccc .'  ' .$this->comp_ins));
		// Cache missed, so query the DB.
		if ( false === $relationship ) {
			$relationship = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->{$this->component_ins}->table_name} WHERE id = %d", $this->id ) );

			wp_cache_set( $this->id, $relationship, $this->bp_cachekey_relation_ins );
		}

		// No relationship found so set the ID and bail.
		if ( empty( $relationship ) || is_wp_error( $relationship ) ) {
			$this->id = 0;
			return;
		}

		$this->initiator_user_id = (int) $relationship->initiator_user_id;
		$this->receiver_id_ins      = (int) $relationship->$receiver_id;
		$this->is_confirmed      = (int) $relationship->is_confirmed;
		$this->is_limited        = (int) $relationship->is_limited;
		$this->date_created      = $relationship->date_created;

		if ( ! empty( $this->populate_relation_details ) ) {
			if ( bp_displayed_user_id() === $this->receiver_id_ins ) {
				$this->relation = new BP_Core_User( $this->initiator_user_id );
			} else {
				$this->relation = new BP_Core_User( $this->receiver_id_ins );
			}
		}
	}

	/**
	 * Save the current relationship to the database.
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

		$this->initiator_user_id = apply_filters( $this->filter_initiator_bs, $this->initiator_user_id, $this->id );
		$this->receiver_id_ins       = apply_filters( $this->filter_receiver_bs, $this->receiver_id_ins, $this->id );
		$this->is_confirmed      = apply_filters( $this->filter_confirmed_bs, $this->is_confirmed, $this->id );
		$this->is_limited        = apply_filters( $this->filter_limited_bs, $this->is_limited, $this->id );
		$this->date_created      = apply_filters( $this->filter_dated_bs, $this->date_created, $this->id );

		/**
		 * Fires before processing and saving the current relationship request.
		 *
		 * @since 1.0.0
		 *
		 * @param BP_Relations_Relationship $value Current relationship object. Passed by reference.
		 */
		do_action_ref_array( self::$action_bs, array( &$this ) );

		// Update.
		if ( ! empty( $this->id ) ) {
			try {
				break_sql("update table {$this->comp} : $bp->{$this->component_ins}->table_name} user: {$this->initiator_user_id} receiver: {$this->receiver_id_ins}");

				$result = $wpdb->query( $wpdb->prepare( <<<SQL
					UPDATE {$bp->{$this->component_ins}->table_name}
					SET initiator_user_id = %d,
						{$this->receiver_id_ins} = %d,
						is_confirmed = %d,
						is_limited = %d,
						date_created = %s
					WHERE id = %d
					SQL,
					$this->initiator_user_id,
					$this->receiver_id_ins,
					$this->is_confirmed,
					$this->is_limited,
					$this->date_created,
					$this->id )
				);

			} catch (Exception $e) { $result = false; }
		// Save.
		} else {
			try {
				break_sql("add to table {$this->comp} : $bp->{$this->component_ins}->table_name} user: {$this->initiator_user_id} receiver: {$this->receiver_id_ins}");

				$result = $wpdb->query( $wpdb->prepare( <<<SQL
					INSERT INTO {$bp->{$this->component_ins}->table_name} 
						( initiator_user_id,
						{$this->receiver_id_ins},
						is_confirmed,
						is_limited,
						date_created )
					VALUES ( %d, %d, %d, %d, %s )
					SQL,
					$this->initiator_user_id,
					$this->receiver_id_ins,
					$this->is_confirmed,
					$this->is_limited,
					$this->date_created )
				);
				$this->id = $wpdb->insert_id;

			} catch (Exception $e) { $result = false; }
		}

		/**
		 * Fires after processing and saving the current relationship request.
		 *
		 * @since 1.0.0
		 *
		 * @param BP_Relations_Relationship $value Current relationship object. Passed by reference.
		 */
		do_action_ref_array( self::$action_as, array( &$this ) );

		return $result;
	}

	/**
	 * Delete the current relationship from the database.
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

		try {
			break_sql('delete relation id: '.json_encode($this->id));

			return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->{$this->component_ins}->table_name} WHERE id = %d", $this->id ) );

		} catch (Exception $e) { $result = false; }
	}

	/** Static Methods ********************************************************/

	/**
	 * Get the relationships for a given user.
	 *
	 * @since 2.6.0
	 *
	 * @param int    $user_id  ID of the user whose relations are being retrieved.
	 * @param array  $args     {
	 *        Optional. Filter parameters.
	 *        @type int    $id                ID of specific relationship to retrieve.
	 *        @type int    $initiator_user_id ID of relationship initiator.
	 *        @type int    $relation_user_id    ID of specific relationship to retrieve.
	 *        @type int    $is_confirmed      Whether the relationship has been accepted.
	 *        @type int    $is_limited        Whether the relationship is limited.
	 *        @type string $order_by          Column name to order by.
	 *        @type string $sort_order        Optional. ASC or DESC. Default: 'DESC'.
	 * }
	 * @param string $operator Optional. Operator to use in `wp_list_filter()`.
	 *
	 * @return array $relationships Array of relationship objects.
	 */
	public static function get_relationships( $user_id, $args = array(), $operator = 'AND' ) {
		if ( empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		$relationships = array();
		$operator    = strtoupper( $operator );

		if ( ! in_array( $operator, array( 'AND', 'OR', 'NOT' ), true ) ) {
			return $relationships;
		}

		$r = bp_parse_args(
			$args,
			array(
				'id'                => null,
				'initiator_user_id' => null,
				static::$receiver_id  => null,
				'is_confirmed'      => null,
				'is_limited'        => null,
				'order_by'          => 'date_created',
				'sort_order'        => 'DESC',
				'page'              => null,
				'per_page'          => null,
			),
			'bp_get_user_relationships'
		);

		// First, we get all relationships that involve the user.
		$relationship_ids = wp_cache_get( $user_id, static::$bp_cachekey_user );
		if ( false === $relationship_ids ) {
			$relationship_ids = static::get_relationship_ids_for_user( $user_id );
			wp_cache_set( $user_id, $relationship_ids, static::$bp_cachekey_user );
		}

		// Prime the membership cache.
		$uncached_relationship_ids = bp_get_non_cached_ids( $relationship_ids, static::$bp_cachekey_relation );
		if ( ! empty( $uncached_relationship_ids ) ) {
			$uncached_relationships = static::get_relationships_by_id( $uncached_relationship_ids );

			foreach ( $uncached_relationships as $uncached_relationship ) {
				wp_cache_set( $uncached_relationship->id, $uncached_relationship, static::$bp_cachekey_relation );
			}
		}

		$int_keys  = array( 'id', 'initiator_user_id', 'receiver_id_ins' );
		$bool_keys = array( 'is_confirmed', 'is_limited' );

		// Assemble filter array.
		$filters = wp_array_slice_assoc( $r, array( 'id', 'initiator_user_id', static::$receiver_id, 'is_confirmed', 'is_limited' ) );
		foreach ( $filters as $filter_name => $filter_value ) {
			if ( is_null( $filter_value ) ) {
				unset( $filters[ $filter_name ] );
			} elseif ( in_array( $filter_name, $int_keys, true ) ) {
				$filters[ $filter_name ] = (int) $filter_value;
			} else {
				$filters[ $filter_name ] = (bool) $filter_value;
			}
		}

		// Populate relationship array from cache, and normalize.
		foreach ( $relationship_ids as $relationship_id ) {
			// Create a limited BP_Relations_Relationship object (don't fetch the user details).
			$relationship = new BP_Relations_Relationship(static::$comp, $relationship_id, false, false );

			// Sanity check.
			if ( ! isset( $relationship->id ) ) {
				continue;
			}

			// Integer values.
			foreach ( $int_keys as $index ) {
				$relationship->{$index} = intval( $relationship->{$index} );
			}

			// Boolean values.
			foreach ( $bool_keys as $index ) {
				$relationship->{$index} = (bool) $relationship->{$index};
			}

			// We need to support the same operators as wp_list_filter().
			if ( 'OR' === $operator || 'NOT' === $operator ) {
				$matched = 0;

				foreach ( $filters as $filter_name => $filter_value ) {
					if ( isset( $relationship->{$filter_name} ) && $filter_value === $relationship->{$filter_name} ) {
						$matched++;
					}
				}

				if ( ( 'OR' === $operator && $matched > 0 )
				  || ( 'NOT' === $operator && 0 === $matched ) ) {
					$relationships[ $relationship->id ] = $relationship;
				}

			} else {
				/*
				 * This is the more typical 'AND' style of filter.
				 * If any of the filters miss, we move on.
				 */
				foreach ( $filters as $filter_name => $filter_value ) {
					if ( ! isset( $relationship->{$filter_name} ) || $filter_value !== $relationship->{$filter_name} ) {
						continue 2;
					}
				}
				$relationships[ $relationship->id ] = $relationship;
			}

		}

		// Sort the results on a column name.
		if ( in_array( $r['order_by'], array( 'id', 'initiator_user_id', static::$receiver_id ) ) ) {
			$relationships = bp_sort_by_key( $relationships, $r['order_by'], 'num', true );
		}

		// Adjust the sort direction of the results.
		if ( 'ASC' === bp_esc_sql_order( $r['sort_order'] ) ) {
			// `true` to preserve keys.
			$relationships = array_reverse( $relationships, true );
		}

		// Paginate the results.
		if ( $r['per_page'] && $r['page'] ) {
			$start       = ( $r['page'] - 1 ) * ( $r['per_page'] );
			$relationships = array_slice( $relationships, $start, $r['per_page'] );
		}
		return $relationships;
	}

	/**
	 * Get all relationship IDs for a user.
	 *
	 * @since 2.7.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $user_id ID of the user.
	 * @return array
	 */
	public static function get_relationship_ids_for_user( $user_id ) {
		global $wpdb;

        $receiver_id = static::$receiver_id;
		$component = static::$component;
		$bp = buddypress();
		$relationship_ids = $wpdb->get_col( $wpdb->prepare( <<<SQL
			SELECT id FROM {$bp->{$component}->table_name}
			WHERE (initiator_user_id = %d OR {$receiver_id} = %d)
			ORDER BY date_created DESC
		SQL, $user_id, $user_id ) );

		return $relationship_ids;
	}

	/**
	 * Get the IDs of a given user's relations.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $user_id              ID of the user whose relations are being retrieved.
	 * @param bool $relation_requests_only Optional. Whether to fetch
	 *                                   unaccepted requests only. Default: false.
	 * @param bool $assoc_arr            Optional. True to receive an array of arrays
	 *                                   keyed as 'user_id' => $user_id; false to get a one-dimensional
	 *                                   array of user IDs. Default: false.
	 * @return array $fids IDs of relations for provided user.
	 */
	public static function get_relation_user_ids( $user_id, $relation_requests_only = false, $assoc_arr = false ) {
		$receiver_id = static::$receiver_id;
		// $reverse_receiver_id = static::$reverse_receiver_id;
		if ( ! empty( $relation_requests_only ) ) {
			$args = array(
				'is_confirmed'   => 0,
				$receiver_id => $user_id,
			);
		} else {
			if (bp_current_component() == static::$component) {
				$args = array(
					'initiator_user_id' => $user_id,
				);
			} elseif (bp_current_component() == static::$component) {
				$args = array(
					$receiver_id => $user_id,
				);
			} else {
				$args = array();
			}
		}

		$relationships = static::get_relationships( $user_id, $args );
		error_log('---$relationships: '.json_encode($relationships));
		$user_id     = (int) $user_id;

		$member_ids = array();
		foreach ( $relationships as $relationship ) {
			$member_id = $relationship->reverse_receiver_id_ins;
			if ( $relationship->reverse_receiver_id_ins === $user_id ) {
				$member_id = $relationship->initiator_user_id;
			}
			if ( ! empty( $assoc_arr ) ) {
				$member_ids[] = array( 'user_id' => $member_id );
			} else {
				$member_ids[] = $member_id;
			}
		}
		return array_map( 'intval', $member_ids );
	}

	/**
	 * Get the ID of the relationship object, if any, between a pair of users.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   The ID of the first user.
	 * @param int $member_id The ID of the second user.
	 * @return int|null The ID of the relationship object if found, otherwise null.
	 */
	public static function get_relationship_id( $user_id, $member_id ) {
		$relation_id = null;
		$receiver_id = static::$receiver_id;
		// Can't relate yourself.
		if ( $user_id === $member_id ) {
			return $relation_id;
		}

		/*
		 * Find relationship where the possible_relation_userid is the
		 * initiator or relation.
		 */
		$args = array(
			'initiator_user_id' => $member_id,
			$receiver_id        => $member_id,
		);
		$result = static::get_relationships( $user_id, $args, 'OR' );
		$result = array_filter($result, function($v, $k) use ($user_id) {
			return $v->initiator_user_id == $user_id;
		}, ARRAY_FILTER_USE_BOTH);
		// error_log('class >>filter-first '. count($result));
		if ( $result ) {
			$relation_id = current( $result )->id;
		}
		return $relation_id;
	}

	/**
	 * Get a list of IDs of users who have requested relationship of a given user.
	 *
	 * @since 1.2.0
	 *
	 * @param int $user_id The ID of the user who has received the
	 *                     relationship requests.
	 * @return array|bool An array of user IDs or false if none are found.
	 */
	public static function get_relationship_request_user_ids( $user_id ) {
		$relation_requests = wp_cache_get( $user_id, static::$bp_cachekey_request );

		if ( false === $relation_requests ) {
			$relation_requests = static::get_relation_user_ids( $user_id, true );

			wp_cache_set( $user_id, $relation_requests, static::$bp_cachekey_request );
		}

		// Integer casting.
		if ( ! empty( $relation_requests ) ) {
			$relation_requests = array_map( 'intval', $relation_requests );
		}
		return $relation_requests;
	}

	/**
	 * Get a total relation count for a given user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id Optional. ID of the user whose relationships you
	 *                     are counting. Default: displayed user (if any), otherwise
	 *                     logged-in user.
	 * @return int relation count for the user.
	 */
	public static function total_relation_count( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
		}

		/*
		 * This is stored in 'total_relation_count' usermeta.
		 * This function will recalculate, update and return.
		 */

		$args        = array( 'is_confirmed' => 1 );
		$relationships = static::get_relationships( $user_id, $args );
		$count       = count( $relationships );

		// Do not update meta if user has never had relations.
		if ( ! $count && ! bp_get_user_meta( $user_id, 'total_relation_count', true ) ) {
			return 0;
		}

		bp_update_user_meta( $user_id, 'total_relation_count', (int) $count );

		return absint( $count );
	}

	/**
	 * Search the relations of a user by a search string.
	 *
	 * @todo Optimize this function.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param string   $filter  The search string, matched against xprofile
	 *                        fields (if available), or usermeta 'nickname' field.
	 * @param int      $user_id ID of the user whose relations are being searched.
	 * @param int|null $limit   Optional. Max number of relations to return.
	 * @param int|null $page    Optional. The page of results to return. Default:
	 *                          null (no pagination - return all results).
	 * @return array|bool On success, an array: {
	 *     @type array $relations IDs of relations returned by the query.
	 *     @type int   $count   Total number of relations (disregarding
	 *                          pagination) who match the search.
	 * }. Returns false on failure.
	 */
	public static function search_relations( $filter, $user_id, $limit = null, $page = null ) {
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

		$relations_ids = static::get_relation_user_ids( $user_id );
		if ( ! $relations_ids ) {
			return false;
		}

		// Get all the user ids for the current user's relations.
		$fids = implode( ',', wp_parse_id_list( $relations_ids ) );

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

		$filtered_relations_ids = $wpdb->get_col( $sql );
		$total_relations_ids    = $wpdb->get_var( $total_sql );

		if ( empty( $filtered_relations_ids ) ) {
			return false;
		}

		return array(
			static::$component => array_map( 'intval', $filtered_relations_ids ),
			'total'   => (int) $total_relations_ids,
		);
	}

	/**
	 * Check relationship status between two users.
	 *
	 * Note that 'pending_relation' means that $initiator_userid has sent a relation
	 * request to $possible_relation_userid that has not yet been approved,
	 * while 'awaiting_response' is the other way around ($possible_relation_userid
	 * sent the initial request).
	 *
	 * @since 1.0.0
	 *
	 * @param int $initiator_userid       The ID of the user who is the initiator
	 *                                    of the potential relationship/request.
	 * @param int $possible_relation_userid The ID of the user who is the
	 *                                    recipient of the potential relationship/request.
	 * @return string|false $value The relationship status, from among 'not_relation',
	 *                             'is_relation', 'pending_relation', and 'awaiting_response'.
	 */
	public static function check_is_relation( $initiator_userid, $possible_relation_userid ) {
		if ( empty( $initiator_userid ) || empty( $possible_relation_userid ) ) {
			return false;
		}

		// Can't relation yourself.
		if ( (int) $initiator_userid === (int) $possible_relation_userid ) {
			return 'not_relation';
		}

		static::update_bp_relations_cache( $initiator_userid, $possible_relation_userid );

		return bp_core_get_incremented_cache( $initiator_userid . ':' . $possible_relation_userid, static::$bp_cachekey );
	}

	/**
	 * Find uncached relationships between a user and one or more other users and cache them.
	 *
	 * @since 3.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int              $user_id             The ID of the primary user for whom we want
	 *                                              to check relationships statuses.
	 * @param int|array|string $possible_member_ids The IDs of the one or more users
	 *                                              to check relationship status with primary user.
	 */
	public static function update_bp_relations_cache( $user_id, $possible_member_ids ) {
		error_log(' ');
		error_log('class >'. json_encode('>>>update_bp_relations_cache'));
		$bp_cache_key = static::$bp_cachekey;
		update_lm_relation_cache(static::$comp, $user_id, $possible_member_ids, $bp_cache_key);
	}
	/**
	 * Get the last active date of many users at once.
	 *
	 * @todo Why is this in the Relations component?
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
	 * Mark a relationship as accepted.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $relationship_id ID of the relationship to be accepted.
	 * @return int Number of database rows updated.
	 */
	public static function accept( $relation_id ) {
		global $wpdb;

        $receiver_id = static::$receiver_id;
		$bp = buddypress();
		try {
			break_sql('>>>accept $relation_id 868: '.json_encode($relation_id));

			return $wpdb->query( $wpdb->prepare( <<<SQL
				UPDATE {$bp->{static::$component}->table_name}
				SET is_confirmed = 1, date_created = %s 
				WHERE id = %d AND {$receiver_id} = %d
			SQL, bp_core_current_time(), $relation_id, bp_loggedin_user_id() ) );

		} catch (Exception $e) { $result = false; }
	}

	/**
	 * Remove a relationship or a relationship request INITIATED BY the logged-in user.
	 *
	 * @since 1.6.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $relationship_id ID of the relationship to be withdrawn.
	 * @return int Number of database rows deleted.
	 */
	public static function withdraw( $relation_id ) {
		global $wpdb;
		$bp = buddypress();
		try {
			break_sql('>>>withdraw $relation_id 868: '.json_encode($relation_id));

			return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->{static::$component}->table_name} WHERE id = %d AND initiator_user_id = %d", $relation_id, bp_loggedin_user_id() ) );

		} catch (Exception $e) { $result = false; }
	}

	/**
	 * Remove a relationship or a relationship request MADE OF the logged-in user.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $relationship_id ID of the relationship to be rejected.
	 * @return int Number of database rows deleted.
	 */
	public static function reject( $relation_id ) {
		global $wpdb;

        $receiver_id = static::$receiver_id;
		$bp = buddypress();
		try {
			break_sql('>>reject $relation_id 820 delete id: '.json_encode($relation_id));

			return $wpdb->query( $wpdb->prepare( <<<SQL
				DELETE FROM {$bp->{static::$component}->table_name}
				WHERE id = %d AND {$receiver_id} = %d
			SQL, $relation_id, bp_loggedin_user_id() ) );

		} catch (Exception $e) {}
	}

	/**
	 * Search users.
	 *
	 * @todo Why does this exist, and why is it in bp-relations?
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
	 * @todo Why does this exist, and why is it in bp-relations?
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
	 * @todo Why does this exist, and why is it in bp-relations?
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
	 * Get a list of random relation IDs.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $user_id       ID of the user whose relations are being retrieved.
	 * @param int $total_relations Optional. Number of random relations to get.
	 *                           Default: 5.
	 * @return array|false An array of random relation user IDs on success;
	 *                     false if none are found.
	 */
	public static function get_random_relations( $user_id, $total_relations = 5 ) {
		global $wpdb;

		$receiver_id = static::$receiver_id;

		$bp      = buddypress();
		$fids    = array();
		$sql     = $wpdb->prepare( <<<SQL
			SELECT {$receiver_id}, initiator_user_id
			FROM {$bp->{static::$component}->table_name} 
			WHERE (relation_user_id = %d || initiator_user_id = %d) && is_confirmed = 1 
			ORDER BY rand() LIMIT %d
		SQL, $user_id, $user_id, $total_relations );
		$results = $wpdb->get_results( $sql );
		$user_id = (int) $user_id;

		for ( $i = 0, $count = count( $results ); $i < $count; ++$i ) {
			$relation_user_id    = (int) $results[ $i ]->$receiver_id;
			$initiator_user_id = (int) $results[ $i ]->initiator_user_id;

			if ( $relation_user_id === $user_id ) {
				$fids[] = $initiator_user_id;
			} else {
				$fids[] = $relation_user_id;
			}
		}

		// Remove duplicates.
		if ( count( $fids ) > 0 ) {
			return array_flip( array_flip( $fids ) );
		}

		return false;
	}

	/**
	 * Get a count of a user's relations who can be invited to a given group.
	 *
	 * Users can invite any of their relations except:
	 *
	 * - users who are already in the group
	 * - users who have a pending invite to the group
	 * - users who have been banned from the group
	 *
	 * @todo Need to do a group component check before using group functions.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id  ID of the user whose relations are being counted.
	 * @param int $group_id ID of the group relations are being invited to.
	 * @return bool|int False if group component is not active, and relation count.
	 */
	public static function get_invitable_relation_count( $user_id, $group_id ) {

		if ( ! bp_is_active( 'group' ) ) {
			return false;
		}

		// Setup some data we'll use below.
		$is_group_admin  = groups_is_user_admin( $user_id, $group_id );
		$relation_ids    = static::get_relation_user_ids( $user_id );
		$invitable_count = 0;

		for ( $i = 0, $count = count( $relation_ids ); $i < $count; ++$i ) {

			// If already a member, they cannot be invited again.
			if ( groups_is_user_member( (int) $relation_ids[ $i ], $group_id ) ) {
				continue;
			}

			// If user already has invite, they cannot be added.
			if ( groups_check_user_has_invite( (int) $relation_ids[ $i ], $group_id ) ) {
				continue;
			}

			// If user is not group admin and relation is banned, they cannot be invited.
			if ( ( false === $is_group_admin ) && groups_is_user_banned( (int) $relation_ids[ $i ], $group_id ) ) {
				continue;
			}

			$invitable_count++;
		}

		return $invitable_count;
	}

	/**
	 * Get relationship objects by ID (or an array of IDs).
	 *
	 * @since 2.7.0
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int|string|array $relationship_ids Single relationship ID or comma-separated/array list of relationship IDs.
	 * @return array
	 */
	public static function get_relationships_by_id( $relationship_ids ) {
		global $wpdb;
		$bp = buddypress();

		$relationship_ids = implode( ',', wp_parse_id_list( $relationship_ids ) );
		return $wpdb->get_results( "SELECT * FROM {$bp->{static::$component}->table_name} WHERE id IN ({$relationship_ids})" );
	}

	/**
	 * Get the relation user IDs for a given relationship.
	 *
	 * @since 1.0.0
	 *
	 * @param int $relationship_id ID of the relationship.
	 * @return null|stdClass
	 */
	public static function get_user_ids_for_relationship( $relationship_id ) {
		$relationship = new BP_Relations_Relationship(static::$comp, $relationship_id, false, false );
		$receiver_id = static::$receiver_id;
		if ( empty( $relationship->id ) ) {
			return null;
		}

		$retval                    = new StdClass();
		$retval->$receiver_id    = $relationship->$receiver_id;
		$retval->initiator_user_id = $relationship->initiator_user_id;

		return $retval;
	}

	/**
	 * Delete all relationships and relation notifications related to a user.
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
		$receiver_id = static::$receiver_id;
        $component = static::$component;
        $component_request = static::$component_request;
        $relationship_accepted = static::$relationship_accepted;
        $bp_cachekey_user = static::$bp_cachekey_user;
        $bp_cachekey_relation = static::$bp_cachekey_relation;

		$user_id = (int) $user_id;

		// Get all relationships, of any status, for the user.
		$relationships    = static::get_relationships( $user_id );
		$relation_ids     = array();
		$relationship_ids = array();
		foreach ( $relationships as $relationship ) {
			$relationship_ids[] = $relationship->id;
			if ( $relationship->is_confirmed ) {
				if ( $relationship->$receiver_id === $user_id ) {
					$relation_ids[] = $relationship->initiator_user_id;
				} else {
					$relation_ids[] = $relationship->$receiver_id;
				}
			}
		}

		// Delete the relationships from the database.
		if ( $relationship_ids ) {
			$relationship_ids_sql = implode( ',', wp_parse_id_list( $relationship_ids ) );
			$wpdb->query( "DELETE FROM {$bp->{$receiver_id}->table_name} WHERE id IN ({$relationship_ids_sql})" );
		}

		// Delete relation request notifications for members who have a
		// notification from this user.
		if ( bp_is_active( 'notifications' ) ) {
			$wpdb->query( $wpdb->prepare( <<<SQL
				DELETE FROM {$bp->notifications->table_name}
				WHERE component_name = {$component} AND ( component_action = {$component_request}
				OR component_action = {$relationship_accepted} ) AND item_id = %d
			SQL, $user_id ) );
		}

		// Clean up the relationships cache.
		foreach ( $relationship_ids as $relationship_id ) {
			wp_cache_delete( $relationship_id, $bp_cachekey_relation );
		}

		// Loop through relation_ids to scrub user caches and update total count metas.
		foreach ( (array) $relation_ids as $relation_id ) {
			// Delete cached relationships.
			wp_cache_delete( $relation_id, $bp_cachekey_user );

			static::total_relation_count( $relation_id );
		}

		// Delete cached relationships.
		wp_cache_delete( $user_id, $bp_cachekey_user );
	}
}
