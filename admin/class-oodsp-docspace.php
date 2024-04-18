<?php
/**
 * Page ONLYOFFICE DocSpace.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin
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

if ( ! function_exists( 'WP_Filesystem' ) ) {
	include ABSPATH . '/wp-admin/includes/file.php';
	WP_Filesystem();
}

/**
 * Page ONLYOFFICE DocSpace.
 *
 * This class defines code necessary displaying a page ONLYOFFICE DocSpace.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_DocSpace {

	/**
	 * OODSP_Utils
	 *
	 * @access   private
	 * @var      OODSP_Utils    $oodsp_utils
	 */
	private $oodsp_utils;


	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->oodsp_utils = new OODSP_Utils();
	}

	/**
	 * Register the JavaScript for the DocSpace area.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			OODSP_PLUGIN_NAME . '-login',
			OODSP_PLUGIN_URL . 'includes/js/oodsp-login.js',
			array( 'jquery', 'wp-util' ),
			OODSP_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				OODSP_PLUGIN_NAME . '-login',
				'onlyoffice-docspace-plugin',
				plugin_dir_path( OODSP_PLUGIN_FILE ) . 'languages/'
			);
		}

		wp_enqueue_script( 'user-profile' );

		$this->oodsp_utils->enqueue_scripts();

		wp_enqueue_script(
			'docspace-integration-sdk',
			OODSP_PLUGIN_URL . 'assets-onlyoffice-docspace/js/docspace-integration-sdk.js',
			array(),
			OODSP_VERSION,
			true
		);
	}

	/**
	 * Register the stylesheets for the DocSpace area.
	 */
	public function enqueue_styles() {
		$this->oodsp_utils->enqueue_styles();

		wp_enqueue_style(
			OODSP_PLUGIN_NAME . '-login',
			OODSP_PLUGIN_URL . 'includes/css/oodsp-login.css',
			array(),
			OODSP_VERSION
		);

		wp_enqueue_style( 'login' );
	}

	/**
	 * Init menu.
	 */
	public function init_menu() {
		global $wp_filesystem;

		$logo_svg = $wp_filesystem->get_contents( plugin_dir_path( OODSP_PLUGIN_FILE ) . 'admin/images/logo.svg' );

		add_menu_page(
			'DocSpace',
			'DocSpace',
			'upload_files',
			'onlyoffice-docspace',
			array( $this, 'docspace_page' ),
			'data:image/svg+xml;base64,' . base64_encode( $logo_svg ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		);

		$hook = add_submenu_page(
			'onlyoffice-docspace',
			'DocSpace',
			'DocSpace',
			'upload_files',
			'onlyoffice-docspace',
			array( $this, 'docspace_page' )
		);
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
				<div id="oodsp-manager-frame" style="width: 100%; height:100%;"></div>
			</div>
		</div>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				wp.oodsp.initLoginManager(
					"oodsp-manager-frame",
					function() {
						DocSpace.SDK.initManager({
							frameId: "oodsp-manager-frame",
							showMenu: true,
							showFilter: true,
							showHeader: true,
							locale: _oodsp.locale,
							showSignOut: false,
							theme: "Base"
						});
					}
				);
			});
		</script>
		<?php
	}

	/**
	 *  DocSpace login template.
	 *
	 * @return void
	 */
	public function docspace_login_template() {
		?>
		<script type="text/html" id="tmpl-oodsp-login">
			<div class="oodsp-login login js">
				<div id="login_error"
				<#
				if ( ! data.error ) {
					#> hidden <#
				}
				#>
				>
					{{{data.error}}}
				</div>
				<form name="loginform" id="oodsp-login-form">
					<h1 id="header">
						<?php esc_html_e( 'WordPress requests access to your ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ); ?>
						<br>
						<div title="{{{data.domain}}}">{{{data.domain}}}</div>
					</h1>
					<h1>
						<a></a>
						<a id="union" style="background-image: url('<?php echo esc_attr( OODSP_PLUGIN_URL ) . 'admin/images/union.svg'; ?>');"></a>
						<a id="logo-onlyoffice" style="background-image: url('<?php echo esc_attr( OODSP_PLUGIN_URL ) . 'admin/images/onlyoffice.svg'; ?>');"></a>
					</h1>

					<p style="padding-bottom: 25px;">
						<label for="user_login">
						<?php
							printf(
								wp_kses(
									/* translators: %s: User email. */
									__( 'Your account <b>%s</b> will be synced with your DocSpace. Please enter your DocSpace password in the field below:', 'onlyoffice-docspace-plugin' ),
									array(
										'b' => array(
											'class' => array(),
										),
									),
								),
								'{{ data.email }}'
							);
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
