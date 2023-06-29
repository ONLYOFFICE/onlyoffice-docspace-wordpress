<?php
/**
 * OODPS Admin hook.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin
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
 * OODPS Admin hook.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Hook {
	/**
	 * OODSP_Request_Manager
	 *
	 * @access   private
	 * @var      OODSP_Request_Manager    $request_manager
	 */
	private $request_manager;

	/**
	 * OODSP_Settings
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $plugin_settings;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->request_manager = new OODSP_Request_Manager();
		$this->plugin_settings = new OODSP_Settings();
	}

	/**
	 * DocSpace Credentials.
	 *
	 * @param int $post_id Post ID.
	 */
	public function rooms_share( $post_id ) {
		$rooms_id = array();
		$files_id = array();

		$post = get_post( $post_id );

		if ($post->post_status === 'publish') {
			preg_match_all( '/' . get_shortcode_regex( array( 'onlyoffice-docspace' ) ) . '/s', $post->post_content, $matches );
			$shortcode_expressions = $matches[3];

			if ( ! empty( $shortcode_expressions ) ) {
				foreach ( $shortcode_expressions as $shortcode_expression ) {
					$atts = shortcode_parse_atts( $shortcode_expression );

					if ( array_key_exists( 'roomid', $atts ) && ! empty( $atts['roomid'] ) ) {
						$rooms_id[] = intval( $atts['roomid'] );
					}

					if ( array_key_exists( 'fileid', $atts ) && ! empty( $atts['fileid'] ) ) {
						$files_id[] = intval( $atts['fileid'] );
					}
				}
			}

			$files_id = array_unique( $files_id );

			foreach ( $files_id as $file_id ) {
				$res_file_information = $this->request_manager->request_file_information( $file_id );

				if ( ! $res_file_information['error'] ) {
					$folder_id = $res_file_information['data']['folderId'];

					$res_folder_information = $this->request_manager->request_folder_information( $folder_id );

					if ( ! $res_folder_information['error'] ) {
						$room_id    = $res_folder_information['data']['pathParts'][1];
						$rooms_id[] = $room_id;
					} else {
						// phpcs:disable WordPress.PHP.DevelopmentFunctions
						error_log(
							sprintf(
								'Error in rooms_share() request_folder_information() (folderId: %1$s, status: %2$s)',
								$folder_id,
								$res_folder_information['error']
							)
						);
						// phpcs:enable
					}
				} else {
					// phpcs:disable WordPress.PHP.DevelopmentFunctions
					error_log(
						sprintf(
							'Error in rooms_share() request_file_information() (fileId: %1$s, status: %2$s)',
							$file_id,
							$res_file_information['error']
						)
					);
					// phpcs:enable
				}
			}

			$rooms_id = array_unique( $rooms_id );

			foreach ( $rooms_id as $room_id ) {
				$res_room_share_public_user = $this->request_manager->request_room_share_public_user( $room_id );
				if ( $res_room_share_public_user['error'] ) {
					// phpcs:disable WordPress.PHP.DevelopmentFunctions
					error_log(
						sprintf(
							'Error in rooms_share() request_room_share_public_user() (roomId: %1$s, status: %2$s)',
							$room_id,
							$res_room_share_public_user['error']
						)
					);
					// phpcs:enable
				}
			}
		}
	}

}
