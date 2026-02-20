<?php
/**
 * Activity Tools — MCP tools for BuddyBoss activity management.
 *
 * Provides 6 tools: list, get, create, update, delete, favorite.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tools\\Activity_Tools' ) ) {

	/**
	 * Activity tool provider.
	 *
	 * @since 1.0.0
	 */
	class Activity_Tools extends Tool_Base {

		/**
		 * Register all activity tools.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		public function register_tools() {
			return array(
				$this->create_tool(
					'buddyboss_list_activities',
					'List activity feed with optional filtering by user, group, component, type, or scope. Returns paginated results.',
					array(
						'page'             => array(
							'type'        => 'integer',
							'description' => 'Page number for pagination. Default: 1.',
						),
						'per_page'         => array(
							'type'        => 'integer',
							'description' => 'Activities per page. Default: 20, max: 100.',
						),
						'search'           => array(
							'type'        => 'string',
							'description' => 'Search activities by content.',
						),
						'user_id'          => array(
							'type'        => 'integer',
							'description' => 'Filter by user ID.',
						),
						'group_id'         => array(
							'type'        => 'integer',
							'description' => 'Filter by group ID.',
						),
						'component'        => array(
							'type'        => 'string',
							'description' => 'Filter by component (e.g., activity, groups, friends).',
						),
						'type'             => array(
							'type'        => 'string',
							'description' => 'Filter by activity type (e.g., activity_update, activity_comment).',
						),
						'scope'            => array(
							'type'        => 'string',
							'description' => 'Activity scope. Options: just-me, friends, groups, favorites.',
						),
						'display_comments' => array(
							'type'        => 'string',
							'description' => 'How to display comments. Options: threaded, stream, false.',
						),
					),
					'list_activities'
				),

				$this->create_tool(
					'buddyboss_get_activity',
					'Get detailed information about a specific activity post by its ID.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The activity ID.',
						),
					),
					'get_activity',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_create_activity',
					'Create a new activity post. Can be a user activity update or a group activity post.',
					array(
						'content'         => array(
							'type'        => 'string',
							'description' => 'The activity content/text.',
						),
						'user_id'         => array(
							'type'        => 'integer',
							'description' => 'User ID of the activity author. Defaults to authenticated user.',
						),
						'component'       => array(
							'type'        => 'string',
							'description' => 'Activity component. Options: activity, groups. Default: activity.',
						),
						'type'            => array(
							'type'        => 'string',
							'description' => 'Activity type. Options: activity_update, activity_comment. Default: activity_update.',
						),
						'primary_item_id' => array(
							'type'        => 'integer',
							'description' => 'Primary item ID (e.g., group_id for group activity).',
						),
					),
					'create_activity',
					array( 'content' )
				),

				$this->create_tool(
					'buddyboss_update_activity',
					'Update the content of an existing activity post.',
					array(
						'id'      => array(
							'type'        => 'integer',
							'description' => 'The activity ID to update.',
						),
						'content' => array(
							'type'        => 'string',
							'description' => 'New activity content.',
						),
					),
					'update_activity',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_delete_activity',
					'Permanently delete an activity post. This cannot be undone.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The activity ID to delete.',
						),
					),
					'delete_activity',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_favorite_activity',
					'Toggle favorite status on an activity post. If already favorited, it will be unfavorited.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The activity ID to favorite/unfavorite.',
						),
					),
					'favorite_activity',
					array( 'id' )
				),
			);
		}

		/**
		 * List activities.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_activities( $args, $user_id ) {
			$params = array(
				'page'     => $this->get_int( $args, 'page', 1 ),
				'per_page' => min( $this->get_int( $args, 'per_page', 20 ), 100 ),
			);

			$search = $this->get_string( $args, 'search' );
			if ( ! empty( $search ) ) {
				$params['search'] = $search;
			}

			$activity_user_id = $this->get_int( $args, 'user_id' );
			if ( $activity_user_id > 0 ) {
				$params['user_id'] = $activity_user_id;
			}

			$group_id = $this->get_int( $args, 'group_id' );
			if ( $group_id > 0 ) {
				$params['group_id'] = $group_id;
			}

			$component = $this->get_string( $args, 'component' );
			if ( ! empty( $component ) ) {
				$params['component'] = $component;
			}

			$type = $this->get_string( $args, 'type' );
			if ( ! empty( $type ) ) {
				$params['type'] = $type;
			}

			$scope = $this->get_string( $args, 'scope' );
			if ( ! empty( $scope ) && in_array( $scope, array( 'just-me', 'friends', 'groups', 'favorites' ), true ) ) {
				$params['scope'] = $scope;
			}

			$display_comments = $this->get_string( $args, 'display_comments' );
			if ( ! empty( $display_comments ) ) {
				$params['display_comments'] = $display_comments;
			}

			$response = $this->rest_client->get( '/buddyboss/v1/activity', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Get a single activity.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function get_activity( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$activity_id = absint( $args['id'] );
			$response    = $this->rest_client->get( '/buddyboss/v1/activity/' . $activity_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Create an activity.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function create_activity( $args, $user_id ) {
			$this->validate_required( $args, array( 'content' ) );

			$params = array(
				'content' => wp_kses_post( $args['content'] ),
			);

			$activity_user_id = $this->get_int( $args, 'user_id' );
			if ( $activity_user_id > 0 ) {
				$params['user_id'] = $activity_user_id;
			}

			$component = $this->get_string( $args, 'component' );
			if ( ! empty( $component ) && in_array( $component, array( 'activity', 'groups' ), true ) ) {
				$params['component'] = $component;
			}

			$type = $this->get_string( $args, 'type' );
			if ( ! empty( $type ) && in_array( $type, array( 'activity_update', 'activity_comment' ), true ) ) {
				$params['type'] = $type;
			}

			$primary_item_id = $this->get_int( $args, 'primary_item_id' );
			if ( $primary_item_id > 0 ) {
				$params['primary_item_id'] = $primary_item_id;
			}

			$response = $this->rest_client->post( '/buddyboss/v1/activity', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Update an activity.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function update_activity( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$activity_id = absint( $args['id'] );
			$params      = array();

			if ( isset( $args['content'] ) ) {
				$params['content'] = wp_kses_post( $args['content'] );
			}

			$response = $this->rest_client->put( '/buddyboss/v1/activity/' . $activity_id, $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Delete an activity.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function delete_activity( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$activity_id = absint( $args['id'] );
			$response    = $this->rest_client->delete( '/buddyboss/v1/activity/' . $activity_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Toggle favorite on an activity.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function favorite_activity( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$activity_id = absint( $args['id'] );
			$response    = $this->rest_client->put( '/buddyboss/v1/activity/' . $activity_id . '/favorite', array(), $user_id );

			return $this->rest_client->format_response( $response );
		}
	}
}
