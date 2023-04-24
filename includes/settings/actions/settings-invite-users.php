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
    }//todo:

    $oodsp_request_manager = new OODSP_Request_Manager();

    $res_docspace_users = $oodsp_request_manager->request_docspace_users();

    if ( $res_docspace_users['error'] ) {
        // todo: error
        wp_redirect( $redirect );
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

    wp_redirect( $redirect );
    exit;

}