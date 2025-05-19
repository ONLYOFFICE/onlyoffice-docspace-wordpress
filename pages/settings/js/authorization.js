/* global jQuery, oodsp, DocspaceIntegrationSdk, HttpError, _oodspAuthorization */

/**
 * Adds authorization functionality to the window.
 *
 * @param {jQuery} $ jQuery object.
 */
( function ( $ ) {
	const loginInput = $( 'input[name="docspace-login"]' );
	const passwordInput = $( 'input[name="docspace-password"]' );

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

	$( '#oodsp-authorization-login-button' ).on( 'click', function ( event ) {
		event.preventDefault();
		oodsp.ui.clearNotices();

		if ( ! oodsp.ui.validateForm( 'oodsp-authorization-form' ) ) {
			return false;
		}

		const userName = loginInput.val();
		const password = passwordInput.val();

		oodsp.ui.showLoader();

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
				try {
					await oodsp.client.postSystemUser( userName, passwordHash );

					currentUrl.searchParams.append(
						'save_docspace_user',
						true
					);
					window.location.href = currentUrl.href;
				} catch ( e ) {
					oodsp.ui.hideLoader();

					handleAuthorizationError( e );
				}
			},
			function ( error ) {
				oodsp.ui.hideLoader();

				onAppError( error );
			}
		);
	};

	const handleAuthorizationError = ( error ) => {
		if ( error instanceof HttpError ) {
			oodsp.ui.addNotice(
				'oodsp-authorization-notice',
				error.message,
				'error'
			);
		}
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
