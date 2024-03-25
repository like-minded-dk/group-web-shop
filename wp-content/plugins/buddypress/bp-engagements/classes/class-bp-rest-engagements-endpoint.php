<?php
/**
 * BP REST: BP_REST_Engagements_Endpoint class
 *
 * @package BuddyPress
 * @since 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * engagementship endpoints.
 *
 * /engagements/
 * /engagements/{id}
 *
 * @since 6.0.0
 */
class BP_REST_Engagements_Endpoint extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->namespace = bp_rest_namespace() . '/' . bp_rest_version();
		$this->rest_base = buddypress()->engagements->id;
	}

	/**
	 * Register the component routes.
	 *
	 * @since 6.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\w-]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Numeric identifier of a user ID.', 'buddypress' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Retrieve engagementships.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$args = array(
			'id'                => $request->get_param( 'id' ),
			'initiator_user_id' => $request->get_param( 'initiator_id' ),
			'receiver_user_id'    => $request->get_param( 'engagement_id' ),
			'is_confirmed'      => $request->get_param( 'is_confirmed' ),
			'order_by'          => $request->get_param( 'order_by' ),
			'sort_order'        => strtoupper( $request->get_param( 'order' ) ),
			'page'              => $request->get_param( 'page' ),
			'per_page'          => $request->get_param( 'per_page' ),
		);

		/**
		 * Filter the query arguments for the request.
		 *
		 * @since 6.0.0
		 *
		 * @param array           $args    Key value array of query var to query value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		$args = apply_filters( 'bp_rest_engagements_get_items_query_args', $args, $request );

		// null is the default values.
		foreach ( $args as $key => $value ) {
			if ( empty( $value ) ) {
				$args[ $key ] = null;
			}
		}

		// Check if user is valid.
		$user = get_user_by( 'id', $request->get_param( 'user_id' ) );
		if ( ! $user instanceof WP_User ) {
			return new WP_Error(
				'bp_rest_engagements_get_items_user_failed',
				__( 'There was a problem confirming if user is valid.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		// Actually, query it.
		$engagementships = BP_Engagements_Engagementship::get_relationships( $user->ID, $args );

		$retval = array();
		foreach ( (array) $engagementships as $engagementship ) {
			$retval[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $engagementship, $request )
			);
		}

		$response = rest_ensure_response( $retval );
		$response = bp_rest_response_add_total_headers( $response, count( $engagementships ), $args['per_page'] );

		/**
		 * Fires after engagementships are fetched via the REST API.
		 *
		 * @since 6.0.0
		 *
		 * @param array            $engagementships Fetched engagementships.
		 * @param WP_REST_Response $response    The response data.
		 * @param WP_REST_Request  $request     The request sent to the API.
		 */
		do_action( 'bp_rest_engagements_get_items', $engagementships, $response, $request );

		return $response;
	}

	/**
	 * Check if a given request has access to engagementship items.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return true|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		$retval = new WP_Error(
			'bp_rest_authorization_required',
			__( 'Sorry, you need to be logged in to perform this action.', 'buddypress' ),
			array(
				'status' => rest_authorization_required_code(),
			)
		);

		if ( is_user_logged_in() ) {
			$retval = true;
		}

		/**
		 * Filter the engagements `get_items` permissions check.
		 *
		 * @since 6.0.0
		 *
		 * @param true|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_rest_engagements_get_items_permissions_check', $retval, $request );
	}

	/**
	 * Retrieve single engagementship.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$user = get_user_by( 'id', $request->get_param( 'id' ) );

		// Check if user is valid.
		if ( false === $user ) {
			return new WP_Error(
				'bp_rest_engagements_get_item_failed',
				__( 'There was a problem confirming if user is valid.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		// Get engagementship.
		$engagementship = $this->get_engagementship_object(
			BP_Engagements_Engagementship::get_relationship_id( bp_loggedin_user_id(), $user->ID )
		);

		if ( ! $engagementship || empty( $engagementship->id ) ) {
			return new WP_Error(
				'bp_rest_invalid_id',
				__( 'engagementship does not exist.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		$retval = array(
			$this->prepare_response_for_collection(
				$this->prepare_item_for_response( $engagementship, $request )
			),
		);

		$response = rest_ensure_response( $retval );

		/**
		 * Fires before a engagementship is retrieved via the REST API.
		 *
		 * @since 6.0.0
		 *
		 * @param BP_Engagements_Engagementship $engagementship  The engagementship object.
		 * @param WP_REST_Response      $response    The response data.
		 * @param WP_REST_Request       $request     The request sent to the API.
		 */
		do_action( 'bp_rest_engagements_get_item', $engagementship, $response, $request );

		return $response;
	}

	/**
	 * Check if a given request has access to get a engagementship.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return true|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$retval = new WP_Error(
			'bp_rest_authorization_required',
			__( 'Sorry, you need to be logged in to perform this action.', 'buddypress' ),
			array(
				'status' => rest_authorization_required_code(),
			)
		);

		if ( is_user_logged_in() ) {
			$retval = true;
		}

		/**
		 * Filter the engagementship `get_item` permissions check.
		 *
		 * @since 6.0.0
		 *
		 * @param true|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_rest_engagements_get_item_permissions_check', $retval, $request );
	}

	/**
	 * Create a new engagementship.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$initiator_id = get_user_by( 'id', $request->get_param( 'initiator_id' ) );
		$engagement_id    = get_user_by( 'id', $request->get_param( 'engagement_id' ) );

		// Check if users are valid.
		if ( ! $initiator_id || ! $engagement_id ) {
			return new WP_Error(
				'bp_rest_engagements_create_item_failed',
				__( 'There was a problem confirming if user is valid.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		// Check if users are engagements or if there is a engagementship request.
		if ( 'not_engagements' !== engagements_check_engagementship_status( $initiator_id->ID, $engagement_id->ID ) ) {
			return new WP_Error(
				'bp_rest_engagements_create_item_failed',
				__( 'Those users are already engagements or have sent engagementship request(s) recently.', 'buddypress' ),
				array(
					'status' => 500,
				)
			);
		}

		$is_moderator    = bp_current_user_can( 'bp_moderate' );
		$current_user_id = bp_loggedin_user_id();

		/**
		 * - Only admins can create engagementship requests for other people.
		 * - Admins can't create engagementship requests to themselves from other people.
		 * - Users can't create engagementship requests to themselves from other people.
		 */
		if (
			( $current_user_id !== $initiator_id->ID && ! $is_moderator )
			|| ( $current_user_id === $engagement_id->ID && $is_moderator )
			|| ( ! in_array( $current_user_id, array( $initiator_id->ID, $engagement_id->ID ), true ) && ! $is_moderator )
		) {
			return new WP_Error(
				'bp_rest_engagements_create_item_failed',
				__( 'You are not allowed to perform this action.', 'buddypress' ),
				array(
					'status' => 403,
				)
			);
		}

		// Only admins can force a engagementship request.
		$force = ( true === $request->get_param( 'force' ) && $is_moderator );

		// Adding engagementship.
		if ( ! engagements_add_engagement( $initiator_id->ID, $engagement_id->ID, $force ) ) {
			return new WP_Error(
				'bp_rest_engagements_create_item_failed',
				__( 'There was an error trying to create the engagementship.', 'buddypress' ),
				array(
					'status' => 500,
				)
			);
		}

		// Get engagementship.
		$engagementship = $this->get_engagementship_object(
			BP_Engagements_Engagementship::get_relationship_id( $initiator_id->ID, $engagement_id->ID )
		);

		if ( ! $engagementship || empty( $engagementship->id ) ) {
			return new WP_Error(
				'bp_rest_invalid_id',
				__( 'engagementship does not exist.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		$retval = array(
			$this->prepare_response_for_collection(
				$this->prepare_item_for_response( $engagementship, $request )
			),
		);

		$response = rest_ensure_response( $retval );

		/**
		 * Fires after a engagementship is created via the REST API.
		 *
		 * @since 6.0.0
		 *
		 * @param BP_Engagements_Engagementship $engagementship The engagementship object.
		 * @param WP_REST_Response      $retval     The response data.
		 * @param WP_REST_Request       $request    The request sent to the API.
		 */
		do_action( 'bp_rest_engagements_create_item', $engagementship, $response, $request );

		return $response;
	}

	/**
	 * Check if a given request has access to create a engagementship.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		$retval = $this->get_item_permissions_check( $request );

		/**
		 * Filter the engagements `create_item` permissions check.
		 *
		 * @since 6.0.0
		 *
		 * @param true|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_rest_engagements_create_item_permissions_check', $retval, $request );
	}

	/**
	 * Update, accept, engagementship.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$user = get_user_by( 'id', $request->get_param( 'id' ) );

		// Check if user is valid.
		if ( false === $user ) {
			return new WP_Error(
				'bp_rest_engagements_update_item_failed',
				__( 'There was a problem confirming if user is valid.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		// Get engagementship.
		$engagementship = $this->get_engagementship_object(
			BP_Engagements_Engagementship::get_relationship_id( bp_loggedin_user_id(), $user->ID )
		);

		if ( ! $engagementship || empty( $engagementship->id ) ) {
			return new WP_Error(
				'bp_rest_invalid_id',
				__( 'Invalid engagementship ID.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		// Accept engagementship.
		if ( false === engagements_accept_engagement( $engagementship->id ) ) {
			return new WP_Error(
				'bp_rest_engagements_cannot_update_item',
				__( 'Could not accept engagementship.', 'buddypress' ),
				array(
					'status' => 500,
				)
			);
		}

		// Getting new, updated, engagementship object.
		$engagementship = $this->get_engagementship_object( $engagementship->id );

		$retval = array(
			$this->prepare_response_for_collection(
				$this->prepare_item_for_response( $engagementship, $request )
			),
		);

		$response = rest_ensure_response( $retval );

		/**
		 * Fires after a engagementship is updated via the REST API.
		 *
		 * @since 6.0.0
		 *
		 * @param BP_Engagements_Engagementship $engagementship engagementship object.
		 * @param WP_REST_Response      $response   The response data.
		 * @param WP_REST_Request       $request    The request sent to the API.
		 */
		do_action( 'bp_rest_engagements_update_item', $engagementship, $response, $request );

		return $response;
	}

	/**
	 * Check if a given request has access to update a engagementship.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		$retval = $this->get_item_permissions_check( $request );

		/**
		 * Filter the engagementship `update_item` permissions check.
		 *
		 * @since 6.0.0
		 *
		 * @param true|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_rest_engagements_update_item_permissions_check', $retval, $request );
	}

	/**
	 * Reject/withdraw/remove engagementship.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$user = get_user_by( 'id', $request->get_param( 'id' ) );

		// Check if user is valid.
		if ( false === $user ) {
			return new WP_Error(
				'bp_rest_engagements_delete_item_failed',
				__( 'There was a problem confirming if user is valid.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		// Get engagementship.
		$engagementship = $this->get_engagementship_object(
			BP_Engagements_Engagementship::get_relationship_id( bp_loggedin_user_id(), $user->ID )
		);

		if ( ! $engagementship || empty( $engagementship->id ) ) {
			return new WP_Error(
				'bp_rest_invalid_id',
				__( 'Invalid engagementship ID.', 'buddypress' ),
				array(
					'status' => 404,
				)
			);
		}

		$previous = $this->prepare_item_for_response( $engagementship, $request );

		// Remove a engagementship.
		if ( true === $request->get_param( 'force' ) ) {
			$deleted = engagements_remove_engagement( $engagementship->initiator_user_id, $engagementship->receiver_user_id );
		} else {

			/**
			 * If this change is being initiated by the initiator,
			 * use the `reject` function.
			 *
			 * This is the user who requested the engagementship, and is doing the withdrawing.
			 */
			if ( bp_loggedin_user_id() === $engagementship->initiator_user_id ) {
				$deleted = engagements_withdraw_engagement( $engagementship->initiator_user_id, $engagementship->receiver_user_id );
			} else {
				/**
				 * Otherwise, this change is being initiated by the user, engagement,
				 * who received the engagementship reject.
				 */
				$deleted = engagements_reject_engagement( $engagementship->id );
			}
		}

		if ( false === $deleted ) {
			return new WP_Error(
				'bp_rest_engagements_cannot_delete_item',
				__( 'Could not delete engagementship.', 'buddypress' ),
				array(
					'status' => 500,
				)
			);
		}

		// Build the response.
		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $previous->get_data(),
			)
		);

		/**
		 * Fires after a engagementship is deleted via the REST API.
		 *
		 * @since 6.0.0
		 *
		 * @param BP_Engagements_Engagementship $engagementship engagementship object.
		 * @param WP_REST_Response      $response   The response data.
		 * @param WP_REST_Request       $request    The request sent to the API.
		 */
		do_action( 'bp_rest_engagements_delete_item', $engagementship, $response, $request );

		return $response;
	}

	/**
	 * Check if a given request has access to delete a engagementship.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$retval = $this->get_item_permissions_check( $request );

		/**
		 * Filter the engagementship `delete_item` permissions check.
		 *
		 * @since 6.0.0
		 *
		 * @param true|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_rest_engagements_delete_item_permissions_check', $retval, $request );
	}

	/**
	 * Prepares engagementship data to return as an object.
	 *
	 * @since 6.0.0
	 *
	 * @param BP_Engagements_Engagementship $engagementship engagementship object.
	 * @param WP_REST_Request       $request    Full details about the request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $engagementship, $request ) {
		$data = array(
			'id'               => (int) $engagementship->id,
			'initiator_id'     => (int) $engagementship->initiator_user_id,
			'engagement_id'        => (int) $engagementship->receiver_user_id,
			'is_confirmed'     => (bool) $engagementship->is_confirmed,
			'date_created'     => bp_rest_prepare_date_response( $engagementship->date_created, get_date_from_gmt( $engagementship->date_created ) ),
			'date_created_gmt' => bp_rest_prepare_date_response( $engagementship->date_created ),
		);

		$context  = ! empty( $request->get_param( 'context' ) ) ? $request->get_param( 'context' ) : 'view';
		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );

		// Add prepare links.
		$response->add_links( $this->prepare_links( $engagementship ) );

		/**
		 * Filter a engagementship value returned from the API.
		 *
		 * @since 6.0.0
		 *
		 * @param WP_REST_Response      $response   Response generated by the request.
		 * @param WP_REST_Request       $request    Request used to generate the response.
		 * @param BP_Engagements_Engagementship $engagementship The engagementship object.
		 */
		return apply_filters( 'bp_rest_engagements_prepare_value', $response, $request, $engagementship );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 6.0.0
	 *
	 * @param BP_Engagements_Engagementship $engagementship engagementship object.
	 * @return array
	 */
	protected function prepare_links( $engagementship ) {
		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self'       => array(
				'href' => rest_url( $base . $engagementship->id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'initiator'  => array(
				'href'       => bp_rest_get_object_url( $engagementship->initiator_user_id, 'members' ),
				'embeddable' => true,
			),
			'engagement'     => array(
				'href'       => bp_rest_get_object_url( $engagementship->receiver_user_id, 'members' ),
				'embeddable' => true,
			),
		);

		/**
		 * Filter links prepared for the REST response.
		 *
		 * @since 5.0.0
		 *
		 * @param array                 $links      The prepared links of the REST response.
		 * @param BP_Engagements_Engagementship $engagementship engagementship object.
		 */
		return apply_filters( 'bp_rest_engagements_prepare_links', $links, $engagementship );
	}

	/**
	 * Get engagementship object.
	 *
	 * @since 6.0.0
	 *
	 * @param int $engagementship_id engagementship ID.
	 * @return BP_Engagements_Engagementship
	 */
	public function get_engagementship_object( $engagementship_id ) {
		return new BP_Engagements_Engagementship( (int) $engagementship_id );
	}

	/**
	 * Edit some arguments for the endpoint's methods.
	 *
	 * @since 6.0.0
	 *
	 * @param string $method Optional. HTTP method of the request.
	 * @return array Endpoint arguments.
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		$args    = parent::get_endpoint_args_for_item_schema( $method );
		$context = 'view';

		$args['id']['required']    = true;
		$args['id']['description'] = __( 'A unique numeric ID of a user.', 'buddypress' );

		if ( WP_REST_Server::EDITABLE === $method ) {
			$key = 'update_item';

			unset( $args['initiator_id'] );
			unset( $args['engagement_id'] );
		} elseif ( WP_REST_Server::CREATABLE === $method ) {
			$key = 'create_item';

			// Remove the ID for POST requests since it is not available.
			unset( $args['id'] );

			// Those fields are required.
			$args['initiator_id']['required'] = true;
			$args['engagement_id']['required']    = true;

			// This one is optional.
			$args['force'] = array(
				'description'       => __( 'Whether to force the engagementship agreement.', 'buddypress' ),
				'default'           => false,
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'validate_callback' => 'rest_validate_request_arg',
			);

		} elseif ( WP_REST_Server::DELETABLE === $method ) {
			$key = 'delete_item';

			// This one is optional.
			$args['force'] = array(
				'description'       => __( 'Whether to force engagementship removal.', 'buddypress' ),
				'default'           => false,
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'validate_callback' => 'rest_validate_request_arg',
			);

			unset( $args['initiator_id'] );
			unset( $args['engagement_id'] );
		} elseif ( WP_REST_Server::READABLE === $method ) {
			$key = 'get_item';

			$args['id']['required'] = true;

			// Removing those args from the GET request.
			unset( $args['initiator_id'] );
			unset( $args['engagement_id'] );
		}

		if ( 'get_item' !== $key ) {
			$context = 'edit';
		}

		$args = array_merge(
			array(
				'context' => $this->get_context_param(
					array(
						'default' => $context,
					)
				),
			),
			$args
		);

		/**
		 * Filters the method query arguments.
		 *
		 * @since 6.0.0
		 *
		 * @param array  $args   Query arguments.
		 * @param string $method HTTP method of the request.
		 */
		return apply_filters( "bp_rest_engagements_{$key}_query_arguments", $args, $method );
	}

	/**
	 * Get the engagements schema, conforming to JSON Schema.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	public function get_item_schema() {
		if ( is_null( $this->schema ) ) {
			$this->schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'bp_engagements',
				'type'       => 'object',
				'properties' => array(
					'id'               => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'Unique numeric identifier of the engagementship.', 'buddypress' ),
						'type'        => 'integer',
					),
					'initiator_id'     => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'The unique numeric identifier of the user who is requesting the engagementship.', 'buddypress' ),
						'type'        => 'integer',
					),
					'engagement_id'        => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'The unique numeric identifier of the user who is invited to agree to the engagementship request.', 'buddypress' ),
						'type'        => 'integer',
					),
					'is_confirmed'     => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'Whether the engagementship been confirmed/accepted.', 'buddypress' ),
						'readonly'    => true,
						'type'        => 'boolean',
					),
					'date_created'     => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'The date the engagementship was created, in the site\'s timezone.', 'buddypress' ),
						'readonly'    => true,
						'type'        => array( 'string', 'null' ),
						'format'      => 'date-time',
					),
					'date_created_gmt' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'The date the engagementship was created, as GMT.', 'buddypress' ),
						'readonly'    => true,
						'type'        => array( 'string', 'null' ),
						'format'      => 'date-time',
					),
				),
			);
		}

		/**
		 * Filters the engagements schema.
		 *
		 * @since 6.0.0
		 *
		 * @param array $schema The endpoint schema.
		 */
		return apply_filters( 'bp_rest_engagements_schema', $this->add_additional_fields_schema( $this->schema ) );
	}

	/**
	 * Get the query params for engagements collections.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		unset( $params['search'] );

		$params['user_id'] = array(
			'description'       => __( 'ID of the member whose engagementships are being retrieved.', 'buddypress' ),
			'default'           => bp_loggedin_user_id(),
			'type'              => 'integer',
			'required'          => true,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['is_confirmed'] = array(
			'description'       => __( 'Wether the engagementship has been accepted.', 'buddypress' ),
			'default'           => 0,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['id'] = array(
			'description'       => __( 'Unique numeric identifier of the engagementship.', 'buddypress' ),
			'default'           => 0,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['initiator_id'] = array(
			'description'       => __( 'The ID of the user who is requesting the engagementship.', 'buddypress' ),
			'default'           => 0,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['engagement_id'] = array(
			'description'       => __( 'The ID of the user who is invited to agree to the engagementship request.', 'buddypress' ),
			'default'           => 0,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['order_by'] = array(
			'description'       => __( 'Column name to order the results by.', 'buddypress' ),
			'default'           => 'date_created',
			'type'              => 'string',
			'enum'              => array( 'date_created', 'initiator_user_id', 'receiver_user_id', 'id' ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['order'] = array(
			'description'       => __( 'Order results ascending or descending.', 'buddypress' ),
			'default'           => 'desc',
			'type'              => 'string',
			'enum'              => array( 'asc', 'desc' ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		/**
		 * Filters the collection query params.
		 *
		 * @param array $params Query params.
		 */
		return apply_filters( 'bp_rest_engagements_collection_params', $params );
	}
}
