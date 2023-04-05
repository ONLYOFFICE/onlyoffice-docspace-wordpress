<?php
/**
 * Plugin settings for ONLYOFFICE DocSpace Plugin.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes
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
 * Plugin settings for ONLYOFFICE DocSpace Plugin.
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Settings {
	/**
	 * ID setting docspace_url.
	 */
	const DOCSPACE_URL = 'onlyoffice_settings_docspace_url';

	/**
	 * ID setting docspace_url_tmp.
	 */
	const DOCSPACE_URL_TEMP = 'onlyoffice_settings_docspace_url_temp';

	/**
	 * ID setting docspace_login.
	 */
	const DOCSPACE_LOGIN = 'onlyoffice_settings_docspace_login';

	/**
	 * ID setting docspace_login_temp.
	 */
	const DOCSPACE_LOGIN_TEMP = 'onlyoffice_settings_docspace_login_temp';

	/**
	 * ID setting docspace_password.
	 */
	const DOCSPACE_PASSWORD = 'onlyoffice_settings_docspace_password';

	/**
	 * ID setting docspace_password_temp.
	 */
	const DOCSPACE_PASSWORD_TMP = 'onlyoffice_settings_docspace_password_temp';

	/**
	 * Init menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		$hook = add_submenu_page(
			'onlyoffice-docspace',
			'ONLYOFFICE DocSpace Settings',
			'Settings',
			'manage_options',
			'onlyoffice-docspace-settings',
			array( $this, 'options_page' )
		);

		global $_wp_http_referer;
		wp_reset_vars( array( '_wp_http_referer' ) );

		if ( ! empty( $_wp_http_referer ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
			exit;
		}

		add_action( "load-$hook", array( $this, 'add_docspace_users_table' ) );
	}

	/**
	 * Add DocSpace Users table.
	 *
	 * @return void
	 */
	public function add_docspace_users_table() {
		add_screen_option( 'per_page' );
		global $oodsp_users_list_table;
		$oodsp_users_list_table = new OODSP_Users_List_Table();
	}

	/**
	 * Init.
	 *
	 * @return void
	 */
	public function init() {
		register_setting( 'onlyoffice_docspace_settings_group', 'onlyoffice_docspace_settings' );

		add_settings_section(
			'onlyoffice_docspace_settings_general_section',
			'',
			array( $this, 'general_section_callback' ),
			'onlyoffice_docspace_settings_group'
		);

		add_settings_field(
			self::DOCSPACE_URL_TEMP,
			__( 'DocSpace Service Address', 'onlyoffce-docspace-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_docspace_settings_group',
			'onlyoffice_docspace_settings_general_section',
			array(
				'id' => self::DOCSPACE_URL_TEMP,
			)
		);

		add_settings_field(
			self::DOCSPACE_LOGIN_TEMP,
			__( 'Login', 'onlyoffce-docspace-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_docspace_settings_group',
			'onlyoffice_docspace_settings_general_section',
			array(
				'id' => self::DOCSPACE_LOGIN_TEMP,
			)
		);

		add_settings_field(
			self::DOCSPACE_PASSWORD_TMP,
			__( 'Password', 'onlyoffce-docspace-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_docspace_settings_group',
			'onlyoffice_docspace_settings_general_section',
			array(
				'id' => self::DOCSPACE_PASSWORD_TMP,
			)
		);
	}

	// ToDo: callbacks are mostly the same, refactor!

	/**
	 * General section callback.
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function general_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Configure ONLYOFFICE DocSpace connector settings ', 'onlyoffce-docspace-plugin' ); ?></p>
		<?php
	}

	/**
	 * Input cb
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function input_cb( $args ) {
		$options = get_option( 'onlyoffice_docspace_settings' );
		?>
		<input id="<?php echo esc_attr( $args['id'] ); ?>" type="text" name="onlyoffice_docspace_settings[<?php echo esc_attr( $args['id'] ); ?>]" value="<?php echo esc_attr( $options[ $args['id'] ] ); ?>">
		<?php
	}

	/**
	 * General section callback.
	 */
	public function options_page() {
		$should_wizard = false;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'onlyoffice_docspace_settings_messages',
				'onlyoffice_docspace_message',
				__( 'Settings Saved', 'onlyoffce-docspace-plugin' ),
				'updated'
			); // ToDo: can also check if settings are valid e.g. make connection to docServer!

			$options = get_option( 'onlyoffice_docspace_settings' );
			if ( $options[ self::DOCSPACE_URL_TEMP ] !== $options[ self::DOCSPACE_URL ]
				|| $options[ self::DOCSPACE_LOGIN_TEMP ] !== $options[ self::DOCSPACE_LOGIN ]
				|| $options[ self::DOCSPACE_PASSWORD_TMP ] !== $options[ self::DOCSPACE_PASSWORD ] ) {
				$should_wizard = true;
			}
		}

		settings_errors( 'onlyoffice_docspace_settings_messages' );

		if ( ! isset( $_GET['users'] ) ) {
			?>
			<div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<form action="options.php" method="post">
					<?php
					settings_fields( 'onlyoffice_docspace_settings_group' );
					do_settings_sections( 'onlyoffice_docspace_settings_group' );
					submit_button( 'Save Settings' );
					?>
				</form>

				<h1 class="wp-heading-inline">
					<?php __( 'DocSpace Users', 'onlyoffce-docspace-plugin' ); ?>
				</h1>
				<p> 
					<?php __( 'To add new users to ONLYOFFICE DocSpace and to start working in plugin, please press', 'onlyoffce-docspace-plugin' ); ?>
					<b><?php __( 'Sync Now', 'onlyoffce-docspace-plugin' ); ?></b>
				</p>

				<p class="submit">
					<?php submit_button( 'Sync Now', 'secondary', 'users', false, array( 'onclick' => 'location.href = location.href + "&users=true";' ) ); ?>
				</p>
			</div>

			<?php if ( $should_wizard ) : ?>
				<script><?php echo( "location.href = location.href.replace('onlyoffice-docspace-settings', 'onlyoffice-docspace-wizard');" ); ?></script>
			<?php endif; ?>
			<?php
		} else {
			global $oodsp_users_list_table;
			$pagenum = $oodsp_users_list_table->get_pagenum();

			global $_wp_http_referer;
			wp_reset_vars( array( '_wp_http_referer' ) );

			if ( ! empty( $_wp_http_referer ) && isset( $_SERVER['REQUEST_URI'] ) ) {
				wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
				exit;
			}

			$oodsp_users_list_table->prepare_items();
			$total_pages = $oodsp_users_list_table->get_pagination_arg( 'total_pages' );
			if ( $pagenum > $total_pages && $total_pages > 0 ) {
				wp_redirect( add_query_arg( 'paged', $total_pages ) );
				exit;
			}
			?>

			<div class="wrap">
				<h1 class="wp-heading-inline">
					<?php __( 'DocSpace Users', 'onlyoffce-docspace-plugin' ); ?>
				</h1>
				<p> 
					<?php __( 'To add new users to ONLYOFFICE DocSpace press Invite or select multiple users and press Invite selected users to DocSpace. To remove users from DocSpace press Disable icon. All new users will be added with User role, if you want to change the role go to Accounts. Role Room admin is paid!', 'onlyoffce-docspace-plugin' ); ?>
				</p>

				<?php
				global $usersearch;
				if ( strlen( $usersearch ) ) {
					echo '<span class="subtitle">';
					printf(
						__( 'Search results for: %s' ),
						'<strong>' . esc_html( $usersearch ) . '</strong>'
					);
					echo '</span>';
				}
				?>

				<hr class="wp-header-end">
				<?php $oodsp_users_list_table->views(); ?>

				<form method="get" l>

					<?php $oodsp_users_list_table->search_box( __( 'Search Users' ), 'user' ); ?>

					<?php if ( ! empty( $_REQUEST['role'] ) ) { ?>
						<input type="hidden" name="role" value="<?php echo esc_attr( $_REQUEST['role'] ); ?>" />
					<?php } ?>
					<?php $oodsp_users_list_table->display(); ?>
				</form>

				<div class="clear"></div>

				<form  method="get">
					<input type="hidden" name="page" value="onlyoffice-docspace-settings">
					<?php submit_button( 'Back to main settings', 'secondary', false ); ?>
				</form>
			</div>
			<?php
		}
	}
}
