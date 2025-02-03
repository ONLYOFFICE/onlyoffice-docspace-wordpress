<?php
/**
 * OODSP Docspace Account
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/model
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
 * Class OODSP_Docspace_Account
 *
 * This class represents a DocSpace account.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/model
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Docspace_Account {
	/**
	 * The DocSpace account username.
	 *
	 * @var string The username of the DocSpace account.
	 */
	private $user_name;

	/**
	 * The DocSpace account password hash.
	 *
	 * @var string The DocSpace account password hash.
	 */
	private $password_hash;

	/**
	 * OODSP_Docspace_Account constructor.
	 *
	 * @param string $user_name     The username of the DocSpace account.
	 * @param string $password_hash The password hash of the DocSpace account.
	 */
	public function __construct( $user_name = '', $password_hash = '' ) {
		$this->user_name     = $user_name;
		$this->password_hash = $password_hash;
	}

	/**
	 * Get the username of the DocSpace account.
	 *
	 * @return string
	 */
	public function get_user_name() {
		return $this->user_name;
	}

	/**
	 * Get the password hash of the DocSpace account.
	 *
	 * @return string
	 */
	public function get_password_hash() {
		return $this->password_hash;
	}

	/**
	 * Convert the DocSpace account to an array.
	 *
	 * @return array
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Create a DocSpace account from an array.
	 *
	 * @param array $data The data array containing the account information.
	 *
	 * @return OODSP_Docspace_Account
	 */
	public static function from_array( $data ) {
		$docspace_account = new OODSP_Docspace_Account();

		$reflection = new ReflectionClass( $docspace_account );
		foreach ( $data as $key => $value ) {
			if ( $reflection->hasProperty( $key ) ) {
					$property = $reflection->getProperty( $key );
					$property->setAccessible( true );
					$property->setValue( $docspace_account, $value );
			}
		}
		return $docspace_account;
	}
}
