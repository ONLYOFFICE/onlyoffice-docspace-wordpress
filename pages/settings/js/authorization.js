/* global jQuery, oodsp, DocspaceIntegrationSdk, HttpError, _oodspAuthorization */

/**
 * Adds authorization functionality to the window.
 *
 * @param {jQuery} $ jQuery object.
 */
( function ( $ ) {
	const loginInput = $( 'input[name="docspace-login"]' );
	const passwordInput = $( 'input[name="docspace-password"]' );
	const codeInput = $( 'input[name="docspace-code"]' );
	const codeRow = $( '#oodsp-authorization-code-row' );
	const codeHint = $( '#oodsp-authorization-code-hint' );
	const loginButton = $( '#oodsp-authorization-login-button' );
	const cancelButton = $( '#oodsp-authorization-cancel-button' );
	const showPasswordButton = $( '#oodsp-authorization-form .wp-hide-pw' );

	// Label restored on the login button when the code step is left.
	const loginButtonLabel = loginButton.val();

	// Credentials awaiting a two-factor authentication code, null outside the code step.
	let pending = null;

	const currentUrl = new URL( window.location.href );
	if ( currentUrl.searchParams.get( 'save_docspace_user' ) ) {
		const wpAdminCanonical =
			document.getElementById( 'wp-admin-canonical' ).href;
		const wpAdminCanonicalUrl = new URL( wpAdminCanonical );

		wpAdminCanonicalUrl.searchParams.delete( 'save_docspace_user' );

		window.history.replaceState(
			null,
			null,
			wpAdminCanonicalUrl.href + window.location.hash
		);

		oodsp.ui.addNotice(
			'oodsp-authorization-notice',
			wp.i18n.__(
				'Successful authorization. Settings saved.',
				'onlyoffice-docspace'
			),
			'success'
		);
	}

	loginButton.on( 'click', function ( event ) {
		event.preventDefault();
		oodsp.ui.clearNotices();

		if ( ! oodsp.ui.validateForm( 'oodsp-authorization-form' ) ) {
			return false;
		}

		oodsp.ui.showLoader();

		if ( pending ) {
			saveSystemUser(
				pending.userName,
				pending.passwordHash,
				codeInput.val().trim()
			);
			return;
		}

		const userName = loginInput.val();
		const password = passwordInput.val();

		DocspaceIntegrationSdk.initScript(
			'oodsp-api-js',
			_oodspAuthorization.docspaceUrl
		)
			.then( () => {
				loginSystemUser( userName, password );
			} )
			.catch( () => {
				oodsp.ui.hideLoader();

				onLoadAppError();
			} );
	} );

	cancelButton.on( 'click', ( event ) => {
		event.preventDefault();
		oodsp.ui.clearNotices();

		resetCodeStep();
	} );

	$( '#oodsp-authorization-logout-button' ).on( 'click', async ( event ) => {
		event.preventDefault();
		oodsp.ui.showLoader();
		oodsp.ui.clearNotices();

		try {
			await oodsp.client.deleteSystemUser();
		} finally {
			window.location.reload();
		}
	} );

	const loginSystemUser = ( userName, password ) => {
		DocspaceIntegrationSdk.createPasswordHash(
			'oodsp-system-frame',
			password,
			async ( passwordHash ) => {
				await saveSystemUser( userName, passwordHash, '' );
			},
			function ( error ) {
				oodsp.ui.hideLoader();

				onAppError( error );
			}
		);
	};

	const saveSystemUser = async ( userName, passwordHash, code ) => {
		try {
			const response = await oodsp.client.postSystemUser(
				userName,
				passwordHash,
				code
			);

			if ( response?.tfaRequired ) {
				pending = { userName, passwordHash };

				oodsp.ui.hideLoader();

				showCodeStep( response.type, response.phoneNoise );
				return;
			}

			currentUrl.searchParams.append( 'save_docspace_user', true );
			window.location.href = currentUrl.href;
		} catch ( e ) {
			oodsp.ui.hideLoader();

			handleAuthorizationError( e );
		}
	};

	/**
	 * Switches the form to the two-factor authentication code step.
	 *
	 * @param {string} type       Challenge type, either 'sms' or 'app'.
	 * @param {string} phoneNoise Masked mobile phone number, for the 'sms' type only.
	 */
	const showCodeStep = ( type, phoneNoise ) => {
		setCredentialsDisabled( true );

		codeInput.val( '' );
		codeRow
			.addClass( 'form-required' )
			.removeClass( 'form-invalid' )
			.removeAttr( 'hidden' );

		codeHint.text(
			'sms' === type
				? wp.i18n.sprintf(
						/* translators: %s: masked mobile phone number */
						wp.i18n.__(
							'Enter the code sent to %s.',
							'onlyoffice-docspace'
						),
						phoneNoise
				  )
				: wp.i18n.__(
						'Enter the code from your authenticator application.',
						'onlyoffice-docspace'
				  )
		);

		loginButton.val( wp.i18n.__( 'Confirm', 'onlyoffice-docspace' ) );
		cancelButton.removeAttr( 'hidden' );

		codeInput.trigger( 'focus' );
	};

	/**
	 * Restores the form to the initial credentials step.
	 */
	const resetCodeStep = () => {
		pending = null;

		codeInput.val( '' );
		codeHint.text( '' );
		codeRow
			.removeClass( 'form-required form-invalid' )
			.attr( 'hidden', 'hidden' );

		setCredentialsDisabled( false );

		loginButton.val( loginButtonLabel );
		cancelButton.attr( 'hidden', 'hidden' );
	};

	/**
	 * Toggles the credentials controls while the code step is active.
	 *
	 * @param {boolean} disabled Whether the controls are disabled.
	 */
	const setCredentialsDisabled = ( disabled ) => {
		loginInput.prop( 'disabled', disabled );
		passwordInput.prop( 'disabled', disabled );
		showPasswordButton.prop( 'disabled', disabled );
	};

	const handleAuthorizationError = ( error ) => {
		if ( ! ( error instanceof HttpError ) ) {
			oodsp.ui.addNotice(
				'oodsp-authorization-notice',
				wp.i18n.__(
					'Something went wrong. Please try again.',
					'onlyoffice-docspace'
				),
				'error'
			);

			return;
		}

		const confirmUrl = error.data?.confirmUrl;

		if ( confirmUrl ) {
			oodsp.ui.addNotice(
				'oodsp-authorization-notice',
				wp.i18n.sprintf(
					/* translators: %1$s: error message, %2$s: opening link tag, %3$s: closing link tag */
					wp.i18n.__(
						'%1$s %2$sOpen ONLYOFFICE DocSpace%3$s',
						'onlyoffice-docspace'
					),
					error.message,
					'<a href="' +
						confirmUrl +
						'" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'error'
			);

			return;
		}

		oodsp.ui.addNotice(
			'oodsp-authorization-notice',
			error.message,
			'error'
		);
	};

	const onAppError = ( error ) => {
		if (
			error ===
			'The current domain is not set in the Content Security Policy (CSP) settings.'
		) {
			oodsp.ui.addNotice(
				'oodsp-authorization-notice',
				wp.i18n.sprintf(
					/* translators: %1$s: opening link tag, %2$s: closing link tag */
					wp.i18n.__(
						'The current domain is not set in the Content Security Policy (CSP) settings. Please add it via %1$sthe Developer Tools section%2$s.',
						'onlyoffice-docspace'
					),
					'<a href="' +
						_oodspAuthorization.developerToolsUrl +
						'" target="_blank">',
					'</a>'
				),
				'error'
			);
		} else {
			oodsp.ui.addNotice( 'oodsp-authorization-notice', error, 'error' );
		}
	};

	const onLoadAppError = () => {
		oodsp.ui.addNotice(
			'oodsp-authorization-notice',
			wp.i18n.__(
				'ONLYOFFICE DocSpace cannot be reached',
				'onlyoffice-docspace'
			),
			'error'
		);
	};
} )( jQuery );
