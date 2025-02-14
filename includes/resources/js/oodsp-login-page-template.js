/* global oodsp */

window.wp = window.wp || {};

( function ( $, wp ) {
	window.oodsp = window.oodsp || {};
	window.oodsp.templates = window.oodsp.templates || {};

	const __ = wp.i18n.__;

	oodsp.templates.loginPage = function (
		targetId,
		url,
		userName,
		password,
		error,
		callback
	) {
		const loginTemplate = wp.template( 'oodsp-login' );

		$( '#' + targetId ).html(
			loginTemplate( {
				userName,
				password,
				domain: new URL( url ).host,
				error: error
					? '<strong>' +
					  __( 'Error', 'onlyoffice-docspace-plugin' ) +
					  ': </strong>' +
					  __(
							'User authentication failed',
							'onlyoffice-docspace-plugin'
					  )
					: null,
			} )
		);

		$( '#oodsp-login-form' ).submit( function ( event ) {
			event.preventDefault();
			$( '#login_error' ).html();

			const userNameValue = $( '#oodsp-username' ).val();
			const passwordValue = $( '#oodsp-password' ).val();

			if ( '' === passwordValue.trim() ) {
				$( '#login_error' ).html(
					'<strong>' +
						__( 'Error', 'onlyoffice-docspace-plugin' ) +
						': </strong>' +
						__(
							'The password field is empty',
							'onlyoffice-docspace-plugin'
						)
				);
				$( '#login_error' ).show();
			} else {
				callback( userNameValue, passwordValue );
			}
		} );

		$( '.wp-hide-pw' ).on( 'click', function () {
			if ( 'password' === $( '#oodsp-password' ).attr( 'type' ) ) {
				$( '#oodsp-password' ).attr( 'type', 'text' );
				resetToggle( false );
			} else {
				$( '#oodsp-password' ).attr( 'type', 'password' );
				resetToggle( true );
			}
		} );
	};

	function resetToggle( show ) {
		$( '.wp-hide-pw' )
			.attr( {
				'aria-label': show
					? __( 'Show password' )
					: __( 'Hide password' ),
			} )
			.find( '.text' )
			.text( show ? __( 'Show' ) : __( 'Hide' ) )
			.end()
			.find( '.dashicons' )
			.removeClass( show ? 'dashicons-hidden' : 'dashicons-visibility' )
			.addClass( show ? 'dashicons-visibility' : 'dashicons-hidden' );
	}
} )( window.jQuery, window.wp );
