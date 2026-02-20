<?php
/**
 * Friends Tools — MCP tools for BuddyBoss friendships management.
 *
 * Provides 4 tools: list, add, remove, list_requests.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tools\\Friends_Tools' ) ) {

	/**
	 * Friends tool provider.
	 *
	 * @since 1.0.0
	 */
	class Friends_Tools extends Tool_Base {

		/**
		 * Register all friend tools.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		public function register_tools() {
			return array(
				$this->create_tool(
					'buddyboss_list_friends',
					'List friendships for a user with optional filtering by confirmation status. Returns paginated results.',
					array(
						'user_id'      => array(
							'type'        => 'integer',
							'description' => 'Filter by user ID.',
						),
						'is_confirmed' => array(
							'type'        => 'boolean',
							'description' => 'Filter by confirmation status.',
						),
						'page'         => array(
							'type'        => 'integer',
							'description' => 'Page number for pagination. Default: 1.',
						),
						'per_page'     => array(
							'type'        => 'integer',
							'description' => 'Friends per page. Default: 20, max: 100.',
						),
					),
					'list_friends'
				),

				$this->create_tool(
					'buddyboss_add_friend',
					'Send a friendship request. Optionally auto-accept with the force parameter.',
					array(
						'initiator_id' => array(
							'type'        => 'integer',
							'description' => 'User ID of the person sending the request.',
						),
						'friend_id'    => array(
							'type'        => 'integer',
							'description' => 'User ID of the person receiving the request.',
						),
						'force'        => array(
							'type'        => 'boolean',
							'description' => 'If true, auto-accept the friendship. Default: false.',
						),
					),
					'add_friend',
					array( 'initiator_id', 'friend_id' )
				),

				$this->create_tool(
					'buddyboss_remove_friend',
					'Remove an existing friendship by its friendship ID.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The friendship ID.',
						),
					),
					'remove_friend',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_list_friend_requests',
					'List pending friendship requests for a specific user.',
					array(
						'user_id'      => array(
							'type'        => 'integer',
							'description' => 'User ID to list requests for.',
						),
						'is_confirmed' => array(
							'type'        => 'boolean',
							'description' => 'Filter by confirmation status. Default: false (pending only).',
						),
					),
					'list_friend_requests',
					array( 'user_id' )
				),
			);
		}

		/**
		 * List friends.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_friends( $args, $user_id ) {
			$params = array(
				'page'     => $this->get_int( $args, 'page', 1 ),
				'per_page' => min( $this->get_int( $args, 'per_page', 20 ), 100 ),
			);

			$friend_user_id = $this->get_int( $args, 'user_id' );
			if ( $friend_user_id > 0 ) {
				$params['user_id'] = $friend_user_id;
			}

			if ( isset( $args['is_confirmed'] ) ) {
				$params['is_confirmed'] = $this->get_bool( $args, 'is_confirmed' );
			}

			$response = $this->rest_client->get( '/buddyboss/v1/friends', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Add a friend (send friendship request).
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function add_friend( $args, $user_id ) {
			$this->validate_required( $args, array( 'initiator_id', 'friend_id' ) );

			$params = array(
				'initiator_id' => absint( $args['initiator_id'] ),
				'friend_id'    => absint( $args['friend_id'] ),
			);

			if ( isset( $args['force'] ) ) {
				$params['force'] = $this->get_bool( $args, 'force' );
			}

			$response = $this->rest_client->post( '/buddyboss/v1/friends', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Remove a friendship.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function remove_friend( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$friendship_id = absint( $args['id'] );
			$response      = $this->rest_client->delete( '/buddyboss/v1/friends/' . $friendship_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * List pending friend requests.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_friend_requests( $args, $user_id ) {
			$this->validate_required( $args, array( 'user_id' ) );

			$params = array(
				'user_id'      => absint( $args['user_id'] ),
				'is_confirmed' => isset( $args['is_confirmed'] ) ? $this->get_bool( $args, 'is_confirmed' ) : false,
			);

			$response = $this->rest_client->get( '/buddyboss/v1/friends', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}
	}
}
