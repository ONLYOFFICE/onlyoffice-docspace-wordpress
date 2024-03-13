<?php
/**
 * OODPS Admin ajax.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin
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
 * OODPS Admin ajax.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Ajax {
	/**
	 * OODSP_Security_Manager
	 *
	 * @access   private
	 * @var      OODSP_Security_Manager    $security_manager
	 */
	private $security_manager;

	/**
	 * OODSP_Settings
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $public_docspace;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->security_manager = new OODSP_Security_Manager();
		$this->public_docspace  = new OODSP_Public_DocSpace();
	}

	/**
	 * DocSpace Credentials.
	 */
	public function oodsp_credentials() {
		$user = wp_get_current_user();

		$hash = isset( $_REQUEST['hash'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hash'] ) ) : ''; // phpcs:ignore

		if ( ! empty( $hash ) ) {
			$result = $this->security_manager->set_oodsp_user_pass( $user->ID, $hash );

			if ( ! $result ) {
				return wp_die( '0', 400 );
			}

			$pass = $hash;
		} else {
			$pass = $this->security_manager->get_oodsp_user_pass( $user->ID );

			if ( empty( $pass ) ) {
				return wp_die( '0', 404 );
			}
		}

		wp_die( esc_attr( $pass ) );
	}
}
