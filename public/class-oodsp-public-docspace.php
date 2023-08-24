<?php
/**
 * Public ONLYOFFICE Docspace.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/public
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
 * Public ONLYOFFICE Docspace.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/public
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Public_DocSpace {
	const OODSP_PUBLIC_USER_LOGIN     = 'wpviewer@onlyoffice.com';
	const OODSP_PUBLIC_USER_PASS      = '8c6b8b3e59010d7c925a47039f749d86fbdc9b37257cd262f2dae7c84a106505';
	const OODSP_PUBLIC_USER_FIRSTNAME = 'WordPress';
	const OODSP_PUBLIC_USER_LASTNAME  = 'Viewer';

	/**
	 * OODSP_Settings
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $plugin_settings;

	/**
	 * OODSP_DocSpace
	 *
	 * @access   private
	 * @var      OODSP_DocSpace    $plugin_docspace
	 */
	private $plugin_docspace;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->plugin_settings = new OODSP_Settings();
		$this->plugin_docspace = new OODSP_DocSpace();
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
			plugin_dir_path( ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_FILE ) . 'onlyoffice-docspace-wordpress-block',
			array(
				'description'     => __( 'Add ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ),
				'render_callback' => array( $this, 'docspace_block_render_callback' ),
			),
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				'onlyoffice-docspace-wordpress-onlyoffice-docspace-editor-script',
				'onlyoffice-docspace-plugin',
				plugin_dir_path( ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_FILE ) . 'languages/'
			);
		}
	}

	/**
	 * Callback function for rendering the onlyoffice-wordpress-block.
	 *
	 * @param array $block_attributes List of attributes that where included in the block settings.
	 * @return string Resulting HTML code for the table.
	 */
	public function docspace_block_render_callback( array $block_attributes ) {
		if ( ! $block_attributes || ( ! array_key_exists( 'roomId', $block_attributes ) && ! array_key_exists( 'fileId', $block_attributes ) ) ) {
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
			'frameId' => 'onlyoffice-docspace-block-' . $instance,
			'width'   => '100%',
			'height'  => '500px',
			'mode'    => 'manager',
			'editorGoBack' => false
		);

		$atts = shortcode_atts( $defaults_atts, $attr, 'onlyoffice-docspace' );

		if ( array_key_exists( 'roomId', $attr ) ) {
			$atts['id']   = $attr['roomId'];
			$atts['mode'] = 'manager';
		} elseif ( array_key_exists( 'fileId', $attr ) ) {
			$atts['id']   = $attr['fileId'];
			$atts['mode'] = 'editor';
		}

		if ( empty( $atts['width'] ) ) {
			$atts['width'] = $defaults_atts['width'];
		}

		if ( empty( $atts['height'] ) ) {
			$atts['height'] = $defaults_atts['height'];
		}

		$post = get_post();

		if ( 'private' === $post->post_status ) {
			$is_public    = false;
			$current_user = wp_get_current_user()->user_email;
		} else {
			$is_public    = true;
			$current_user = self::OODSP_PUBLIC_USER_LOGIN;
		}

		wp_enqueue_script(
			'docspace-component-api',
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL . 'assets/js/docspace-component-api.js',
			array(),
			ONLYOFFICE_DOCSPACE_WORDPRESS_VERSION,
			true
		);

		$error_message = __( 'Portal unavailable! Please contact the administrator!', 'onlyoffice-docspace-plugin' );

		if ( current_user_can( 'manage_options' ) && ! $is_public ) {
			$error_message = __( 'Go to the settings to configure ONLYOFFICE DocSpace connector.', 'onlyoffice-docspace-plugin' );
		}

		$unauthorized_header = __( 'Authorization unsuccessful!', 'onlyoffice-docspace-plugin' );
		$unauthorized_message = __( 'Please contact the administrator.', 'onlyoffice-docspace-plugin' );

		if ( current_user_can( 'manage_options' ) && $is_public ) {
			$unauthorized_header = __( 'Reset WordPress Viewer to continue', 'onlyoffice-docspace-plugin' );
			$unauthorized_message = __( 'You may experience issues with access to your content because WordPress Viewer data has been lost. Please proceed to the DocSpace plugin settings and click the Save button. WordPress Viewer will be added again to DocSpace.', 'onlyoffice-docspace-plugin' );
		} elseif ( ! $is_public ) {
			$unauthorized_message = __( 'Please proceed to the DocSpace plugin via the left side menu and enter your password to restore access.', 'onlyoffice-docspace-plugin' );
		}

		wp_localize_script(
			'docspace-component-api',
			'DocSpaceComponent',
			array(
				'url'         => $this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_URL ),
				'currentUser' => $current_user,
				'isPublic'    => $is_public,
				'locale'      => $this->plugin_docspace->get_locale_for_docspace(),
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'images'      => array(
					'onlyoffice'  => plugins_url( 'public/images/onlyoffice.svg', ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_FILE ),
					'unavailable' => plugins_url( 'public/images/unavailable.svg', ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_FILE ),
				),
				'messages'	  => array(
					'error'                => $error_message,
					'unauthorized_header'  => $unauthorized_header,
					'unauthorized_message' => $unauthorized_message,
				),
			)
		);

		wp_enqueue_style(
			'docspace-components-api',
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL . 'assets/css/docspace-component-api.css',
			array(),
			ONLYOFFICE_DOCSPACE_WORDPRESS_VERSION
		);

		wp_enqueue_script(
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_NAME . '-public-docspace',
			ONLYOFFICE_DOCSPACE_WORDPRESS_PLUGIN_URL . 'public/js/public-docspace.js',
			array( 'jquery' ),
			ONLYOFFICE_DOCSPACE_WORDPRESS_VERSION,
			true
		);

		$output  = '<div>';
		$output .= "<div class='onlyoffice-docspace-block' data-config='" . wp_json_encode( $atts ) . "' id='onlyoffice-docspace-block-" . $instance . "' style='overflow: overlay; width:" . $atts['width'] . '; height:' . $atts['height'] . "'></div>";
		$output .= '</div>';

		return apply_filters( 'wp_onlyoffice_docspace_shortcode', $output, $atts );
	}

}
