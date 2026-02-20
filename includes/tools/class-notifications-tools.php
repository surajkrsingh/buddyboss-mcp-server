<?php
/**
 * Notifications Tools — MCP tools for BuddyBoss notifications management.
 *
 * Provides 4 tools: list, get, mark_read, delete.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tools\\Notifications_Tools' ) ) {

	/**
	 * Notifications tool provider.
	 *
	 * @since 1.0.0
	 */
	class Notifications_Tools extends Tool_Base {

		/**
		 * Register all notification tools.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		public function register_tools() {
			return array(
				$this->create_tool(
					'buddyboss_list_notifications',
					'List notifications for a user with optional filtering by read status or component. Returns paginated results.',
					array(
						'user_id'        => array(
							'type'        => 'integer',
							'description' => 'Filter by user ID.',
						),
						'is_new'         => array(
							'type'        => 'boolean',
							'description' => 'If true, only unread notifications.',
						),
						'component_name' => array(
							'type'        => 'string',
							'description' => 'Filter by component name (e.g., activity, groups, friends, messages).',
						),
						'page'           => array(
							'type'        => 'integer',
							'description' => 'Page number for pagination. Default: 1.',
						),
						'per_page'       => array(
							'type'        => 'integer',
							'description' => 'Notifications per page. Default: 20, max: 100.',
						),
					),
					'list_notifications'
				),

				$this->create_tool(
					'buddyboss_get_notification',
					'Get detailed information about a specific notification by its ID.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The notification ID.',
						),
					),
					'get_notification',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_mark_notification_read',
					'Mark a notification as read or unread.',
					array(
						'id'     => array(
							'type'        => 'integer',
							'description' => 'The notification ID.',
						),
						'is_new' => array(
							'type'        => 'boolean',
							'description' => 'Set to false for read, true for unread.',
						),
					),
					'mark_notification_read',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_delete_notification',
					'Permanently delete a notification. This cannot be undone.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The notification ID to delete.',
						),
					),
					'delete_notification',
					array( 'id' )
				),
			);
		}

		/**
		 * List notifications.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_notifications( $args, $user_id ) {
			$params = array(
				'page'     => $this->get_int( $args, 'page', 1 ),
				'per_page' => min( $this->get_int( $args, 'per_page', 20 ), 100 ),
			);

			$notif_user_id = $this->get_int( $args, 'user_id' );
			if ( $notif_user_id > 0 ) {
				$params['user_id'] = $notif_user_id;
			}

			if ( isset( $args['is_new'] ) ) {
				$params['is_new'] = $this->get_bool( $args, 'is_new' );
			}

			$component_name = $this->get_string( $args, 'component_name' );
			if ( ! empty( $component_name ) ) {
				$params['component_name'] = $component_name;
			}

			$response = $this->rest_client->get( '/buddyboss/v1/notifications', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Get a single notification.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function get_notification( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$notification_id = absint( $args['id'] );
			$response        = $this->rest_client->get( '/buddyboss/v1/notifications/' . $notification_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Mark a notification as read or unread.
		 *
		 * Defaults to marking as read (`is_new = false`) when the
		 * `is_new` parameter is not explicitly provided.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function mark_notification_read( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$notification_id = absint( $args['id'] );
			$params          = array();

			if ( isset( $args['is_new'] ) ) {
				$params['is_new'] = $this->get_bool( $args, 'is_new' );
			} else {
				$params['is_new'] = false;
			}

			$response = $this->rest_client->put( '/buddyboss/v1/notifications/' . $notification_id, $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Delete a notification.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function delete_notification( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$notification_id = absint( $args['id'] );
			$response        = $this->rest_client->delete( '/buddyboss/v1/notifications/' . $notification_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}
	}
}
