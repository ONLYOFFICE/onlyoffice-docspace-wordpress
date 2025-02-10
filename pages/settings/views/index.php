<?php
/**
 * ONLYOFFICE DocSpace Setting page view.
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

<div id="oodsp-settings" class="wrap">
	<div class="header">
		<div class="h1"><?php echo esc_html( get_admin_page_title() ); ?></div>

		<div><?php esc_html_e( 'Configure ONLYOFFICE DocSpace plugin settings.', 'onlyoffice-docspace-plugin' ); ?></div>
	</div>

	<?php settings_errors(); ?>
	<div id="onlyoffice-docspace-settings-notice"></div>

	<div class="oodsp-white-frame">
		<div class="header-section"><?php esc_html_e( 'General settings', 'onlyoffice-docspace-plugin' ); ?></div>

		<div
			id="oodsp-settings-csp-notice"
			class="oodsp-notice-section"
			<?php echo ! empty( $this->oodsp_settings_manager->get_docspace_url() ) ? 'hidden' : ''; ?>
		>
			<p><b><?php esc_html_e( 'Check the CSP settings', 'onlyoffice-docspace-plugin' ); ?></b></p>
			<p>
				<?php
				echo wp_kses(
					__( 'Before connecting the app, please go to the <b>DocSpace Settings - Developer tools - JavaScript SDK</b> and add the following credentials to the allow list:', 'onlyoffice-docspace-plugin' ),
					array(
						'b' => array(
							'class' => array(),
						),
					)
				);
				?>
			</p>
			<p><b><?php esc_html_e( 'WordPress portal address:', 'onlyoffice-docspace-plugin' ); ?><span> <?php echo esc_html( get_site_url() ); ?></span></b></p>
		</div>
		
		<form id='oodsp-general-settings-form' action="admin.php?page=onlyoffice-docspace-settings" method="post" autocomplete="off">
			<?php
				settings_fields( 'onlyoffice_docspace_settings' );
			?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr class="form-field form-required">
						<th scope="row"><?php esc_html_e( 'DocSpace Service Address', 'onlyoffice-docspace-plugin' ); ?></th>
						<td>
							<input
								id="docspace_url"
								name="docspace_url"
								type="text"
								class="regular-text"
								placeholder="https://"
								value="<?php echo esc_attr( $this->oodsp_settings_manager->get_docspace_url() ); ?>"
								<?php echo ! empty( $this->oodsp_settings_manager->get_docspace_url() ) ? 'disabled' : ''; ?>
							>
						</td>
					</tr>
				</tbody>
			</table>
			<p
				id="oodsp-general-settings-buttons"
				<?php echo ! empty( $this->oodsp_settings_manager->get_docspace_url() ) ? 'hidden' : ''; ?>
			>
				<input
					id="oodsp-general-settings-save-button"
					class="button button-primary"
					type="submit"
					value="<?php esc_html_e( 'Connect', 'onlyoffice-docspace-plugin' ); ?>"
				>
				<input
					id="oodsp-general-settings-cancel-button"
					class="button"
					type="submit"
					value="<?php esc_html_e( 'Cancel', 'onlyoffice-docspace-plugin' ); ?>"
					style="<?php echo empty( $this->oodsp_settings_manager->get_docspace_url() ) ? 'display: none;' : ''; ?>"
				>
			</p>
			<p
				id="oodsp-general-settings-change-buttons"
				<?php echo empty( $this->oodsp_settings_manager->get_docspace_url() ) ? 'hidden' : ''; ?>
			>
				<input
					id="oodsp-general-settings-change-button"
					type="submit"
					class="button"
					value="<?php esc_html_e( 'Change', 'onlyoffice-docspace-plugin' ); ?>"
				>
				<input
					id="oodsp-general-settings-disconnect-button"	
					type="submit"
					class="button"
					value="<?php esc_html_e( 'Disconnect', 'onlyoffice-docspace-plugin' ); ?>"
				>
			<p>
		</form>
	</div>

	<?php
	if ( ! empty( $this->oodsp_settings_manager->get_docspace_url() ) ) {
		include $this->class_path . '/views/authorization.php';
	}
	?>
	<?php
	if ( $this->oodsp_settings_manager->exist_system_user() ) {
		include $this->class_path . '/views/users.php';
	}
	?>
</div>

<div hidden>
	<div
		id="oodsp-disconnect-confirm-dialog"
		title="<?php esc_html_e( 'Warning', 'onlyoffice-docspace-plugin' ); ?>"
	>
		<p><?php esc_html_e( 'If you press the Disconnect button, you will not have access to ONLYOFFICE DocSpace. This will remove the connections between Rooms and Pages, and disconnect all users.', 'onlyoffice-docspace-plugin' ); ?></p>
	</div>
</div>
