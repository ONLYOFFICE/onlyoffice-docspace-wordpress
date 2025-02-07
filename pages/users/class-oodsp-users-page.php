<?php
/**
 * ONLYOFFICE DocSpace Plugin Users Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/users
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
 * ONLYOFFICE DocSpace Plugin Users Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/users
 */
class OODSP_Users_Page {
	/**
	 * The path to the class file.
	 *
	 * @var string
	 */
	protected $class_path;

	/**
	 * The URL to the class file.
	 *
	 * @var string
	 */
	protected $class_url;

	/**
	 * OODSP_Docspace_Client
	 *
	 * @var      OODSP_Docspace_Client    $oodsp_docspace_client
	 */
	private OODSP_Docspace_Client $oodsp_docspace_client;

	/**
	 * OODSP_User_Service
	 *
	 * @var      OODSP_User_Service    $oodsp_user_service
	 */
	private OODSP_User_Service $oodsp_user_service;

	/**
	 * OODSP_Settings_Manager
	 *
	 * @var      OODSP_Settings_Manager    $oodsp_settings_manager
	 */
	private OODSP_Settings_Manager $oodsp_settings_manager;

	/**
	 * Constructor for the OODSP_Main_Page class.
	 *
	 * @param OODSP_Docspace_Client  $oodsp_docspace_client  DocSpace client instance.
	 * @param OODSP_User_Service     $oodsp_user_service     User service instance.
	 * @param OODSP_Settings_Manager $oodsp_settings_manager Settings manager instance.
	 */
	public function __construct(
		OODSP_Docspace_Client $oodsp_docspace_client,
		OODSP_User_Service $oodsp_user_service,
		OODSP_Settings_Manager $oodsp_settings_manager
	) {
		$this->class_path = plugin_dir_path( __FILE__ );
		$this->class_url  = plugin_dir_url( __FILE__ );

		$this->oodsp_docspace_client  = $oodsp_docspace_client;
		$this->oodsp_settings_manager = $oodsp_settings_manager;
		$this->oodsp_user_service     = $oodsp_user_service;

		if ( empty( $this->oodsp_settings_manager->get_docspace_url() ) ) {
			return;
		}

		add_action( 'admin_head', array( $this, 'add_users_help_tab' ) );
		add_action( 'admin_head', array( $this, 'load_resources' ) );
		add_filter( 'manage_users_columns', array( $this, 'add_docspace_account_user_column' ) );
		add_filter( 'manage_users_sortable_columns', array( $this, 'make_docspace_account_user_column_sortable' ) );
		add_action( 'pre_get_users', array( $this, 'sort_users_by_docspace_account' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'show_docspace_account_column_data' ), 10, 3 );

		if ( $this->oodsp_settings_manager->exist_system_user() ) {
			add_filter( 'bulk_actions-users', array( $this, 'add_create_docspace_user_bulk_action' ) );
			add_filter( 'handle_bulk_actions-users', array( $this, 'handle_create_docspace_user_bulk_action' ), 10, 3 );
			add_filter( 'removable_query_args', array( $this, 'create_docspace_user_bulk_action_removable_query_args' ) );
			add_action( 'admin_notices', array( $this, 'create_docspace_user_bulk_action_admin_notice' ) );
		}
	}

	/**
	 * Adds a custom help tab to the Users page.
	 *
	 * This function checks if the current screen is the Users page,
	 * and if so, adds a new help item explaining the "Create in DocSpace" action.
	 *
	 * @return void
	 */
	public function add_users_help_tab() {
		$current_screen = get_current_screen();
		if ( 'users' !== $current_screen->id ) {
				return;
		}

		$help_tab_action_links = $current_screen->get_help_tab( 'action-links' );

		$help_tab_action_links['content'] = preg_replace( '/<\/ul>$/', '', $help_tab_action_links['content'] );

		$help_tab_action_links['content'] .= '<li>'
			. __( '<strong>Create in DocSpace</strong> allows you to create a new user in DocSpace. You can also create multiple users at once by using bulk actions.', 'onlyoffice-docspace-plugin' )
			. '</li>';
		$help_tab_action_links['content'] .= '</ul>';

		$current_screen->add_help_tab( $help_tab_action_links );
	}

