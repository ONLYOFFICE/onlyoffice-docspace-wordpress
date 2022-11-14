<?php

class OODSPlugin
{

    protected $plugin_name;
    protected $version;
    public function __construct()
    {

        $this->version = ONLYOFFICE_DOCSPACE_PLUGIN_VERSION;
        $this->plugin_name = 'onlyoffice-docspace-plugin';

    }

    public function run()
    {
    }
