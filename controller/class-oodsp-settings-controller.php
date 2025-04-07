<?php
/**
 * OODSP Settings Controller
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
	 */
	public function set_system_user() {
		check_ajax_referer( 'oodsp_settings_controller' );

		$user_name     = trim( OODSP_Utils::get_var_from_request( 'userName' ) );
		$password_hash = trim( OODSP_Utils::get_var_from_request( 'passwordHash' ) );

		if ( empty( $user_name ) || empty( $password_hash ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The required fields are empty', 'onlyoffice-docspace-plugin' ) ),
				400
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The current user does not have permission to perform this action', 'onlyoffice-docspace-plugin' ) ),
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
				array( 'message' => __( 'Invalid credentials. Please try again.', 'onlyoffice-docspace-plugin' ) ),
				401
			);
		}

		$docspace_user = $this->oodsp_docspace_client->get_user_by_name(
			$user_name,
			$authentication['token']
		);

		if ( ! $docspace_user['isAdmin'] ) {
			wp_send_json_error(
				array( 'message' => __( 'The specified user is not a ONLYOFFICE DocSpace administrator', 'onlyoffice-docspace-plugin' ) ),
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
