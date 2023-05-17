<?php
/**
 * Controller init ONLYOFFICE Docspace.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/controllers
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
 * Controller init ONLYOFFICE Docspace.
 *
 * @package    Onlyoffice_Docspace_Plugin
 * @subpackage Onlyoffice_Docspace_Plugin/controllers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Frontend_Controller {
	const OODSP_PUBLIC_USER_LOGIN = 'wp_public_user@wp_public_user.com';
	const OODSP_PUBLIC_USER_PASS = '8c6b8b3e59010d7c925a47039f749d86fbdc9b37257cd262f2dae7c84a106505';
	const OODSP_PUBLIC_USER_FIRSTNAME = 'wp_public_user';
	const OODSP_PUBLIC_USER_LASTNAME = 'wp_public_user';
	
	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $plugin_settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->plugin_settings = new OODSP_Settings();
	}

	/**
	 * Register ONLYOFFICE Docspace Shortcodes.
	 */
	public function init_shortcodes() {
		add_shortcode( 'onlyoffice-docspace', array( $this, 'wp_onlyoffice_docspace_shortcode' ) );
	}

	/**
	 * Register the onlyoffice-wordpress-block and its dependencies.
	 */
	public function onlyoffice_custom_block() {
		register_block_type(
			__DIR__ . '/../onlyoffice-docspace-wordpress-block',
			array(
				'description' => __( 'Add ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ),
				'render_callback' => array( $this, 'docspace_block_render_callback' ),
			),
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 
				'onlyoffice-docspace-onlyoffice-docspace-editor-script', 
				'onlyoffice-docspace-plugin',
				plugin_dir_path( dirname( __FILE__ ) ) . 'languages/' 
			);
		}
	}

	/**
	 * Callback function for rendering the onlyoffice-wordpress-block.
	 *
	 * @param array $block_attributes List of attributes that where included in the block settings.
	 * @return string Resulting HTML code for the table.
	 */
	public function docspace_block_render_callback ( array $block_attributes ) {
		if ( !$block_attributes ) {
			return;
		}

		return $this->wp_onlyoffice_docspace_shortcode( $block_attributes );
	}

	/**
	 * Handle Shortcode [onlyoffice-docspace /].
	 *
	 * @param array $attr List of attributes that where included in the Shortcode.
	 * @return string Resulting HTML code.
	 */
	public function wp_onlyoffice_docspace_shortcode( $attr ) {
		static $instance = 0;
		$instance++;

		$defaults_atts = array(
			'frameId' => 'onlyoffice-docpace-block-' . $instance,
			'width'   => "100%",
			'height'   => "100%",
			'mode'   => "manager",
			'itemId'   => null,
		);

		$atts = shortcode_atts( $defaults_atts, $attr, 'onlyoffice-docspace' );

		$atts['id'] = $attr['fileId'];

		wp_enqueue_script(
			$this->plugin_name . '-ds-component-script',
			plugin_dir_url( __FILE__ ) . '../public/js/docspace-components-api.js',
			array( 'jquery' ),
			ONLYOFFICE_DOCSPACE_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			$this->plugin_name . '-ds-component-script',
			'DocSpaceComponent',
			array( 
				'docSpaceUrl'   => $this->plugin_settings->get_onlyoffice_docspace_setting(OODSP_Settings::DOCSPACE_URL),
				'user'          => wp_get_current_user()->user_email,
				'publicUser'    => array( 
					'email'    => OODSP_Frontend_Controller::OODSP_PUBLIC_USER_LOGIN,
					'password' => OODSP_Frontend_Controller::OODSP_PUBLIC_USER_PASS,
				),
				'wp_plugin_url' => WP_PLUGIN_URL
			)
		);

		wp_enqueue_script(
			'oodsp-frontend-controller',
			plugin_dir_url( __FILE__ ) . 'js/oodsp-frontend-controller.js',
			array( ),
			ONLYOFFICE_DOCSPACE_PLUGIN_VERSION,
			true
		);

		wp_enqueue_style(
			$this->plugin_name . '-ds-component-style',
			plugin_dir_url( __FILE__ ) . '../public/css/docspace-components-api.css'
		);

		$output  = '<div>';
		$output .= "<div class='onlyoffice-docpace-block' data-config='" . wp_json_encode( $atts ) . "' id='onlyoffice-docpace-block-". $instance . "' style='overflow: overlay; width:". $atts['width'] ."; height:". $atts['height'] ."'></div>";
		$output .= '</div>';

		return apply_filters( 'wp_onlyoffice_docspace_shortcode', $output, $atts );
	}

}