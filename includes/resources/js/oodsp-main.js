/* global oodsp, _oodspMain, DocspaceIntegrationSdk */

( function () {
	window.oodsp = window.oodsp || {};
	window.oodsp.main = window.oodsp.main || {};

	oodsp.main.loadDocspace = function ( frameId, onSuccessLogin ) {
		DocspaceIntegrationSdk.initScript(
			'oodsp-api-js',
			_oodspMain.docspaceUrl
		)
			.then( function () {
				if ( ! _oodspMain.docspaceUser ) {
					openLoginPage(
						frameId,
						_oodspMain.docspaceUrl,
						'',
						'',
						false,
						onSuccessLogin
					);
					return;
				}

				DocspaceIntegrationSdk.loginByPasswordHash(
					frameId,
					_oodspMain.docspaceUser.user_name,
					function () {
						return _oodspMain.docspaceUser.password_hash;
					},
					onSuccessLogin,
					function () {
						openLoginPage(
							frameId,
							_oodspMain.docspaceUrl,
							_oodspMain.docspaceUser.user_name,
							'',
							true,
							onSuccessLogin
						);
					}
				);
			} )
			.catch( function ( error ) {
				// eslint-disable-next-line no-console
				console.error( error );

				oodsp.templates.docspaceUnavailable( frameId );
			} );
	};

	const openLoginPage = function (
		frameId,
		url,
		userName,
		password,
		error = false,
		onSuccessLogin
	) {
		oodsp.templates.loginPage(
			frameId,
			url,
			userName,
			password,
			error,
			async function ( _userName, _password ) {
				DocspaceIntegrationSdk.loginByPassword(
					frameId,
					_userName,
					_password,
					async function ( userNameValue, passwordHashValue ) {
						await oodsp.client.postUser(
							userNameValue,
							passwordHashValue
						);
						onSuccessLogin();
					},
					function () {
						openLoginPage(
							frameId,
							url,
							_userName,
							_password,
							true,
							onSuccessLogin
						);
					}
				);
			}
		);
	};
} )();
