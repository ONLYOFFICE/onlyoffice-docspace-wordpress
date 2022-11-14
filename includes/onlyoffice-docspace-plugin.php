<?php

class OODSPlugin
{

    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $settings;

    public function __construct()
    {

        $this->version = ONLYOFFICE_DOCSPACE_PLUGIN_VERSION;
        $this->plugin_name = 'onlyoffice-docspace-plugin';

        $this->load_dependencies();
        $this->set_locale();
        $this->init_settings();
    }

    private function load_dependencies()
    {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-docspace-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-docspace-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-docspace-settings.php';

        $this->loader = new OODSP_Loader();
    }

    private function set_locale()
    {

        $plugin_i18n = new OODSP_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function init_settings()
    {
        $plugin_settings = new OODSP_Settings($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_menu', $plugin_settings, 'init_menu');
        $this->loader->add_action('admin_init', $plugin_settings, 'init');
    }

    public function run()
    {
        $this->loader->run();
    }
    }
