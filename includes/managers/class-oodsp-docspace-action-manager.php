<?php
/**
 * DocSpace Action Manager
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
class OODSP_Docspace_Action_Manager {
	/**
	 * OODSP_Settings
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $plugin_settings;

	/**
	 * OODSP_Settings
	 *
	 * @access   private
	 * @var      OODSP_Request_Manager    $plugin_settings
	 */
	private $request_manager;


	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->plugin_settings = new OODSP_Settings();
		$this->request_manager = new OODSP_Request_Manager();
	}


	/**
	 * Invite users to shared group.
	 *
	 * @param array $users_uuids List of group member IDs.
	 */
	public function invite_users_to_shared_group( $users_uuids ) {
		$docspace_shared_group_uuid = $this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_SHARED_GROUP, '' );

		if ( empty( $docspace_shared_group_uuid ) ) {
			return $this->create_shared_group( $users_uuids );
		}

		$res_docspace_group = $this->request_manager->request_update_group(
			$docspace_shared_group_uuid,
			null,
			$users_uuids,
			null
		);

		if ( $res_docspace_group['error'] ) {
			if ( OODSP_Request_Manager::ERROR_SHARED_GROUP_NOT_FOUND === $res_docspace_group['error'] ) {
				return $this->create_shared_group( $users_uuids );
			}
		}

		return $res_docspace_group;
	}

	/**
	 * Create shared group.
	 *
	 * @param array $users_uuids List of group member IDs.
	 */
	public function create_shared_group( $users_uuids ) {
		$res_docspace_group = $this->request_manager->request_create_group(
			'WordPress Users(' . get_bloginfo( 'name' ) . ')',
			$users_uuids
		);

		if ( $res_docspace_group['error'] ) {
			return $res_docspace_group;
		}

		$options = get_option( 'oodsp_settings' );
		$options[ OODSP_Settings::DOCSPACE_SHARED_GROUP ] = $res_docspace_group['data']['id'];
		update_option( 'oodsp_settings', $options );

		return $res_docspace_group;
	}
}
