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
    }

    private function load_dependencies()
    {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-docspace-loader.php';
        $this->loader = new OODSP_Loader();
    }

    public function run()
    {
        $this->loader->run();
    }
    }
