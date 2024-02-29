<?php
/**
 * BP Resell Class
 *
 * @package BP-Resell
 * @subpackage Class
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress Resell class.
 *
 * Handles populating and saving resell relationships.
 *
 * @since 1.0.0
 */
class BP_Resell {
	/**
	 * The resell ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id = 0;

	/**
	 * The ID of the item we want to resell.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $leader_id;

	/**
	 * The ID for the item initiating the resell request.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $reseller_id;

	/**
	 * The type of resell connection.
	 *
	 * Defaults to nothing, which will fetch users.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	public $resell_type = '';

	/**
	 * The UTC date the resell item was recorded in 'Y-m-d h:i:s' format.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	public $date_recorded;

	/**
	 * Constructor.
	 *
	 * @param int    $leader_id    The ID of the item wewant to resell.
	 * @param int    $reseller_id  The ID initiating the resell request.
	 * @param string $resell_type  The type of resell connection.
	 */
	public function __construct( $leader_id = 0, $reseller_id = 0, $resell_type = '' ) {
		if ( ! empty( $leader_id ) && ! empty( $reseller_id ) ) {
			$this->leader_id   = (int) $leader_id;
			$this->reseller_id = (int) $reseller_id;
			$this->resell_type = $resell_type;

			$this->populate();
		}
	}

	/**
	 * Populate method.
	 *
	 * Used in constructor.
	 *
	 * @since 1.0.0
	 */
	protected function populate() {
		global $wpdb;

		// we always require a leader ID.
		if ( empty( $this->leader_id ) ) {
			return;
		}

		// check cache first.
		$key = "{$this->leader_id}:{$this->reseller_id}:{$this->resell_type}";
		$data = wp_cache_get( $key, 'bp_resell_data' );

		// Run query if no cache.
		if ( false === $data ) {
			// SQL statement.
			$sql = self::get_select_sql( 'id, date_recorded' );
			$sql .= self::get_where_sql( array(
				'leader_id'   => $this->leader_id,
				'reseller_id' => $this->reseller_id,
				'resell_type' => $this->resell_type,
			) );

			// Run the query.
			$data = $wpdb->get_results( $sql );

			// Got a match; grab the results.
			if ( ! empty( $data ) ) {
				$data = $data[0];

			// No match. Set cache to zero to prevent further hits to database.
			} else {
				$data = 0;
			}

			// Set the cache.
			wp_cache_set( $key, $data, 'bp_resell_data' );
		}

		// Populate some other properties.
		if ( ! empty( $data ) ) {
			$this->id = $data->id;
			$this->date_recorded = $data->date_recorded;
		}
	}

	/**
	 * Saves a resell relationship into the database.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		global $wpdb, $bp;

		// do not use these filters
		// use the 'bp_resell_before_save' hook instead.
		$this->leader_id   = apply_filters( 'bp_resell_leader_id_before_save',   $this->leader_id,   $this->id );
		$this->reseller_id = apply_filters( 'bp_resell_reseller_id_before_save', $this->reseller_id, $this->id );

		do_action_ref_array( 'bp_resell_before_save', array( &$this ) );

		// leader ID is required
		// this allows plugins to bail out of saving a resell relationship
		// use hooks above to redeclare 'leader_id' so it is empty if you need to bail.
		if ( empty( $this->leader_id ) ) {
			return false;
		}

		// make sure a date is added for those directly using the save() method.
		if ( empty( $this->date_recorded ) ) {
			$this->date_recorded = bp_core_current_time();
		}

		// update existing entry.
		if ( $this->id ) {
			$result = $wpdb->query( $wpdb->prepare(
				"UPDATE {$bp->resell->table_name} SET leader_id = %d, reseller_id = %d, resell_type = %s, date_recorded = %s WHERE id = %d",
				$this->leader_id,
				$this->reseller_id,
				$this->resell_type,
				$this->date_recorded,
				$this->id
			) );

		// add new entry
		} else {
			$result = $wpdb->query( $wpdb->prepare(
				"INSERT INTO {$bp->resell->table_name} ( leader_id, reseller_id, resell_type, date_recorded ) VALUES ( %d, %d, %s, %s )",
				$this->leader_id,
				$this->reseller_id,
				$this->resell_type,
				$this->date_recorded
			) );
			$this->id = $wpdb->insert_id;
		}

		// Save cache.
		$data = new stdClass();
		$data->id = $this->id;
		$data->date_recorded = $this->date_recorded;

		wp_cache_set( "{$this->leader_id}:{$this->reseller_id}:{$this->resell_type}", $data, 'bp_resell_data' );

		do_action_ref_array( 'bp_resell_after_save', array( &$this ) );

		return $result;
	}

	/**
	 * Deletes a resell relationship from the database.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		global $wpdb, $bp;

		// SQL statement.
		$sql  = "DELETE FROM {$bp->resell->table_name} ";
		$sql .= self::get_where_sql( array(
			'id' => $this->id,
		) );

		// Delete cache.
		wp_cache_delete( "{$this->leader_id}:{$this->reseller_id}:{$this->resell_type}", 'bp_resell_data' );

		return $wpdb->query( $sql );
	}

	/** STATIC METHODS *****************************************************/

