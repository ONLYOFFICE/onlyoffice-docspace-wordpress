<?php
/**
 * ONLYOFFICE DocSpace Plugin Main Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/main
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

if ( ! function_exists( 'WP_Filesystem' ) ) {
	include ABSPATH . '/wp-admin/includes/file.php';
	WP_Filesystem();
}

/**
 * ONLYOFFICE DocSpace Plugin Main Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/main
 */
class OODSP_Main_Page extends OODSP_Base_Page {
	/**
	 * OODSP_Docspace_Client
	 *
	 * @var      OODSP_Docspace_Client    $oodsp_docspace_client
	 */
	private OODSP_Docspace_Client $oodsp_docspace_client;

	/**
	 * OODSP_User_Service
	 *
	 * @var      OODSP_User_Service    $oodsp_user_service
	 */
	private OODSP_User_Service $oodsp_user_service;

	/**
	 * OODSP_Settings_Manager
	 *
	 * @var      OODSP_Settings_Manager    $oodsp_settings_manager
	 */
	private OODSP_Settings_Manager $oodsp_settings_manager;

	/**
	 * Constructor for the OODSP_Main_Page class.
	 *
	 * @param OODSP_Docspace_Client  $oodsp_docspace_client  DocSpace client instance.
	 * @param OODSP_User_Service     $oodsp_user_service     User service instance.
	 * @param OODSP_Settings_Manager $oodsp_settings_manager Settings manager instance.
	 */
	public function __construct(
		OODSP_Docspace_Client $oodsp_docspace_client,
		OODSP_User_Service $oodsp_user_service,
		OODSP_Settings_Manager $oodsp_settings_manager
	) {
		parent::__construct(
			'onlyoffice-docspace',
			'DocSpace',
			'DocSpace',
			'upload_files',
			'onlyoffice-docspace',
			plugin_dir_path( __FILE__ ),
			plugin_dir_url( __FILE__ ),
			array( 'oodsp-main' ),
			array( 'oodsp-main' )
		);

		$this->oodsp_docspace_client  = $oodsp_docspace_client;
		$this->oodsp_settings_manager = $oodsp_settings_manager;
		$this->oodsp_user_service     = $oodsp_user_service;
	}

	/**
	 * Initialize the menu.
	 */
	public function init_menu() {
		global $wp_filesystem;

		$logo_svg = $wp_filesystem->get_contents( $this->class_path . '/images/menu_icon.svg' );

		add_menu_page(
			'DocSpace',
			'DocSpace',
			'upload_files',
			'onlyoffice-docspace',
			array( $this, 'view' ),
			'data:image/svg+xml;base64,' . base64_encode( $logo_svg ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		);

		parent::init_menu();
	}
}
