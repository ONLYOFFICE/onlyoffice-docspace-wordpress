<?php
/**
 * OODSP Settings Manager
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/managers
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
 * Class OODSP_Settings_Manager
 *
 * This class manages the settings for the ONLYOFFICE DocSpace plugin in WordPress.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Settings_Manager {
	/**
	 * DocSpace URL setting key.
	 * Used to store and retrieve the DocSpace instance URL.
	 */
	const DOCSPACE_URL = 'docspace_url';

	/**
	 * System user setting key.
	 * Used to store and retrieve the system user credentials.
	 */
	const SYSTEM_USER = 'system_user';

	/**
	 * Retrieves the DocSpace URL from settings.
	 *
	 * @return string The configured DocSpace URL or empty string if not set.
	 */
	public function get_docspace_url() {
		return $this->get_setting( self::DOCSPACE_URL, '' );
	}

	/**
	 * Retrieves the system user information.
	 *
	 * @return OODSP_System_User|null The system user object if exists, null otherwise.
	 */
	public function get_system_user(): OODSP_System_User|null {
		$data = $this->get_setting( self::SYSTEM_USER, '' );

		if ( empty( $data ) ) {
			return null;
		}

		return OODSP_System_User::from_array( $data );
	}

	/**
	 * Checks if a system user exists in the settings.
	 *
	 * @return bool True if system user exists, false otherwise.
	 */
	public function exist_system_user() {
		$data = $this->get_setting( self::SYSTEM_USER, '' );

		return ! empty( $data );
	}

	/**
	 * Sets the DocSpace URL in the settings.
	 *
	 * @param  string $docspace_url The URL of the DocSpace instance.
	 * @return mixed Result of the setting update operation.
	 */
	public function set_docspace_url( $docspace_url ) {
		return $this->set_setting( self::DOCSPACE_URL, $docspace_url );
	}

	/**
	 * Sets the system user information in the settings.
	 *
	 * @param  OODSP_System_User $system_user The system user object to store.
	 * @return mixed Result of the setting update operation.
	 */
	public function set_system_user( OODSP_System_User $system_user ) {
		return $this->set_setting( self::SYSTEM_USER, $system_user->to_array() );
	}

	/**
	 * Deletes the system user information from settings.
	 * This effectively removes the system user credentials.
	 */
	public function delete_system_user() {
		$this->set_setting( self::SYSTEM_USER, null );
	}

	/**
	 * Resets the settings to their default values.
	 *
	 * This function is responsible for resetting all the settings
	 * managed by the OODSP Settings Manager to their default state.
	 *
	 * @return void
	 */
	public function reset_settings() {
		update_option( 'oodsp_settings', array() );
	}

	/**
	 * Get a setting value by key.
	 *
	 * @param  string $key The key of the setting.
	 * @param  mixed  $def The default value to return if the setting is not found.
	 * @return mixed The value of the setting or the default value.
	 */
	private function get_setting( $key, $def = '' ) {
		$options = get_option( 'oodsp_settings' );

		if ( ! empty( $options ) && array_key_exists( $key, $options ) ) {
			return $options[ $key ];
		}

		return $def;
	}

	/**
	 * Set a setting value by key.
	 *
	 * @param string $key   The key of the setting.
	 * @param mixed  $value The value to set for the setting.
	 */
	private function set_setting( $key, $value ) {
		$options = get_option( 'oodsp_settings' );

		$options[ $key ] = $value;

		update_option( 'oodsp_settings', $options );
	}
}
