<?php
/**
 * ONLYOFFICE DocSpace Plugin Public DocSpace Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/public-docspace
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

if ( ! function_exists( 'WP_Filesystem' ) ) {
	include ABSPATH . '/wp-admin/includes/file.php';
	WP_Filesystem();
}

/**
 * ONLYOFFICE DocSpace Plugin Public DocSpace Page.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/pages/public-docspace
 */
class OODSP_Public_DocSpace_Page {
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
	 * OODSP_User_Service
	 *
	 * @var OODSP_User_Service $oodsp_user_service
	 */
	private OODSP_User_Service $oodsp_user_service;

	/**
	 * OODSP_Settings_Manager
	 *
	 * @var OODSP_Settings_Manager $oodsp_settings_manager
	 */
	private OODSP_Settings_Manager $oodsp_settings_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param OODSP_Settings_Manager $oodsp_settings_manager Instance of OODSP_Settings_Manager.
	 * @param OODSP_User_Service     $oodsp_user_service Instance of OODSP_User_Service.
	 */
	public function __construct(
		OODSP_Settings_Manager $oodsp_settings_manager,
		OODSP_User_Service $oodsp_user_service
	) {
		$this->class_path = plugin_dir_path( __FILE__ );
		$this->class_url  = plugin_dir_url( __FILE__ );

		$this->oodsp_user_service     = $oodsp_user_service;
		$this->oodsp_settings_manager = $oodsp_settings_manager;
	}

	/**
	 * Register ONLYOFFICE Docspace Shortcode.
	 */
	public function init_shortcode() {
		add_shortcode(
			'onlyoffice-docspace',
			array( $this, 'docspace_shortcode_render_callback' )
		);
	}

