<?php
/**
 * OODSP User Service
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/service
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
 * Class OODSP_User_Service
 *
 * This class represents a user service.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/service
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_User_Service {

	private const META_KEY = 'docspace_account';


	/**
	 * Retrieves all DocSpace accounts, optionally including empty accounts.
	 *
	 * This function queries the database for user metadata to find all users
	 * with DocSpace accounts. If $return_empty is set to true, it also includes
	 * users without DocSpace accounts.
	 *
	 * @param bool $return_empty Whether to include users without DocSpace accounts.
	 * @return array An associative array of user IDs and their DocSpace accounts.
	 */
	public function get_all_docspace_accounts( $return_empty = false ) {
		$args = array(
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key'     => self::META_KEY,
					'compare' => 'EXISTS',
				),
			),
			'fields'     => array( 'ID' ),
		);

		if ( $return_empty ) {
			$args['meta_query']['relation'] = 'OR';
			array_push(
				$args['meta_query'],
				array(
					'key'     => self::META_KEY,
					'compare' => 'NOT EXISTS',
				)
			);
		}

		$user_query = new WP_User_Query( $args );
		$users      = $user_query->get_results();

		$docspace_accounts = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$docspace_accounts[ $user->ID ] = self::get_docspace_account( $user->ID );
			}
		}

		return $docspace_accounts;
	}

	/**
	 * Retrieves a DocSpace account associated with a user.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return OODSP_Docspace_Account|null The DocSpace account instance or null if not found.
	 */
	public function get_docspace_account( $user_id ): OODSP_Docspace_Account|null {
		$data = get_user_meta( $user_id, self::META_KEY, true );

		if ( empty( $data ) ) {
			return null;
		}

		return OODSP_Docspace_Account::from_array( $data );
	}

	/**
	 * Updates a DocSpace account for a user.
	 *
	 * @param int                    $user_id          The user ID.
	 * @param OODSP_Docspace_Account $docspace_account The DocSpace account instance.
	 */
	public function put_docspace_account( $user_id, OODSP_Docspace_Account $docspace_account ) {
		update_user_meta(
			$user_id,
			self::META_KEY,
			$docspace_account->to_array()
		);
	}

	/**
	 * Deletes a DocSpace account for a user.
	 *
	 * @param int $user_id The user ID.
	 */
	public function delete_docspace_account( $user_id ) {
		delete_user_meta(
			$user_id,
			self::META_KEY
		);
	}

	/**
	 * Deletes DocSpace accounts for all users.
	 */
	public static function delete_docspace_account_for_all_users() {
		delete_metadata( 'user', 0, self::META_KEY, '', true );
	}
}
