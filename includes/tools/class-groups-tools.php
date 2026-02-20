<?php
/**
 * Groups Tools — MCP tools for BuddyBoss groups management.
 *
 * Provides 8 tools: list, get, create, update, delete,
 * list_members, add_member, remove_member.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tools\\Groups_Tools' ) ) {

	/**
	 * Groups tool provider.
	 *
	 * @since 1.0.0
	 */
	class Groups_Tools extends Tool_Base {

		/**
		 * Register all group tools.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		public function register_tools() {
			return array(
				$this->create_tool(
					'buddyboss_list_groups',
					'List BuddyBoss groups with optional filtering by status, search query, user membership, or group type. Returns paginated results.',
					array(
						'page'       => array(
							'type'        => 'integer',
							'description' => 'Page number for pagination. Default: 1.',
						),
						'per_page'   => array(
							'type'        => 'integer',
							'description' => 'Groups per page. Default: 20, max: 100.',
						),
						'search'     => array(
							'type'        => 'string',
							'description' => 'Search groups by name or description.',
						),
						'status'     => array(
							'type'        => 'string',
							'description' => 'Filter by group status. Options: public, private, hidden.',
						),
						'user_id'    => array(
							'type'        => 'integer',
							'description' => 'Only groups this user belongs to.',
						),
						'group_type' => array(
							'type'        => 'string',
							'description' => 'Filter by group type slug.',
						),
					),
					'list_groups'
				),

				$this->create_tool(
					'buddyboss_get_group',
					'Get detailed information about a specific BuddyBoss group by its ID, including name, description, status, member count, and creation date.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The group ID.',
						),
					),
					'get_group',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_create_group',
					'Create a new BuddyBoss group with a name, optional description, and privacy status.',
					array(
						'name'         => array(
							'type'        => 'string',
							'description' => 'Group name.',
						),
						'description'  => array(
							'type'        => 'string',
							'description' => 'Group description.',
						),
						'status'       => array(
							'type'        => 'string',
							'description' => 'Group privacy status. Options: public, private, hidden. Default: public.',
						),
						'enable_forum' => array(
							'type'        => 'boolean',
							'description' => 'Whether to enable a discussion forum for this group. Default: false.',
						),
						'creator_id'   => array(
							'type'        => 'integer',
							'description' => 'User ID of the group creator. Defaults to authenticated user.',
						),
					),
					'create_group',
					array( 'name' )
				),

				$this->create_tool(
					'buddyboss_update_group',
					'Update an existing BuddyBoss group. You can change the name, description, or privacy status.',
					array(
						'id'          => array(
							'type'        => 'integer',
							'description' => 'The group ID to update.',
						),
						'name'        => array(
							'type'        => 'string',
							'description' => 'New group name.',
						),
						'description' => array(
							'type'        => 'string',
							'description' => 'New group description.',
						),
						'status'      => array(
							'type'        => 'string',
							'description' => 'New group status. Options: public, private, hidden.',
						),
					),
					'update_group',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_delete_group',
					'Permanently delete a BuddyBoss group and all its data (members, activity, media). This cannot be undone.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The group ID to delete.',
						),
					),
					'delete_group',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_list_group_members',
					'List all members of a specific BuddyBoss group. Can filter by role (admin, mod, member, banned).',
					array(
						'group_id' => array(
							'type'        => 'integer',
							'description' => 'The group ID.',
						),
						'page'     => array(
							'type'        => 'integer',
							'description' => 'Page number. Default: 1.',
						),
						'per_page' => array(
							'type'        => 'integer',
							'description' => 'Members per page. Default: 20.',
						),
						'roles'    => array(
							'type'        => 'string',
							'description' => 'Filter by member role. Options: admin, mod, member, banned. Comma-separated for multiple.',
						),
					),
					'list_group_members',
					array( 'group_id' )
				),

				$this->create_tool(
					'buddyboss_add_group_member',
					'Add a user to a BuddyBoss group with a specific role (admin, mod, or member).',
					array(
						'group_id' => array(
							'type'        => 'integer',
							'description' => 'The group ID.',
						),
						'user_id'  => array(
							'type'        => 'integer',
							'description' => 'The user ID to add.',
						),
						'role'     => array(
							'type'        => 'string',
							'description' => 'Member role. Options: admin, mod, member. Default: member.',
						),
					),
					'add_group_member',
					array( 'group_id', 'user_id' )
				),

				$this->create_tool(
					'buddyboss_remove_group_member',
					'Remove a user from a BuddyBoss group.',
					array(
						'group_id' => array(
							'type'        => 'integer',
							'description' => 'The group ID.',
						),
						'user_id'  => array(
							'type'        => 'integer',
							'description' => 'The user ID to remove.',
						),
					),
					'remove_group_member',
					array( 'group_id', 'user_id' )
				),
			);
		}

		/**
		 * List groups.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_groups( $args, $user_id ) {
			$params = array(
				'page'     => $this->get_int( $args, 'page', 1 ),
				'per_page' => min( $this->get_int( $args, 'per_page', 20 ), 100 ),
			);

			$search = $this->get_string( $args, 'search' );
			if ( ! empty( $search ) ) {
				$params['search'] = $search;
			}

			$status = $this->get_string( $args, 'status' );
			if ( ! empty( $status ) ) {
				$params['status'] = $status;
			}

			$group_user_id = $this->get_int( $args, 'user_id' );
			if ( $group_user_id > 0 ) {
				$params['user_id'] = $group_user_id;
			}

			$group_type = $this->get_string( $args, 'group_type' );
			if ( ! empty( $group_type ) ) {
				$params['group_type'] = $group_type;
			}

			$response = $this->rest_client->get( '/buddyboss/v1/groups', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Get a single group.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function get_group( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$group_id = absint( $args['id'] );
			$response = $this->rest_client->get( '/buddyboss/v1/groups/' . $group_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Create a group.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function create_group( $args, $user_id ) {
			$this->validate_required( $args, array( 'name' ) );

			$params = array(
				'name' => sanitize_text_field( $args['name'] ),
			);

			$description = $this->get_string( $args, 'description' );
			if ( ! empty( $description ) ) {
				$params['description'] = wp_kses_post( $args['description'] );
			}

			$status = $this->get_string( $args, 'status' );
			if ( ! empty( $status ) && in_array( $status, array( 'public', 'private', 'hidden' ), true ) ) {
				$params['status'] = $status;
			}

			if ( isset( $args['enable_forum'] ) ) {
				$params['enable_forum'] = $this->get_bool( $args, 'enable_forum' );
			}

			// BuddyBoss requires explicit creator_id for group creation.
			$creator_id            = $this->get_int( $args, 'creator_id' );
			$params['creator_id'] = $creator_id > 0 ? $creator_id : $user_id;

			$response = $this->rest_client->post( '/buddyboss/v1/groups', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Update a group.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function update_group( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$group_id = absint( $args['id'] );
			$params   = array();

			$name = $this->get_string( $args, 'name' );
			if ( ! empty( $name ) ) {
				$params['name'] = $name;
			}

			if ( isset( $args['description'] ) ) {
				$params['description'] = wp_kses_post( $args['description'] );
			}

			$status = $this->get_string( $args, 'status' );
			if ( ! empty( $status ) && in_array( $status, array( 'public', 'private', 'hidden' ), true ) ) {
				$params['status'] = $status;
			}

			$response = $this->rest_client->put( '/buddyboss/v1/groups/' . $group_id, $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Delete a group.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function delete_group( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$group_id = absint( $args['id'] );
			$response = $this->rest_client->delete( '/buddyboss/v1/groups/' . $group_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * List group members.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_group_members( $args, $user_id ) {
			$this->validate_required( $args, array( 'group_id' ) );

			$group_id = absint( $args['group_id'] );
			$params   = array(
				'page'     => $this->get_int( $args, 'page', 1 ),
				'per_page' => min( $this->get_int( $args, 'per_page', 20 ), 100 ),
			);

			$roles = $this->get_string( $args, 'roles' );
			// BuddyBoss requires explicit roles to return members.
			$params['roles'] = ! empty( $roles ) ? $roles : 'admin,mod,member';

			$response = $this->rest_client->get(
				'/buddyboss/v1/groups/' . $group_id . '/members',
				$params,
				$user_id
			);

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Add a member to a group.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function add_group_member( $args, $user_id ) {
			$this->validate_required( $args, array( 'group_id', 'user_id' ) );

			$group_id = absint( $args['group_id'] );
			$params   = array(
				'user_id' => absint( $args['user_id'] ),
			);

			$role = $this->get_string( $args, 'role' );
			if ( ! empty( $role ) && in_array( $role, array( 'admin', 'mod', 'member' ), true ) ) {
				$params['role'] = $role;
			}

			$response = $this->rest_client->put(
				'/buddyboss/v1/groups/' . $group_id . '/members/' . $params['user_id'],
				$params,
				$user_id
			);

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Remove a member from a group.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function remove_group_member( $args, $user_id ) {
			$this->validate_required( $args, array( 'group_id', 'user_id' ) );

			$group_id   = absint( $args['group_id'] );
			$member_id  = absint( $args['user_id'] );

			$response = $this->rest_client->delete(
				'/buddyboss/v1/groups/' . $group_id . '/members/' . $member_id,
				array(),
				$user_id
			);

			return $this->rest_client->format_response( $response );
		}
	}
}
