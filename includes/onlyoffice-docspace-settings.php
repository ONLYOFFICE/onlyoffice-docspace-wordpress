<?php

class OODSP_Settings
{
    const docspace_url = 'onlyoffice_settings_docspace_url';

    public function init_menu()
    {
        add_submenu_page(
            'onlyoffice-docspace',
            'ONLYOFFICE DocSpace Settings',
            'Settings',
            'manage_options',
            'onlyoffice-docspace-settings',
            array($this, 'options_page')
        );
    }

    public function init()
    {
        register_setting('onlyoffice_docspace_settings_group', 'onlyoffice_docspace_settings');

        add_settings_section(
            'onlyoffice_docspace_settings_general_section',
            __('General Settings', 'onlyoffce-docspace-plugin'),
            array($this, 'general_section_callback'),
            'onlyoffice_docspace_settings_group'
        );

        add_settings_field(
            'onlyoffice_docspace_settings_docserver_url',
            __('docspace url', 'onlyoffce-docspace-plugin'),
            array($this, 'docspace_url_cb'),
            'onlyoffice_docspace_settings_group',
            'onlyoffice_docspace_settings_general_section',
            array(
                'label_for'         => OODSP_Settings::docspace_url
            )
        );
    }

    // ToDo: callbacks are mostly the same, refactor

    public function docspace_url_cb($args)
    {
        $options = get_option('onlyoffice_docspace_settings');
?>
        <input id="<?php echo esc_attr($args['label_for']) ?>" type="text" name="onlyoffice_docspace_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo $options[$args['label_for']]; ?>">
        <p class="description">
            <?php esc_html_e('docspace url', 'onlyoffce-docspace-plugin'); ?>
        </p>
    <?php
    }

    public function general_section_callback($args)
    {
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('General settings section', 'onlyoffce-docspace-plugin'); ?></p>
    <?php
    }

    public function options_page()
    {

        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error('onlyoffice_docspace_settings_messages', 'onlyoffice_docspace_message', __('Settings Saved', 'onlyoffce-docspace-plugin'), 'updated'); // ToDo: can also check if settings are valid e.g. make connection to docServer
        }

        settings_errors('onlyoffice_docspace_settings_messages');
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
        </div>
<?php
    }
}
