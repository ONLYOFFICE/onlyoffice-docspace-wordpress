<?php
/**
 * OODSP User Controller
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/controller
 */

/**
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
	 * @param OODSP_Docspace_Client  $oodsp_docspace_client  The DocSpace client instance.
	 * @param OODSP_User_Service     $oodsp_user_service     The user service instance.
	 * @param OODSP_Settings_Manager $oodsp_settings_manager The settings manager instance.
	 */
	public function __construct(
		OODSP_Docspace_Client $oodsp_docspace_client,
		OODSP_User_Service $oodsp_user_service,
		OODSP_Settings_Manager $oodsp_settings_manager
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
				array( 'message' => __( 'The required fields are empty', 'onlyoffice-docspace-wordpress' ) ),
				400
			);
		}

		$user = wp_get_current_user();

		$docspace_account = new OODSP_Docspace_Account(
			$id,
			$user_name,
			$password_hash
		);

		$this->oodsp_user_service->put_docspace_account(
			$user->ID,
			$docspace_account
		);
	}

	/**
	 * Sets the system user for DocSpace integration.
	 *
	 * This method handles the authentication and setting of the system user
	 * for DocSpace. It verifies the provided credentials, checks for necessary
	 * permissions, and updates the system user information if successful.
	 *
	 * @return void
	 */
	public function set_system_user() {
		check_ajax_referer( 'oodsp_user_controller' );

		$user_name     = trim( OODSP_Utils::get_var_from_request( 'userName' ) );
		$password_hash = trim( OODSP_Utils::get_var_from_request( 'passwordHash' ) );

		if ( empty( $user_name ) || empty( $password_hash ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The required fields are empty', 'onlyoffice-docspace-wordpress' ) ),
				400
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The current user does not have permission to perform this action', 'onlyoffice-docspace-wordpress' ) ),
				403
			);
		}

		try {
			$authentication = $this->oodsp_docspace_client->login(
				$user_name,
				password_hash: $password_hash
			);
		} catch ( OODSP_Docspace_Client_Exception $e ) {
			wp_send_json_error(
				array( 'message' => __( 'User authentication failed', 'onlyoffice-docspace-wordpress' ) ),
				401
			);
		}

		$docspace_user = $this->oodsp_docspace_client->get_user_by_name(
			$user_name,
			$authentication['token']
		);

		if ( ! $docspace_user['isAdmin'] ) {
			wp_send_json_error(
				array( 'message' => __( 'The specified user is not a ONLYOFFICE DocSpace administrator', 'onlyoffice-docspace-wordpress' ) ),
				403
			);
		}

		$user = wp_get_current_user();

		$docspace_account = new OODSP_Docspace_Account(
			$docspace_user['id'],
			$user_name,
			$password_hash
		);

		$this->oodsp_user_service->put_docspace_account(
			$user->ID,
			$docspace_account
		);

		$system_user = new OODSP_System_User(
			$user->ID,
			$authentication['token']
		);

		$this->oodsp_settings_manager->set_system_user( $system_user );
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

		$user        = wp_get_current_user();
		$system_user = $this->oodsp_settings_manager->get_system_user();

		if ( ! empty( $system_user ) && $user->ID === $system_user->get_id() ) {
			try {
				$this->oodsp_docspace_client->logout();
			} catch ( OODSP_Docspace_Client_Exception $e ) {
				$e->printStackTrace();
			}

			$this->oodsp_settings_manager->delete_system_user();
		}

		$this->oodsp_user_service->delete_docspace_account( $user->ID );
	}
}
