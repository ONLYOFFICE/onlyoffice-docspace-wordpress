<?php
/**
 * OODSP Docspace Client Exception
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/exception
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
 * Class OODSP_Docspace_Client_Exception
 *
 * This class represents an exception specific to the ONLYOFFICE Docspace client.
 * It extends the built-in RuntimeException class to provide additional context
 * and functionality for handling errors related to the ONLYOFFICE Docspace client.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */
class OODSP_Docspace_Client_Exception extends RuntimeException {

	/**
	 * Prints the stack trace of the exception.
	 *
	 * This method outputs the stack trace of the current exception instance.
	 *
	 * @return void
	 */
	public function printStackTrace() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $this->__tostring() );
		}
	}
}