	/**
	 * Generate the SELECT SQL statement used to query resell relationships.
	 *
	 * @since 1.3.0
	 *
	 * @param string $column Column.
	 * @return string
	 */
	protected static function get_select_sql( $column = '' ) {
		$bp = $GLOBALS['bp'];

		return sprintf( 'SELECT %s FROM %s ', esc_sql( $column ), esc_sql( $bp->resell->table_name ) );
	}

	/**
	 * Generate the WHERE SQL statement used to query resell relationships.
	 *
	 * @todo Add support for date ranges with 'date_recorded' column
	 *
	 * @since 1.3.0
	 *
	 * @param array $params Where params.
	 * @return string
	 */
	protected static function get_where_sql( $params = array() ) {
		global $wpdb;

		$where_conditions = array();

		if ( ! empty( $params['id'] ) ) {
			$in = implode( ',', wp_parse_id_list( $params['id'] ) );
			$where_conditions['id'] = "id IN ({$in})";
		}

		if ( ! empty( $params['leader_id'] ) ) {
			$leader_ids = implode( ',', wp_parse_id_list( $params['leader_id'] ) );
			$where_conditions['leader_id'] = "leader_id IN ({$leader_ids})";

		// If null, return no results.
		} elseif ( array_key_exists( 'leader_id', $params ) && is_null( $params['leader_id'] ) ) {
			$where_conditions['no_results'] = '1 = 0';
		}

		if ( ! empty( $params['reseller_id'] ) ) {
			$reseller_ids = implode( ',', wp_parse_id_list( $params['reseller_id'] ) );
			$where_conditions['reseller_id'] = "reseller_id IN ({$reseller_ids})";

		// If null, return no results.
		} elseif ( array_key_exists( 'reseller_id', $params ) && is_null( $params['reseller_id'] ) ) {
			$where_conditions['no_results'] = '1 = 0';
		}

		if ( isset( $params['resell_type'] ) ) {
			$where_conditions['resell_type'] = $wpdb->prepare( 'resell_type = %s', $params['resell_type'] );
		}

		return 'WHERE ' . join( ' AND ', $where_conditions );
	}

	/**
	 * Generate the ORDER BY SQL statement used to query resell relationships.
	 *
	 * @since 1.3.0
	 *
	 * @param array $params {
	 *     Array of arguments.
	 *     @type string $orderby The DB column to order results by. Default: 'id'.
	 *     @type string $order The order. Either 'ASC' or 'DESC'. Default: 'DESC'.
	 * }
	 * @return string
	 */
	protected static function get_orderby_sql( $params = array() ) {
		$r = wp_parse_args( $params, array(
			'orderby' => 'id',
			'order'   => 'DESC',
		) );

		// sanitize 'orderby' DB oclumn lookup.
		switch ( $r['orderby'] ) {
			// columns available for lookup.
			case 'id':
			case 'leader_id':
			case 'reseller_id':
			case 'resell_type':
			case 'date_recorded':
				break;

			// fallback to 'id' column on anything else.
			default:
				$r['orderby'] = 'id';
				break;
		}

		// only allow ASC or DESC for order.
		if ( 'ASC' !== $r['order'] || 'DESC' !== $r['order'] ) {
			$r['order'] = 'DESC';
		}

		return sprintf( ' ORDER BY %s %s', $r['orderby'], $r['order'] );
	}

	/**
	 * Get the reseller IDs for a given item.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $leader_id The leader ID.
	 * @param string $resell_type The resell type.  Leave blank to query users.
	 * @param array  $query_args {
	 *     Various query arguments
	 *     @type array $date_query See {@link WP_Date_Query}.
	 *     @type string $orderby The DB column to order results by. Default: 'id'.
	 *     @type string $order The order. Either 'ASC' or 'DESC'. Default: 'DESC'.
	 * }
	 * @return array
	 */
	public static function get_resellers( $leader_id = 0, $resell_type = '', $query_args = array() ) {
		global $wpdb;

		// SQL statement.
		$sql  = self::get_select_sql( 'reseller_id' );
		$sql .= self::get_where_sql( array(
			'leader_id'   => $leader_id,
			'resell_type' => $resell_type,
		) );

		// Setup date query.
		if ( ! empty( $query_args['date_query'] ) && class_exists( 'WP_Date_Query' ) ) {
			add_filter( 'date_query_valid_columns', array( __CLASS__, 'register_date_column' ) );
			$date_query = new WP_Date_Query( $query_args['date_query'], 'date_recorded' );
			$sql .= $date_query->get_sql();
			remove_filter( 'date_query_valid_columns', array( __CLASS__, 'register_date_column' ) );
		}

		// Setup orderby query.
		$orderby = array();
		if ( ! empty( $query_args['orderby'] ) ) {
			$orderby = $query_args['orderby'];
		}
		if ( ! empty( $query_args['order'] ) ) {
			$orderby = $query_args['order'];
		}
		$sql .= self::get_orderby_sql( $orderby );

		// do the query.
		return $wpdb->get_col( $sql );
	}

