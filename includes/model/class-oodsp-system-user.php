<?php
/**
 * OODSP System User
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/model
 */

/**
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
 * Class OODSP_System_User
 *
 * This class represents a system user.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/model
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_System_User {
	/**
	 * The ID of the system user.
	 *
	 * @var string The ID of the system user.
	 */
	private $id;

	/**
	 * The username of the system user.
	 *
	 * @var string The username of the system user.
	 */
	private $user_name;

	/**
	 * The password hash of the system user.
	 *
	 * @var string The password hash of the system user.
	 */
	private $password_hash;

	/**
	 * The token of the system user.
	 *
	 * @var string The token of the system user.
	 */
	private $token;

	/**
	 * Constructor for the system user.
	 *
	 * @param string $id           The ID of the system user.
	 * @param string $user_name    The username of the system user.
	 * @param string $password_hash The password hash of the system user.
	 * @param string $token        The token of the system user.
	 */
	public function __construct( $id = '', $user_name = '', $password_hash = '', $token = '' ) {
		$this->id            = $id;
		$this->user_name     = $user_name;
		$this->password_hash = $password_hash;
		$this->token         = $token;
	}

	/**
	 * Get the ID of the system user.
	 *
	 * @return string The ID of the system user.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the username of the system user.
	 *
	 * @return string The username of the system user.
	 */
	public function get_user_name() {
		return $this->user_name;
	}

	/**
	 * Get the password hash of the system user.
	 *
	 * @return string The password hash of the system user.
	 */
	public function get_password_hash() {
		return $this->password_hash;
	}

	/**
	 * Get the token of the system user.
	 *
	 * @return string The token of the system user.
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Convert the system user to an array.
	 *
	 * @return array The array representation of the system user.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Create a system user from an array.
	 *
	 * @param array $data The data array containing the system user information.
	 *
	 * @return OODSP_System_User The created system user.
	 */
	public static function from_array( $data ) {
		$system_user = new OODSP_System_User();

		$reflection = new ReflectionClass( $system_user );
		foreach ( $data as $key => $value ) {
			if ( $reflection->hasProperty( $key ) ) {
				$property = $reflection->getProperty( $key );
				$property->setAccessible( true );
				$property->setValue( $system_user, $value );
			}
		}
		return $system_user;
	}
}