	/**
	 * Load necessary resources for the users page.
	 *
	 * Enqueues scripts and styles specific to the users page.
	 * Also adds a confirmation dialog to the admin footer.
	 *
	 * @return void
	 */
	public function load_resources() {
		$current_screen = get_current_screen();
		if ( 'users' !== $current_screen->id ) {
				return;
		}

		wp_enqueue_script(
			OODSP_PLUGIN_NAME . '-users',
			$this->class_url . 'js/index.js',
			array( 'jquery', 'jquery-ui-dialog' ),
			OODSP_VERSION,
			true
		);

		wp_enqueue_style(
			OODSP_PLUGIN_NAME . '-users',
			$this->class_url . 'css/index.css',
			array( 'wp-jquery-ui-dialog' ),
			OODSP_VERSION
		);

		add_action( 'admin_footer', array( $this, 'oodsp_create_docspace_user_confirm_dialog' ), 30 );
	}

	/**
	 * Adds a DocSpace account column to the users list table.
	 *
	 * This function adds a new column 'DocSpace Account' to the WordPress users list table.
	 * The column header includes both text and an icon.
	 *
	 * @param array $columns An array of column name ⇒ label.
	 * @return array The modified array of columns.
	 */
	public function add_docspace_account_user_column( $columns ) {
		$columns['docspace_account'] = '<span> '
			. __( 'DocSpace Account', 'onlyoffice-docspace-plugin' )
			. '</span>'
			. '<span class="i">'
			. '<img src="' . esc_url( OODSP_PLUGIN_URL ) . 'includes/resources/images/alert.svg">'
			. '</span>';
		return $columns;
	}

	/**
	 * Makes the DocSpace account user column sortable.
	 *
	 * This function adds the 'docspace_account' column to the list of sortable columns
	 * in the WordPress users table.
	 *
	 * @param array $columns The current array of sortable columns.
	 * @return array The modified array of sortable columns.
	 */
	public function make_docspace_account_user_column_sortable( $columns ) {
		$columns['docspace_account'] = 'docspace_account';
		return $columns;
	}

	/**
	 * Sorts users by their DocSpace account status.
	 *
	 * This function modifies the query to sort users based on whether they have
	 * a DocSpace account or not. It adds a meta query to check for the existence
	 * of the 'docspace_account' meta key.
	 *
	 * @param WP_User_Query $query The WP_User_Query object.
	 */
	public function sort_users_by_docspace_account( $query ) {
		if ( 'docspace_account' !== $query->get( 'orderby' ) ) {
			return;
		}

		$order = isset( $_GET['order'] ) && 'desc' === $_GET['order'] ? 'DESC' : 'ASC';

		$docspace_accounts = $this->oodsp_user_service->get_all_docspace_accounts( true );

		uasort(
			$docspace_accounts,
			function ( $a, $b ) use ( $order ) {
				if ( empty( $a ) && ! empty( $b ) ) {
					return 'DESC' === $order ? 1 : -1;
				}
				if ( ! empty( $a ) && empty( $b ) ) {
					return 'DESC' === $order ? -1 : 1;
				}
				if ( empty( $a ) && empty( $b ) ) {
					return 0;
				}

				$user_name_a = $a->get_user_name();
				$user_name_b = $b->get_user_name();

				[ $user_a ] = explode( '@', $user_name_a );
				[ $user_b ] = explode( '@', $user_name_b );

				if ( 'DESC' === $order ) {
					return strcmp( $user_a, $user_b );
				} else {
					return strcmp( $user_b, $user_a );
				}
			}
		);

		$sorted_ids = array_keys( $docspace_accounts );

		$query->set( 'order', 'order' );
		$query->set( 'orderby', 'include' );
		$query->set( 'include', $sorted_ids );
	}

