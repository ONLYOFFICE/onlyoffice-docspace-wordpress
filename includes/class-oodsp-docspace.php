<?php
/**
 * Page ONLYOFFICE DocSpace.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes/files
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2023
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

/**
 * Page ONLYOFFICE DocSpace.
 *
 * This class defines code necessary displaying a page ONLYOFFICE DocSpace.
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_DocSpace {
	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Init menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		$hook = null;

		add_menu_page(
			'DocSpace',
			'DocSpace',
			'manage_options',
			'onlyoffice-docspace',
			array( $this, 'docspace_page' ),
			'dashicons-media-document'
		);

		$hook = add_submenu_page(
			'onlyoffice-docspace',
			'DocSpace',
			'DocSpace',
			'manage_options',
			'onlyoffice-docspace',
			array( $this, 'docspace_page' )
		);
	}

	/**
	 * Add DocSpace API JS.
	 *
	 * @return void
	 */
	public function add_docspace_js() {
		$options    = get_option( 'onlyoffice_docspace_settings' );
		$script_url = $options[ OODSP_Settings::DOCSPACE_URL ] . 'static/scripts/api.js?withSubfolders=true&showHeader=false&showTitle=true&showMenu=false&showFilter=false';
		wp_enqueue_script( 'onlyoffice_docspace_sdk', $script_url, array(), ONLYOFFICE_DOCSPACE_PLUGIN_VERSION, false );
	}

	/**
	 * Register the JavaScript for the DocSpace page.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-ds-component-script',
			plugin_dir_url( __FILE__ ) . '../public/js/docspace-components-api.js',
			array( 'jquery' ),
			ONLYOFFICE_DOCSPACE_PLUGIN_VERSION,
			true
		);

		$options = get_option( 'onlyoffice_docspace_settings' );

		wp_localize_script(
			$this->plugin_name . '-ds-component-script',
			'DocSpaceComponent',
			array( 'docSpaceUrl' => $options[ OODSP_Settings::DOCSPACE_URL ] )
		);
	}

	/**
	 *  DocSpace page.
	 *
	 * @return void
	 */
	public function docspace_page() {
		$this->add_docspace_js();

		?>
		<div class="ds-frame" style="height: 100vh;">
			<div id="ds-frame">Fallback text</div>
		</div>
		<?php
	}
}
