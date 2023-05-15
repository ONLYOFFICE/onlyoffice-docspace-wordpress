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
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $plugin_settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->plugin_settings = new OODSP_Settings();
	}

	/**
	 * Init menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		$logo_svg = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . '/public/images/logo.svg' );

		add_menu_page(
			'DocSpace',
			'DocSpace',
			'manage_options',
			'onlyoffice-docspace',
			array( $this, 'docspace_page' ),
			'data:image/svg+xml;base64,' . base64_encode( $logo_svg )
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
	 * Register the JavaScript for the DocSpace page.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
	}

	/**
	 *  DocSpace page.
	 *
	 * @return void
	 */
	public function docspace_page() {
		?>
		<div class="wrap"> 
			<div class="ds-frame" style="height: calc(100vh - 65px - 32px);">
				<div id="oodsp-manager-frame"></div>
			</div>
		</div>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				DocSpaceComponent.initScript().then(function() {
					DocSpaceComponent.initLoginDocSpace(
						"oodsp-manager-frame", 
						function() {
							DocSpace.SDK.initManager({
								frameId: "oodsp-manager-frame",
								showMenu: true
							});
						},
						function() {
							DocSpaceComponent.renderError("oodsp-manager-frame", { message: "<?php esc_html_e('Portal unavailable! Please contact the administrator!', 'onlyoffice-docspace-plugin') ?>"})
						}
					);
				});
			});
		</script>
		<?php
	}
}
