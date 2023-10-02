/**
 * JS for login in DocSpace.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

window.wp = window.wp || {};

( function ( $, wp ) {
	wp.oodsp = wp.oodsp || {};

	var __ = wp.i18n.__;

	wp.oodsp.login = function (frameId, url, email, error, callback) {
		const loginTemplate = wp.template( 'oodsp-login' );

		$( '#' + frameId ).html(
			loginTemplate(
				{
					email: email,
					domain: new URL( url ).host,
					error: error ? __( '<strong>Error:</strong> User authentication failed.', 'onlyoffice-docspace-plugin' ) : null
				}
			)
		);

		$( '#oodsp-login-form' ).submit(
			function ( event ) {
				event.preventDefault();
				$( '#login_error' ).html();

				var password = $( '#oodsp-password' ).val();

				if ( '' == password.trim() ) {
					$( '#login_error' ).html( __( '<strong>Error:</strong> The password field is empty.', 'onlyoffice-docspace-plugin' ) );
					$( '#login_error' ).show();
				} else {
					callback( password );
				}
			}
		);

		$( '.wp-hide-pw' ).on(
			'click',
			function () {
				if ( 'password' === $( '#oodsp-password' ).attr( 'type' ) ) {
					$( '#oodsp-password' ).attr( 'type', 'text' );
					resetToggle( false );
				} else {
					$( '#oodsp-password' ).attr( 'type', 'password' );
					resetToggle( true );
				}
			}
		);
	};

	function resetToggle( show ) {
		$( '.wp-hide-pw' )
			.attr(
				{
					'aria-label': show ? __( 'Show password' ) : __( 'Hide password' )
				}
			)
			.find( '.text' )
				.text( show ? __( 'Show' ) : __( 'Hide' ) )
			.end()
			.find( '.dashicons' )
				.removeClass( show ? 'dashicons-hidden' : 'dashicons-visibility' )
				.addClass( show ? 'dashicons-visibility' : 'dashicons-hidden' );
	}
}( window.jQuery, window.wp ));