	/**
	 * Get the IDs that a user is reselling.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The user ID.
	 * @param string $resell_type The resell type.  Leave blank to query users.
	 * @param array $query_args {
	 *     Various query arguments
	 *     @type array $date_query See {@link WP_Date_Query}.
	 *     @type string $orderby The DB column to order results by. Default: 'id'.
	 *     @type string $order The order. Either 'ASC' or 'DESC'. Default: 'DESC'.
	 * }
	 * @return array
	 */
	public static function get_reselling( $user_id = 0, $resell_type = '', $query_args = array() ) {
		global $wpdb;

		// SQL statement.
		$sql  = self::get_select_sql( 'leader_id' );
		$sql .= self::get_where_sql( array(
			'reseller_id' => $user_id,
			'resell_type' => $resell_type,
		) );

		// Setup date query.
		if ( ! empty( $query_args['date_query'] ) && class_exists( 'WP_Date_Query' ) ) {
			add_filter( 'date_query_valid_columns', array( __CLASS__, 'register_date_column' ) );
			$date_query = new WP_Date_Query( $query_args['date_query'], 'date_recorded' );
			$sql .= $date_query->get_sql();
			remove_filter( 'date_query_valid_columns', array( __CLASS__, 'register_date_column' ) );
		}

		// Setup orderby query.
		$orderby = array();
		if ( ! empty( $query_args['orderby'] ) ) {
			$orderby = $query_args['orderby'];
		}
		if ( ! empty( $query_args['order'] ) ) {
			$orderby = $query_args['order'];
		}
		$sql .= self::get_orderby_sql( $orderby );

		// do the query.
		return $wpdb->get_col( $sql );
	}

	/**
	 * Get the resellers count for a particular item.
	 *
	 * @since 1.3.0
	 *
	 * @param int    $leader_id   The leader ID to grab the resellers count for.
	 * @param string $resell_type The resell type. Leave blank to query for users.
	 * @return int
	 */
	public static function get_resellers_count( $leader_id = 0, $resell_type = '' ) {
		global $wpdb;

		$sql  = self::get_select_sql( 'COUNT(id)' );
		$sql .= self::get_where_sql( array(
			'leader_id'   => $leader_id,
			'resell_type' => $resell_type,
		) );

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Get the reselling count for a particular item.
	 *
	 * @since 1.3.0
	 *
	 * @param int    $id          The object ID to grab the reselling count for.
	 * @param string $resell_type The resell type. Leave blank to query for users.
	 * @return int
	 */
	public static function get_reselling_count( $id = 0, $resell_type = '' ) {
		global $wpdb;

		$sql  = self::get_select_sql( 'COUNT(id)' );
		$sql .= self::get_where_sql( array(
			'reseller_id' => $id,
			'resell_type' => $resell_type,
		) );

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Get the counts for a given item.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $id          The ID to fetch counts for.
	 * @param string $resell_type The resell type.
	 * @return array
	 */
	public static function get_counts( $id = 0, $resell_type = '' ) {
		$reselling = self::get_reselling_count( $id, $resell_type );
		$resellers = self::get_resellers_count( $id, $resell_type );

		return array(
			'resellers' => $resellers,
			'reselling' => $reselling,
		);
	}

	/**
	 * Bulk check the resell status for a user against a list of user IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $leader_ids The user IDs to check the resell status for.
	 * @param int    $user_id The user ID to check against the list of leader IDs.
	 * @param string $resell_type The type of resell connection.
	 * @return array
	 */
	public static function bulk_check_resell_status( $leader_ids = array(), $user_id = 0, $resell_type = '' ) {
		global $wpdb;

		if ( empty( $resell_type ) && empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		if ( empty( $user_id ) ) {
			return false;
		}

		// SQL statement.
		$sql  = self::get_select_sql( 'leader_id, id' );
		$sql .= self::get_where_sql( array(
			'reseller_id' => $user_id,
			'leader_id'   => (array) $leader_ids,
			'resell_type' => $resell_type,
		) );

		return $wpdb->get_results( $sql );
	}

	/**
	 * Deletes all resell relationships for a given user.
	 *
	 * @since 1.1.0
	 *
	 * @param int $user_id The user ID.
	 */
	public static function delete_all_for_user( $user_id = 0 ) {
		global $wpdb;

		$bp = $GLOBALS['bp'];

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->resell->table_name} WHERE leader_id = %d OR reseller_id = %d AND resell_type = ''", $user_id, $user_id ) );
	}

	/**
	 * Register our 'date_recorded' DB column to WP's date query columns.
	 *
	 * @since 1.3.0
	 *
	 * @param array $retval Current DB columns.
	 * @return array
	 */
	public static function register_date_column( $retval ) {
		$retval[] = 'date_recorded';

		return $retval;
	}
}
