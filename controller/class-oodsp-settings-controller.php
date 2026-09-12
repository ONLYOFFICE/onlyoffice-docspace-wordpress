<?php
/**
 * OODSP Settings Controller
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/controller
 */

/**
 * (c) Copyright Ascensio System SIA 2026
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
 * Class OODSP_Settings_Controller
 *
 * This class represents a settings controller.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/controller
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Settings_Controller {
	/**
	 * OODSP_Docspace_Client
	 *
	 * @var OODSP_Docspace_Client $oodsp_docspace_client
	 */
	private OODSP_Docspace_Client $oodsp_docspace_client;

	/**
	 * OODSP_User_Service
	 *
	 * @var OODSP_User_Service $oodsp_user_service
	 */
	private OODSP_User_Service $oodsp_user_service;

	/**
	 * OODSP_Settings_Manager
	 *
	 * @var OODSP_Settings_Manager $oodsp_settings_manager
	 */
	private OODSP_Settings_Manager $oodsp_settings_manager;

	/**
	 * OODSP_Docspace_Action_Manager
	 *
	 * @var OODSP_Docspace_Action_Manager $oodsp_docspace_action_manager
	 */
	private OODSP_Docspace_Action_Manager $oodsp_docspace_action_manager;

	/**
	 * Constructor for the OODSP_Settings_Controller class.
	 *
	 * @param OODSP_Docspace_Client         $oodsp_docspace_client         The DocSpace client instance.
	 * @param OODSP_User_Service            $oodsp_user_service            The user service instance.
	 * @param OODSP_Settings_Manager        $oodsp_settings_manager        The settings manager instance.
	 * @param OODSP_Docspace_Action_Manager $oodsp_docspace_action_manager The DocSpace action manager instance.
	 */
	public function __construct(
		OODSP_Docspace_Client $oodsp_docspace_client,
		OODSP_User_Service $oodsp_user_service,
		OODSP_Settings_Manager $oodsp_settings_manager,
		OODSP_Docspace_Action_Manager $oodsp_docspace_action_manager
	) {
		$this->oodsp_docspace_client         = $oodsp_docspace_client;
		$this->oodsp_user_service            = $oodsp_user_service;
		$this->oodsp_settings_manager        = $oodsp_settings_manager;
		$this->oodsp_docspace_action_manager = $oodsp_docspace_action_manager;
	}

	/**
	 * Set system user for DocSpace authentication.
	 * Validates user credentials and ensures admin privileges.
	 * With two-factor authentication enabled, the first request returns a challenge
	 * instead of a token, and the client repeats the call with the one-time code.
	 */
	public function set_system_user() {
		check_ajax_referer( 'oodsp_settings_controller' );

		$user_name     = trim( OODSP_Utils::get_var_from_request( 'userName' ) );
		$password_hash = trim( OODSP_Utils::get_var_from_request( 'passwordHash' ) );
		$code          = trim( OODSP_Utils::get_var_from_request( 'code' ) );

		if ( empty( $user_name ) || empty( $password_hash ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The required fields are empty', 'onlyoffice-docspace' ) ),
				400
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The current user does not have permission to perform this action', 'onlyoffice-docspace' ) ),
				403
			);
		}

		$authentication = $this->authenticate( $user_name, $password_hash, $code );

		if ( empty( $authentication['token'] ) ) {
			$this->send_two_factor_challenge( $authentication );
		}

		$docspace_user = $this->oodsp_docspace_client->get_user_by_name(
			$user_name,
			$authentication['token']
		);

		if ( ! $docspace_user['isAdmin'] ) {
			wp_send_json_error(
				array( 'message' => __( 'The specified user is not a ONLYOFFICE DocSpace administrator', 'onlyoffice-docspace' ) ),
				403
			);
		}

		$system_user = new OODSP_System_User(
			$docspace_user['id'],
			$user_name,
			$password_hash,
			$authentication['token']
		);

		$this->oodsp_settings_manager->set_system_user( $system_user );

		$user             = wp_get_current_user();
		$docspace_account = $this->oodsp_user_service->get_docspace_account( $user->ID );

		if ( empty( $docspace_account ) ) {
			$docspace_account = new OODSP_Docspace_Account(
				$docspace_user['id'],
				$user_name,
				$password_hash
			);

			$this->oodsp_user_service->put_docspace_account(
				$user->ID,
				$docspace_account
			);
		}

		try {
			$this->oodsp_docspace_action_manager->init_shared_group();
		} catch ( OODSP_Docspace_Client_Exception $e ) {
			$e->printStackTrace();
		}

		wp_send_json_success( array( 'tfaRequired' => false ) );
	}

	/**
	 * Authenticates the DocSpace user, with or without a two-factor authentication code.
	 *
	 * Sends the error to the client and terminates the request if the authentication fails.
	 *
	 * @param string $user_name     The DocSpace user name.
	 * @param string $password_hash The hashed password of the DocSpace user.
	 * @param string $code          The two-factor authentication code, empty on the first attempt.
	 *
	 * @return array The authentication response from DocSpace.
	 */
	private function authenticate( $user_name, $password_hash, $code ) {
		try {
			if ( empty( $code ) ) {
				return $this->oodsp_docspace_client->login(
					$user_name,
					password_hash: $password_hash
				);
			}

			return $this->oodsp_docspace_client->login_by_code(
				$user_name,
				$password_hash,
				$code
			);
		} catch ( OODSP_Docspace_Client_Exception $e ) {
			$this->send_authentication_error( $e, ! empty( $code ) );
		}
	}

	/**
	 * Sends the two-factor authentication challenge returned by DocSpace to the client.
	 *
	 * @param array $authentication The authentication response from DocSpace.
	 *
	 * @return never This method always terminates the request.
	 */
	private function send_two_factor_challenge( $authentication ) {
		$sms         = ! empty( $authentication['sms'] );
		$tfa         = ! empty( $authentication['tfa'] );
		$phone_noise = $authentication['phoneNoise'] ?? '';
		$tfa_key     = $authentication['tfaKey'] ?? '';

		// A mobile phone is registered, DocSpace has sent the code by SMS.
		if ( $sms && ! empty( $phone_noise ) ) {
			wp_send_json_success(
				array(
					'tfaRequired' => true,
					'type'        => 'sms',
					'phoneNoise'  => $phone_noise,
				)
			);
		}

		// The authenticator application is already connected, no new secret is issued.
		if ( $tfa && empty( $tfa_key ) ) {
			wp_send_json_success(
				array(
					'tfaRequired' => true,
					'type'        => 'app',
					'phoneNoise'  => '',
				)
			);
		}

		// Neither factor is announced, so the credentials themselves were rejected.
		if ( ! $sms && ! $tfa ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid credentials. Please try again.', 'onlyoffice-docspace' ) ),
				401
			);
		}

		// Two-factor authentication is on, but the account has not finished setting it up.
		wp_send_json_error(
			array(
				'message'    => $sms
					? __( 'Two-factor authentication is enabled for this account, but no mobile phone is specified. Please set it in ONLYOFFICE DocSpace and try again.', 'onlyoffice-docspace' )
					: __( 'Two-factor authentication is enabled for this account, but the authenticator application is not connected yet. Please connect it in ONLYOFFICE DocSpace and try again.', 'onlyoffice-docspace' ),
				'confirmUrl' => esc_url_raw( $authentication['confirmUrl'] ?? '' ),
			),
			403
		);
	}

	/**
	 * Sends the authentication error to the client.
	 *
	 * @param OODSP_Docspace_Client_Exception $exception     The exception thrown by the DocSpace client.
	 * @param bool                            $with_code     Whether a two-factor authentication code was submitted.
	 *
	 * @return never This method always terminates the request.
	 */
	private function send_authentication_error( $exception, $with_code ) {
		$status_code = $exception->getCode();

		if ( 429 === $status_code ) {
			wp_send_json_error(
				array( 'message' => __( 'Too many login attempts. Please try again later.', 'onlyoffice-docspace' ) ),
				429
			);
		}

		if ( $with_code ) {
			if ( 403 === $status_code ) {
				wp_send_json_error(
					array( 'message' => __( 'The authentication code is not available. Please try again.', 'onlyoffice-docspace' ) ),
					403
				);
			}

			wp_send_json_error(
				array( 'message' => __( 'Invalid code. Please try again.', 'onlyoffice-docspace' ) ),
				401
			);
		}

		wp_send_json_error(
			array( 'message' => __( 'Invalid credentials. Please try again.', 'onlyoffice-docspace' ) ),
			401
		);
	}

	/**
	 * Delete system user from DocSpace and logout.
	 * Handles logging out and removing the system user credentials.
	 */
	public function delete_system_user() {
		check_ajax_referer( 'oodsp_settings_controller' );

		$system_user = $this->oodsp_settings_manager->get_system_user();

		if ( ! empty( $system_user ) ) {
			try {
				$this->oodsp_docspace_client->logout();
			} catch ( OODSP_Docspace_Client_Exception $e ) {
				$e->printStackTrace();
			}

			$this->oodsp_settings_manager->delete_system_user();
		}
	}
}
