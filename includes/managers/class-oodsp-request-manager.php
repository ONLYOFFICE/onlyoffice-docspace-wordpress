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


	/**
	 *
	 */
	private $plugin_settings;

	public function __construct( $args = array() ) {
		$this->plugin_settings = new OODSP_Settings();
	}

	public function auth_docspace ( $docspace_url = null, $docspace_login = null, $docspace_pass = null ) {
		$result = array(
			'error' => 0,
			'data'  => null
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
					if ( $res_is_admin['data'] ) {
						$result['data'] = $current_docspace_token;
						return $result;
					} else {
						$result['error'] = 3; // Error user is not admin
						return $result;
					}
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
			$result['error'] = 1; // Error authentication
			return $result;
		}

		// Check is admin with new token
		$res_is_admin = $this->is_admin_docspace( $docspace_url, $docspace_login, $res_authentication['data'] );

		if ( $res_is_admin['error'] ) {
			$result['error'] = 2; // Error getting user data
			return $result;
		} else {
			if ( $res_is_admin['data'] ) {
				$options = get_option( 'onlyoffice_docspace_settings' );
				$options[OODSP_Settings::DOCSPACE_TOKEN] = $res_authentication['data'];
				update_option( 'onlyoffice_docspace_settings', $options );

				$result['data'] = $res_authentication['data'];
				return $result;
			} else {
				$result['error'] = 3; // Error user is not admin
				return $result;
			}
		}
	}

	private function is_admin_docspace ( $docspace_url, $docspace_login, $docspace_token ) {
		$result = array(
			'error' => 0,
			'data'   => null
		);

		$responce = wp_remote_get(
			$docspace_url . "api/2.0/people/email?email=" . $docspace_login,
			array(
				'cookies' => array( 'asc_auth_key' => $docspace_token )
			) 
		);

		if ( is_wp_error( $responce ) || 200 !== wp_remote_retrieve_response_code( $responce ) ) {
			$result['error'] = 1;
			return $result;
		}

		$body = json_decode( wp_remote_retrieve_body( $responce ), true );
		$docspace_user = $body['response'];
		$result['data'] = $docspace_user['isAdmin'];

		return $result;
	}

	private function request_authentication ( $docspace_url, $docspace_login, $docspace_pass ) {
		$result = array(
			'error' => 0,
			'data'   => false
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
			$result['error'] = 1;
			return $result;
		}

		$body = json_decode( wp_remote_retrieve_body( $responce ), true );

		$result['data'] = $body['response']['token'];
		return $result;
	}

}