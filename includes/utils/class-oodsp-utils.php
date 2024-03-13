<?php
/**
 * OODSP utils
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/utils
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
 * OODPS Utils.
 *
 * @package    Onlyoffice_Docspace_Wordpress
 * @subpackage Onlyoffice_Docspace_Wordpress/includes/utils
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class OODSP_Utils {
	/**
	 * LOCALES for DocSpace
	 */
	const LOCALES = array(
		'az',
		'bg',
		'cs',
		'de',
		'el-GR',
		'en-GB',
		'en-US',
		'es',
		'fi',
		'fr',
		'hy-AM',
		'it',
		'ja-JP',
		'ko-KR',
		'lo-LA',
		'lv',
		'nl',
		'pl',
		'pt',
		'pt-BR',
		'ro',
		'ru',
		'sk',
		'sl',
		'tr',
		'uk-UA',
		'vi',
		'zh-CN',
	);

	/**
	 * OODSP_Settings
	 *
	 * @access   private
	 * @var      OODSP_Settings    $plugin_settings
	 */
	private $plugin_settings;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->plugin_settings = new OODSP_Settings();
	}

	/**
	 * Register the JavaScript for the OODSP Utils.
	 */
	public function enqueue_scripts() {
		$current_user = wp_get_current_user()->user_email;

		$message_docspace_unavailable = __( 'Portal unavailable! Please contact the administrator!', 'onlyoffice-docspace-plugin' );

		if ( is_user_logged_in() ) {
			if ( current_user_can( 'manage_options' ) ) {
				$message_docspace_unavailable = __( 'Go to the settings to configure ONLYOFFICE DocSpace connector.', 'onlyoffice-docspace-plugin' );
			}

			$message_unauthorized_header  = __( 'Authorization unsuccessful!', 'onlyoffice-docspace-plugin' );
			$message_unauthorized_message = __( 'Please contact the administrator.', 'onlyoffice-docspace-plugin' );

			if ( current_user_can( 'upload_files' ) ) {
				$message_unauthorized_message = __( 'Please proceed to the DocSpace plugin via the left side menu and enter your password to restore access.', 'onlyoffice-docspace-plugin' );
			}
		} else {
			$message_unauthorized_header  = __( 'Access denied!', 'onlyoffice-docspace-plugin' );
			$message_unauthorized_message = __( 'Please log in to the site!', 'onlyoffice-docspace-plugin' );
		}

		wp_enqueue_script(
			'oodsp-utils',
			OODSP_PLUGIN_URL . 'includes/js/oodsp-utils.js',
			array( 'wp-util' ),
			OODSP_VERSION,
			true
		);

		wp_localize_script(
			'oodsp-utils',
			'_oodsp',
			array(
				'docspaceUrl' => $this->plugin_settings->get_onlyoffice_docspace_setting( OODSP_Settings::DOCSPACE_URL ),
				'currentUser' => $current_user,
				'locale'      => $this->get_locale_for_docspace(),
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'isAnonymous' => ! is_user_logged_in(),
				'messages'    => array(
					'docspaceUnavailable' => $message_docspace_unavailable,
					'unauthorizedHeader'  => $message_unauthorized_header,
					'unauthorizedMessage' => $message_unauthorized_message,
				),
			)
		);

		add_action( 'wp_footer', array( $this, 'oodsp_error_template' ), 30 );
		add_action( 'admin_footer', array( $this, 'oodsp_error_template' ), 30 );
	}

	/**
	 * Register the stylesheets for the OODSP Utils.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'oodsp-utils',
			OODSP_PLUGIN_URL . 'includes/css/oodsp-utils.css',
			array(),
			OODSP_VERSION
		);
	}

	/**
	 *  DocSpace login template.
	 */
	public function get_locale_for_docspace() {
		$locale = str_replace( '_', '-', get_user_locale() );

		if ( in_array( $locale, self::LOCALES, true ) ) {
			return $locale;
		} else {
			$locale = explode( '-', $locale )[0];
			foreach ( self::LOCALES as $value ) {
				if ( str_starts_with( $value, $locale ) ) {
					return $value;
				}
			}
		}

		return 'en-US';
	}

	/**
	 *  OODSP error template.
	 *
	 * @return void
	 */
	public function oodsp_error_template() {
		?>
		<script type="text/html" id="tmpl-oodsp-error">
			<div class="onlyoffice-error" >
				<div class="onlyoffice-error-body">
					<div class="onlyoffice-error-table js">
						<div>
							<img src="<?php echo esc_url( OODSP_PLUGIN_URL . 'includes/images/onlyoffice.svg' ); ?>" style="width: 100%; padding: 0 10px;" />
						</div>
						<div style="padding: 16px;">
							<img src="<?php echo esc_url( OODSP_PLUGIN_URL . 'includes/images/unavailable.svg' ); ?>" style="width: 100%"/>
						</div>
						<div class="header-message">{{{data.header || ""}}}</div>
						<div class="message">{{{data.message}}}</div>
					</div>
				</div>
			</div>
		</script>
		<?php
	}
}
