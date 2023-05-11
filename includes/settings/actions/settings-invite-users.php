<?php
/**
 * Invite ONLYOFFICE DocSpace users action.
 *
 * @package Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes/settings/actions
 */

function invite_users() {
    if ( isset( $_REQUEST['wp_http_referer'] ) ) {
        $redirect = remove_query_arg( array( 'wp_http_referer', 'updated', 'delete_count' ), wp_unslash( $_REQUEST['wp_http_referer'] ) );
    } else {
        $redirect = 'admin.php?page=onlyoffice-docspace-settings&users=true';
    }

    check_admin_referer( 'bulk-users' );

    if ( empty( $_REQUEST['users'] ) ) {
        wp_redirect( $redirect );
        exit;
    }

    $users = array_map( function(string $user) {
        $user = explode('$$', $user, 2);
        return array(
            'id' => $user[0],
            'hash' => $user[1] 
        );
    }, (array) $_REQUEST['users'] );

    if ( empty( $users ) ) {
        wp_redirect( $redirect );
        exit;
    }

    $oodsp_request_manager = new OODSP_Request_Manager();
    $res_docspace_users = $oodsp_request_manager->request_docspace_users();

    if ( $res_docspace_users['error'] ) {
        add_oodsp_users_message( 'users_invited', __( 'Error getting users from ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ), 'error' );
        set_transient( 'oodsp_users_messages', get_oodsp_users_messages(), 30 );

        wp_safe_redirect( admin_url( 'admin.php?page=onlyoffice-docspace-settings&users=true&invited=true' ) );
        exit;
    }

    $docspace_users = array_map( 
        function($docspace_user) {
            return $docspace_user['email'];
        },
        $res_docspace_users['data']
    );

    $count_invited = $count_skipped = $count_error = 0;

    foreach ( $users as $user ) {
        $user_id   = $user['id'];
        $user_hash = $user['hash'];

        $user = get_user_to_edit( $user_id  );

        if ( in_array( $user->user_email, $docspace_users ) ) {
            $count_skipped++;
        } else {
            $res_invite_user = $oodsp_request_manager->request_invite_user(
                $user->user_email,
                $user_hash,
                $user->first_name,
                $user->last_name,
                2,
                $user->locale
            );

            if ( $res_invite_user['error'] ){
                $count_error++;
            } else {
                global $wpdb;

                $docspace_user_table = $wpdb->prefix . "docspace_users";

                $result = $wpdb->update( 
                    $docspace_user_table , 
                    array( 
                        'user_id'   => $user_id,
                        'user_pass' => $user_hash
                    ), 
                    array( 
                        'user_id' => $user_id ) 
                    );

                if (!$result) {
                    $wpdb->insert( 
                        $docspace_user_table,
                        array( 
                            'user_id'   => $user_id,
                            'user_pass' => $user_hash
                        ) 
                    );
                }

                $count_invited++;
            } 
        }
    }
    
    if ( $count_error !== 0 ) {
        add_oodsp_users_message( 'users_invited', sprintf( __( 'Invite with error for %s/%s users', 'onlyoffice-docspace-plugin' ),  $count_error,  count( $users ) ), 'error' );
    }
    
    if ( $count_skipped !== 0 ) {
        add_oodsp_users_message( 'users_invited', sprintf( __('Invite skipped for %s/%s users', 'onlyoffice-docspace-plugin'),  $count_skipped,  count( $users ) ), 'warning' );
    }

    if ( $count_invited !== 0 ) {
        add_oodsp_users_message( 'users_invited', sprintf( __( 'Invite sucessed for %s/%s users', 'onlyoffice-docspace-plugin' ),  $count_invited,  count( $users ) ), 'success' );
    }

    set_transient( 'oodsp_users_messages', get_oodsp_users_messages(), 30 );

    wp_safe_redirect( admin_url( 'admin.php?page=onlyoffice-docspace-settings&users=true&invited=true' ) );
    exit;
}

function add_oodsp_users_message( $code, $message, $type = 'error' ) {
	global $wp_oodsp_users_messages;

	$wp_oodsp_users_messages[] = array(
		'code'    => $code,
		'message' => $message,
		'type'    => $type,
	);
}

function get_oodsp_users_messages() {
	global $wp_oodsp_users_messages;

	if ( isset( $_GET['users'] ) && $_GET['users'] && get_transient( 'oodsp_users_messages' ) ) {
		$wp_oodsp_users_messages = array_merge( (array) $wp_oodsp_users_messages, get_transient( 'oodsp_users_messages' ) );
		delete_transient( 'oodsp_users_messages' );
	}

	if ( empty( $wp_oodsp_users_messages ) ) {
		return array();
	}

	return $wp_oodsp_users_messages;
}

function oodsp_users_messages() {
	$oodsp_users_messages = get_oodsp_users_messages();

	if ( empty( $oodsp_users_messages ) ) {
		return;
	}

	$output = '';

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

		$output .= "<div id='$css_id' class='$css_class'> \n";
		$output .= "<p><strong>{$details['message']}</strong></p>";
		$output .= "</div> \n";
	}

	echo $output;
}