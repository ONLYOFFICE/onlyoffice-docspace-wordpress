<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       ttps://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/public
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
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/public
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {}


	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {}

	/**
	 * Register the routes.
	 *
	 * @since    1.0.0
	 */
	public function register_routes() {
		register_rest_route( 'oodsp', '/credential', array(
			'methods'             => 'POST',
			'callback'            => array($this, 'get_oodsp_credential'),
			'permission_callback' => array($this, 'permissions_check')
		) );

		register_rest_route( 'oodsp', '/credential', array(
			'methods'  => 'PUT',
			'callback' => array($this, 'set_oodsp_credential'),
			'permission_callback' => array($this, 'permissions_check')
		) );
	}

	public function get_oodsp_credential( $request ) {
		if ( isset( $_COOKIE[LOGGED_IN_COOKIE] ) ) {
			$user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );

			if ( $user_id ) {
				$oodsp_security_manager = new OODSP_Security_Manager();
				
				return $oodsp_security_manager->get_oodsp_user_pass( $user_id );
			}
		} 

		return new WP_Error( 'rest_forbidden', '', array( 'status' => 401 ) );
	}

	public function set_oodsp_credential( $request ) {
		if ( isset( $_COOKIE[LOGGED_IN_COOKIE] ) ) {
			$user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
			$body = json_decode( $request->get_body(), true );

			if ( $user_id && !empty( $body['hash'] ) ) {
				$oodsp_security_manager = new OODSP_Security_Manager();

				$result = $oodsp_security_manager->set_oodsp_user_pass( $user_id, $body['hash'] );

				if ( ! $result ) {
					return wp_send_json_error();
				}
				
				return wp_send_json_success();
			}
		} 

		return new WP_Error( 'rest_forbidden', '', array( 'status' => 401 ) );
	}

	public function permissions_check( $request ) {
		if ( isset( $_COOKIE[LOGGED_IN_COOKIE] ) ) {
			$user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );

			if ( $user_id ) {
				return true;
			}
		}

		return false;
	}
}
