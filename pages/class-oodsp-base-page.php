<?php
/**
 * ONLYOFFICE DocSpace Plugin Base Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ONLYOFFICE DocSpace Plugin Base Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages
 */
abstract class OODSP_Base_Page {
	/**
	 * The slug name for the parent menu.
	 *
	 * @var string
	 */
	protected $parent_slug;

	/**
	 * The text to be displayed in the title tags of the page when the menu is selected.
	 *
	 * @var string
	 */
	protected $page_title;

	/**
	 * The text to be used for the menu.
	 *
	 * @var string
	 */
	protected $menu_title;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string
	 */
	protected $capability;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @var string
	 */
	protected $menu_slug;

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
	 * The default JavaScript dependencies.
	 *
	 * @var array
	 */
	protected $def_js_deps;

	/**
	 * The default CSS dependencies.
	 *
	 * @var array
	 */
	protected $def_css_deps;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $parent_slug  The slug name for the parent menu.
	 * @param string $page_title   The text to be displayed in the title tags.
	 * @param string $menu_title   The text to be used for the menu.
	 * @param string $capability   The capability required for this menu.
	 * @param string $menu_slug    The slug name to refer to this menu.
	 * @param string $class_path   The path to the class file.
	 * @param string $class_url    The URL to the class file.
	 * @param array  $def_js_deps  Optional. The default JavaScript dependencies. Default empty array.
	 * @param array  $def_css_deps Optional. The default CSS dependencies. Default empty array.
	 */
	public function __construct(
		$parent_slug,
		$page_title,
		$menu_title,
		$capability,
		$menu_slug,
		$class_path,
		$class_url,
		$def_js_deps = array(),
		$def_css_deps = array()
	) {
		$this->parent_slug  = $parent_slug;
		$this->page_title   = $page_title;
		$this->menu_title   = $menu_title;
		$this->capability   = $capability;
		$this->menu_slug    = $menu_slug;
		$this->class_path   = untrailingslashit( $class_path );
		$this->class_url    = untrailingslashit( $class_url );
		$this->def_js_deps  = $def_js_deps;
		$this->def_css_deps = $def_css_deps;
	}

	/**
	 * Initialize the menu for the plugin.
	 *
	 * This method sets up the menu items for the OnlyOffice DocSpace WordPress plugin.
	 *
	 * @return void
	 */
	public function init_menu() {
		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		$hook = add_submenu_page(
			$this->parent_slug,
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			array( $this, 'view' )
		);

		add_action( "load-$hook", array( $this, 'callback' ) );
	}

	/**
	 * Load necessary resources for the page.
	 *
	 * This method is responsible for loading all the required resources
	 * such as scripts, styles, and other dependencies needed for the page.
	 *
	 * @return void
	 */
	protected function load_resources() {
		if ( file_exists( $this->class_path . '/js/index.js' ) ) {
			wp_enqueue_script(
				OODSP_PLUGIN_NAME . '_' . $this->menu_slug,
				$this->class_url . '/js/index.js',
				$this->def_js_deps,
				OODSP_VERSION,
				true
			);

			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations(
					OODSP_PLUGIN_NAME . '_' . $this->menu_slug,
					'onlyoffice-docspace-plugin',
					plugin_dir_path( OODSP_PLUGIN_FILE ) . 'languages/'
				);
			}
		}

		if ( file_exists( $this->class_path . '/css/index.css' ) ) {
			wp_enqueue_style(
				OODSP_PLUGIN_NAME . '_' . $this->menu_slug,
				$this->class_url . '/css/index.css',
				$this->def_css_deps,
				OODSP_VERSION
			);
		}
	}

	/**
	 * Renders the view for the current page.
	 *
	 * This method should be overridden by subclasses to provide the specific
	 * implementation for rendering the view.
	 *
	 * @return void
	 */
	public function view() {
		$this->load_resources();

		include $this->class_path . '/views/index.php';
	}

	/**
	 * Callback function for handling specific actions or events.
	 *
	 * This function is intended to be overridden by subclasses to provide
	 * specific functionality when the callback is triggered.
	 *
	 * @return void
	 */
	public function callback() {
	}

	/**
	 * Get current action from request.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string|false The current action or false if no action is set.
	 */
	protected function current_action() {
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
