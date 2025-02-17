<?php
/**
 * OODSP utils
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/utils
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
 * OODPS Utils.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/utils
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Utils {
	/**
	 * LOCALES for DocSpace
	 */
	const LOCALES = array(
		'az',
		'bg',
		'cs',
		'de',
		'el-GR',
		'en-GB',
		'en-US',
		'es',
		'fi',
		'fr',
		'hy-AM',
		'it',
		'ja-JP',
		'ko-KR',
		'lo-LA',
		'lv',
		'nl',
		'pl',
		'pt',
		'pt-BR',
		'ro',
		'ru',
		'sk',
		'sl',
		'tr',
		'uk-UA',
		'vi',
		'zh-CN',
	);

	/**
	 *  DocSpace login template.
	 */
	public static function get_locale_for_docspace() {
		$locale = str_replace( '_', '-', get_user_locale() );

		if ( in_array( $locale, self::LOCALES, true ) ) {
			return $locale;
		} else {
			$locale = explode( '-', $locale )[0];
			foreach ( self::LOCALES as $value ) {
				if ( str_starts_with( $value, $locale ) ) {
					return $value;
				}
			}
		}

		return 'en-US';
	}

	/**
	 * Generates a random DocSpace password hash.
	 *
	 * @param array $hash_settings An array containing hash settings (salt, iterations, size).
	 * @return string The generated password hash.
	 */
	public static function generate_random_docspace_password_hash( $hash_settings ) {
		$password = wp_generate_password(
			16,
			true,
			false
		);

		$bits = hash_pbkdf2(
			'sha256',
			$password,
			$hash_settings['salt'],
			$hash_settings['iterations'],
			$hash_settings['size'] / 8,
			true
		);

		return bin2hex( $bits );
	}

	/**
	 * Extracts DocSpace user data from a WordPress user object.
	 *
	 * @param WP_User $user WordPress user object.
	 * @return array An array containing email, first name, and last name.
	 */
	public static function get_docspace_user_data_from_wp_user( $user ) {
		$email      = $user->user_email;
		$login      = $user->user_login;
		$first_name = preg_replace( '/[^\p{L}\p{M} \-]/u', '-', $user->first_name );
		$last_name  = preg_replace( '/[^\p{L}\p{M} \-]/u', '-', $user->last_name );

		if ( $first_name && ! $last_name ) {
			$last_name = $first_name;
		}

		if ( ! $first_name && $last_name ) {
			$first_name = $last_name;
		}

		if ( ! $first_name && ! $last_name ) {
			$first_name = preg_replace( '/[^\p{L}\p{M} \-]/u', '-', $login );
			$last_name  = $first_name;
		}

		return array( $email, $first_name, $last_name );
	}

	/**
	 * Get sanitized variable from request.
	 *
	 * @param string $var_name      The name of the variable to retrieve.
	 * @param string $filter_type   Optional. The type of filtering to apply. Default 'sanitize_text_field'.
	 * @param mixed  $default_value Optional. Default value to return if the variable is not set.
	 *
	 * @return mixed The filtered value of the requested variable.
	 */
	public static function get_var_from_request(
		$var_name,
		$filter_type = 'sanitize_text_field',
		$default_value = ''
	) {
		if ( ! isset( $_REQUEST[ $var_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $default_value;
		}

		$var_value = wp_unslash( $_REQUEST[ $var_name ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		switch ( $filter_type ) {
			case 'sanitize_text_field':
				return sanitize_text_field( $var_value );
			case 'sanitize_url':
				return esc_url_raw( $var_value );
			case 'sanitize_email':
				return sanitize_email( $var_value );
			case 'absint':
				return absint( $var_value );
			default:
				return sanitize_text_field( $var_value );
		}
	}
}
