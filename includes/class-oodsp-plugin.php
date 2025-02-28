<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes
 */

/**
 *
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
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var OODSP_Loader $loader    Maintains and registers all hooks for the plugin.
	 */
	private $loader;

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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();

		$this->loader                        = new OODSP_Loader();
		$this->oodsp_settings_manager        = new OODSP_Settings_Manager();
		$this->oodsp_user_service            = new OODSP_User_Service();
		$this->oodsp_docspace_client         = new OODSP_Docspace_Client( $this->oodsp_settings_manager );
		$this->oodsp_docspace_action_manager = new OODSP_Docspace_Action_Manager(
			$this->oodsp_docspace_client,
			$this->oodsp_user_service,
			$this->oodsp_settings_manager
		);

		$this->set_locale();
		$this->register_resources();
		$this->define_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( __DIR__ ) . 'controller/class-oodsp-settings-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'controller/class-oodsp-user-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/client/class-oodsp-docspace-client.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/exception/class-oodsp-docspace-client-exception.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-oodsp-docspace-action-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-oodsp-settings-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/model/class-oodsp-docspace-account.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/model/class-oodsp-system-user.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/resources/templates/class-oodsp-templates.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/resources/class-oodsp-resource-registry.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/service/class-oodsp-user-service.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/utils/class-oodsp-utils.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-oodsp-i18n.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-oodsp-loader.php';
		require_once plugin_dir_path( __DIR__ ) . 'pages/class-oodsp-base-page.php';
		require_once plugin_dir_path( __DIR__ ) . 'pages/main/class-oodsp-main-page.php';
		require_once plugin_dir_path( __DIR__ ) . 'pages/public-docspace/class-oodsp-public-docspace-page.php';
		require_once plugin_dir_path( __DIR__ ) . 'pages/settings/class-oodsp-settings-page.php';
		require_once plugin_dir_path( __DIR__ ) . 'pages/users/class-oodsp-users-page.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Onlyoffice_Plugin_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new OODSP_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register resources for the plugin.
	 *
	 * This method initializes and registers the resource registry,
	 * which handles the registration of scripts, styles, and other assets
	 * required for the plugin's functionality.
	 *
	 * @access private
	 * @return void
	 */
	private function register_resources() {
		$oodsp_resource_registry = new OODSP_Resource_Registry(
			$this->oodsp_user_service,
			$this->oodsp_settings_manager
		);

		$this->loader->add_action( 'init', $oodsp_resource_registry, 'register_resources' );
	}

	/**
	 * Define the hooks for the plugin.
	 *
	 * This method sets up various hooks for admin and public functionality.
	 *
	 * @access   private
	 */
	private function define_hooks() {
		$oodsp_main_page            = new OODSP_Main_Page(
			$this->oodsp_docspace_client,
			$this->oodsp_user_service,
			$this->oodsp_settings_manager,
		);
		$oodsp_settings_page        = new OODSP_Settings_Page(
			$this->oodsp_settings_manager,
			$this->oodsp_docspace_client,
			$this->oodsp_user_service
		);
		$oodsp_docspace_public_page = new OODSP_Public_DocSpace_Page(
			$this->oodsp_settings_manager,
			$this->oodsp_user_service
		);
		$oodsp_users_page           = new OODSP_Users_page(
			$this->oodsp_docspace_client,
			$this->oodsp_user_service,
			$this->oodsp_settings_manager,
		);

		$this->loader->add_action( 'admin_menu', $oodsp_main_page, 'init_menu' );
		$this->loader->add_action( 'admin_menu', $oodsp_settings_page, 'init_menu' );
		$this->loader->add_action( 'init', $oodsp_docspace_public_page, 'init_shortcode' );
		$this->loader->add_action( 'init', $oodsp_docspace_public_page, 'init_block' );

		$oodsp_settings_controller = new OODSP_Settings_Controller(
			$this->oodsp_docspace_client,
			$this->oodsp_user_service,
			$this->oodsp_settings_manager,
			$this->oodsp_docspace_action_manager
		);

		$oodsp_user_controller = new OODSP_User_Controller(
			$this->oodsp_docspace_client,
			$this->oodsp_user_service,
			$this->oodsp_settings_manager,
		);

		$this->loader->add_action( 'wp_ajax_oodsp_set_system_user', $oodsp_settings_controller, 'set_system_user' );
		$this->loader->add_action( 'wp_ajax_oodsp_delete_system_user', $oodsp_settings_controller, 'delete_system_user' );
		$this->loader->add_action( 'wp_ajax_oodsp_set_user', $oodsp_user_controller, 'set_user' );
		$this->loader->add_action( 'wp_ajax_oodsp_delete_user', $oodsp_user_controller, 'delete_user' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}
}
