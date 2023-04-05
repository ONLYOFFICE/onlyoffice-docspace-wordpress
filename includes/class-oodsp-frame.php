<?php

/**
 *
 * (c) Copyright Ascensio System SIA 2022
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

class OODSP_DocSpace
{
    public function init_menu()
    {
        $hook = null;

        add_menu_page(
            'DocSpace',
            'DocSpace',
            'manage_options',
            'onlyoffice-docspace',
            array($this, 'docspace_page'),
            'dashicons-media-document'
        );

        $hook = add_submenu_page(
            'onlyoffice-docspace',
            'DocSpace',
            'DocSpace',
            'manage_options',
            'onlyoffice-docspace',
            array($this, 'docspace_page')
        );
    }

    function add_docspace_js()
    {
        $options = get_option('onlyoffice_docspace_settings');
        $script_url = $options[OODSP_Settings::docspace_url] . 'static/scripts/api.js?withSubfolders=true&showHeader=false&showTitle=true&showMenu=false&showFilter=false';
        wp_enqueue_script('onlyoffice_docspace_sdk', $script_url, array());
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script(
            $this->plugin_name . '-ds-component-script',
            plugin_dir_url(__FILE__) . '../public/js/docspace-components-api.js',
            array('jquery'),
            $this->version,
            true
        );

        $options = get_option('onlyoffice_docspace_settings');

        wp_localize_script($this->plugin_name . '-ds-component-script', 'DocSpaceComponent', array(
            'docSpaceUrl' => $options[OODSP_Settings::docspace_url]
        ));
    }

    public function docspace_page()
    {
        $this->add_docspace_js();

?>
        <div class="ds-frame" style="height: 100vh;">
            <div id="ds-frame">Fallback text</div>
        </div>
<?php
    }
}
