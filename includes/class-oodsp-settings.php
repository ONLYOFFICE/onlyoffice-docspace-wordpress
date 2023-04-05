<?php

class OODSP_Settings
{
    const docspace_url = 'onlyoffice_settings_docspace_url';
    const docspace_url_temp = 'onlyoffice_settings_docspace_url_temp';
    const docspace_login = 'onlyoffice_settings_docspace_login';
    const docspace_login_temp = 'onlyoffice_settings_docspace_login_temp';
    const docspace_password = 'onlyoffice_settings_docspace_password';
    const docspace_password_temp = 'onlyoffice_settings_docspace_password_temp';
    
    public function init_menu()
    {
        $hook = add_submenu_page(
            'onlyoffice-docspace',
            'ONLYOFFICE DocSpace Settings',
            'Settings',
            'manage_options',
            'onlyoffice-docspace-settings',
            array($this, 'options_page')
        );

        global $_wp_http_referer;
        wp_reset_vars( array( '_wp_http_referer' ) );
            
        if ( ! empty( $_wp_http_referer ) && isset( $_SERVER['REQUEST_URI'] ) ) {
            wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
            exit;
        }

        add_action( "load-$hook", array( $this, 'add_docspace_users_table' ) );
    }

    public function add_docspace_users_table() {
        add_screen_option( 'per_page' );
        global $oodsp_users_list_table;
        $oodsp_users_list_table = new OODSP_Users_List_Table();
    }

    public function init()
    {
        register_setting('onlyoffice_docspace_settings_group', 'onlyoffice_docspace_settings');

        add_settings_section(
            'onlyoffice_docspace_settings_general_section',
            '',
            array($this, 'general_section_callback'),
            'onlyoffice_docspace_settings_group'
        );

        add_settings_field(
            OODSP_Settings::docspace_url_temp,
            __('DocSpace Service Address', 'onlyoffce-docspace-plugin'),
            array($this, 'input_cb'),
            'onlyoffice_docspace_settings_group',
            'onlyoffice_docspace_settings_general_section',
            array(
                'id'         => OODSP_Settings::docspace_url_temp
            )
        );

        add_settings_field(
            OODSP_Settings::docspace_login_temp,
            __('Login', 'onlyoffce-docspace-plugin'),
            array($this, 'input_cb'),
            'onlyoffice_docspace_settings_group',
            'onlyoffice_docspace_settings_general_section',
            array(
                'id'         => OODSP_Settings::docspace_login_temp
            )
        );

        add_settings_field(
            OODSP_Settings::docspace_password_temp,
            __('Password', 'onlyoffce-docspace-plugin'),
            array($this, 'input_cb'),
            'onlyoffice_docspace_settings_group',
            'onlyoffice_docspace_settings_general_section',
            array(
                'id'         => OODSP_Settings::docspace_password_temp
            )
        );
    }

    // ToDo: callbacks are mostly the same, refactor

    public function general_section_callback($args)
    {
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Configure ONLYOFFICE DocSpace connector settings ', 'onlyoffce-docspace-plugin'); ?></p>
    <?php
    }

    public function input_cb($args)
    {
        $options = get_option('onlyoffice_docspace_settings');
    ?>
        <input id="<?php echo esc_attr($args['id']) ?>" type="text" name="onlyoffice_docspace_settings[<?php echo esc_attr($args['id']); ?>]" value="<?php echo $options[$args['id']]; ?>">
    <?php
    }

    public function options_page()
    {
        $should_wizard = false;

        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error('onlyoffice_docspace_settings_messages', 'onlyoffice_docspace_message', __('Settings Saved', 'onlyoffce-docspace-plugin'), 'updated'); // ToDo: can also check if settings are valid e.g. make connection to docServer
            
            $options = get_option('onlyoffice_docspace_settings');
            if ($options[OODSP_Settings::docspace_url_temp] != $options[OODSP_Settings::docspace_url]
                || $options[OODSP_Settings::docspace_login_temp] != $options[OODSP_Settings::docspace_login]
                || $options[OODSP_Settings::docspace_password_temp] != $options[OODSP_Settings::docspace_password])
            {
                $should_wizard = true;
            }
        }

