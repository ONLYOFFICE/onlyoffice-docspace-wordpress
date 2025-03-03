<?php
/**
 * ONLYOFFICE DocSpace Setting page authorization.
 *
 * @package Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/settings/view
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2025
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

?>

<?php
$system_user = $this->oodsp_settings_manager->get_system_user();
?>

<div id="oodsp-authorization-notice"></div>

<div class="oodsp-white-frame">
	<div class="header-section"><?php esc_html_e( 'Log in as a DocSpace Admin', 'onlyoffice-docspace-plugin' ); ?></div>

	<form id="oodsp-authorization-form" action="admin.php?page=onlyoffice-docspace-settings" method="post" autocomplete="off">
		<?php
			settings_fields( 'onlyoffice_docspace_settings' );
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><?php esc_html_e( 'Email', 'onlyoffice-docspace-plugin' ); ?></th>
					<td>
						<input
							name="docspace-login"
							type="text"
							class="regular-text"
							placeholder="<?php esc_html_e( 'Email', 'onlyoffice-docspace-plugin' ); ?>"
							value="<?php echo ! empty( $system_user ) ? esc_attr( $system_user->get_user_name() ) : ''; ?>"
							<?php echo ! empty( $system_user ) ? 'disabled' : ''; ?>
						>
					</td>
				</tr>
				<tr class="form-field form-required form-pwd">
					<th scope="row"><?php esc_html_e( 'Password', 'onlyoffice-docspace-plugin' ); ?></th>
					<td>
						<div class="js">
							<div class="user-pass-wrap">
								<div class="wp-pwd">
									<div class="password-input-wrapper">
										<input
											id="user_pass"
											name="docspace-password"
											type="password" 
											class="input password-input"
											placeholder="<?php esc_html_e( 'Password', 'onlyoffice-docspace-plugin' ); ?>"
											value="<?php echo ! empty( $system_user ) ? '********' : ''; ?>"
											<?php echo ! empty( $system_user ) ? 'disabled' : ''; ?>
										/>
									</div>
									<button
										type="button"
										class="button button-secondary wp-hide-pw hide-if-no-js"
										data-toggle="0"
										aria-label="<?php esc_attr_e( 'Show password' ); ?>"
										<?php echo ! empty( $system_user ) ? 'disabled' : ''; ?>
									>
										<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
									</button>
								</div>
							</div>
						</div
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<?php
			esc_html_e( 'We use this account to make API calls from DocSpace to WordPress. This account must have the DocSpace Admin type to export users from WordPress to DocSpace, create groups, manage group members in DocSpace.', 'onlyoffice-docspace-plugin' );
			?>
			</p>
		<p
			class="block-buttons"
			<?php echo ! empty( $system_user ) ? 'hidden' : ''; ?>
		>	
			<input
				type="submit"
				id="oodsp-authorization-login-button"
				class="button button-primary"
				value="<?php esc_html_e( 'Sign in', 'onlyoffice-docspace-plugin' ); ?>"
			>
		</p>
		<p
			class="block-buttons"
			<?php echo empty( $system_user ) ? 'hidden' : ''; ?>
		>	
			<input
				type="submit"
				id="oodsp-authorization-logout-button"
				class="button button-primary"
				value="<?php esc_html_e( 'Sign out', 'onlyoffice-docspace-plugin' ); ?>"
			>
		</p>
	</form>

	<div hidden>
		<div id="oodsp-system-frame"></div>
	</div>
	<div id="oodsp-loader" class="notification-dialog-background" style="display: none">
		<div class="loader"></div>
	</div>
	<div hidden>
		<div
			id="oodsp-save-system-user-confirm-dialog"
			title="<?php esc_html_e( 'Warning', 'onlyoffice-docspace-plugin' ); ?>"
		>
			<p><?php esc_html_e( 'Do you agree to connect your DocSpace account? The app will use it to perform actions.', 'onlyoffice-docspace-plugin' ); ?></p>
		</div>
	</div>
</div>
