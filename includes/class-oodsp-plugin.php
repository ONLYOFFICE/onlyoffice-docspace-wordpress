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
		$this->version     = OODSP_VERSION;
		$this->plugin_name = OODSP_PLUGIN_NAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
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
		require_once plugin_dir_path( __DIR__ ) . 'admin/settings/class-oodsp-settings.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-oodsp-docspace.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-oodsp-ajax.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-oodsp-request-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-oodsp-security-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/utils/class-oodsp-utils.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/users/class-oodsp-users-list-table.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-oodsp-i18n.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-oodsp-loader.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-oodsp-public-docspace.php';

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
		$plugin_docspace = new OODSP_DocSpace();

		$this->loader->add_action( 'admin_menu', $plugin_docspace, 'init_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_docspace, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_docspace, 'enqueue_styles' );
		$this->loader->add_action( 'admin_footer', $plugin_docspace, 'docspace_login_template', 30 );

		$plugin_settings = new OODSP_Settings();

		add_filter(
			'set-screen-option',
			function ( $status, $option, $value ) {
				return ( 'docspace_page_onlyoffice_docspace_settings_per_page' === $option ) ? (int) $value : $status;
			},
			10,
			3
		);

		$this->loader->add_action( 'admin_menu', $plugin_settings, 'init_menu' );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'init' );

		$plugin_ajax = new OODSP_Ajax();
		$this->loader->add_action( 'wp_ajax_oodsp_credentials', $plugin_ajax, 'oodsp_credentials' );
		$this->loader->add_action( 'wp_ajax_nopriv_oodsp_credentials', $plugin_ajax, 'no_priv_oodsp_credentials' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public_docspace = new OODSP_Public_DocSpace();

		$this->loader->add_action( 'init', $plugin_public_docspace, 'init_shortcodes' );
		$this->loader->add_action( 'init', $plugin_public_docspace, 'onlyoffice_custom_block' );
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
