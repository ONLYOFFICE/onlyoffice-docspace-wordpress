<?php

/**
 * Plugin Name:       ONLYOFFICE DocSpace Wordpress plugin
 * Plugin URI:        https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * Description:       ONLYOFFICE Description
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ascensio System SIA
 * Author URI:        https://www.onlyoffice.com
 * License:           GNU General Public License v2.0
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       onlyoffce-docspace-plugin
 * Domain Path:       /languages
 */

define('ONLYOFFICE_DOCSPACE_PLUGIN_VERSION', '1.0.0');

function activate_onlyoffice_docspace_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/onlyoffice-docspace-activator.php';
	OODSP_Activator::activate();
}
function deactivate_onlyoffice_docspace_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/onlyoffice-docspace-deactivator.php';
	OODSP_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_onlyoffice_docspace_plugin');
register_deactivation_hook(__FILE__, 'deactivate_onlyoffice_docspace_plugin');

require plugin_dir_path(__FILE__) . 'includes/onlyoffice-docspace-plugin.php';

function run_onlyoffice_docspace_plugin()
{

	$plugin = new OODSPlugin();
	$plugin->run();
}
run_onlyoffice_docspace_plugin();
