<?php
/**
 * Request manager
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes/managers
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2023
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

/**
 * Request manager
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Request_Manager {
	const UNAUTHORIZED      = 1;
	const USER_NOT_FOUND    = 2;
	const FORBIDDEN         = 3;
	const ERROR_USER_INVITE = 4;
	const ERROR_GET_USERS   = 5;

	/**
	 *
	 */
	private $plugin_settings;

	public function __construct( $args = array() ) {
		$this->plugin_settings = new OODSP_Settings();
	}

	public function auth_docspace ( $docspace_url = null, $docspace_login = null, $docspace_pass = null ) {
		$result = array(
			'error' => NULL,
			'data'  => NULL
		);

		$current_docspace_url = $this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_URL);
		$current_docspace_login = $this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_LOGIN);
		$current_docspace_pass = $this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_PASS);
		
		// Try authentication with current credintails, if new credintails equals null or new credintails equals current credintails
		if (
			( $docspace_url === null
				&& $docspace_login === null
				&& $docspace_pass === null )
			|| (
				$current_docspace_url === $docspace_url 
				&& $current_docspace_login === $docspace_login 
				&& $current_docspace_pass === $docspace_pass
			) ) {
			$current_docspace_token = $this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_TOKEN);
			
			if ( $current_docspace_token !== '' ) {
				// Check is admin with current token
				$res_is_admin = $this->is_admin_docspace( $current_docspace_url, $current_docspace_login, $current_docspace_token );

				if ( !$res_is_admin['error'] ) {
					if ( !$res_is_admin['data'] ) {
						$result['error'] = self::FORBIDDEN; // Error user is not admin
						return $result;
					}

					$result['data'] = $current_docspace_token; // Return current token
					return $result;
				}
			}

			$docspace_url = $current_docspace_url;
			$docspace_login = $current_docspace_login;
			$docspace_pass = $current_docspace_pass;
		}

		// Try authentication with new credintails
		// Try get new token
		$res_authentication = $this->request_authentication( $docspace_url, $docspace_login, $docspace_pass  );

		if ( $res_authentication['error'] ) {
			return $res_authentication; // Error authentication
		}

		// Check is admin with new token
		$res_is_admin = $this->is_admin_docspace( $docspace_url, $docspace_login, $res_authentication['data'] );

		if ( $res_is_admin['error'] ) {
			return $res_is_admin; // Error getting user data
		}

		if ( !$res_is_admin['data'] ) {
			$result['error'] = self::FORBIDDEN; // Error user is not admin
			return $result;
		}

		$options = get_option( 'onlyoffice_docspace_settings' );
		$options[OODSP_Settings::DOCSPACE_TOKEN] = $res_authentication['data'];
		update_option( 'onlyoffice_docspace_settings', $options );

		$result['data'] = $res_authentication['data']; // Return new current token
		return $result;
	}

	public function request_docspace_users () {
		$result = array(
			'error' => NULL,
			'data'  => NULL
		);

		$res_auth = $this->auth_docspace();

		if ( $res_auth['error'] ) {
			return $res_auth;
		}

		$res_users = wp_remote_get(
			$this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_URL) . "api/2.0/people",
			array( 'cookies' => array( 'asc_auth_key' => $res_auth['data'] ) ) 
		);
		
		if ( is_wp_error( $res_users ) && 200 === wp_remote_retrieve_response_code( $res_users ) ) {
			$result['error'] = self::ERROR_GET_USERS;
			return $result;
		}

		$body = json_decode( wp_remote_retrieve_body( $res_users ), true );
		$users = $body['response'];
		$result['data'] = $users;

		return $result;
	}

	public function request_invite_user ( $email, $passwordHash, $firstname, $lastname, $type, $cultureName ) {
		$result = array(
			'error' => NULL,
			'data'  => NULL
		);

		$res_auth = $this->auth_docspace();

		if ( $res_auth['error'] ) {
			return $res_auth;
		}

		$responce = wp_remote_post(
			$this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_URL) . "api/2.0/people/active",
			array(
				'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
				'cookies' => array( 'asc_auth_key' => $res_auth['data'] ),
				'body'    => json_encode(
					array(
						'email' => $email,
						'passwordHash' => $passwordHash,
						'firstname' => $firstname,
						'lastname' => $lastname,
						'type' => $type,
						'cultureName' => $cultureName,
					)
				),
				'method'  => 'POST'
			)
		);

		if ( is_wp_error( $responce ) || 200 !== wp_remote_retrieve_response_code( $responce ) ) {
			$result['error'] = self::ERROR_USER_INVITE;
			return $result;
		}

		$body = json_decode( wp_remote_retrieve_body( $responce ), true );
		$result['data'] = $body['response'];

		return $result;
	}

	private function is_admin_docspace ( $docspace_url, $docspace_login, $docspace_token ) {
		$result = array(
			'error' => NULL,
			'data'  => NULL
		);

		$responce = wp_remote_get(
			$docspace_url . "api/2.0/people/email?email=" . $docspace_login,
			array(
				'cookies' => array( 'asc_auth_key' => $docspace_token )
			)
		);

		if ( is_wp_error( $responce ) || 200 !== wp_remote_retrieve_response_code( $responce ) ) {
			$result['error'] = self::USER_NOT_FOUND;
			return $result;
		}

		$body = json_decode( wp_remote_retrieve_body( $responce ), true );
		$docspace_user = $body['response'];
		$result['data'] = $docspace_user['isAdmin'];

		return $result;
	}

	private function request_authentication ( $docspace_url, $docspace_login, $docspace_pass ) {
		$result = array(
			'error' => NULL,
			'data'  => NULL
		);

		$responce = wp_remote_post(
			$docspace_url . "api/2.0/authentication",
			array(
				'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
				'body'    => json_encode(
					array(
						'userName' => $docspace_login,
						'passwordHash' => $docspace_pass
					)
				),
				'method'  => 'POST'
			)
		);

		if ( is_wp_error( $responce ) || 200 !== wp_remote_retrieve_response_code( $responce ) ) {
			$result['error'] = self::UNAUTHORIZED;
			return $result;
		}

		$body = json_decode( wp_remote_retrieve_body( $responce ), true );

		$result['data'] = $body['response']['token'];
		return $result;
	}

}