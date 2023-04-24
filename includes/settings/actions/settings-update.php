<?php
/**
 * Update ONLYOFFICE DocSpace Setting action.
 *
 * @package Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes/settings/actions
 */

function update_settings() {
    if ( isset( $_POST[OODSP_Settings::DOCSPACE_URL] ) && isset( $_POST[OODSP_Settings::DOCSPACE_LOGIN] ) && isset( $_POST[OODSP_Settings::DOCSPACE_PASS] ) ) {
        check_admin_referer( 'onlyoffice_docspace_settings-options' );

        $docspace_url = prepare_value( $_POST[OODSP_Settings::DOCSPACE_URL] );
        $docspace_login = prepare_value( $_POST[OODSP_Settings::DOCSPACE_LOGIN] );
        $docspace_pass = prepare_value( $_POST[OODSP_Settings::DOCSPACE_PASS] );

        $docspace_url = substr($docspace_url, -1) === '/' ? $docspace_url : $docspace_url . '/';

        $oodsp_request_manager = new OODSP_Request_Manager();

        $res_auth = $oodsp_request_manager->auth_docspace( $docspace_url, $docspace_login, $docspace_pass );

        if ( $res_auth['error'] === 1) {
            add_settings_error( 'general', 'settings_updated', 'Invalid credentials. Please try again!' );
        }

        if ( $res_auth['error'] === 2) {
            add_settings_error( 'general', 'settings_updated', 'Error getting data user. Please try again!' );
        }
        if ( $res_auth['error'] === 3) {
            add_settings_error( 'general', 'settings_updated', 'Not Admin. Please try again!' );
        }

        if ( ! get_settings_errors() ) {
            $value = array(
                OODSP_Settings::DOCSPACE_URL   =>  $docspace_url,
                OODSP_Settings::DOCSPACE_LOGIN => $docspace_login, 
                OODSP_Settings::DOCSPACE_PASS  => $docspace_pass,
                OODSP_Settings::DOCSPACE_TOKEN  => $res_auth['data'],
            );

            update_option( 'onlyoffice_docspace_settings', $value );

            add_settings_error( 'general', 'settings_updated', __( 'Settings Saved', 'onlyoffce-docspace-plugin' ), 'success' );
        }

        set_transient( 'settings_errors', get_settings_errors(), 30 );

        wp_safe_redirect( admin_url( 'admin.php?page=onlyoffice-docspace-settings&settings-updated=true' ) );
        exit;
    } else {
        wp_die( 'The required parameters is missing!', '', array( 'response' => 400 ) );
    }
}

function prepare_value ( $value ) {
    if ( ! is_array( $value ) ) {
        $value = trim( $value );
    }

    return wp_unslash( $value );
}