<?php
/**
 * List Table API: OODSP_Users_List_Table class
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/includes/users
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


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Core class used to implement displaying users in a list table for the network admin.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/files
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Users_List_Table extends WP_List_Table {

	/**
	 * The list docspace users.
	 *
	 */
	private $docspace_users;


	/**
	 * The list docspace users.
	 *
	 */
	private $is_connected_to_docspace = false;

	/**
	 *
	 */
	private $plugin_settings;

	/**
	 * Constructor.
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		parent::__construct(
			array(
				'singular' => 'user',
				'plural'   => 'users',
				'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
			)
		);

		$this->plugin_settings = new OODSP_Settings();
	}

	/**
	 * Check the current user's permissions.
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can( 'list_users' );
	}

	/**
	 * Prepare the users list for display.
	 *
	 * @global string $role
	 * @global string $usersearch
	 */
	public function prepare_items() {
		global $role, $usersearch;

		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';

		$per_page       = 'docspace_page_onlyoffice_docspace_settings_per_page';
		$users_per_page = $this->get_items_per_page( $per_page );

		$paged = $this->get_pagenum();

		$args = array(
			'number' => $users_per_page,
			'offset' => ( $paged - 1 ) * $users_per_page,
			'role'   => $role,
			'search' => $usersearch,
			'fields' => 'all_with_meta',
		);

		if ( '' !== $args['search'] ) {
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = $_REQUEST['orderby'];
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$args['order'] = $_REQUEST['order'];
		}

		$args = apply_filters( 'users_list_table_query_args', $args );

		$wp_user_search = new WP_User_Query( $args );

		$this->items = $wp_user_search->get_results();

		$this->set_pagination_args(
			array(
				'total_items' => $wp_user_search->get_total(),
				'per_page'    => $users_per_page,
			)
		);

		$res_auth = wp_remote_post(
			$this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_URL) . "api/2.0/authentication",
			array(
				'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
				'body'    => json_encode(
					array(
						'userName' => $this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_LOGIN),
						'password' => $this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_PASS)
					)
				),
				'method'  => 'POST'
			)
		);

		if ( !is_wp_error( $res_auth ) && 200 === wp_remote_retrieve_response_code( $res_auth ) ) {
			$data_auth = json_decode( wp_remote_retrieve_body( $res_auth ), true );

			$token = $data_auth['response']['token'];

			$res_users = wp_remote_get(
				$this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_URL) . "api/2.0/people",
				array('cookies' => array('asc_auth_key' => $token)) 
			);
			
			if ( !is_wp_error( $res_users ) && 200 === wp_remote_retrieve_response_code( $res_users ) ) {
				$data_users = json_decode( wp_remote_retrieve_body( $res_users ), true );
				
				$this->docspace_users = $data_users['response'];
				$this->is_connected_to_docspace = true;
			}
		}
	}

	/**
	 * Output 'no users' message.
	 *
	 * @since 3.1.0
	 */
	public function no_items() {
		esc_html_e( 'No users found.' );
	}

	/**
	 * Return an associative array listing all the views that can be used
	 * with this table.
	 *
	 * @global string $role
	 *
	 * @return string[] An array of HTML links keyed by their view.
	 */
	protected function get_views() {
		global $role;

		$wp_roles = wp_roles();

		$count_users = ! wp_is_large_user_count();

		$url = 'admin.php?page=onlyoffice-docspace-settings&users=true';

		$role_links  = array();
		$avail_roles = array();
		$all_text    = __( 'All' );

		if ( $count_users ) {
			$users_of_blog = count_users();

			$total_users = $users_of_blog['total_users'];
			$avail_roles =& $users_of_blog['avail_roles'];
			unset( $users_of_blog );

			$all_text = sprintf(
				/* translators: %s: Number of users. */
				_nx(
					'All <span class="count">(%s)</span>',
					'All <span class="count">(%s)</span>',
					$total_users,
					'users'
				),
				number_format_i18n( $total_users )
			);
		}

		$role_links['all'] = array(
			'url'     => $url,
			'label'   => $all_text,
			'current' => empty( $role ),
		);

		foreach ( $wp_roles->get_names() as $this_role => $name ) {
			if ( $count_users && ! isset( $avail_roles[ $this_role ] ) ) {
				continue;
			}

			$name = translate_user_role( $name );
			if ( $count_users ) {
				$name = sprintf(
					/* translators: 1: User role name, 2: Number of users. */
					__( '%1$s <span class="count">(%2$s)</span>' ),
					$name,
					number_format_i18n( $avail_roles[ $this_role ] )
				);
			}

			$role_links[ $this_role ] = array(
				'url'     => esc_url( add_query_arg( 'role', $this_role, $url ) ),
				'label'   => $name,
				'current' => $this_role === $role,
			);
		}

		if ( ! empty( $avail_roles['none'] ) ) {

			$name = __( 'No role' );
			$name = sprintf(
				/* translators: 1: User role name, 2: Number of users. */
				__( '%1$s <span class="count">(%2$s)</span>' ),
				$name,
				number_format_i18n( $avail_roles['none'] )
			);

			$role_links['none'] = array(
				'url'     => esc_url( add_query_arg( 'role', 'none', $url ) ),
				'label'   => $name,
				'current' => 'none' === $role,
			);
		}

		return $this->get_views_links( $role_links );
	}

	/**
	 * Retrieve an associative array of bulk actions available on this table.
	 *
	 * @return array Array of bulk action labels keyed by their action.
	 */
	protected function get_bulk_actions() {
		$actions = array();

		$actions['invite'] = __( 'Invite to Docspace' );
		$actions['delete'] = __( 'Delete from Docspace' );

		return $actions;
	}

	/**
	 * Get a list of columns for the list table.
	 *
	 * @return string[] Array of column titles keyed by their column name.
	 */
	public function get_columns() {
		$columns = array(
			'cb'                    => '<input type="checkbox" />',
			'username'              => __( 'Username' ),
			'name'                  => __( 'Name' ),
			'email'                 => __( 'Email' ),
			'role'                  => __( 'Role' ),
			'in_docspace'           => __( 'In DocSpace' ),
			'type_user_in_docspace' => __( 'Type' ),
		);

		return $columns;
	}

	/**
	 * Get a list of sortable columns for the list table.
	 *
	 * @return array Array of sortable columns.
	 */
	protected function get_sortable_columns() {
		$columns = array(
			'username' => 'login',
			'email'    => 'email',
		);

		return $columns;
	}

	/**
	 * Generates the tbody element for the list table.
	 *
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			if ($this->is_connected_to_docspace) {
				$this->display_rows();
			} else {
				echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
				echo '<span>Error to connect DocSpace Server!</space>';
				echo '</td></tr>';
			}
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generate the list table rows.
	 */
	public function display_rows() {
		foreach ( $this->items as $userid => $user_object ) {
			echo "\n\t" . $this->single_row( $user_object );
		}
	}

	/**
	 * Generate HTML for a single row.
	 *
	 * @param WP_User $user_object The current user object.
	 * @return string Output for a single row.
	 */
	public function single_row( $user_object ) {
		if ( ! ( $user_object instanceof WP_User ) ) {
			$user_object = get_userdata( (int) $user_object );
		}
		$user_object->filter = 'display';
		$email               = $user_object->user_email;

		$user_roles = $this->get_role_list( $user_object );

		// Set up the hover actions for this user.
		$checkbox    = '';
		$super_admin = '';

		if ( is_multisite() && current_user_can( 'manage_network_users' ) ) {
			if ( in_array( $user_object->user_login, get_super_admins(), true ) ) {
				$super_admin = ' &mdash; ' . __( 'Super Admin' );
			}
		}

		$user_docspace_status = -1;

		for($i = 0; $i < count( $this->docspace_users ); ++$i) {
			if( $this->docspace_users[$i]['email'] === $email) {
				$user_docspace_status =$this->docspace_users[$i]['activationStatus'];
			}
		}

		// Check if the user for this row is editable.
		if ( current_user_can( 'list_users' ) ) {
			// Role classes.
			$role_classes = esc_attr( implode( ' ', array_keys( $user_roles ) ) );

			// Set up the checkbox (because the user is editable, otherwise it's empty).
			$checkbox = sprintf(
				'<label class="screen-reader-text" for="user_%1$s">%2$s</label>' .
				'<input type="checkbox" name="users[]" id="user_%1$s" class="%3$s" value="%1$s" />',
				$user_object->ID,
				/* translators: Hidden accessibility text. %s: User login. */
				sprintf( __( 'Select %s' ), $user_object->user_login ),
				$role_classes
			);

		}

		$edit = "<strong>{$user_object->user_login}{$super_admin}</strong>";

		$avatar = get_avatar( $user_object->ID, 32 );

		// Comma-separated list of user roles.
		$roles_list = implode( ', ', $user_roles );

		$row = "<tr id='user-$user_object->ID'>";

		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}

			if ( in_array( $column_name, $hidden, true ) ) {
				$classes .= ' hidden';
			}

			$data = 'data-colname="' . esc_attr( wp_strip_all_tags( $column_display_name ) ) . '"';

			$attributes = "class='$classes' $data";

			if ( 'cb' === $column_name ) {
				$row .= "<th scope='row' class='check-column'>$checkbox</th>";
			} else {
				$row .= "<td $attributes>";
				switch ( $column_name ) {
					case 'username':
						$row .= "$avatar $edit";
						break;
					case 'name':
						if ( $user_object->first_name && $user_object->last_name ) {
							$row .= sprintf(
								/* translators: 1: User's first name, 2: Last name. */
								_x( '%1$s %2$s', 'Display name based on first name and last name' ),
								$user_object->first_name,
								$user_object->last_name
							);
						} elseif ( $user_object->first_name ) {
							$row .= $user_object->first_name;
						} elseif ( $user_object->last_name ) {
							$row .= $user_object->last_name;
						} else {
							$row .= sprintf(
								'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
								/* translators: Hidden accessibility text. */
								_x( 'Unknown', 'name' )
							);
						}
						break;
					case 'email':
						$row .= "<a href='" . esc_url( "mailto:$email" ) . "'>$email</a>";
						break;
					case 'role':
						$row .= esc_html( $roles_list );
						break;
					case 'in_docspace':
						if ( $user_docspace_status == 0 ) {
							$row .= "<img src='" . esc_url( plugins_url( '../../public/images/done.svg', __FILE__ ) ) . "'/>";
						} else if ( $user_docspace_status == 2 ) {
							$row .= "Invited";
						} else {
							$row .= "<img src='" . esc_url( plugins_url( '../../public/images/delete.svg', __FILE__ ) ) . "'/>";
						}
						break;
					case 'type_user_in_docspace':
						$row .= esc_html( 'User' );
						break;
					default:
						/**
						 * Filters the display output of custom columns in the Users list table.
						 *
						 * @since 2.8.0
						 *
						 * @param string $output      Custom column output. Default empty.
						 * @param string $column_name Column name.
						 * @param int    $user_id     ID of the currently-listed user.
						 */
						$row .= apply_filters( 'manage_users_custom_column', '', $column_name, $user_object->ID );
				}
				$row .= '</td>';
			}
		}
		$row .= '</tr>';

		return $row;
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @return string Name of the default primary column, in this case, 'username'.
	 */
	protected function get_default_primary_column_name() {
		return 'username';
	}

	/**
	 * Returns an array of translated user role names for a given user object.
	 *
	 * @param WP_User $user_object The WP_User object.
	 * @return string[] An array of user role names keyed by role.
	 */
	protected function get_role_list( $user_object ) {
		$wp_roles = wp_roles();

		$role_list = array();

		foreach ( $user_object->roles as $role ) {
			if ( isset( $wp_roles->role_names[ $role ] ) ) {
				$role_list[ $role ] = translate_user_role( $wp_roles->role_names[ $role ] );
			}
		}

		if ( empty( $role_list ) ) {
			$role_list['none'] = _x( 'None', 'no user roles' );
		}

		/**
		 * Filters the returned array of translated role names for a user.
		 *
		 * @param string[] $role_list   An array of translated user role names keyed by role.
		 * @param WP_User  $user_object A WP_User object.
		 */
		return apply_filters( 'get_role_list', $role_list, $user_object );
	}


	/**
	 * Displays the search box.
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 * @global string $s
	 * @global string $page
	 */
	public function search_box( $text, $input_id ) {
		global $s, $page;
		wp_reset_vars( array( 's', 'page' ) );

		if ( empty( $s ) && ! $this->has_items() ) {
			return;
		}

		if ( ! empty( $page ) ) {
			echo '<input type="hidden" name="page" value="' . esc_attr( $page ) . '" />';
			echo '<input type="hidden" name="users" value="true" />';
		}

		parent::search_box( $text, $input_id );

	}

}
