<?php
/**
 * OODSP User Controller
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/controller
 */

/**
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
 * Class OODSP_User_Controller
 *
 * This class represents a user controller.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/controller
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_User_Controller {
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
	 * Constructor for the OODSP_User_Controller class.
	 *
	 * @param OODSP_Docspace_Client  $oodsp_docspace_client         The DocSpace client instance.
	 * @param OODSP_User_Service     $oodsp_user_service            The user service instance.
	 * @param OODSP_Settings_Manager $oodsp_settings_manager        The settings manager instance.
	 */
	public function __construct(
		OODSP_Docspace_Client $oodsp_docspace_client,
		OODSP_User_Service $oodsp_user_service,
		OODSP_Settings_Manager $oodsp_settings_manager,
	) {
		$this->oodsp_docspace_client  = $oodsp_docspace_client;
		$this->oodsp_user_service     = $oodsp_user_service;
		$this->oodsp_settings_manager = $oodsp_settings_manager;
	}

	/**
	 * Sets the DocSpace account for the current user.
	 *
	 * This method handles the creation or update of the DocSpace account
	 * for the current WordPress user. It validates the input, creates a
	 * new OODSP_Docspace_Account object, and stores it using the user service.
	 *
	 * @return void
	 */
	public function set_user() {
		check_ajax_referer( 'oodsp_user_controller' );

		$id            = trim( OODSP_Utils::get_var_from_request( 'id' ) );
		$user_name     = trim( OODSP_Utils::get_var_from_request( 'userName' ) );
		$password_hash = trim( OODSP_Utils::get_var_from_request( 'passwordHash' ) );

		if ( empty( $id ) || empty( $user_name ) || empty( $password_hash ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The required fields are empty', 'onlyoffice-docspace-plugin' ) ),
				400
			);
		}

		$user         = wp_get_current_user();
		$system_user  = $this->oodsp_settings_manager->get_system_user();
		$shared_group = $this->oodsp_settings_manager->get_shared_group();

		$docspace_account = new OODSP_Docspace_Account(
			$id,
			$user_name,
			$password_hash
		);

		$this->oodsp_user_service->put_docspace_account(
			$user->ID,
			$docspace_account
		);

		if ( ! empty( $system_user ) && ! empty( $shared_group ) ) {
			try {
				$this->oodsp_docspace_client->update_group(
					$shared_group,
					'',
					'',
					array( $docspace_account->get_id() ),
					array(),
				);
			} catch ( OODSP_Docspace_Client_Exception $e ) {
				$e->printStackTrace();
			}
		}
	}

	/**
	 * Deletes the DocSpace account for the current user.
	 *
	 * This method handles the deletion of the user's DocSpace account,
	 * including logging out from DocSpace if the user is the system user,
	 * and removing the associated DocSpace account information.
	 */
	public function delete_user() {
		check_ajax_referer( 'oodsp_user_controller' );

		$user = wp_get_current_user();

		$this->oodsp_user_service->delete_docspace_account( $user->ID );
	}

	/**
	 * Resets the password for a DocSpace user account.
	 *
	 * This method handles the password reset request by sending
	 * a reset link to the specified email address. It validates
	 * the email input and communicates with the DocSpace client
	 * to initiate the password reset process.
	 */
	public function reset_password() {
		check_ajax_referer( 'oodsp_user_controller' );

		$email = trim( OODSP_Utils::get_var_from_request( 'email' ) );

		if ( empty( $email ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The required fields are empty', 'onlyoffice-docspace-plugin' ) ),
				400
			);
		}

		try {
			$this->oodsp_docspace_client->reset_password( $email );
		} catch ( OODSP_Docspace_Client_Exception $e ) {
			wp_send_json_error(
				array( 'message' => $e->getMessage() ),
				$e->getCode()
			);
		}
	}
}
