<?php
/**
 * OODSP Docspace Client Exception
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/exception
 */

/**
 *
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
 * Class OODSP_Docspace_Client_Exception
 *
 * This class represents an exception specific to the ONLYOFFICE Docspace client.
 * It extends the built-in RuntimeException class to provide additional context
 * and functionality for handling errors related to the ONLYOFFICE Docspace client.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */
class OODSP_Docspace_Client_Exception extends RuntimeException {
	private const MESSAGE_TEMPLATE     = '%s: %s in %s:%s';
	private const STACK_TRACE_TEMPLATE = "\nStack trace:\n%s";
	private const REQUEST_TEMPLATE     = "\nRequest:\n%s";
	private const RESPONSE_TEMPLATE    = "\nResponse:\n%s";

	/**
	 * The request that caused the exception.
	 *
	 * @var mixed
	 */
	private $request;

	/**
	 * The response received from the request.
	 *
	 * @var mixed
	 */
	private $response;

	/**
	 * OODSP_Docspace_Client_Exception constructor.
	 *
	 * Initializes a new instance of the OODSP_Docspace_Client_Exception class.
	 *
	 * @param string $message  The exception message.
	 * @param mixed  $request  The request that caused the exception.
	 * @param mixed  $response The response received from the request.
	 * @param int    $code     The exception code (optional).
	 */
	public function __construct( $message, $request, $response, $code = 0 ) {
		parent::__construct( $message, $code );

		$this->request  = $request;
		$this->response = $response;
	}

	/**
	 * Prints the stack trace of the exception.
	 *
	 * This method outputs the stack trace of the current exception instance.
	 *
	 * @return void
	 */
	public function printStackTrace() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

			$message = sprintf(
				self::MESSAGE_TEMPLATE,
				get_class( $this ),
				$this->getMessage(),
				$this->getFile(),
				$this->getLine()
			);

			if ( ! empty( $this->request ) ) {
				$message .= sprintf(
					self::REQUEST_TEMPLATE,
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions
					print_r( $this->request, true )
				);
			}

			if ( ! empty( $this->response ) && is_array( $this->response ) ) {
				if ( isset( $this->response['http_response'] )
					&& $this->response['http_response'] instanceof WP_HTTP_Requests_Response
				) {
					$http_response = $this->response['http_response'];

					$message .= sprintf(
						self::RESPONSE_TEMPLATE,
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions
						print_r( $http_response->get_response_object(), true )
					);
				}
			}

			$message .= sprintf(
				self::STACK_TRACE_TEMPLATE,
				$this->getTraceAsString()
			);

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $message );
		}
	}
}
