/* global jQuery, oodsp, DocspaceIntegrationSdk, HttpError, _oodspAuthorization, DocSpace */

/**
 * Adds authorization functionality to the window.
 *
 * @param {jQuery} $ jQuery object.
 */
( function ( $ ) {
	const loginInput = $( 'input[name="docspace-login"]' );
	const passwordInput = $( 'input[name="docspace-password"]' );
	const systemUserCheckbox = jQuery( 'input[name="docspace-system-user"]' );

	$( '#oodsp-authorization-login-button' ).on( 'click', function ( event ) {
		event.preventDefault();
		oodsp.ui.clearNotices();

		if ( ! oodsp.ui.validateForm( 'oodsp-authorization-form' ) ) {
			return false;
		}

		oodsp.ui.showLoader();

		const userName = loginInput.val();
		const password = passwordInput.val();
		const systemUser = systemUserCheckbox.is( ':checked' );

		DocspaceIntegrationSdk.initScript(
			'oodsp-api-js',
			_oodspAuthorization.docspaceUrl
		)
			.then( () => {
				if ( systemUser ) {
					loginSystemUser( userName, password );
				} else {
					loginUser( userName, password );
				}
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
			await oodsp.client.deleteUser();
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

					window.location.reload();
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

	const loginUser = ( userName, password ) => {
		DocspaceIntegrationSdk.loginByPassword(
			'oodsp-system-frame',
			userName,
			password,
			async ( passwordHash ) => {
				try {
					const userInfo =
						await DocSpace.SDK.frames[
							'oodsp-system-frame'
						].getUserInfo();

					await oodsp.client.postUser(
						userInfo.id,
						userName,
						passwordHash
					);

					window.location.reload();
				} catch ( e ) {
					oodsp.ui.hideLoader();

					handleAuthorizationError( e );
				}
			},
			() => {
				oodsp.ui.hideLoader();

				oodsp.ui.addNotice(
					'oodsp-authorization-notice',
					wp.i18n.__(
						'User authentication failed',
						'onlyoffice-docspace-plugin'
					),
					'error'
				);
			},
			( error ) => {
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
				wp.i18n.sprintf(
					/* translators: %1$s: opening link tag, %2$s: closing link tag */
					wp.i18n.__(
						'The current domain is not set in the Content Security Policy (CSP) settings. Please add it via %1$sthe Developer Tools section%2$s.',
						'onlyoffice-docspace-plugin'
					),
					'<a href="' +
						// stripTrailingSlash( docspaceUrl ) +
						'/portal-settings/developer-tools/javascript-sdk" target="_blank">',
					'</a>'
				),
				'error'
			);
		} else {
			oodsp.ui.addNotice( error, 'error' );
		}
	};

	const onLoadAppError = () => {
		oodsp.ui.addNotice(
			wp.i18n.__(
				'ONLYOFFICE DocSpace cannot be reached.',
				'onlyoffice-docspace-plugin'
			),
			'error'
		);
	};
} )( jQuery );
