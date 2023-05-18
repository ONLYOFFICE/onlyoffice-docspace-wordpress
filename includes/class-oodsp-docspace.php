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
						null,
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

	public function docspace_login_template() {
		?>
		<script type="text/html" id="tmpl-oodsp-login">
			<div class="oodsp-login login js">
				<div id="login_error" hidden>
				</div>
				<form name="loginform" id="oodsp-login-form">
					<h1 id="header">
						<?php esc_html_e( 'WordPress requests access to your ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ) ?>
						<br>
						<a href="{{data.url}}" target="_blank">{{{data.domain}}}</a>
					</h1>
					<h1>
						<a></a>
						<a id="union" style="background-image: url('<?php echo plugins_url( '../public/images/union.svg', __FILE__ ) ; ?>');"></a>
						<a id="logo-onlyoffice" style="background-image: url('<?php echo plugins_url( '../public/images/onlyoffice.svg', __FILE__ ) ; ?>');"></a>
					</h1>
					
					<p style="padding-bottom: 25px;">
						<label for="user_login">
						<?php
							/* translators: %s: User email. */
							printf( __( 'Your account <b>%s</b> will be synced with your DocSpace. Please enter your DocSpace password in the field below:', 'onlyoffice-docspace-plugin' ), '{{ data.email }}' );
							?>
						</label>
					</p>

					<div class="user-pass-wrap">
						<div class="wp-pwd">
							<input type="password" name="pwd" id="oodsp-password" aria-describedby="login-message" class="input password-input" value="" size="20" autocomplete="current-password" spellcheck="false">
							<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Show password' ); ?>">
								<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
							</button>
						</div>
					</div>

					<p>
						<input style="width: 100%;"  type="submit" name="wp-submit" id="oodsp-submit-password" class="button button-primary button-large" value="<?php esc_attr_e( 'Log In' ); ?>">
					</p>
				</form>
			</div>
		</script>
		<?php
	}
}