        settings_errors('onlyoffice_docspace_settings_messages');

        if (!isset($_GET['users'])) {
?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <form action="options.php" method="post">
                    <?php
                    settings_fields('onlyoffice_docspace_settings_group');
                    do_settings_sections('onlyoffice_docspace_settings_group');
                    submit_button('Save Settings');
                    ?>
                </form>

                <h1 class="wp-heading-inline">
                    <?php echo __ ( 'DocSpace Users', 'onlyoffce-docspace-plugin' ) ?>
                </h1>
                <p> 
                    <?php echo  __( 'To add new users to ONLYOFFICE DocSpace and to start working in plugin, please press', 'onlyoffce-docspace-plugin') ?>
                    <b><?php echo __( 'Sync Now', 'onlyoffce-docspace-plugin') ?></b>
                </p>

                <p class="submit">
                    <?php submit_button('Sync Now', 'secondary', 'users', false,  array( 'onclick' => 'location.href = location.href + "&users=true";' ) ); ?>
                </p>
            </div>

            <?php if($should_wizard): ?>
                <script><?php echo("location.href = location.href.replace('onlyoffice-docspace-settings', 'onlyoffice-docspace-wizard');");?></script>
            <?php endif; ?>
<?php
        } else {
            global $oodsp_users_list_table;
            $pagenum = $oodsp_users_list_table->get_pagenum();
            
            global $_wp_http_referer;
            wp_reset_vars( array( '_wp_http_referer' ) );
                
            if ( ! empty( $_wp_http_referer ) && isset( $_SERVER['REQUEST_URI'] ) ) {
                wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
                exit;
            }

            $oodsp_users_list_table->prepare_items();
            $total_pages = $oodsp_users_list_table->get_pagination_arg( 'total_pages' );
            if ( $pagenum > $total_pages && $total_pages > 0 ) {
                wp_redirect( add_query_arg( 'paged', $total_pages ) );
                exit;
            }
?>
            <div class="wrap">
                <h1 class="wp-heading-inline">
                    <?php echo __ ( 'DocSpace Users', 'onlyoffce-docspace-plugin' ) ?>
                </h1>
                <p> <?php echo  __( 'To add new users to ONLYOFFICE DocSpace press Invite or select multiple users and press Invite selected users to DocSpace. To remove users from DocSpace press Disable icon. All new users will be added with User role, if you want to change the role go to Accounts. Role Room admin is paid!', 'onlyoffce-docspace-plugin') ?></p>

                <?php
                    global $usersearch;
                    if ( strlen( $usersearch ) ) {
                        echo '<span class="subtitle">';
                        printf(
                            __( 'Search results for: %s' ),
                            '<strong>' . esc_html( $usersearch ) . '</strong>'
                        );
                        echo '</span>';
                    }
                ?>

                <hr class="wp-header-end">
                <?php $oodsp_users_list_table->views(); ?>

                <form method="get" l>

                    <?php $oodsp_users_list_table->search_box( __( 'Search Users' ), 'user' ); ?>

                <?php if ( ! empty( $_REQUEST['role'] ) ) { ?>
                        <input type="hidden" name="role" value="<?php echo esc_attr( $_REQUEST['role'] ); ?>" />
                    <?php } ?>
                    <?php $oodsp_users_list_table->display(); ?>
                </form>

                <div class="clear"></div>
                
                <form  method="get">
                    <input type="hidden" name="page" value="onlyoffice-docspace-settings">
                    <?php submit_button('Back to main settings', 'secondary', false  ); ?>
                </form>
            </div>
<?php
        }
    }
}
