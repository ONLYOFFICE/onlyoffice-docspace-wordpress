<?php

class OODSP_i18n
{

    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'onlyoffce-docspace-plugin',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
