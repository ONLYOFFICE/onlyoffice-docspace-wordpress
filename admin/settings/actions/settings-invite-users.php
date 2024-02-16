<?php
/**
 * Invite ONLYOFFICE DocSpace users action.
 *
 * @package Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin/settings/actions
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
 * Innite users.
 */
function oodsp_invite_users() {
	if ( isset( $_REQUEST['wp_http_referer'] ) ) {
		$redirect = remove_query_arg( array( 'wp_http_referer', 'updated', 'delete_count' ), sanitize_text_field( wp_unslash( $_REQUEST['wp_http_referer'] ) ) );
	} else {
		$redirect = 'admin.php?page=onlyoffice-docspace-settings&users=true';
	}

	check_admin_referer( 'bulk-users' );

	if ( empty( $_REQUEST['users'] ) ) {
		wp_safe_redirect( $redirect );
		exit;
	}

	if ( is_array( $_REQUEST['users'] ) ) {
		$users = array_map(
			function ( string $user ) {
				$user = explode( '$$', $user, 2 );
				return array(
					'id'   => $user[0],
					'hash' => $user[1],
				);
			},
			(array) array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['users'] ) )
		);
	}

	if ( empty( $users ) ) {
		wp_safe_redirect( $redirect );
		exit;
	}

	$oodsp_request_manager = new OODSP_Request_Manager();
	$res_docspace_users    = $oodsp_request_manager->request_docspace_users();

	if ( $res_docspace_users['error'] ) {
		oodsp_add_users_message( 'users_invited', __( 'Error getting users from ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ), 'error' );
		set_transient( 'oodsp_users_messages', oodsp_get_users_messages(), 30 );

		wp_safe_redirect( admin_url( 'admin.php?page=onlyoffice-docspace-settings&users=true&invited=true' ) );
		exit;
	}

	$docspace_users = array_map(
		function ( $docspace_user ) {
			return $docspace_user['email'];
		},
		$res_docspace_users['data']
	);

	$count_invited = 0;
	$count_skipped = 0;
	$count_error   = 0;

	foreach ( $users as $user ) {
		$user_id   = $user['id'];
		$user_hash = $user['hash'];

		$user = get_user_to_edit( $user_id );

		if ( in_array( $user->user_email, $docspace_users, true ) ) {
			++$count_skipped;
		} else {
			[$email, $first_name, $last_name] = $oodsp_request_manager->get_user_data( $user );

			$res_invite_user = $oodsp_request_manager->request_invite_user(
				$email,
				$user_hash,
				$first_name,
				$last_name,
				2
			);

			if ( $res_invite_user['error'] ) {
				++$count_error;
			} else {
				$oodsp_security_manager = new OODSP_Security_Manager();

				$oodsp_security_manager->set_oodsp_user_pass( $user_id, $user_hash );

				++$count_invited;
			}
		}
	}

	if ( 0 !== $count_error ) {
		oodsp_add_users_message(
			'users_invited',
			sprintf(
				/* translators: %1$s: count error; %2$s: count users */
				__( 'Invitation failed for %1$s/%2$s user(s)', 'onlyoffice-docspace-plugin' ),
				$count_error,
				count( $users )
			),
			'error'
		);
	}

	if ( 0 !== $count_skipped ) {
		oodsp_add_users_message(
			'users_invited',
			sprintf(
				/* translators: %1$s: count skiped; %2$s: count users */
				__( 'Invitation skipped for %1$s/%2$s user(s). User(s) with the indicated email(s) may already exist in DocSpace.', 'onlyoffice-docspace-plugin' ),
				$count_skipped,
				count( $users )
			),
			'warning'
		);
	}

	if ( 0 !== $count_invited ) {
		oodsp_add_users_message(
			'users_invited',
			sprintf(
				/* translators: %1$s: count invited; %2$s: count users */
				__( 'Invitation successfully sent to %1$s/%2$s user(s)', 'onlyoffice-docspace-plugin' ),
				$count_invited,
				count( $users )
			),
			'success'
		);
	}

	set_transient( 'oodsp_users_messages', oodsp_get_users_messages(), 30 );

	wp_safe_redirect( admin_url( 'admin.php?page=onlyoffice-docspace-settings&users=true&invited=true' ) );
	exit;
}

/**
 * Add oodsp users messages.
 *
 * @param string $code The code.
 * @param string $message The message.
 * @param string $type The type.
 */
function oodsp_add_users_message( $code, $message, $type = 'error' ) {
	global $wp_oodsp_users_messages;

	$wp_oodsp_users_messages[] = array(
		'code'    => $code,
		'message' => $message,
		'type'    => $type,
	);
}

/**
 * Return oodsp users messages.
 */
function oodsp_get_users_messages() {
	global $wp_oodsp_users_messages;

	if ( get_transient( 'oodsp_users_messages' ) ) {
		$wp_oodsp_users_messages = array_merge( (array) $wp_oodsp_users_messages, get_transient( 'oodsp_users_messages' ) );
		delete_transient( 'oodsp_users_messages' );
	}

	if ( empty( $wp_oodsp_users_messages ) ) {
		return array();
	}

	return $wp_oodsp_users_messages;
}

/**
 * Return oodsp users messages.
 */
function oodsp_users_messages() {
	$oodsp_users_messages = oodsp_get_users_messages();

	if ( empty( $oodsp_users_messages ) ) {
		return;
	}

	foreach ( $oodsp_users_messages as $key => $details ) {
		if ( 'updated' === $details['type'] ) {
			$details['type'] = 'success';
		}

		if ( in_array( $details['type'], array( 'error', 'success', 'warning', 'info' ), true ) ) {
			$details['type'] = 'notice-' . $details['type'];
		}

		$css_id    = sprintf(
			'oodsp_users-%s',
			esc_attr( $details['code'] )
		);
		$css_class = sprintf(
			'notice %s is-dismissible',
			esc_attr( $details['type'] )
		);

		echo '<div id="' . esc_attr( $css_id ) . '" class="' . esc_attr( $css_class ) . '">';
		echo '<p><strong>' . esc_html( $details['message'] ) . '</strong></p>';
		echo '</div>';
	}
}
