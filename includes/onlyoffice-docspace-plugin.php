<?php

class OODSPlugin
{

    protected $loader;
    protected $plugin_name;
    protected $version;
    public function __construct()
    {

        $this->version = ONLYOFFICE_DOCSPACE_PLUGIN_VERSION;
        $this->plugin_name = 'onlyoffice-docspace-plugin';

        $this->load_dependencies();
        $this->set_locale();
    }

    private function load_dependencies()
    {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-docspace-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-docspace-i18n.php';

        $this->loader = new OODSP_Loader();
    }

    private function set_locale()
    {

        $plugin_i18n = new OODSP_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    public function run()
    {
        $this->loader->run();
    }
    }
