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
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin/settings
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

require_once plugin_dir_path( __DIR__ ) . 'settings/actions/settings-update.php';
require_once plugin_dir_path( __DIR__ ) . 'settings/actions/settings-invite-users.php';

/**
 * Plugin settings for ONLYOFFICE DocSpace Plugin.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/admin/settings
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Settings {
	/**
	 * ID setting docspace_url.
	 */
	const DOCSPACE_URL = 'docspace_url';

	/**
	 * ID setting docspace_login.
	 */
	const DOCSPACE_LOGIN = 'docspace_login';

	/**
	 * ID setting docspace_password.
	 */
	const DOCSPACE_PASS = 'docspace_pass';

	/**
	 * ID setting docspace_token.
	 */
	const DOCSPACE_TOKEN = 'docspace_token';

	/**
	 * Init menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		$hook = add_submenu_page(
			'onlyoffice-docspace',
			__( 'ONLYOFFICE DocSpace Settings', 'onlyoffice-docspace-plugin' ),
			__( 'Settings', 'onlyoffice-docspace-plugin' ),
			'manage_options',
			'onlyoffice-docspace-settings',
			array( $this, 'do_get' )
		);

		add_action( "load-$hook", array( $this, 'on_load_onlyoffice_docspace_settings' ) );
	}

	/**
	 * Add DocSpace Users table.
	 *
	 * @return void
	 */
	public function on_load_onlyoffice_docspace_settings() {
		add_screen_option( 'per_page' );

		global $oodsp_users_list_table;
		$oodsp_users_list_table = new OODSP_Users_List_Table();

		switch ( $this->current_action() ) {
			case 'update':
				oodsp_update_settings();
				break;
			case 'invite':
				oodsp_invite_users();
				break;
		}

		global $_wp_http_referer;
		wp_reset_vars( array( '_wp_http_referer' ) );

		if ( ! empty( $_wp_http_referer ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
			exit;
		}
	}

	/**
	 * Init.
	 *
	 * @return void
	 */
	public function init() {
		register_setting( 'onlyoffice_docspace_settings', 'onlyoffice_docspace_settings' );

		add_settings_section(
			'general',
			'',
			'__return_false',
			'onlyoffice_docspace_settings'
		);

		add_settings_field(
			self::DOCSPACE_URL,
			__( 'DocSpace Service Address', 'onlyoffice-docspace-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_docspace_settings',
			'general',
			array(
				'id'    => self::DOCSPACE_URL,
				'class' => 'form-field form-required',
			)
		);

		add_settings_field(
			self::DOCSPACE_LOGIN,
			__( 'Login', 'onlyoffice-docspace-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_docspace_settings',
			'general',
			array(
				'id'    => self::DOCSPACE_LOGIN,
				'class' => 'form-field form-required',
			)
		);

		add_settings_field(
			self::DOCSPACE_PASS,
			__( 'Password', 'onlyoffice-docspace-plugin' ),
			array( $this, 'input_pass_cb' ),
			'onlyoffice_docspace_settings',
			'general',
			array(
				'id'    => self::DOCSPACE_PASS,
				'class' => 'form-field form-required form-pwd',
			)
		);
	}

	/**
	 * Return ONLYOFFICE DocSpace Setting
	 *
	 * @param string $key Setting key.
	 * @param string $def Default value.
	 */
	public function get_onlyoffice_docspace_setting( $key, $def = '' ) {
		$options = get_option( 'oodsp_settings' );
		if ( ! empty( $options ) && array_key_exists( $key, $options ) ) {
			return $options[ $key ];
		}

		return $def;
	}

	/**
	 * Input cb
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function input_cb( $args ) {
		$id = $args['id'];
		echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $this->get_onlyoffice_docspace_setting( $id ) ) . '" />';
	}

	/**
	 * Input pass cb
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function input_pass_cb( $args ) {
		$id = $args['id'];
		?>
		<div class="js">
			<div class="user-pass-wrap">
				<div class="wp-pwd">
					<div class="wp-pwd-input">
						<input type="password" id="user_pass" name="<?php echo esc_attr( $id ); ?>" class="input password-input" value="" />
					</div>
					<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Show password' ); ?>">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</button>
				</div>
			</div>
		</div
		<?php
	}

	/**
	 * Return result for get request.
	 */
	public function do_get() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_script(
			OODSP_PLUGIN_NAME . '-settings',
			OODSP_PLUGIN_URL . 'admin/js/settings.js',
			array( 'jquery' ),
			OODSP_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				OODSP_PLUGIN_NAME . '-settings',
				'onlyoffice-docspace-plugin',
				plugin_dir_path( OODSP_PLUGIN_FILE ) . 'languages/'
			);
		}

		wp_enqueue_script( 'user-profile' );

		wp_enqueue_style(
			OODSP_PLUGIN_NAME . '-settings',
			OODSP_PLUGIN_URL . 'admin/css/settings.css',
			array(),
			OODSP_VERSION
		);

		wp_enqueue_style(
			OODSP_PLUGIN_NAME . '-loader',
			OODSP_PLUGIN_URL . 'admin/css/loader.css',
			array(),
			OODSP_VERSION
		);

		global $users;
		wp_reset_vars( array( 'users' ) );

		if ( 'true' !== $users ) {
			?>
			<div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<?php settings_errors(); ?>
				<div id="onlyoffice-docspace-settings-notice"></div>
				<form id='onlyoffice-docspace-settings' action="admin.php?page=onlyoffice-docspace-settings" method="post">
					<?php
					settings_fields( 'onlyoffice_docspace_settings' );
					do_settings_sections( 'onlyoffice_docspace_settings' );
					?>
					<div class="oodsp-settings-notice">
						<p>
						<?php
						echo wp_kses(
							__( 'The current WordPress user will be added to DocSpace with the <b>Room admin</b> role.', 'onlyoffice-docspace-plugin' ),
							array(
								'b' => array(
									'class' => array(),
								),
							)
						);
						?>
						</p>
					</div>
					<?php
					submit_button( __( 'Save', 'onlyoffice-docspace-plugin' ), 'primary', null, true, array( 'id' => 'save-settings' ) );
					?>
				</form>

				<?php
				if ( ! empty( $this->get_onlyoffice_docspace_setting( self::DOCSPACE_URL ) )
					&& ! empty( $this->get_onlyoffice_docspace_setting( self::DOCSPACE_LOGIN ) )
					&& ! empty( $this->get_onlyoffice_docspace_setting( self::DOCSPACE_PASS ) )
					) {
					?>
				<h1 class="wp-heading-inline"><?php esc_html_e( 'DocSpace Users', 'onlyoffice-docspace-plugin' ); ?></h1>
				<p>
					<?php esc_html_e( 'To add new users to ONLYOFFICE DocSpace and to start working in plugin, please press', 'onlyoffice-docspace-plugin' ); ?>
					<b><?php esc_html_e( 'Export Now', 'onlyoffice-docspace-plugin' ); ?></b>
				</p>
				<p class="submit">
					<?php submit_button( __( 'Export Now', 'onlyoffice-docspace-plugin' ), 'secondary', 'users', false, array( 'onclick' => 'location.href = location.href + "&users=true";' ) ); ?>
				</p>
				<?php } ?>
			</div>
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
				wp_safe_redirect( add_query_arg( 'paged', $total_pages ) );
				exit;
			}
			?>

			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'DocSpace Users', 'onlyoffice-docspace-plugin' ); ?></h1>

				<form method="get" class="go-back-in-header">
					<input type="hidden" name="page" value="onlyoffice-docspace-settings">
					<?php submit_button( __( 'Back to main settings', 'onlyoffice-docspace-plugin' ), 'secondary', false ); ?>
				</form>

				<p>
					<?php
					echo wp_kses(
						__( 'To add new users to ONLYOFFICE DocSpace select multiple users and press <b>Invite to DocSpace</b>. All new users will be added with <b>User</b> role, if you want to change the role go to Accounts. Role <b>Room admin</b> is paid!', 'onlyoffice-docspace-plugin' ),
						array(
							'b' => array(
								'class' => array(),
							),
						)
					);
					?>
				</p>
				<?php
				global $s;
				if ( strlen( $s ?? '' ) ) {
					echo '<span class="subtitle">';
					printf(
						wp_kses(
							/* translators: %s: Search query. */
							__( 'Search results for: %s' ),
						),
						'<strong>' . esc_html( $s ) . '</strong>'
					);
					echo '</span>';
				}
				?>

				<hr class="wp-header-end">
				<?php
					oodsp_users_messages();
					$oodsp_users_list_table->views();
				?>

				<form id="onlyoffice-docspace-settings-users" >
					<?php
						$oodsp_users_list_table->search_box( __( 'Search Users' ), 'user' );

						global $role;
						wp_reset_vars( array( 'role' ) );

					if ( ! empty( $role ) ) {
						?>
					<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
						<?php
					}
					?>
					<?php $oodsp_users_list_table->display(); ?>
				</form>

				<div class="clear"></div>
			</div>
			<?php
		}
		?>
			<div hidden><div id="oodsp-system-frame"></div></div>
			<div id="onlyoffice-docspace-settings-loader" class="notification-dialog-background" hidden><div class="loader"></div></div>
		<?php
	}

	/**
	 * Return current actrion.
	 */
	private function current_action() {
		global $filter_action, $action;
		wp_reset_vars( array( 'filter_action', 'action' ) );

		if ( ! empty( $filter_action ) ) {
			return false;
		}

		if ( ! empty( $action ) && -1 !== $action ) {
			return $action;
		}

		return false;
	}
}
