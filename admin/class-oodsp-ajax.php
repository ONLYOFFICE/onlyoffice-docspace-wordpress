<?php
/**
 * Page ONLYOFFICE DocSpace.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes/files
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
 * Page ONLYOFFICE DocSpace.
 *
 * This class defines code necessary displaying a page ONLYOFFICE DocSpace.
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/admin
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Ajax {
	/**
	 *
	 * @access   private
	 * @var      OODSP_Security_Manager    $security_manager
	 */
	private $security_manager;

		/**
	 *
	 * @access   private
	 * @var      OODSP_Security_Manager    $security_manager
	 */
	private $public_docspace;

	public function __construct() {
		$this->security_manager = new OODSP_Security_Manager();
		$this->public_docspace = new OODSP_Public_DocSpace();
	}

	public function oodsp_credentials() {
		$user      = wp_get_current_user();
		$is_public = isset( $_REQUEST['is_public'] ) ? $_REQUEST['is_public'] : '';

		if( ! empty( $is_public ) &&  $is_public === "true" ) {
			$pass = $this->public_docspace::OODSP_PUBLIC_USER_PASS;
		} else {
			$hash = isset( $_REQUEST['hash'] ) ? $_REQUEST['hash'] : '';

			if( ! empty( $hash ) ) {
				$result = $this->security_manager->set_oodsp_user_pass( $user->ID, $hash );
				
				if ( ! $result ) {
					return wp_die( '0', 400 );
				}
				
				$pass = $hash;
			} else {
				$pass = $this->security_manager->get_oodsp_user_pass( $user->ID );

				if ( empty( $pass) ) {
					return wp_die( '0', 404 );
				}
			}
		}

		wp_die( $pass );
	}

	public function no_priv_oodsp_credentials() {
		wp_die( $this->public_docspace::OODSP_PUBLIC_USER_PASS );
	}

}
