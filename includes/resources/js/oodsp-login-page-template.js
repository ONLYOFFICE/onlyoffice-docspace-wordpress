/* global oodsp */

window.wp = window.wp || {};

( function ( $, wp ) {
	window.oodsp = window.oodsp || {};
	window.oodsp.templates = window.oodsp.templates || {};

	const __ = wp.i18n.__;
	let _targetId = '';
	let _callbackLogin;
	let _callbackResetPassword;

	oodsp.templates.loginPage = function (
		targetId,
		url,
		userName,
		password,
		error,
		callbackLogin,
		callbackResetPassword
	) {
		_targetId = targetId;
		_callbackLogin = callbackLogin;
		_callbackResetPassword = callbackResetPassword;

		const domain = new URL( url ).host;
		const messages = [];
		if ( error ) {
			messages.push( _messages.userAuthFailed );
		}

		openLoginWindow( domain, userName, password, messages );
	};

	const openLoginWindow = ( domain, userName, password, messages ) => {
		const loginTemplate = wp.template( 'oodsp-login' );

		$( '#' + _targetId ).html(
			loginTemplate( {
				domain,
				userName,
				password,
				messages,
				resetPassword: false,
			} )
		);

		initLoginWindowEvents( domain );
	};

	const openResetPasswordWindow = ( domain, userName, messages ) => {
		const loginTemplate = wp.template( 'oodsp-login' );

		$( '#' + _targetId ).html(
			loginTemplate( {
				domain,
				userName,
				messages,
				resetPassword: true,
			} )
		);

		initResetPasswordWindowEvents( domain );
	};

	const initLoginWindowEvents = ( domain ) => {
		$( '#oodsp-login-form' ).submit( ( event ) => {
			event.preventDefault();
			const userNameValue = $( '#oodsp-username' ).val().trim();
			const passwordValue = $( '#oodsp-password' ).val();

			if ( '' === userNameValue ) {
				openLoginWindow( domain, userNameValue, passwordValue, [
					_messages.emptyEmail,
				] );
				return;
			}

			if ( ! isValidEmail( userNameValue ) ) {
				openLoginWindow( domain, userNameValue, passwordValue, [
					_messages.notValidEmail,
				] );
				return;
			}

			if ( '' === passwordValue.trim() ) {
				openLoginWindow( domain, userNameValue, passwordValue, [
					_messages.emptyPassword,
				] );
				return;
			}

			_callbackLogin( userNameValue, passwordValue );
		} );

		$( '#oodsp-reset-password-link' ).on( 'click', () => {
			const userNameValue = $( '#oodsp-username' ).val().trim();

			openResetPasswordWindow( domain, userNameValue );
		} );

		$( '.wp-hide-pw' ).on( 'click', () => {
			if ( 'password' === $( '#oodsp-password' ).attr( 'type' ) ) {
				$( '#oodsp-password' ).attr( 'type', 'text' );
				resetToggle( false );
			} else {
				$( '#oodsp-password' ).attr( 'type', 'password' );
				resetToggle( true );
			}
		} );
	};

	const initResetPasswordWindowEvents = ( domain ) => {
		$( '#oodsp-login-form' ).submit( async ( event ) => {
			event.preventDefault();
			const userNameValue = $( '#oodsp-username' ).val().trim();

			if ( '' === userNameValue ) {
				openResetPasswordWindow( domain, userNameValue, [
					_messages.emptyEmail,
				] );
				return;
			}

			if ( ! isValidEmail( userNameValue ) ) {
				openResetPasswordWindow( domain, userNameValue, [
					_messages.notValidEmail,
				] );
				return;
			}

			try {
				await _callbackResetPassword( userNameValue );
			} catch ( error ) {
				openResetPasswordWindow( domain, userNameValue, [
					_messages.rateLimitExceeded,
				] );
				return;
			}

			const message = {
				type: 'info',
				text: wp.i18n.sprintf(
					/* translators: %s: email address */
					__(
						'The password change instruction has been sent to <strong>%s</strong> email address.',
						'onlyoffice-docspace-plugin'
					),
					userNameValue
				),
			};

			openLoginWindow( domain, userNameValue, '', [ message ] );
		} );

		$( '#oodsp-reset-password-cancel' ).on( 'click', () => {
			const userNameValue = $( '#oodsp-username' ).val().trim();

			openLoginWindow( domain, userNameValue, '', [] );
		} );
	};

	const resetToggle = ( show ) => {
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
	};

	const isValidEmail = ( email ) => {
		const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return regex.test( email );
	};

	const _messages = {
		userAuthFailed: {
			type: 'error',
			text: __(
				'Invalid credentials. Please try again.',
				'onlyoffice-docspace-plugin'
			),
		},
		emptyEmail: {
			type: 'error',
			text: __(
				'The email field is empty',
				'onlyoffice-docspace-plugin'
			),
		},
		emptyPassword: {
			type: 'error',
			text: __(
				'The password field is empty',
				'onlyoffice-docspace-plugin'
			),
		},
		notValidEmail: {
			type: 'error',
			text: __( 'Incorrect email', 'onlyoffice-docspace-plugin' ),
		},
		rateLimitExceeded: {
			type: 'error',
			text: __( 'Rate limit exceeded', 'onlyoffice-docspace-plugin' ),
		},
	};
} )( window.jQuery, window.wp );
