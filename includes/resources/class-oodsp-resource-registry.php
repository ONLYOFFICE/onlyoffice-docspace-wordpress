<?php
/**
 * OODSP Resource Registry
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/resources
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
 * Class OODSP_Resource_Registry
 *
 * This class represents a resource registry.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/resources
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Resource_Registry {
	private const RESOURCE_NAME_PREFIX = 'oodsp';
	private const RESOURCE_JS_PATH     = OODSP_PLUGIN_URL . 'includes/resources/js/';
	private const RESOURCE_CSS_PATH    = OODSP_PLUGIN_URL . 'includes/resources/css/';

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
	 * OODSP_Resource_Registry constructor.
	 *
	 * @param OODSP_User_Service     $oodsp_user_service The user service instance.
	 * @param OODSP_Settings_Manager $oodsp_settings_manager The settings manager instance.
	 */
	public function __construct(
		OODSP_User_Service $oodsp_user_service,
		OODSP_Settings_Manager $oodsp_settings_manager
	) {
		$this->oodsp_user_service     = $oodsp_user_service;
		$this->oodsp_settings_manager = $oodsp_settings_manager;
	}

	/**
	 * Register resources for the plugin.
	 *
	 * This method registers various JavaScript and CSS resources
	 * needed for the plugin's functionality.
	 *
	 * @return void
	 */
	public function register_resources() {
		$user             = wp_get_current_user();
		$docspace_account = $this->oodsp_user_service->get_docspace_account( $user->ID );

		$this->oodsp_docspace_integration_sdk();
		$this->oodsp_client();
		$this->oodsp_ui();
		$this->oodsp_login_page_template();
		$this->oodsp_error_page_template();
		$this->oodsp_main(
			$this->oodsp_settings_manager->get_docspace_url(),
			$docspace_account
		);
	}

	/**
	 * Registers and localizes the main script for ONLYOFFICE DocSpace integration.
	 *
	 * This function registers the main JavaScript file for the plugin and sets up
	 * necessary dependencies. It also localizes the script with important data
	 * such as the DocSpace URL and user account information.
	 *
	 * @param string                      $docspace_url     The URL of the DocSpace instance.
	 * @param OODSP_Docspace_Account|null $docspace_account The DocSpace account object or null if not available.
	 *
	 * @return void
	 */
	private function oodsp_main( $docspace_url, OODSP_Docspace_Account|null $docspace_account ) {
		wp_register_script(
			self::RESOURCE_NAME_PREFIX . '-main',
			self::RESOURCE_JS_PATH . 'oodsp-main.js',
			array(
				'jquery',
				'wp-util',
				'docspace-integration-sdk',
				'oodsp-client',
				'oodsp-login-page-template',
				'oodsp-error-page-template',
			),
			OODSP_VERSION,
			true
		);

		wp_register_style(
			self::RESOURCE_NAME_PREFIX . '-main',
			'',
			array(
				'oodsp-login-page-template',
				'oodsp-error-page-template',
			),
			OODSP_VERSION
		);

		wp_localize_script(
			self::RESOURCE_NAME_PREFIX . '-main',
			'_oodspMain',
			array(
				'docspaceUrl'  => $docspace_url,
				'docspaceUser' => ! empty( $docspace_account )
					? $docspace_account->to_array()
					: null,
				'locale'       => OODSP_Utils::get_locale_for_docspace(),
			)
		);
	}

	/**
	 * Registers and localizes the DocSpace client script.
	 *
	 * This function registers the client-side JavaScript file and sets up
	 * localized data for AJAX requests and other client-side operations.
	 *
	 * @return void
	 */
	private function oodsp_client() {
		wp_register_script(
			self::RESOURCE_NAME_PREFIX . '-client',
			self::RESOURCE_JS_PATH . 'oodsp-client.js',
			array(),
			OODSP_VERSION,
			true
		);

		wp_localize_script(
			self::RESOURCE_NAME_PREFIX . '-client',
			'_oodspClient',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'oodsp_user_controller' ),
			)
		);
	}

	/**
	 * Registers and enqueues the UI scripts and styles.
	 *
	 * This function registers the JavaScript and CSS files for the UI components
	 * of the ONLYOFFICE DocSpace plugin.
	 *
	 * @return void
	 */
	private function oodsp_ui() {
		wp_register_script(
			self::RESOURCE_NAME_PREFIX . '-ui',
			self::RESOURCE_JS_PATH . 'oodsp-ui.js',
			array( 'jquery', 'wp-util', 'jquery-ui-tooltip' ),
			OODSP_VERSION,
			true
		);

		wp_register_style(
			self::RESOURCE_NAME_PREFIX . '-ui',
			self::RESOURCE_CSS_PATH . 'oodsp-ui.css',
			array(),
			OODSP_VERSION
		);
	}

	/**
	 * Registers and enqueues the login page template resources.
	 *
	 * This method sets up the necessary JavaScript and CSS for the login page,
	 * including translations and template rendering.
	 *
	 * @return void
	 */
	private function oodsp_login_page_template() {
		wp_register_script(
			self::RESOURCE_NAME_PREFIX . '-login-page-template',
			self::RESOURCE_JS_PATH . 'oodsp-login-page-template.js',
			array( 'jquery', 'wp-util' ),
			OODSP_VERSION,
			true
		);

		wp_register_style(
			self::RESOURCE_NAME_PREFIX . '-login-page-template',
			self::RESOURCE_CSS_PATH . 'oodsp-login-page-template.css',
			array( 'login' ),
			OODSP_VERSION
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				self::RESOURCE_NAME_PREFIX . '-login-page-template',
				'onlyoffice-docspace-plugin',
				plugin_dir_path( OODSP_PLUGIN_FILE ) . 'languages/'
			);
		}

		add_action( 'admin_footer', array( 'OODSP_Templates', 'oodsp_login_page_template' ), 30 );
	}

	/**
	 * Registers and enqueues the error page template resources.
	 *
	 * This method sets up the necessary JavaScript and CSS for the error page,
	 * including translations and template rendering.
	 *
	 * @return void
	 */
	private static function oodsp_error_page_template() {
		wp_register_script(
			self::RESOURCE_NAME_PREFIX . '-error-page-template',
			self::RESOURCE_JS_PATH . 'oodsp-error-page-template.js',
			array( 'jquery', 'wp-util' ),
			OODSP_VERSION,
			true
		);

		wp_register_style(
			self::RESOURCE_NAME_PREFIX . '-error-page-template',
			self::RESOURCE_CSS_PATH . 'oodsp-error-page-template.css',
			array(),
			OODSP_VERSION
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				self::RESOURCE_NAME_PREFIX . '-error-page-template',
				'onlyoffice-docspace-plugin',
				plugin_dir_path( OODSP_PLUGIN_FILE ) . 'languages/'
			);
		}

		wp_localize_script(
			self::RESOURCE_NAME_PREFIX . '-error-page-template',
			'_oodspTemplates',
			array(
				'resourceUrl'       => esc_url( OODSP_PLUGIN_URL . 'includes/resources/images/' ),
				'isAdmin'           => current_user_can( 'manage_options' ),
				'hasDocSpaceWindow' => current_user_can( 'upload_files' ),
				'isAnonymous'       => ! is_user_logged_in(),
				'settingsPageUrl'   => esc_url( admin_url( 'admin.php?page=onlyoffice-docspace-settings' ) ),
			)
		);

		add_action( 'admin_footer', array( 'OODSP_Templates', 'oodsp_error_page_template' ), 30 );
	}

	/**
	 * Registers the DocSpace Integration SDK script.
	 *
	 * This method registers the external DocSpace Integration SDK JavaScript file,
	 * which is essential for integrating DocSpace functionality into the plugin.
	 *
	 * @return void
	 */
	private function oodsp_docspace_integration_sdk() {
		wp_register_script(
			'docspace-integration-sdk',
			OODSP_PLUGIN_URL . 'assets-onlyoffice-docspace/js/docspace-integration-sdk.js',
			array(),
			OODSP_VERSION,
			true
		);
	}
}
