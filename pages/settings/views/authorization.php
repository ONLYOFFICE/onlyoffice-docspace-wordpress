<?php
/**
 * ONLYOFFICE DocSpace Setting page authorization.
 *
 * @package Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/settings/view
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

?>

<?php
$user             = wp_get_current_user();
$docspace_account = $this->oodsp_user_service->get_docspace_account( $user->ID );
$system_user      = $this->oodsp_settings_manager->get_system_user();
$is_system_user   = false;

if ( ! empty( $system_user ) && $user->ID === $system_user->get_id() ) {
	$is_system_user = true;
}
?>

<div id="oodsp-authorization-notice"></div>

<div class="oodsp-white-frame">
	<div class="header-section"><?php esc_html_e( 'DocSpace Authorization', 'onlyoffice-docspace-plugin' ); ?></div>

	<form id="oodsp-authorization-form" action="admin.php?page=onlyoffice-docspace-settings" method="post" autocomplete="off">
		<?php
			settings_fields( 'onlyoffice_docspace_settings' );
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><?php esc_html_e( 'Login', 'onlyoffice-docspace-plugin' ); ?></th>
					<td>
						<input
							name="docspace-login"
							type="text"
							class="regular-text"
							value="<?php echo ! empty( $docspace_account ) ? esc_attr( $docspace_account->get_user_name() ) : ''; ?>"
							<?php echo ! empty( $docspace_account ) ? 'disabled' : ''; ?>
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
											value="<?php echo ! empty( $docspace_account ) ? '********' : ''; ?>"
											<?php echo ! empty( $docspace_account ) ? 'disabled' : ''; ?>
										/>
									</div>
									<button
										type="button"
										class="button button-secondary wp-hide-pw hide-if-no-js"
										data-toggle="0"
										aria-label="<?php esc_attr_e( 'Show password' ); ?>"
										<?php echo ! empty( $docspace_account ) ? 'disabled' : ''; ?>
									>
										<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
									</button>
								</div>
							</div>
						</div
					</td>
				</tr>
				<?php if ( empty( $docspace_account ) || $is_system_user ) { ?>
				<tr class="form-field">
					<th scope="row"></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<span>Membership</span>
							</legend>
							<label for="users_can_register">
								<input
									name="docspace-system-user"
									type="checkbox"
									<?php echo $is_system_user || ( empty( $docspace_account ) && ! $this->oodsp_settings_manager->exist_system_user() ) ? 'checked' : ''; ?>
									<?php echo ! empty( $docspace_account ) || ! $this->oodsp_settings_manager->exist_system_user() ? 'disabled' : ''; ?>
								>
								System User
							</label>
						</fieldset>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<p <?php echo ! empty( $docspace_account ) ? 'hidden' : ''; ?> >	
			<input
				type="submit"
				id="oodsp-authorization-login-button"
				class="button button-primary"
				value="<?php esc_html_e( 'Login', 'onlyoffice-docspace-plugin' ); ?>"
			>
		</p>
		<p <?php echo empty( $docspace_account ) ? 'hidden' : ''; ?> >	
			<input
				type="submit"
				id="oodsp-authorization-logout-button"
				class="button button-primary"
				value="<?php esc_html_e( 'Logout', 'onlyoffice-docspace-plugin' ); ?>"
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
