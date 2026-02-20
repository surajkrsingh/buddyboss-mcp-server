<?php
/**
 * Messages Tools — MCP tools for BuddyBoss messages management.
 *
 * Provides 5 tools: list_threads, get_thread, send, delete_thread, mark_read.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tools\\Messages_Tools' ) ) {

	/**
	 * Messages tool provider.
	 *
	 * @since 1.0.0
	 */
	class Messages_Tools extends Tool_Base {

		/**
		 * Register all message tools.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		public function register_tools() {
			return array(
				$this->create_tool(
					'buddyboss_list_message_threads',
					'List message threads for the authenticated user. Can filter by mailbox (inbox, sentbox, starred) and read status.',
					array(
						'user_id'  => array(
							'type'        => 'integer',
							'description' => 'Filter by user ID.',
						),
						'box'      => array(
							'type'        => 'string',
							'description' => 'Mailbox to list. Options: inbox, sentbox, starred.',
						),
						'type'     => array(
							'type'        => 'string',
							'description' => 'Filter type. Options: all, read, unread.',
						),
						'page'     => array(
							'type'        => 'integer',
							'description' => 'Page number for pagination. Default: 1.',
						),
						'per_page' => array(
							'type'        => 'integer',
							'description' => 'Threads per page. Default: 20, max: 100.',
						),
					),
					'list_message_threads'
				),

				$this->create_tool(
					'buddyboss_get_message_thread',
					'Get a specific message thread with all its messages by thread ID.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The thread ID.',
						),
					),
					'get_message_thread',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_send_message',
					'Send a new message to one or more recipients. Creates a new thread or adds to an existing one.',
					array(
						'recipients' => array(
							'type'        => 'string',
							'description' => 'Comma-separated user IDs to send the message to.',
						),
						'subject'    => array(
							'type'        => 'string',
							'description' => 'Message subject.',
						),
						'message'    => array(
							'type'        => 'string',
							'description' => 'Message body text.',
						),
						'sender_id'  => array(
							'type'        => 'integer',
							'description' => 'Sender user ID. Defaults to authenticated user.',
						),
					),
					'send_message',
					array( 'recipients', 'subject', 'message' )
				),

				$this->create_tool(
					'buddyboss_delete_message_thread',
					'Delete a message thread. This cannot be undone.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The thread ID to delete.',
						),
					),
					'delete_message_thread',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_mark_message_read',
					'Mark a message thread as read.',
					array(
						'id' => array(
							'type'        => 'integer',
							'description' => 'The thread ID to mark as read.',
						),
					),
					'mark_message_read',
					array( 'id' )
				),
			);
		}

		/**
		 * List message threads.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_message_threads( $args, $user_id ) {
			$params = array(
				'page'     => $this->get_int( $args, 'page', 1 ),
				'per_page' => min( $this->get_int( $args, 'per_page', 20 ), 100 ),
			);

			$msg_user_id = $this->get_int( $args, 'user_id' );
			if ( $msg_user_id > 0 ) {
				$params['user_id'] = $msg_user_id;
			}

			$box = $this->get_string( $args, 'box' );
			if ( ! empty( $box ) && in_array( $box, array( 'inbox', 'sentbox', 'starred' ), true ) ) {
				$params['box'] = $box;
			}

			$type = $this->get_string( $args, 'type' );
			if ( ! empty( $type ) && in_array( $type, array( 'all', 'read', 'unread' ), true ) ) {
				$params['type'] = $type;
			}

			$response = $this->rest_client->get( '/buddyboss/v1/messages', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Get a single message thread.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function get_message_thread( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$thread_id = absint( $args['id'] );
			$response  = $this->rest_client->get( '/buddyboss/v1/messages/' . $thread_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Send a message.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function send_message( $args, $user_id ) {
			$this->validate_required( $args, array( 'recipients', 'subject', 'message' ) );

			$params = array(
				'recipients' => sanitize_text_field( $args['recipients'] ),
				'subject'    => sanitize_text_field( $args['subject'] ),
				'message'    => wp_kses_post( $args['message'] ),
			);

			$sender_id = $this->get_int( $args, 'sender_id' );
			if ( $sender_id > 0 ) {
				$params['sender_id'] = $sender_id;
			}

			$response = $this->rest_client->post( '/buddyboss/v1/messages', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Delete a message thread.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function delete_message_thread( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$thread_id = absint( $args['id'] );
			$response  = $this->rest_client->delete( '/buddyboss/v1/messages/' . $thread_id, array(), $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Mark a message thread as read.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function mark_message_read( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$thread_id = absint( $args['id'] );
			$response  = $this->rest_client->put( '/buddyboss/v1/messages/' . $thread_id . '/read', array(), $user_id );

			return $this->rest_client->format_response( $response );
		}
	}
}
