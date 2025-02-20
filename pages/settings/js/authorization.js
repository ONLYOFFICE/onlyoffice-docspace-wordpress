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
				'onlyoffice-docspace-plugin'
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
		const systemUser = systemUserCheckbox.is( ':checked' );

		if ( systemUser && _oodspAuthorization.existSystemUser ) {
			showConfirmDialog( userName, password, systemUser );
		} else {
			handleAuthorization( userName, password, systemUser );
		}
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

	const handleAuthorization = ( userName, password, systemUser ) => {
		oodsp.ui.showLoader();

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
	};

	const showConfirmDialog = ( userName, password, systemUser ) => {
		$( '#oodsp-save-system-user-confirm-dialog' ).dialog( {
			autoOpen: false,
			modal: true,
			buttons: [
				{
					text: wp.i18n.__(
						'Continue',
						'onlyoffice-docspace-plugin'
					),
					click: () => {
						handleAuthorization( userName, password, systemUser );
						$( '#oodsp-save-system-user-confirm-dialog' ).dialog(
							'close'
						);
					},
					class: 'ok',
				},
				{
					text: wp.i18n.__( 'Cancel', 'onlyoffice-docspace-plugin' ),
					click: () => {
						$( '#oodsp-save-system-user-confirm-dialog' ).dialog(
							'close'
						);
					},
					class: 'cancel',
				},
			],
		} );
		$( '#oodsp-save-system-user-confirm-dialog' ).dialog( 'open' );
	};

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
			() => {
				oodsp.ui.hideLoader();

				oodsp.ui.addNotice(
					'oodsp-authorization-notice',
					wp.i18n.__(
						'Invalid credentials. Please try again.',
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
				'oodsp-authorization-notice',
				wp.i18n.sprintf(
					/* translators: %1$s: opening link tag, %2$s: closing link tag */
					wp.i18n.__(
						'The current domain is not set in the Content Security Policy (CSP) settings. Please add it via %1$sthe Developer Tools section%2$s.',
						'onlyoffice-docspace-plugin'
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
				'onlyoffice-docspace-plugin'
			),
			'error'
		);
	};
} )( jQuery );
