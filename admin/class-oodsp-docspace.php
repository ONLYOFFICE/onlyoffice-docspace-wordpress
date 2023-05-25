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
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_DocSpace {
	/**
	 * OODSP_Settings
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $plugin_settings;


	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->plugin_settings = new OODSP_Settings();
	}

	/**
	 * Register the JavaScript for the DocSpace area.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_NAME . '-login',
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL . 'admin/js/login.js',
			array( 'jquery' ),
			ONLYOFFICE_DOCSPACE_WORDPRESS_VERSION,
			true
		);

		wp_localize_script(
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_NAME . '-login',
			'messages',
			array(
				'empty-password' => __( '<strong>Error:</strong> The password field is empty.' ),
				'auth-failed'    => __( '<strong>Error:</strong> User authentication failed', 'onlyoffice-docspace-plugin' ),
			)
		);

		wp_enqueue_script(
			'docspace-component-api',
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL . 'assets/js/docspace-component-api.js',
			array(),
			ONLYOFFICE_DOCSPACE_WORDPRESS_VERSION,
			true
		);

		wp_localize_script(
			'docspace-component-api',
			'DocSpaceComponent',
			array(
				'url'         => $this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_URL ),
				'currentUser' => wp_get_current_user()->user_email,
				'isPublic'    => false,
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'images'      => array(
					'onlyoffice'  => plugins_url( 'public/images/onlyoffice.svg', ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_FILE ),
					'unavailable' => plugins_url( 'public/images/unavailable.svg', ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_FILE ),
				),
			)
		);

		wp_enqueue_script( 'user-profile' );
	}

	/**
	 * Register the stylesheets for the DocSpace area.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'docspace-components-api',
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL . 'assets/css/docspace-component-api.css',
			array(),
			ONLYOFFICE_DOCSPACE_WORDPRESS_VERSION
		);

		wp_enqueue_style(
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_NAME . '-login',
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL . 'admin/css/login.css',
			array(),
			ONLYOFFICE_DOCSPACE_WORDPRESS_VERSION
		);

		wp_enqueue_style( 'login' );
	}

	/**
	 * Init menu.
	 */
	public function init_menu() {
		$logo_svg = file_get_contents( ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL . 'admin/images/logo.svg' );

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
							DocSpaceComponent.renderError("oodsp-manager-frame", { message: "<?php esc_html_e( 'Portal unavailable! Please contact the administrator!', 'onlyoffice-docspace-plugin' ); ?>"})
						}
					);
				}).catch(function() {
					DocSpaceComponent.renderError("oodsp-manager-frame", { message: "<?php esc_html_e( 'Portal unavailable! Please contact the administrator!', 'onlyoffice-docspace-plugin' ); ?>"})
				});
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
						<span>{{{data.domain}}}</span>
					</h1>
					<h1>
						<a></a>
						<a id="union" style="background-image: url('<?php echo esc_attr( ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL ) . 'admin/images/union.svg'; ?>');"></a>
						<a id="logo-onlyoffice" style="background-image: url('<?php echo esc_attr( ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL ) . 'admin/images/onlyoffice.svg'; ?>');"></a>
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