	/**
	 * Displays DocSpace account data in the user column.
	 *
	 * This function is responsible for populating the 'DocSpace Account' column
	 * in the WordPress users list table. It retrieves the DocSpace account
	 * information for a given user and returns the appropriate display value.
	 *
	 * @param string $value      The current content of the column.
	 * @param string $column_name The name of the column being displayed.
	 * @param int    $user_id    The ID of the user for the current row.
	 *
	 * @return string The content to be displayed in the column.
	 */
	public function show_docspace_account_column_data( $value, $column_name, $user_id ) {
		if ( 'docspace_account' === $column_name ) {
			$docspace_account = $this->oodsp_user_service->get_docspace_account( $user_id );

			return $docspace_account ? $docspace_account->get_user_name() : '—';
		}

		return $value;
	}

	/**
	 * Adds a bulk action to create DocSpace users.
	 *
	 * @param array $bulk_actions Existing bulk actions.
	 * @return array Modified bulk actions including the new 'Create in DocSpace' option.
	 */
	public function add_create_docspace_user_bulk_action( $bulk_actions ) {
		$bulk_actions['create-docspace-user'] = __( 'Create in DocSpace', 'onlyoffice-docspace-plugin' );
		return $bulk_actions;
	}

	/**
	 * Handles the bulk action to create DocSpace users.
	 *
	 * This function processes the 'create-docspace-user' bulk action for selected WordPress users.
	 * It attempts to create corresponding DocSpace accounts for each selected user, keeping track
	 * of successful creations, skipped users (those who already have DocSpace accounts), and errors.
	 *
	 * @param string $redirect_to The URL to redirect to after processing.
	 * @param string $action      The bulk action being performed.
	 * @param array  $user_ids    An array of user IDs selected for the bulk action.
	 *
	 * @return string The modified redirect URL with query parameters indicating the results.
	 */
	public function handle_create_docspace_user_bulk_action( $redirect_to, $action, $user_ids ) {
		if ( 'create-docspace-user' !== $action ) {
			return $redirect_to;
		}

		try {
			$settings = $this->oodsp_docspace_client->get_settings();
		} catch ( OODSP_Docspace_Client_Exception $e ) {
			$e->printStackTrace();

			$redirect_to = add_query_arg(
				'update',
				'error_create_docspace_user',
				$redirect_to
			);
			return $redirect_to;
		}

		$hash_settings = $settings['passwordHash'];

		$create_count  = 0;
		$skipped_count = 0;
		$error_count   = 0;
		foreach ( $user_ids as $user_id ) {
			$user             = get_user( $user_id );
			$docspace_account = $this->oodsp_user_service->get_docspace_account( $user_id );
			if ( ! empty( $docspace_account ) ) {
				++$skipped_count;
				continue;
			}

			[$email, $first_name, $last_name] = OODSP_Utils::get_docspace_user_data_from_wp_user( $user );
			$password_hash                    = OODSP_Utils::generate_random_docspace_password_hash(
				$hash_settings
			);

			try {
				$docspace_user = $this->oodsp_docspace_client->create_user(
					$email,
					$password_hash,
					$first_name,
					$last_name,
					4 // User.
				);

				$docspace_account = new OODSP_Docspace_Account(
					$docspace_user['id'],
					$email,
					$password_hash
				);

				$this->oodsp_user_service->put_docspace_account(
					$user_id,
					$docspace_account
				);
				++$create_count;
			} catch ( OODSP_Docspace_Client_Exception $e ) {
				++$error_count;
			}
		}

		$redirect_to = add_query_arg(
			'update',
			'create_docspace_user',
			$redirect_to
		);
		$redirect_to = add_query_arg(
			'create_count',
			$create_count,
			$redirect_to
		);
		$redirect_to = add_query_arg(
			'skipped_count',
			$skipped_count,
			$redirect_to
		);
		$redirect_to = add_query_arg(
			'error_count',
			$error_count,
			$redirect_to
		);

		return $redirect_to;
	}

