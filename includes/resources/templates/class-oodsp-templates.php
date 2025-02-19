<?php
/**
 * OODSP Templates
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/resources/templates
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
 * Class OODSP_Templates
 *
 * Handles the rendering of various templates used in the ONLYOFFICE DocSpace WordPress plugin.
 *
 * This class contains static methods for rendering HTML templates, such as login forms,
 * error messages, and other UI components related to the DocSpace integration.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/resources/templates
 */
class OODSP_Templates {
	/**
	 *  DocSpace login template.
	 *
	 * @return void
	 */
	public static function oodsp_login_page_template() {
		?>
		<script type="text/html" id="tmpl-oodsp-login">
			<div class="oodsp-login-container">
				<div class="oodsp-login login js">
					<# _.each(data.messages, function(message) { #>
						<div class="notice notice-{{{message.type}}}">{{{message.text}}}</div>
					<# }); #>

					<form name="loginform" id="oodsp-login-form">
						<h1 id="header">
							<?php esc_html_e( 'WordPress requests access to your ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ); ?>
							<br>
							<div title="{{{data.domain}}}">{{{data.domain}}}</div>
						</h1>
						<h1>
							<a></a>
							<a id="union" style="background-image: url('<?php echo esc_attr( OODSP_PLUGIN_URL ) . 'includes/resources/images/union.svg'; ?>');"></a>
							<a id="logo-onlyoffice" style="background-image: url('<?php echo esc_attr( OODSP_PLUGIN_URL ) . 'includes/resources/images/onlyoffice.svg'; ?>');"></a>
						</h1>
						
						<#
						if ( ! data.resetPassword ) {
						#> 
						<div>
							<p style="padding-bottom: 25px;">
								<label for="user_login">
								<?php esc_html_e( 'Please enter your DocSpace credentials to sync it with your WordPress account:', 'onlyoffice-docspace-plugin' ); ?>
								</label>
							</p>

							<p>
								<input
									type="text"
									id="oodsp-username"
									aria-describedby="login-message"
									class="input"
									size="20" 
									autocapitalize="off"
									required="required"
									value="{{{data.userName}}}"
								/>
							</p>

							<div class="user-pass-wrap">
								<div class="wp-pwd">
									<input
										type="password"
										name="pwd"
										id="oodsp-password"
										aria-describedby="login-message"
										class="input password-input"
										size="20"
										spellcheck="false"
										required="required"
										value="{{{data.password}}}"
									>
									<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Show password' ); ?>">
										<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
									</button>
								</div>
							</div>

							<div>
								<input
									id="oodsp-submit-password"
									type="submit" 
									class="button button-primary button-large" 
									value="<?php esc_attr_e( 'Log In' ); ?>"
								>
							</div>
							<div class="reset-password-link-wrap">
								<a id="oodsp-reset-password-link" class="reset-password-link"><?php esc_html_e( 'Reset password' ); ?></a>
							</div>
						</div>
						<#
						} else {
						#> 
						<div>
							<p style="padding-bottom: 25px;">
								<label for="user_login">
								<?php esc_html_e( 'Please enter the email address you used while registering in DocSpace to get the password recovery instructions.', 'onlyoffice-docspace-plugin' ); ?>
								</label>
							</p>

							<p>
								<input
									type="text"
									id="oodsp-username"
									aria-describedby="login-message"
									class="input"
									size="20" 
									autocapitalize="off"
									required="required"
									value="{{{data.userName}}}"
								/>
							</p>

							<p>
								<input
									id="oodsp-reset-password-submit"
									type="submit"
									name="wp-submit"
									class="button button-primary button-large"
									value="<?php esc_attr_e( 'Send' ); ?>"
								>
								<input
									id="oodsp-reset-password-cancel"
									type="button"
									class="button button-large"
									value="<?php esc_attr_e( 'Cancel' ); ?>"
								>
							</p>
						</div>
						<#
						}
						#>
					</form>
				</div>
			</div>
		</script>
		<?php
	}

	/**
	 *  OODSP error template.
	 *
	 * @return void
	 */
	public static function oodsp_error_page_template() {
		?>
		<script type="text/html" id="tmpl-oodsp-error">
			<div class="onlyoffice-error" >
				<div class="main">
					<div class="header">
						<img src="<?php echo esc_url( OODSP_PLUGIN_URL . 'includes/resources/images/onlyoffice-docspace.svg' ); ?>" />
					</div>
					<div class="image">
						<img src="{{{data.image}}}" />
					</div>
					<div class="info">
						<div class="text-bold">{{{data.header || ""}}}</div>
						<div class="text-normal">{{{data.message}}}</div>
					</div>
				</div>
			</div>
		</script>
		<?php
	}
}

?>