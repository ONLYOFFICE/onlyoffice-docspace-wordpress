<?php
/**
 * ONLYOFFICE DocSpace Plugin Settings Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/settings
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
 * ONLYOFFICE DocSpace Plugin Settings Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/settings
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Settings_Page extends OODSP_Base_Page {

	/**
	 * Settings manager instance.
	 *
	 * @var    OODSP_Settings_Manager $oodsp_settings_manager Settings manager instance.
	 */
	protected OODSP_Settings_Manager $oodsp_settings_manager;

	/**
	 * DocSpace client instance.
	 *
	 * @var    OODSP_Docspace_Client $oodsp_docspace_client DocSpace client instance.
	 */
	protected OODSP_Docspace_Client $oodsp_docspace_client;

	/**
	 * User service instance.
	 *
	 * @var    OODSP_User_Service $oodsp_user_service User service instance.
	 */
	protected OODSP_User_Service $oodsp_user_service;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param OODSP_Settings_Manager $oodsp_settings_manager Settings manager instance.
	 * @param OODSP_Docspace_Client  $oodsp_docspace_client DocSpace client instance.
	 * @param OODSP_User_Service     $oodsp_user_service    User service instance.
	 */
	public function __construct(
		OODSP_Settings_Manager $oodsp_settings_manager,
		OODSP_Docspace_Client $oodsp_docspace_client,
		OODSP_User_Service $oodsp_user_service
	) {
		parent::__construct(
			'onlyoffice-docspace',
			__( 'ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ),
			__( 'Settings', 'onlyoffice-docspace-plugin' ),
			'manage_options',
			'onlyoffice-docspace-settings',
			plugin_dir_path( __FILE__ ),
			plugin_dir_url( __FILE__ ),
			array( 'jquery', 'jquery-ui-dialog', 'oodsp-ui' ),
			array( 'wp-jquery-ui-dialog', 'oodsp-ui' )
		);

		$this->oodsp_settings_manager = $oodsp_settings_manager;
		$this->oodsp_docspace_client  = $oodsp_docspace_client;
		$this->oodsp_user_service     = $oodsp_user_service;
	}

	/**
	 * Load necessary resources for the page.
	 *
	 * @return void
	 */
	protected function load_resources() {
		parent::load_resources();

		wp_enqueue_script(
			OODSP_PLUGIN_NAME . $this->menu_slug . '-authorization',
			$this->class_url . '/js/authorization.js',
			array( 'jquery', 'user-profile', 'oodsp-ui', 'oodsp-client', 'docspace-integration-sdk' ),
			OODSP_VERSION,
			true
		);

		wp_enqueue_style(
			OODSP_PLUGIN_NAME . $this->menu_slug . '-authorization',
			$this->class_url . '/css/authorization.css',
			array( 'oodsp-ui' ),
			OODSP_VERSION
		);

		wp_localize_script(
			OODSP_PLUGIN_NAME . $this->menu_slug . '-authorization',
			'_oodspAuthorization',
			array(
				'docspaceUrl' => $this->oodsp_settings_manager->get_docspace_url(),
			)
		);
	}

	/**
	 * Handle callback actions.
	 *
	 * @return void
	 */
	public function callback() {
		switch ( $this->current_action() ) {
			case 'update':
				$this->do_update_action();
				break;
			case 'delete':
				$this->do_delete_action();
				break;
		}
	}

	/**
	 * Handle update action.
	 *
	 * @return void
	 */
	private function do_update_action() {
		check_admin_referer( 'onlyoffice_docspace_settings-options' );

		$docspace_url = trim(
			OODSP_Utils::get_var_from_request(
				'docspace_url',
				'sanitize_url',
				''
			)
		);

		if ( empty( $docspace_url ) ) {
			wp_die( 'The required parameters is missing!', '', array( 'response' => 400 ) );
		}

		try {
			$docspace_csp_settings = $this->oodsp_docspace_client->get_csp_settings( $docspace_url );
			$site_url              = rtrim( get_site_url(), '/' );

			$allowed_domains = array_filter(
				$docspace_csp_settings['domains'],
				function ( $domain ) use ( $site_url ) {
					return rtrim( $domain, '/' ) === $site_url;
				}
			);

			if ( empty( $allowed_domains ) ) {
				$docspace_url        = rtrim( $docspace_url, '/' );
				$developer_tools_url = sprintf( '%s/portal-settings/developer-tools/javascript-sdk', $docspace_url );

				add_settings_error(
					'general',
					'settings_updated',
					sprintf(
						wp_kses(
							/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
							__( 'The current domain is not set in the Content Security Policy (CSP) settings. Please add it via %1$sthe Developer Tools section%2$s.', 'onlyoffice-docspace-plugin' ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							)
						),
						'<a href="' . esc_url( $developer_tools_url ) . '" target="_blank">',
						'</a>'
					)
				);
				return;
			}

			$this->oodsp_settings_manager->set_docspace_url( $docspace_url );

			add_settings_error(
				'general',
				'settings_updated',
				esc_html__( 'Settings saved', 'onlyoffice-docspace-plugin' ),
				'success'
			);
		} catch ( OODSP_Docspace_Client_Exception $e ) {
			add_settings_error(
				'general',
				'settings_updated',
				esc_html__( 'ONLYOFFICE DocSpace cannot be reached.', 'onlyoffice-docspace-plugin' )
			);

			$e->printStackTrace();
		}
	}

	/**
	 * Handle delete action.
	 *
	 * @return void
	 */
	private function do_delete_action() {
		check_admin_referer( 'onlyoffice_docspace_settings-options' );

		$this->oodsp_settings_manager->reset_settings();
		$this->oodsp_user_service->delete_docspace_account_for_all_users();

		add_settings_error(
			'general',
			'settings_updated',
			esc_html__( 'ONLYOFFICE DocSpace successfully disconnected', 'onlyoffice-docspace-plugin' ),
			'success'
		);
	}
}
