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

		$output  = '<div class="wp-block-onlyoffice-docspace-wordpress-onlyoffice-docspace ' . esc_attr( $align ) . ' size-full" style="' . esc_attr( $size ) . '">';
		$output .= "<div class='onlyoffice-docspace-block' data-config='" . wp_json_encode( $config ) . "' id='onlyoffice-docspace-block-" . $instance . "'></div>";
		$output .= '</div>';

		return apply_filters( 'wp_onlyoffice_docspace_shortcode', $output, $attr );
	}

	/**
	 * Map attributes.
	 *
	 * @param array $default_attributes List of default attributes.
	 * @param array $attributes List of source attributes.
	 * @return array Resulting HTML code.
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
