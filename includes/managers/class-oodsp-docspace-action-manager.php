<?php
/**
 * OODSP DocSpace Action Manager
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/managers
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
 * Class OODSP_DocSpace_Action_Manager
 *
 * This class manages the actions for the ONLYOFFICE DocSpace plugin in WordPress.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_DocSpace_Action_Manager {
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
	 * Initializes the shared group.
	 *
	 * This method is responsible for setting up the shared group
	 * configuration within the DocSpace environment.
	 */
	public function init_shared_group() {
		$system_user             = $this->oodsp_settings_manager->get_system_user();
		$system_docspace_account = $this->oodsp_user_service->get_docspace_account( $system_user->get_id() );
		$docspace_accounts       = $this->oodsp_user_service->get_all_docspace_accounts();
		$docspace_accounts_ids   = array();
		
		foreach ( $docspace_accounts as $key => $docspace_account ) {
			$docspace_accounts_ids[] = $docspace_account->get_id();
		}

		$shared_group = $this->oodsp_settings_manager->get_shared_group();

		if ( empty( $shared_group ) ) {
			$docspace_group = $this->oodsp_docspace_client->create_group(
				'WordPress Users(' . get_bloginfo( 'name' ) . ')',
				$system_docspace_account->get_id(),
				$docspace_accounts_ids
			);

			$this->oodsp_settings_manager->set_shared_group( $docspace_group['id'] );
		} else {
			try {
				$docspace_group = $this->oodsp_docspace_client->update_group(
					$shared_group,
					'',
					$system_docspace_account->get_id(),
					$docspace_accounts_ids,
					array()
				);
			} catch ( OODSP_Docspace_Client_Exception $e ) {
				if ( 404 === $e->getCode() ) {
					$this->oodsp_settings_manager->delete_shared_group();

					$this->init_shared_group();
				}
			}
		}
	}
}
