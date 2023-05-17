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

        if ( $res_auth['error'] === OODSP_Request_Manager::UNAUTHORIZED) {
            add_settings_error( 'general', 'settings_updated', __( 'Invalid credentials. Please try again!', 'onlyoffice-docspace-plugin' ) );
        }
        if ( $res_auth['error'] === OODSP_Request_Manager::USER_NOT_FOUND) {
            add_settings_error( 'general', 'settings_updated', __( 'Error getting data user. Please try again!', 'onlyoffice-docspace-plugin' ) );
        }
        if ( $res_auth['error'] === OODSP_Request_Manager::FORBIDDEN) {
            add_settings_error( 'general', 'settings_updated', __( 'The specified user is not a DocSpace administrator!', 'onlyoffice-docspace-plugin' ) );
        }

        if ( ! get_settings_errors() ) {
            $value = array(
                OODSP_Settings::DOCSPACE_URL   =>  $docspace_url,
                OODSP_Settings::DOCSPACE_LOGIN => $docspace_login, 
                OODSP_Settings::DOCSPACE_PASS  => $docspace_pass,
                OODSP_Settings::DOCSPACE_TOKEN  => $res_auth['data'],
            );

            update_option( 'onlyoffice_docspace_settings', $value );

            add_settings_error( 'general', 'settings_updated', __( 'Settings saved', 'onlyoffice-docspace-plugin' ), 'success' );

            $res_create_public_user = $oodsp_request_manager->request_create_public_user(  $docspace_url, $res_auth['data'] );

            if ( $res_create_public_user['error'] === OODSP_Request_Manager::ERROR_USER_INVITE) {
                add_settings_error( 'general', 'settings_updated', __( 'Public DocSpace user was not created! View content will not be available on public pages.', 'onlyoffice-docspace-plugin' ), 'warning' );
            } else if ( $res_create_public_user['error'] === OODSP_Request_Manager::ERROR_SET_USER_PASS) {
                add_settings_error( 'general', 'settings_updated', __( 'Public DocSpace user already created, but failed to update authorization.', 'onlyoffice-docspace-plugin' ), 'warning' );
            } else if ( $res_create_public_user['error'] ) {
                add_settings_error( 'general', 'settings_updated', __( 'Public DocSpace user was not created. View content will not be available on public pages.', 'onlyoffice-docspace-plugin' ), 'warning' );
            } else {
                add_settings_error( 'general', 'settings_updated', __( 'Public DocSpace user successfully created.', 'onlyoffice-docspace-plugin' ), 'success' );
            }
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