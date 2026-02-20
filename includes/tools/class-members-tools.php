<?php
/**
 * Members Tools — MCP tools for BuddyBoss members management.
 *
 * Provides 5 tools: list, get, create, update, delete.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tools\\Members_Tools' ) ) {

	/**
	 * Members tool provider.
	 *
	 * @since 1.0.0
	 */
	class Members_Tools extends Tool_Base {

		/**
		 * Register all member tools.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		public function register_tools() {
			return array(
				$this->create_tool(
					'buddyboss_list_members',
					'List BuddyBoss members with optional filtering by role, member type, or search query. Returns paginated results.',
					array(
						'page'        => array(
							'type'        => 'integer',
							'description' => 'Page number for pagination. Default: 1.',
						),
						'per_page'    => array(
							'type'        => 'integer',
							'description' => 'Members per page. Default: 20, max: 100.',
						),
						'search'      => array(
							'type'        => 'string',
							'description' => 'Search members by name.',
						),
						'role'        => array(
							'type'        => 'string',
							'description' => 'Filter by WordPress role (e.g., administrator, subscriber).',
						),
						'member_type' => array(
							'type'        => 'string',
							'description' => 'Filter by BuddyBoss member type slug.',
						),
						'exclude'     => array(
							'type'        => 'string',
							'description' => 'Comma-separated user IDs to exclude.',
						),
					),
					'list_members'
				),

				$this->create_tool(
					'buddyboss_get_member',
					'Get detailed profile information about a specific BuddyBoss member by their user ID.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The member/user ID.',
						),
					),
					'get_member',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_create_member',
					'Create a new BuddyBoss member account with username, email, and password.',
					array(
						'username' => array(
							'type'        => 'string',
							'description' => 'Login username.',
						),
						'email'    => array(
							'type'        => 'string',
							'description' => 'Email address.',
						),
						'password' => array(
							'type'        => 'string',
							'description' => 'Account password.',
						),
						'name'     => array(
							'type'        => 'string',
							'description' => 'Display name.',
						),
					),
					'create_member',
					array( 'username', 'email', 'password' )
				),

				$this->create_tool(
					'buddyboss_update_member',
					'Update an existing BuddyBoss member profile. Can change name, email, or member type.',
					array(
						'id'          => array(
							'type'        => 'integer',
							'description' => 'The member/user ID to update.',
						),
						'name'        => array(
							'type'        => 'string',
							'description' => 'New display name.',
						),
						'email'       => array(
							'type'        => 'string',
							'description' => 'New email address.',
						),
						'member_type' => array(
							'type'        => 'string',
							'description' => 'New BuddyBoss member type slug.',
						),
					),
					'update_member',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_delete_member',
					'Permanently delete a BuddyBoss member account. Optionally reassign their posts to another user. This cannot be undone.',
					array(
						'id'       => array(
							'type'        => 'integer',
							'description' => 'The member/user ID to delete.',
						),
						'reassign' => array(
							'type'        => 'integer',
							'description' => 'User ID to reassign the deleted member\'s posts to.',
						),
					),
					'delete_member',
					array( 'id' )
				),
			);
		}

		/**
		 * List members.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_members( $args, $user_id ) {
			$params = array(
				'page'     => $this->get_int( $args, 'page', 1 ),
				'per_page' => min( $this->get_int( $args, 'per_page', 20 ), 100 ),
			);

			$search = $this->get_string( $args, 'search' );
			if ( ! empty( $search ) ) {
				$params['search'] = $search;
			}

			$role = $this->get_string( $args, 'role' );
			if ( ! empty( $role ) ) {
				$params['role'] = $role;
			}

			$member_type = $this->get_string( $args, 'member_type' );
			if ( ! empty( $member_type ) ) {
				$params['member_type'] = $member_type;
			}

			$exclude = $this->get_string( $args, 'exclude' );
			if ( ! empty( $exclude ) ) {
				$params['exclude'] = $exclude;
			}

			$response = $this->rest_client->get( '/buddyboss/v1/members', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Get a single member.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function get_member( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$member_id = absint( $args['id'] );
			$response  = $this->rest_client->get( '/buddyboss/v1/members/' . $member_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Create a member.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function create_member( $args, $user_id ) {
			$this->validate_required( $args, array( 'username', 'email', 'password' ) );

			$params = array(
				'user_login' => sanitize_user( $args['username'] ),
				'email'      => sanitize_email( $args['email'] ),
				'password'   => $args['password'],
			);

			$name = $this->get_string( $args, 'name' );
			if ( ! empty( $name ) ) {
				$params['name'] = $name;
			}

			$response = $this->rest_client->post( '/buddyboss/v1/members', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Update a member.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function update_member( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$member_id = absint( $args['id'] );
			$params    = array();

			$name = $this->get_string( $args, 'name' );
			if ( ! empty( $name ) ) {
				$params['name'] = $name;
			}

			$email = $this->get_string( $args, 'email' );
			if ( ! empty( $email ) ) {
				$params['email'] = sanitize_email( $email );
			}

			$member_type = $this->get_string( $args, 'member_type' );
			if ( ! empty( $member_type ) ) {
				$params['member_type'] = $member_type;
			}

			$response = $this->rest_client->put( '/buddyboss/v1/members/' . $member_id, $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Delete a member.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function delete_member( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$member_id = absint( $args['id'] );
			$params    = array();

			$reassign = $this->get_int( $args, 'reassign' );
			if ( $reassign > 0 ) {
				$params['reassign'] = $reassign;
			}

			$response = $this->rest_client->delete( '/buddyboss/v1/members/' . $member_id, $params, $user_id );

			return $this->rest_client->format_response( $response );
		}
	}
}
