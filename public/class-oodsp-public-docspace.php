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
 * Public ONLYOFFICE Docspace.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/public
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Public_DocSpace {

	/**
	 * OODSP_Utils
	 *
	 * @access   private
	 * @var      OODSP_Utils    $oodsp_utils
	 */
	private $oodsp_utils;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->oodsp_utils = new OODSP_Utils();
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
			plugin_dir_path( OODSP_PLUGIN_FILE ) . 'onlyoffice-docspace-wordpress-block',
			array(
				'description'     => __( 'Add ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' ),
				'render_callback' => array( $this, 'docspace_block_render_callback' ),
			),
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				'onlyoffice-docspace-wordpress-onlyoffice-docspace-editor-script',
				'onlyoffice-docspace-plugin',
				plugin_dir_path( OODSP_PLUGIN_FILE ) . 'languages/'
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
		++$instance;

		$defaults_atts = array(
			'frameId'      => 'onlyoffice-docspace-block-' . $instance,
			'width'        => '100%',
			'height'       => '500px',
			'align'        => '',
			'mode'         => 'manager',
			'editorGoBack' => false,
			'theme'        => 'Base',
			'editorType'   => 'embedded',
		);

		$atts = shortcode_atts( $defaults_atts, $attr, 'onlyoffice-docspace' );

		if ( array_key_exists( 'roomId', $attr ) ) {
			$atts['id']               = $attr['roomId'];
			$atts['mode']             = 'manager';
			$atts['viewTableColumns'] = 'Name,Size,Type';
		} elseif ( array_key_exists( 'fileId', $attr ) ) {
			$atts['id']                  = $attr['fileId'];
			$atts['mode']                = 'editor';
			$atts['editorCustomization'] = array(
				'anonymous'       => array(
					'request' => false,
				),
				'integrationMode' => 'embed',
			);
		}

		if ( empty( $atts['width'] ) ) {
			$atts['width'] = $defaults_atts['width'];
		}

		if ( empty( $atts['height'] ) ) {
			$atts['height'] = $defaults_atts['height'];
		}

		if ( empty( $atts['theme'] ) ) {
			$atts['theme'] = $defaults_atts['theme'];
		}

		if ( empty( $atts['editorType'] ) ) {
			$atts['editorType'] = $defaults_atts['editorType'];
		}

		if ( array_key_exists( 'requestToken', $attr ) ) {
			$atts['requestToken'] = $attr['requestToken'];
			$atts['rootPath']     = '/rooms/share';
		}

		$this->oodsp_utils->enqueue_scripts();
		$this->oodsp_utils->enqueue_styles();

		wp_enqueue_script(
			'docspace-integration-sdk',
			OODSP_PLUGIN_URL . 'assets-onlyoffice-docspace/js/docspace-integration-sdk.js',
			array(),
			OODSP_VERSION,
			true
		);

		wp_enqueue_script(
			OODSP_PLUGIN_NAME . '-public-docspace',
			OODSP_PLUGIN_URL . 'public/js/public-docspace.js',
			array( 'jquery' ),
			OODSP_VERSION,
			true
		);

		wp_enqueue_style(
			OODSP_PLUGIN_NAME . '-public-docspace',
			OODSP_PLUGIN_URL . 'public/css/public-docspace.css',
			array(),
			OODSP_VERSION
		);

		$align = ! empty( $atts['align'] ) ? 'align' . $atts['align'] : '';
		$size  = ! empty( $atts['width'] ) && ! ( 'full' === $atts['align'] ) ? 'width: ' . $atts['width'] . ';' : '';
		$size .= ! empty( $atts['height'] ) ? 'height: ' . $atts['height'] . ';' : '';

		$output  = '<div class="wp-block-onlyoffice-docspace-wordpress-onlyoffice-docspace ' . $align . ' size-full" style="' . $size . '">';
		$output .= "<div class='onlyoffice-docspace-block' data-config='" . wp_json_encode( $atts ) . "' id='onlyoffice-docspace-block-" . $instance . "'></div>";
		$output .= '</div>';

		return apply_filters( 'wp_onlyoffice_docspace_shortcode', $output, $atts );
	}
}