	/**
	 * Register the onlyoffice-wordpress-block and its dependencies.
	 */
	public function init_block() {
		$this->register_block_resources();

		register_block_type(
			plugin_dir_path( OODSP_PLUGIN_FILE ) . 'onlyoffice-docspace-wordpress-block',
			array(
				'description'     => __( 'Add ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ),
				'render_callback' => array( $this, 'docspace_block_render_callback' ),
				'editorScript'    => OODSP_PLUGIN_NAME . '-block-editor',
				'editorStyle'     => OODSP_PLUGIN_NAME . '-block-editor',
			),
		);
	}

	/**
	 * Register block resources for the ONLYOFFICE DocSpace block.
	 *
	 * This method registers the JavaScript and CSS files required for the block editor.
	 */
	protected function register_block_resources() {
		wp_register_script(
			OODSP_PLUGIN_NAME . '-block-editor',
			OODSP_PLUGIN_URL . 'onlyoffice-docspace-wordpress-block/build/index.js',
			array( 'oodsp-main' ),
			OODSP_VERSION,
			true
		);

		wp_register_style(
			OODSP_PLUGIN_NAME . '-block-editor',
			OODSP_PLUGIN_URL . 'onlyoffice-docspace-wordpress-block/build/index.css',
			array( 'oodsp-main' ),
			OODSP_VERSION,
		);
	}

	/**
	 * Load resources required for DocSpace functionality.
	 *
	 * This method enqueues the necessary JavaScript files and localizes
	 * script data for the DocSpace public interface. It includes the main
	 * DocSpace script, sets up user data, and provides essential configuration
	 * information for the client-side functionality.
	 */
	private function load_resources() {
		if ( file_exists( $this->class_path . '/js/index.js' ) ) {
			wp_enqueue_script(
				OODSP_PLUGIN_NAME . '-docspace-public',
				$this->class_url . '/js/index.js',
				array( 'jquery', 'docspace-integration-sdk', 'oodsp-error-page-template' ),
				OODSP_VERSION,
				true
			);

			$user             = wp_get_current_user();
			$docspace_account = $this->oodsp_user_service->get_docspace_account( $user->ID );

			wp_localize_script(
				OODSP_PLUGIN_NAME . '-docspace-public',
				'_oodspDocspacePublic',
				array(
					'docspaceUrl'  => $this->oodsp_settings_manager->get_docspace_url(),
					'isAnonymous'  => ! is_user_logged_in(),
					'locale'       => OODSP_Utils::get_locale_for_docspace(),
					'docspaceUser' => ! empty( $docspace_account )
						? $docspace_account->to_array()
						: null,
				)
			);
		}

		if ( file_exists( $this->class_path . '/css/index.css' ) ) {
			wp_enqueue_style(
				OODSP_PLUGIN_NAME . '-docspace-public',
				$this->class_url . '/css/index.css',
				array( 'oodsp-error-page-template' ),
				OODSP_VERSION
			);
		}

		add_action( 'wp_footer', array( 'OODSP_Templates', 'oodsp_error_page_template' ), 30 );
	}

	/**
	 * Render callback for the DocSpace block.
	 *
	 * This function is responsible for rendering the DocSpace block on the frontend.
	 * It checks for the presence of required attributes (roomId or fileId) and
	 * delegates the rendering to the shortcode callback if the attributes are valid.
	 *
	 * @param array $block_attributes The attributes of the block.
	 * @return string|void The rendered HTML for the block, or void if attributes are invalid.
	 */
	public function docspace_block_render_callback( array $block_attributes ) {
		if ( ! $block_attributes ||
			( ! array_key_exists( 'roomId', $block_attributes ) &&
				! array_key_exists( 'fileId', $block_attributes ) )
		) {
			return;
		}

		return $this->docspace_shortcode_render_callback( $block_attributes );
	}

	/**
	 * Renders the DocSpace shortcode.
	 *
	 * This callback function processes the shortcode attributes and generates
	 * the appropriate HTML output for displaying DocSpace content.
	 *
	 * @param array $attr An array of attributes passed to the shortcode.
	 * @return string The HTML output for the DocSpace content.
	 */
	public function docspace_shortcode_render_callback( $attr ) {
		static $instance = 0;
		++$instance;

		$width  = '100%';
		$height = '500px';
		$align  = '';

		$default_config = array(
			'frameId'      => 'onlyoffice-docspace-block-' . $instance,
			'width'        => '100%',
			'height'       => '100%',
			'mode'         => 'manager',
			'editorGoBack' => false,
			'theme'        => 'Base',
			'editorType'   => 'embedded',
		);

		$attr_lower_case = array_change_key_case( $attr, CASE_LOWER );
		$config          = $this->map_attributes( $default_config, $attr_lower_case );

		if ( array_key_exists( 'roomid', $attr_lower_case ) ) {
			$config['id']               = $attr_lower_case['roomid'];
			$config['mode']             = 'manager';
			$config['viewTableColumns'] = 'Name,Size,Type';
		} elseif ( array_key_exists( 'fileid', $attr_lower_case ) ) {
			$config['id']                  = $attr_lower_case['fileid'];
			$config['mode']                = 'editor';
			$config['editorCustomization'] = array(
				'anonymous'       => array(
					'request' => false,
				),
				'integrationMode' => 'embed',
			);
		}

		if ( array_key_exists( 'requesttoken', $attr_lower_case ) ) {
			$config['requestToken'] = $attr_lower_case['requesttoken'];
			$config['rootPath']     = '/rooms/share';
		}

		if ( ! empty( $attr_lower_case['width'] ) ) {
			$width = sanitize_text_field( $attr_lower_case['width'] );
		}

		if ( ! empty( $attr_lower_case['height'] ) ) {
			$height = sanitize_text_field( $attr_lower_case['height'] );
		}

		if ( ! empty( $attr_lower_case['align'] ) ) {
			$align = sanitize_text_field( $attr_lower_case['align'] );
		}

		$size  = ! ( 'full' === $align ) ? 'width: ' . $width . ';' : '';
		$size .= 'height: ' . $height . ';';
		$align = ! empty( $align ) ? 'align' . $align : '';

		return $this->view(
			$attr,
			$instance,
			$config,
			$align,
			$size
		);
	}

	/**
	 * Renders the DocSpace view.
	 *
	 * This method generates the HTML output for displaying the DocSpace interface.
	 * It loads necessary resources, creates the container div with appropriate
	 * attributes and styles, and applies any custom filters to the output.
	 *
	 * @param array  $attr     Shortcode attributes.
	 * @param int    $instance Unique instance identifier.
	 * @param array  $config   Configuration options for DocSpace.
	 * @param string $align    Alignment class for the container.
	 * @param string $size     CSS size properties for the container.
	 *
	 * @return string HTML output for the DocSpace view.
	 */
	private function view( $attr, $instance, $config, $align, $size ) {
		$this->load_resources();

		$output  = '<div class="wp-block-onlyoffice-docspace-wordpress-onlyoffice-docspace '
			. esc_attr( $align ) . ' size-full" style="' . esc_attr( $size ) . '">';
		$output .= "<div class='onlyoffice-docspace-block' data-config='"
			. wp_json_encode( $config ) . "' id='onlyoffice-docspace-block-" . $instance . "'></div>";
		$output .= '</div>';

		return apply_filters( 'wp_onlyoffice_docspace_shortcode', $output, $attr );
	}

	/**
	 * Maps input attributes to default attributes.
	 *
	 * This function takes a set of default attributes and user-provided attributes,
	 * and returns a merged array where user-provided values override defaults.
	 * It handles case-insensitive attribute keys.
	 *
	 * @param array $default_attributes The default set of attributes.
	 * @param array $attributes The user-provided attributes.
	 * @return array The merged and mapped attributes.
	 */
	private function map_attributes( $default_attributes, $attributes ) {
		$attributes = (array) $attributes;
		$out        = array();

		foreach ( $default_attributes as $key => $value ) {
			if ( array_key_exists( $key, $attributes ) && ! empty( $attributes[ $key ] ) ) {
				$out[ $key ] = $attributes[ $key ];
			} elseif ( array_key_exists( strtolower( $key ), $attributes ) && ! empty( $attributes[ strtolower( $key ) ] ) ) {
				$out[ $key ] = $attributes[ strtolower( $key ) ];
			} else {
				$out[ $key ] = $value;
			}
		}

		return $out;
	}
}