	/**
	 * Adds query arguments to be removed after the DocSpace user bulk action.
	 *
	 * This function appends 'create_count', 'skipped_count', and 'error_count'
	 * to the list of removable query arguments. These arguments are used to
	 * display the results of the bulk action and should be removed from the URL
	 * after the admin notice is displayed.
	 *
	 * @param array $query_args Existing removable query arguments.
	 * @return array Modified list of removable query arguments.
	 */
	public function create_docspace_user_bulk_action_removable_query_args( $query_args ) {
		array_push(
			$query_args,
			'create_count',
			'skipped_count',
			'error_count',
			'error_create_docspace_user'
		);

		return $query_args;
	}

	/**
	 * Displays admin notices for DocSpace user bulk actions.
	 *
	 * This function checks for update parameters in the URL and displays
	 * appropriate success, warning, or error messages based on the results
	 * of the bulk action to create DocSpace users.
	 */
	public function create_docspace_user_bulk_action_admin_notice() {
		if ( ! empty( $_GET['update'] ) ) {
			$messages = array();
			switch ( $_GET['update'] ) {
				case 'create_docspace_user':
					if ( ! empty( $_GET['create_count'] ) ) {
						$messages[] = wp_get_admin_notice(
							__( 'DocSpace users updated successfully.', 'onlyoffice-docspace-plugin' ),
							array(
								'id'          => 'create_docspace_user-success-message',
								'type'        => 'success',
								'dismissible' => true,
							)
						);
					}

					if ( ! empty( $_GET['skipped_count'] ) ) {
						$messages[] = wp_get_admin_notice(
							__( 'DocSpace users skiped successfully.', 'onlyoffice-docspace-plugin' ),
							array(
								'id'          => 'create_docspace_user-warning-message',
								'type'        => 'warning',
								'dismissible' => true,
							)
						);
					}

					if ( ! empty( $_GET['error_count'] ) ) {
						$messages[] = wp_get_admin_notice(
							__( 'DocSpace users error.', 'onlyoffice-docspace-plugin' ),
							array(
								'id'          => 'create_docspace_user-error-message',
								'type'        => 'error',
								'dismissible' => true,
							)
						);
					}
					break;
				case 'error_create_docspace_user':
					$messages[] = wp_get_admin_notice(
						__( 'ONLYOFFICE DocSpace cannot be reached.', 'onlyoffice-docspace-plugin' ),
						array(
							'id'          => 'create_docspace_user-error-message',
							'type'        => 'error',
							'dismissible' => true,
						)
					);
					break;
			}

			foreach ( $messages as $message ) {
				echo wp_kses_post( $message );
			}
		}
	}

	/**
	 * Renders the confirmation dialog for creating DocSpace users.
	 *
	 * This function outputs a hidden div containing the dialog content
	 * that is shown when users attempt to create DocSpace users from
	 * the WordPress users list. The dialog explains the process and
	 * implications of exporting users to DocSpace.
	 */
	public function oodsp_create_docspace_user_confirm_dialog() {
		?>
		<div hidden>
			<div
				id="oodsp-create-docspace-user-confirm-dialog"
				title="<?php esc_html_e( 'DocSpace Export', 'onlyoffice-docspace-plugin' ); ?>"
			>
				<p>
					<?php esc_html_e( 'WordPress email will be used for the users login in DocSpace. Password will be generated automatically. Users can change their password manually in their profiles.The password for existing DocSpace accounts wont be overwritten. These users can use their current credentials.', 'onlyoffice-docspace-plugin' ); ?>
				</p>
			</div>
		</div>
		<?php
	}
}
