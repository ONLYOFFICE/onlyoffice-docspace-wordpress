<?php
/**
 * The plugin bootstrap file.
 *
 * @package           Onlyoffice_Docspace_Plugin
 *
 * Plugin Name:       ONLYOFFICE DocSpace
 * Plugin URI:        https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * Description:       Add ONLYOFFICE DocSpace on page
 * Version:           2.1.1
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            Ascensio System SIA
 * Author URI:        https://www.onlyoffice.com
 * License:           GNU General Public License v2.0
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       onlyoffce-docspace-plugin
 * Domain Path:       /languages
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2024
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
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 */
define( 'OODSP_PLUGIN_NAME', 'onlyoffice-docspace-wordpress' );
define( 'OODSP_VERSION', '2.1.1' );
define( 'OODSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OODSP_PLUGIN_FILE', __FILE__ );


/**
 * The code that runs during plugin activation.
 */
function oodsp_activate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-oodsp-activator.php';
	OODSP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function oodsp_deactivate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-oodsp-deactivator.php';
	OODSP_Deactivator::deactivate();
}

/**
 * The code that runs during plugin unistall.
 */
function oodsp_unistall_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-oodsp-deactivator.php';
	OODSP_Deactivator::uninstall();
}

register_activation_hook( __FILE__, 'oodsp_activate_plugin' );
register_deactivation_hook( __FILE__, 'oodsp_deactivate_plugin' );
register_uninstall_hook( __FILE__, 'oodsp_unistall_plugin' );

require plugin_dir_path( __FILE__ ) . 'includes/class-oodsp-plugin.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function oodsp_run_plugin() {
	$plugin = new OODSP_Plugin();
	$plugin->run();
}
oodsp_run_plugin();
