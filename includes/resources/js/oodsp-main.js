/* global oodsp, _oodspMain, DocspaceIntegrationSdk, DocSpace */

( function () {
	window.oodsp = window.oodsp || {};
	window.oodsp.main = window.oodsp.main || {};

	oodsp.main.loadDocspace = function ( frameId, callback ) {
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

		const onSuccessLogin = function () {
			const config = {
				events: {
					onSignOut,
				},
			};

			callback( config );
		};

		const onSignOut = async () => {
			await DocSpace.SDK.frames[ frameId ].destroyFrame();
			await DocSpace.SDK.initSystem( {
				frameId,
				src: DocSpace.SDK.src,
			} );
			await oodsp.client.deleteUser();
			await DocSpace.SDK.frames[ frameId ].destroyFrame();

			openLoginPage(
				frameId,
				_oodspMain.docspaceUrl,
				_oodspMain.docspaceUser.user_name,
				'',
				false,
				onSuccessLogin
			);
		};
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
						const userInfo =
							await DocSpace.SDK.frames[ frameId ].getUserInfo();

						await oodsp.client.postUser(
							userInfo.id,
							userNameValue,
							passwordHashValue
						);

						_oodspMain.docspaceUser = {
							id: userInfo.id,
							user_name: userNameValue,
							password_hash: passwordHashValue,
						};

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
			},
			async function ( email ) {
				await oodsp.client.resetPassword( email );
			}
		);
	};
} )();
