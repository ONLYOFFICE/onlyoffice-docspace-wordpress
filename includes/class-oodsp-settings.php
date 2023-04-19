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
			array( $this, 'do_get' )
		);

		// global $_wp_http_referer;
		// wp_reset_vars( array( '_wp_http_referer' ) );

		//  if ( ! empty( $_wp_http_referer ) && isset( $_SERVER['REQUEST_URI'] ) ) {
		// 	// 	wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		// 	// 	exit;
		//  }

		add_action( "load-$hook", array( $this, 'add_docspace_users_table' ) );
	}

	/**
	 * Add DocSpace Users table.
	 *
	 * @return void
	 */
	public function add_docspace_users_table() {
		$this->do_post();
		add_screen_option( 'per_page' );
		global $oodsp_users_list_table;
		$oodsp_users_list_table = new OODSP_Users_List_Table();

		if ( isset( $_REQUEST['wp_http_referer'] ) ) {
			$redirect = remove_query_arg( array( 'wp_http_referer', 'updated', 'delete_count' ), wp_unslash( $_REQUEST['wp_http_referer'] ) );
		} else {
			$redirect = 'admin.php?page=onlyoffice-docspace-settings&users=true';
		}

		switch ( $oodsp_users_list_table->current_action() ) {
			case 'invite':
				check_admin_referer( 'bulk-users' );

				
				if ( empty( $_REQUEST['users'] ) ) {
					wp_redirect( $redirect );
					exit;
				}


				$userids = array_map( 'intval', (array) $_REQUEST['users'] );

				error_log( print_r($userids, true));
				foreach ( $userids as $id ) {
					$data = array( 
						'user_id' => $id,
						'user_pass' => md5(wp_generate_password( 18 ))
					);

					global $wpdb;
					$wpdb->insert( $wpdb->prefix . 'docspace_users', $data );
				}

				wp_redirect( $redirect );
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
			__( 'Configure ONLYOFFICE DocSpace connector settings ', 'onlyoffce-docspace-plugin' ),
			'__return_false',
			'onlyoffice_docspace_settings'
		);

		add_settings_field(
			self::DOCSPACE_URL,
			__( 'DocSpace Service Address', 'onlyoffce-docspace-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_docspace_settings',
			'general',
			array(
				'id' => self::DOCSPACE_URL,
			)
		);

		add_settings_field(
			self::DOCSPACE_LOGIN,
			__( 'Login', 'onlyoffce-docspace-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_docspace_settings',
			'general',
			array(
				'id' => self::DOCSPACE_LOGIN,
			)
		);

		add_settings_field(
			self::DOCSPACE_PASS,
			__( 'Password', 'onlyoffce-docspace-plugin' ),
			array( $this, 'input_pass_cb' ),
			'onlyoffice_docspace_settings',
			'general',
			array(
				'id' => self::DOCSPACE_PASS,
			)
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'user-profile' );
	}

	public function get_onlyoffice_docspace_setting( $key, $default = "" ) {
		$options = get_option( 'onlyoffice_docspace_settings' );
		if (! empty( $options ) && array_key_exists( $key, $options )) {
			return $options[$key];
		}

		return $default;
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
		echo '<input id="' . esc_attr ( $id ) . '" name="' . esc_attr ( $id ) . '" type="text" value="' . esc_attr( $this->get_onlyoffice_docspace_setting( $id ) ) . '" />';
		echo '<p class="description"></p>';
	}

	public function input_pass_cb( $args ) {
		$id = $args['id'];
		?>
		<div class="login js">
			<div class="user-pass-wrap">
				<div class="wp-pwd">
					<input type="password" id="user_pass" name="<?php echo esc_attr ( $id ) ?>" class="input password-input" value="" />
					<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Show password' ); ?>">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</button>
				</div>
			</div>
		</div
		<?php
	}

	public function do_get () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_GET['users'] ) ) {
			?>
			<div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<?php settings_errors(); ?>
				<form action="admin.php?page=onlyoffice-docspace-settings" method="post">
					<?php
					settings_fields( 'onlyoffice_docspace_settings' );
					do_settings_sections( 'onlyoffice_docspace_settings' );
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
						/* translators: %s: Search query. */
						esc_html_e( 'Search results for: %s' ),
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

	public function do_post () {
		if ( isset( $_POST[self::DOCSPACE_URL] ) && isset( $_POST[self::DOCSPACE_LOGIN] ) && isset( $_POST[self::DOCSPACE_PASS] ) ) {
			check_admin_referer( 'onlyoffice_docspace_settings-options' );
			
			$docspace_url = $this->prepare_value( $_POST[self::DOCSPACE_URL] );
			$docspace_login = $this->prepare_value( $_POST[self::DOCSPACE_LOGIN] );
			$docspace_pass = $this->prepare_value( $_POST[self::DOCSPACE_PASS] );

			$this->auth_docspace( $docspace_url, $docspace_login, $docspace_pass );
			
			if ( ! get_settings_errors() ) {
				$value = array(
					self::DOCSPACE_URL   =>  $docspace_url,
					self::DOCSPACE_LOGIN => $docspace_login, 
					self::DOCSPACE_PASS  => $docspace_pass,
				);
	
				update_option( 'onlyoffice_docspace_settings', $value );

				add_settings_error( 'general', 'settings_updated', __( 'Settings Saved', 'onlyoffce-docspace-plugin' ), 'success' );
			}
		
			set_transient( 'settings_errors', get_settings_errors(), 30 );

			wp_safe_redirect( admin_url( 'admin.php?page=onlyoffice-docspace-settings&settings-updated=true' ) );
			exit;
		}
	}

	private function auth_docspace ( $docspace_url, $docspace_login, $docspace_pass ) {
		$res_auth = wp_remote_post(
			$docspace_url . "api/2.0/authentication",
			array(
				'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
				'body'    => json_encode(
					array(
						'userName' => $docspace_login,
						'password' => $docspace_pass
					)
				),
				'method'  => 'POST'
			)
		);

		if ( is_wp_error( $res_auth ) || 200 !== wp_remote_retrieve_response_code( $res_auth ) ) {
			add_settings_error( 'general', 'settings_updated', 'Invalid credentials. Please try again!' );
			return;
		}

		$data_auth = json_decode( wp_remote_retrieve_body( $res_auth ), true );

		$token = $data_auth['response']['token'];

		$res_users = wp_remote_get(
			$docspace_url . "api/2.0/people/email?email=" . $docspace_login,
			array('cookies' => array('asc_auth_key' => $token)) 
		);
			
		if ( is_wp_error( $res_users ) || 200 !== wp_remote_retrieve_response_code( $res_users ) ) {
			add_settings_error( 'general', 'settings_updated', 'Error getting data user. Please try again!' );
			return;
		}

		$data_users = json_decode( wp_remote_retrieve_body( $res_users ), true );
		
		$docspace_user = $data_users['response'];

		if ( ! $docspace_user['isAdmin'] ) {
			add_settings_error( 'general', 'settings_updated', 'Not Admin. Please try again!' );
			return;
		}
	}

	private function prepare_value ( $value ) {
		if ( ! is_array( $value ) ) {
			$value = trim( $value );
		}
		
		return wp_unslash( $value );
	}
}
