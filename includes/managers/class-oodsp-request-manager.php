<?php
/**
 * Request manager
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/managers
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
 * Request manager
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Request_Manager {
	const UNAUTHORIZED          = 1;
	const USER_NOT_FOUND        = 2;
	const FORBIDDEN             = 3;
	const ERROR_USER_INVITE     = 4;
	const ERROR_GET_USERS       = 5;
	const ERROR_SET_USER_PASS   = 6;
	const ERROR_GET_FILE_INFO   = 7;
	const ERROR_GET_FOLDER_INFO = 8;
	const ERROR_SHARE_ROOM      = 9;

	/**
	 * OODSP_Settings
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $plugin_settings;


	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->plugin_settings = new OODSP_Settings();
	}

	/**
	 * Authentication DocSapace.
	 *
	 * @param string $docspace_url DocSpace URL.
	 * @param string $docspace_login DocSpace user login.
	 * @param string $docspace_pass DocSpace user password.
	 */
	public function auth_docspace( $docspace_url = null, $docspace_login = null, $docspace_pass = null ) {
		$result = array(
			'error' => null,
			'data'  => null,
		);

		$current_docspace_url   = $this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_URL );
		$current_docspace_login = $this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_LOGIN );
		$current_docspace_pass  = $this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_PASS );

		// Try authentication with current credintails, if new credintails equals null or new credintails equals current credintails.
		if (
			( null === $docspace_url
				&& null === $docspace_login
				&& null === $docspace_pass )
			|| (
				$current_docspace_url === $docspace_url
				&& $current_docspace_login === $docspace_login
				&& $current_docspace_pass === $docspace_pass
			) ) {
			$current_docspace_token = $this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_TOKEN );

			if ( '' !== $current_docspace_token ) {
				// Check is admin with current token.
				$res_docspace_user = $this->request_docspace_user( $current_docspace_url, $current_docspace_login, $current_docspace_token );

				if ( ! $res_docspace_user['error'] ) {
					if ( ! $res_docspace_user['data']['isAdmin'] ) {
						$result['error'] = self::FORBIDDEN; // Error user is not admin.
						return $result;
					}

					$result['data'] = $current_docspace_token; // Return current token.
					return $result;
				}
			}

			$docspace_url   = $current_docspace_url;
			$docspace_login = $current_docspace_login;
			$docspace_pass  = $current_docspace_pass;
		}

		// Try authentication with new credintails.
		// Try get new token.
		$res_authentication = $this->request_authentication( $docspace_url, $docspace_login, $docspace_pass );

		if ( $res_authentication['error'] ) {
			return $res_authentication; // Error authentication.
		}

		// Check is admin with new token.
		$res_docspace_user = $this->request_docspace_user( $docspace_url, $docspace_login, $res_authentication['data'] );

		if ( $res_docspace_user['error'] ) {
			return $res_docspace_user; // Error getting user data.
		}

		if ( ! $res_docspace_user['data']['isAdmin'] ) {
			$result['error'] = self::FORBIDDEN; // Error user is not admin.
			return $result;
		}

		$options                                   = get_option( 'oodsp_settings' );
		$options[ OODSP_Settings::DOCSPACE_TOKEN ] = $res_authentication['data'];
		update_option( 'oodsp_settings', $options );

		$result['data'] = $res_authentication['data']; // Return new current token.
		return $result;
	}

	/**
	 * Request DocSapce users.
	 */
	public function request_docspace_users() {
		$result = array(
			'error' => null,
			'data'  => null,
		);

		$res_auth = $this->auth_docspace();

		if ( $res_auth['error'] ) {
			return $res_auth;
		}

		$res_users = wp_remote_get(
			$this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_URL ) . 'api/2.0/people',
			array( 'cookies' => array( 'asc_auth_key' => $res_auth['data'] ) )
		);

		if ( is_wp_error( $res_users ) && 200 === wp_remote_retrieve_response_code( $res_users ) ) {
			$result['error'] = self::ERROR_GET_USERS;
			return $result;
		}

		$body           = json_decode( wp_remote_retrieve_body( $res_users ), true );
		$users          = $body['response'];
		$result['data'] = $users;

		return $result;
	}

	/**
	 * Request invite user to DocSpace.
	 *
	 * @param string $email User email.
	 * @param string $password_hash User password hash.
	 * @param string $firstname User firstname.
	 * @param string $lastname User lastname.
	 * @param string $type User type.
	 * @param string $docspace_token DocSpace token.
	 */
	public function request_invite_user( $email, $password_hash, $firstname, $lastname, $type, $docspace_token = null ) {
		$result = array(
			'error' => null,
			'data'  => null,
		);

		if ( ! $docspace_token ) {
			$res_auth = $this->auth_docspace();

			if ( $res_auth['error'] ) {
				return $res_auth;
			}

			$docspace_token = $res_auth['data'];
		}

		$responce = wp_remote_post(
			$this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_URL ) . 'api/2.0/people/active',
			array(
				'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'cookies' => array( 'asc_auth_key' => $docspace_token ),
				'body'    => wp_json_encode(
					array(
						'email'        => $email,
						'passwordHash' => $password_hash,
						'firstname'    => $firstname,
						'lastname'     => $lastname,
						'type'         => $type,
					)
				),
				'method'  => 'POST',
			)
		);

		if ( is_wp_error( $responce ) || 200 !== wp_remote_retrieve_response_code( $responce ) ) {
			$result['error'] = self::ERROR_USER_INVITE;
			return $result;
		}

		$body           = json_decode( wp_remote_retrieve_body( $responce ), true );
		$result['data'] = $body['response'];

		return $result;
	}

	/**
	 * Request DocSpace user.
	 *
	 * @param string $docspace_url DocSpace URL.
	 * @param string $docspace_login DocSpace user login.
	 * @param string $docspace_token DocSpace token.
	 */
	public function request_docspace_user( $docspace_url, $docspace_login, $docspace_token ) {
		$result = array(
			'error' => null,
			'data'  => null,
		);

		$responce = wp_remote_get(
			$docspace_url . 'api/2.0/people/email?email=' . $docspace_login,
			array(
				'cookies' => array( 'asc_auth_key' => $docspace_token ),
			)
		);

		if ( is_wp_error( $responce ) || 200 !== wp_remote_retrieve_response_code( $responce ) ) {
			$result['error'] = self::USER_NOT_FOUND;
			return $result;
		}

		$body           = json_decode( wp_remote_retrieve_body( $responce ), true );
		$result['data'] = $body['response'];

		return $result;
	}

	/**
	 * Request Set DocSpace user password.
	 *
	 * @param string $docspace_user_id DocSpace user ID.
	 * @param string $password_hash DocSpace user password hash.
	 * @param string $docspace_token DocSpace token.
	 */
	public function request_set_user_pass( $docspace_user_id, $password_hash, $docspace_token ) {
		$result = array(
			'error' => null,
			'data'  => null,
		);

		$responce = wp_remote_request(
			$this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_URL ) . 'api/2.0/people/' . $docspace_user_id . '/password',
			array(
				'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'cookies' => array( 'asc_auth_key' => $docspace_token ),
				'body'    => wp_json_encode(
					array(
						'passwordHash' => $password_hash,
					)
				),
				'method'  => 'PUT',
			)
		);

		if ( is_wp_error( $responce ) || 200 !== wp_remote_retrieve_response_code( $responce ) ) {
			$result['error'] = self::ERROR_SET_USER_PASS;
			return $result;
		}

		$body           = json_decode( wp_remote_retrieve_body( $responce ), true );
		$result['data'] = $body['response'];

		return $result;
	}

	/**
	 * Request Authentication in DocSpace.
	 *
	 * @param string $docspace_url DocSpace URL.
	 * @param string $docspace_login DocSpace User login.
	 * @param string $docspace_pass DocSpace User password.
	 */
	private function request_authentication( $docspace_url, $docspace_login, $docspace_pass ) {
		$result = array(
			'error' => null,
			'data'  => null,
		);

		$responce = wp_remote_post(
			$docspace_url . 'api/2.0/authentication',
			array(
				'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'    => wp_json_encode(
					array(
						'userName'     => $docspace_login,
						'passwordHash' => $docspace_pass,
					)
				),
				'method'  => 'POST',
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

	/**
	 * Return array: email, first name, last name.
	 *
	 * @param WP_User $user User Entity.
	 */
	public function get_user_data( $user ) {
		$email      = $user->user_email;
		$login      = $user->user_login;
		$first_name = preg_replace( '/[^\p{L}\p{M} \-]/u', '-', $user->first_name );
		$last_name  = preg_replace( '/[^\p{L}\p{M} \-]/u', '-', $user->last_name );

		if ( $first_name && ! $last_name ) {
			$last_name = $first_name;
		}

		if ( ! $first_name && $last_name ) {
			$first_name = $last_name;
		}

		if ( ! $first_name && ! $last_name ) {
			$first_name = preg_replace( '/[^\p{L}\p{M} \-]/u', '-', $login );
			$last_name  = $first_name;
		}

		return array( $email, $first_name, $last_name );
	}
}
