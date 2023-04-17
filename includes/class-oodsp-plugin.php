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
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes
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
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      OODSP_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

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

		$this->version     = ONLYOFFICE_DOCSPACE_PLUGIN_VERSION;
		$this->plugin_name = 'onlyoffice-docspace-plugin';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->init_ds_frame();
		$this->init_settings();
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-oodsp-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'controllers/class-oodsp-frontend-controller.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/users/class-oodsp-users-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-oodsp-docspace.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-oodsp-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-oodsp-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-oodsp-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-oodsp-wizard.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-oodsp-public.php';

		$this->loader = new OODSP_Loader();
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
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new OODSP_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new OODSP_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_routes' );
	}

	/**
	 * Init DocSpace page.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_ds_frame() {
		$plugin_ds_frame = new OODSP_DocSpace( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $plugin_ds_frame, 'init_menu' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_ds_frame, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_ds_frame, 'enqueue_scripts' );

		$OODSP_frontend_controller = new OODSP_Frontend_Controller( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $OODSP_frontend_controller, 'init_shortcodes' );
		$this->loader->add_action( 'init', $OODSP_frontend_controller, 'onlyoffice_custom_block' );
	}

	/**
	 * Init DocSpace Settings page.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_settings() {
		$plugin_settings = new OODSP_Settings();
		// $plugin_wizard   = new OODSP_Wizard( $this->get_plugin_name(), $this->get_version() );

		add_filter(
			'set-screen-option',
			function( $status, $option, $value ) {
				return ( 'docspace_page_onlyoffice_docspace_settings_per_page' === $option ) ? (int) $value : $status;
			},
			10,
			3
		);

		$this->loader->add_action( 'admin_menu', $plugin_settings, 'init_menu' );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'init' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_settings, 'enqueue_scripts' );

		// $this->loader->add_action( 'admin_menu', $plugin_wizard, 'init_menu' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    OODSP_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
