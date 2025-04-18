<?php
/**
 * OODSP Docspace Client
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/client
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2025
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OODSP_Docspace_Client
 *
 * This class handles the client-side interactions with the ONLYOFFICE Docspace within the WordPress environment.
 * It provides methods to communicate with the ONLYOFFICE Docspace API and manage documents.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */
class OODSP_Docspace_Client {
	private const DEFAULT_TIMEOUT = 15;

	/**
	 * OODSP_Settings_Manager
	 *
	 * @var      OODSP_Settings_Manager    $oodsp_settings_manager
	 */
	private OODSP_Settings_Manager $oodsp_settings_manager;

	/**
	 * Constructor for the OODSP_Docspace_Client class.
	 *
	 * @param OODSP_Settings_Manager $oodsp_settings_manager The settings manager instance.
	 */
	public function __construct( OODSP_Settings_Manager $oodsp_settings_manager ) {
		$this->oodsp_settings_manager = $oodsp_settings_manager;
	}

	/**
	 * Gets the CSP settings for a specific DocSpace URL.
	 *
	 * @param string $docspace_url The DocSpace URL.
	 *
	 * @return array The CSP settings.
	 */
	public function get_csp_settings( $docspace_url ) {
		$response = $this->request(
			'/api/2.0/security/csp',
			array(),
			$docspace_url,
			use_system_user_authorization: false
		);

		return $response['response'];
	}

	/**
	 * Gets the settings from ONLYOFFICE DocSpace.
	 *
	 * @return array The settings data.
	 */
	public function get_settings() {
		$response = $this->request(
			'/api/2.0/settings',
			array(),
			'',
			use_system_user_authorization: false
		);

		return $response['response'];
	}

	/**
	 * Authenticates a user with ONLYOFFICE DocSpace.
	 *
	 * @param string $user_name The username for authentication.
	 * @param string $password_hash The hashed password for authentication.
	 *
	 * @return array The response data from the authentication request.
	 */
	public function login( $user_name, $password_hash ) {
		$response = $this->request(
			'/api/2.0/authentication',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode(
					array(
						'userName'     => $user_name,
						'passwordHash' => $password_hash,
					)
				),
			),
			'',
			false
		);

