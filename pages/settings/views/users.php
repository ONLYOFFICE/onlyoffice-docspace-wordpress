<?php
/**
 * ONLYOFFICE DocSpace Setting page users.
 *
 * @package Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/settings/views
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

<div class="oodsp-white-frame">
	<div class="header-section">
		<?php esc_html_e( 'User export (Optional)', 'onlyoffice-docspace-plugin' ); ?>
	</div>
	<p>
		<?php
		echo wp_kses(
			__(
				'If your WordPress users do not have DocSpace accounts, click the <strong>Open user list</strong> button to export them to DocSpace. After clicking on this button, you will be redirected to the WordPress Users Module. If your WordPress users already have DocSpace accounts, they can continue using their current accounts.',
				'onlyoffice-docspace-plugin'
			),
			array(
				'strong' => array(),
			)
		);
		?>
	</p>
	<form method="get" action="users.php">
		<?php
		submit_button(
			__( 'Open user list', 'onlyoffice-docspace-plugin' ),
			'secondary',
			false,
			false
		);
		?>
	</form>
</div>
