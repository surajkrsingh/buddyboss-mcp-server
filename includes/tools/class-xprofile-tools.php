<?php
/**
 * XProfile Tools — MCP tools for BuddyBoss extended profile management.
 *
 * Provides 5 tools: list_groups, list_fields, get_field, get_data, update_data.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tools\\XProfile_Tools' ) ) {

	/**
	 * XProfile tool provider.
	 *
	 * @since 1.0.0
	 */
	class XProfile_Tools extends Tool_Base {

		/**
		 * Register all xprofile tools.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		public function register_tools() {
			return array(
				$this->create_tool(
					'buddyboss_list_xprofile_groups',
					'List profile field groups. Optionally include fields in the response.',
					array(
						'fetch_fields' => array(
							'type'        => 'boolean',
							'description' => 'If true, include fields in the response.',
						),
					),
					'list_xprofile_groups'
				),

				$this->create_tool(
					'buddyboss_list_xprofile_fields',
					'List profile fields, optionally filtered by profile group.',
					array(
						'profile_group_id' => array(
							'type'        => 'integer',
							'description' => 'Filter by profile group ID.',
						),
						'fetch_field_data' => array(
							'type'        => 'boolean',
							'description' => 'If true, include field data in the response.',
						),
					),
					'list_xprofile_fields'
				),

				$this->create_tool(
					'buddyboss_get_xprofile_field',
					'Get detailed information about a specific profile field.',
					array(
						'id'               => array(
							'type'        => 'integer',
							'description' => 'The profile field ID.',
						),
						'fetch_field_data' => array(
							'type'        => 'boolean',
							'description' => 'If true, include field data.',
						),
					),
					'get_xprofile_field',
					array( 'id' )
				),

				$this->create_tool(
					'buddyboss_get_xprofile_data',
					'Get a specific user\'s data for a profile field.',
					array(
						'field_id' => array(
							'type'        => 'integer',
							'description' => 'The profile field ID.',
						),
						'user_id'  => array(
							'type'        => 'integer',
							'description' => 'The user ID.',
						),
					),
					'get_xprofile_data',
					array( 'field_id', 'user_id' )
				),

				$this->create_tool(
					'buddyboss_update_xprofile_data',
					'Update a specific user\'s data for a profile field.',
					array(
						'field_id' => array(
							'type'        => 'integer',
							'description' => 'The profile field ID.',
						),
						'user_id'  => array(
							'type'        => 'integer',
							'description' => 'The user ID.',
						),
						'value'    => array(
							'type'        => 'string',
							'description' => 'The new field value.',
						),
					),
					'update_xprofile_data',
					array( 'field_id', 'user_id', 'value' )
				),
			);
		}

		/**
		 * List xprofile groups.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_xprofile_groups( $args, $user_id ) {
			$params = array();

			if ( isset( $args['fetch_fields'] ) ) {
				$params['fetch_fields'] = $this->get_bool( $args, 'fetch_fields' );
			}

			$response = $this->rest_client->get( '/buddyboss/v1/xprofile/groups', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * List xprofile fields.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function list_xprofile_fields( $args, $user_id ) {
			$params = array();

			$profile_group_id = $this->get_int( $args, 'profile_group_id' );
			if ( $profile_group_id > 0 ) {
				$params['profile_group_id'] = $profile_group_id;
			}

			if ( isset( $args['fetch_field_data'] ) ) {
				$params['fetch_field_data'] = $this->get_bool( $args, 'fetch_field_data' );
			}

			$response = $this->rest_client->get( '/buddyboss/v1/xprofile/fields', $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Get a single xprofile field.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function get_xprofile_field( $args, $user_id ) {
			$this->validate_required( $args, array( 'id' ) );

			$field_id = absint( $args['id'] );
			$params   = array();

			if ( isset( $args['fetch_field_data'] ) ) {
				$params['fetch_field_data'] = $this->get_bool( $args, 'fetch_field_data' );
			}

			$response = $this->rest_client->get( '/buddyboss/v1/xprofile/fields/' . $field_id, $params, $user_id );

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Get xprofile data for a user and field.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function get_xprofile_data( $args, $user_id ) {
			$this->validate_required( $args, array( 'field_id', 'user_id' ) );

			$field_id      = absint( $args['field_id'] );
			$data_user_id  = absint( $args['user_id'] );

			$response = $this->rest_client->get(
				'/buddyboss/v1/xprofile/' . $field_id . '/data/' . $data_user_id,
				array(),
				$user_id
			);

			return $this->rest_client->format_response( $response );
		}

		/**
		 * Update xprofile data for a user and field.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args    Tool arguments.
		 * @param int   $user_id Authenticated user ID.
		 * @return string JSON response.
		 */
		public function update_xprofile_data( $args, $user_id ) {
			$this->validate_required( $args, array( 'field_id', 'user_id', 'value' ) );

			$field_id     = absint( $args['field_id'] );
			$data_user_id = absint( $args['user_id'] );
			$params       = array(
				'value' => sanitize_text_field( $args['value'] ),
			);

			$response = $this->rest_client->post(
				'/buddyboss/v1/xprofile/' . $field_id . '/data/' . $data_user_id,
				$params,
				$user_id
			);

			return $this->rest_client->format_response( $response );
		}
	}
}