		return $response['response'];
	}

	/**
	 * Logs out the user.
	 */
	public function logout() {
		$this->request(
			'/api/2.0/authentication/logout',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
			)
		);
	}

	/**
	 * Gets a user by their name.
	 *
	 * @param string $user_name The user name.
	 * @param string $token The user token.
	 *
	 * @return array The user data.
	 */
	public function get_user_by_name( $user_name, $token = '' ) {
		$response = $this->request(
			'/api/2.0/people/email?email=' . $user_name,
			array(
				'method'  => 'GET',
				'cookies' => array(
					'asc_auth_key' => $token,
				),
			)
		);

		return $response['response'];
	}

	/**
	 * Gets a user by their ID.
	 *
	 * @param int $id The user ID.
	 *
	 * @return array The user data.
	 */
	public function get_user_by_id( $id ) {
		$response = $this->request(
			'/api/2.0/people/' . $id,
			array(
				'method' => 'GET',
			)
		);

		return $response['response'];
	}

	/**
	 * Creates a new user in ONLYOFFICE DocSpace.
	 *
	 * @param string $email The email address of the new user.
	 * @param string $password_hash The hashed password for the new user.
	 * @param string $firstname The first name of the new user.
	 * @param string $lastname The last name of the new user.
	 * @param string $type The type of user account to create.
	 *
	 * @return array The response data from the user creation request.
	 */
	public function create_user(
		$email,
		$password_hash,
		$firstname,
		$lastname,
		$type
	) {
		$response = $this->request(
			'/api/2.0/people/active',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode(
					array(
						'email'        => $email,
						'passwordHash' => $password_hash,
						'firstname'    => $firstname,
						'lastname'     => $lastname,
						'type'         => $type,
					)
				),
			)
		);

		return $response['response'];
	}

	/**
	 * Initiates a password reset for a user in ONLYOFFICE DocSpace.
	 *
	 * @param string $email The email address of the user whose password needs to be reset.
	 *
	 * @return array The response data from the password reset request.
	 */
	public function reset_password( $email ) {
		$response = $this->request(
			'/api/2.0/people/password',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode(
					array(
						'email' => $email,
					)
				),
			),
			'',
			false
		);

		return $response['response'];
	}

	/**
	 * Creates a new group in ONLYOFFICE DocSpace.
	 *
	 * @param string $group_name    The name of the new group.
	 * @param string $group_manager The ID of the group manager.
	 * @param array  $members       Array of user IDs to be added as members.
	 *
	 * @return array The response data from the group creation request.
	 */
	public function create_group( $group_name, $group_manager, $members ) {
		$response = $this->request(
			'/api/2.0/group',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode(
					array(
						'groupName'    => $group_name,
						'groupManager' => $group_manager,
						'members'      => $members,
					)
				),
			)
		);

		return $response['response'];
	}

	/**
	 * Updates an existing group in ONLYOFFICE DocSpace.
	 *
	 * @param string $group_id         The ID of the group to update.
	 * @param string $group_name       The new name for the group (optional).
	 * @param string $group_manager    The ID of the new group manager (optional).
	 * @param array  $members_to_add   Array of user IDs to add to the group (optional).
	 * @param array  $members_to_remove Array of user IDs to remove from the group (optional).
	 *
	 * @return array The response data from the group update request.
	 */
	public function update_group( $group_id, $group_name, $group_manager, $members_to_add, $members_to_remove ) {
		$body = array();

		if ( ! empty( $group_name ) ) {
			$body['groupName'] = $group_name;
		}

		if ( ! empty( $group_manager ) ) {
			$body['groupManager'] = $group_manager;
		}

		if ( ! empty( $members_to_add ) ) {
			$body['membersToAdd'] = $members_to_add;
		}

		if ( ! empty( $members_to_remove ) ) {
			$body['membersToRemove'] = $members_to_remove;
		}

		$response = $this->request(
			'/api/2.0/group/' . $group_id,
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		return $response['response'];
	}

	/**
	 * Makes a request to the ONLYOFFICE DocSpace API.
	 *
	 * @param string $path The path to the API endpoint.
	 * @param array  $args The arguments for the request.
	 * @param string $base_url The base URL for the request.
	 * @param bool   $use_system_user_authorization Whether to use the system user authorization.
	 *
	 * @return array The response from the API.
	 */
	private function request( $path, $args = array(), $base_url = '', $use_system_user_authorization = true ) {
		$args['timeout'] = self::DEFAULT_TIMEOUT;

		if ( empty( $base_url ) ) {
			$base_url = $this->oodsp_settings_manager->get_docspace_url();
		}

		if ( ! $this->exist_asc_auth_key( $args ) && $use_system_user_authorization ) {
			$system_user = $this->oodsp_settings_manager->get_system_user();

			if ( ! empty( $system_user ) ) {
				$args['cookies'] = array(
					'asc_auth_key' => $system_user->get_token(),
				);
			}
		}

		if ( ! isset( $args['method'] ) ) {
			$args['method'] = 'GET';
		}

		$response = wp_remote_request(
			rtrim( $base_url, '/' ) . $path,
			$args
		);

		return $this->handle_response(
			$args,
			$response,
			! empty( $system_user )
		);
	}

	/**
	 * Handles the response from the ONLYOFFICE DocSpace API.
	 *
	 * @param array $request The original request arguments.
	 * @param array $response The response from the API.
	 * @param bool  $reset_system_user_on_fail Whether to reset the system user on failure.
	 *
	 * @return array The decoded response data.
	 *
	 * @throws OODSP_Docspace_Client_Exception If the request fails or the response is invalid.
	 */
	private function handle_response( $request, $response, $reset_system_user_on_fail = false ) {
		if ( is_wp_error( $response ) ) {
			throw new OODSP_Docspace_Client_Exception(
				'HTTP ' . esc_attr( $request['method'] ) . ' request failed: ' . esc_attr( $response->get_error_message() ),
				// phpcs:ignore WordPress.Security.EscapeOutput
				$request,
				// phpcs:ignore WordPress.Security.EscapeOutput
				$response
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( $reset_system_user_on_fail && 401 === $status_code ) {
			$this->oodsp_settings_manager->delete_system_user();
		}

		if ( 200 !== $status_code ) {
			throw new OODSP_Docspace_Client_Exception(
				'Unexpected HTTP status code: ' . esc_attr( $status_code ),
				// phpcs:ignore WordPress.Security.EscapeOutput
				$request,
				// phpcs:ignore WordPress.Security.EscapeOutput
				$response,
				// phpcs:ignore WordPress.Security.EscapeOutput
				$status_code
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new OODSP_Docspace_Client_Exception(
				'Invalid JSON response: ' . esc_attr( json_last_error_msg() ),
				// phpcs:ignore WordPress.Security.EscapeOutput
				$request,
				// phpcs:ignore WordPress.Security.EscapeOutput
				$response
			);
		}

		return $data;
	}

	/**
	 * Checks if the "asc_auth_key" cookie is present in the arguments.
	 *
	 * @param array $args The arguments to check.
	 * @return bool True if the "asc_auth_key" cookie exists, false otherwise.
	 */
	private function exist_asc_auth_key( $args ) {
		if ( ! array_key_exists( 'cookies', $args ) ) {
			return false;
		}

		if ( ! array_key_exists( 'asc_auth_key', $args['cookies'] ) ) {
			return false;
		}

		return ! empty( $args['cookies']['asc_auth_key'] );
	}
}
