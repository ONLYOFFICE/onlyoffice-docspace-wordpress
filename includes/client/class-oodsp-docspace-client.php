<?php
/**
 * OODSP Docspace Client
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/client
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2024
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
	 *
	 * @return array The response data.
	 */
	public function logout() {
		$response = $this->request(
			'/api/2.0/authentication/logout',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
			)
		);

		return $response['response'];
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

			$args['cookies'] = array(
				'asc_auth_key' => $system_user->get_token(),
			);
		}

		$response = wp_remote_request(
			rtrim( $base_url, '/' ) . $path,
			$args
		);

		return $this->handle_response(
			$response,
			! empty( $system_user )
		);
	}

	/**
	 * Handles the response from the API call.
	 *
	 * @param mixed $response The response from the API call.
	 * @param bool  $reset_system_user_on_fail Whether to reset the system user if the request fails.
	 * @return mixed
	 * @throws OODSP_Docspace_Client_Exception If the HTTP request fails or the response is invalid.
	 */
	private function handle_response( $response, $reset_system_user_on_fail = false ) {
		if ( is_wp_error( $response ) ) {
			throw new OODSP_Docspace_Client_Exception(
				'HTTP GET request failed: ' . esc_attr( $response->get_error_message() )
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( $reset_system_user_on_fail && 401 === $status_code ) {
			$this->oodsp_settings_manager->delete_system_user();
		}

		if ( 200 !== $status_code ) {
			throw new OODSP_Docspace_Client_Exception(
				'Unexpected HTTP status code:' . esc_attr( $status_code )
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new OODSP_Docspace_Client_Exception(
				'Invalid JSON response: ' . esc_attr( json_last_error_msg() )
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
