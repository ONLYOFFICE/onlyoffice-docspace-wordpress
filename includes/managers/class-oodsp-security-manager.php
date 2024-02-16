<?php
/**
 * Security manager
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/managers
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
 * Request manager
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Security_Manager {
	const DOCSPACE_USERS_TABLE = 'docspace_users';

	/**
	 * Add DocSpace user password to DB.
	 *
	 * @param string $user_id User ID.
	 * @param string $password Password DocSpace user.
	 */
	public function set_oodsp_user_pass( $user_id, $password ) {
		global $wpdb;
		$oodsp_users_table = $wpdb->prefix . self::DOCSPACE_USERS_TABLE;

		$old_user_pass = $this->get_oodsp_user_pass( $user_id );

		if ( $old_user_pass ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$result = $wpdb->update(
				$oodsp_users_table,
				array(
					'user_id'   => $user_id,
					'user_pass' => $password,
				),
				array( 'user_id' => $user_id )
			);

		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$result = $wpdb->insert(
				$oodsp_users_table,
				array(
					'user_id'   => $user_id,
					'user_pass' => $password,
				)
			);
		}

		return $result;
	}

	/**
	 * Return DocSpace user password.
	 *
	 * @param string $user_id User ID.
	 */
	public function get_oodsp_user_pass( $user_id ) {
		global $wpdb;
		$oodsp_users_table = $wpdb->prefix . self::DOCSPACE_USERS_TABLE;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->get_row( $wpdb->prepare( 'SELECT user_pass FROM %i WHERE user_id = %s LIMIT 1', $oodsp_users_table, $user_id ) );

		if ( ! empty( $result ) ) {
			return $result->user_pass;
		}

		return null;
	}
}
