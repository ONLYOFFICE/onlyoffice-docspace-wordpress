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

class OODSP_Wizard
{
    public function init_menu()
    {
        $hook = null;

        $hook = add_submenu_page(
            null,
            'DocSpace Wizard',
            'DocSpace Wizard',
            'manage_options',
            'onlyoffice-docspace-wizard',
            array($this, 'docspace_wizard')
        );
    }
    
    function add_docspace_js()
    {
        $options = get_option('onlyoffice_docspace_settings');
        $script_url = $options[OODSP_Settings::docspace_url_temp] . 'static/scripts/api.js';
        wp_enqueue_script('onlyoffice_docspace_sdk', $script_url, array());
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script(
            $this->plugin_name . '-ds-component-wizard-script',
            plugin_dir_url(__FILE__) . '../admin/js/docspace-components-wizard-api.js',
            array('jquery'),
            $this->version,
            true
        );

        $options = get_option('onlyoffice_docspace_settings');

        wp_localize_script($this->plugin_name . '-ds-component-wizard-script', 'DocSpaceWizardComponent', array(
            'docSpaceUrl' => $options[OODSP_Settings::docspace_url_temp],
            'docSpaceLogin' => $options[OODSP_Settings::docspace_login_temp],
            'docSpacePassword' => $options[OODSP_Settings::docspace_password_temp]
        ));
    }

    public function docspace_wizard()
    {
        $this->add_docspace_js();

?>
        <div class="ds-frame" style="height: 100vh;">
            <div id="ds-wizard-frame">Fallback text</div>
        </div>
<?php
    }
}
