/* global jQuery, oodsp, DocSpace, DocspaceIntegrationSdk, _oodspDocspacePublic  */

/**
 * Adds functionality to the window.
 */
( function () {
	'use strict';

	const defaultConfig = {
		width: '100%',
		height: '100%',
		locale: _oodspDocspacePublic.locale,
	};

	document.addEventListener( 'DOMContentLoaded', function () {
		const frames = document.getElementsByClassName(
			'onlyoffice-docspace-block'
		);
		const oodspConfigs = [];
		const oodspPublicConfigs = [];

		for ( const frame of frames ) {
			let config = JSON.parse( frame.dataset.config );
			config = Object.assign( config, defaultConfig );

			if ( config.requestToken && config.requestToken.length > 0 ) {
				oodspPublicConfigs.push( config );
			} else {
				oodspConfigs.push( config );
			}
		}

		const countElements = oodspConfigs.length;

		DocspaceIntegrationSdk.initScript(
			'oodsp-api-js',
			_oodspDocspacePublic.docspaceUrl
		)
			.then( function () {
				_initAllPublicFrames( oodspPublicConfigs );

				for ( let i = 0; i < countElements; i++ ) {
					if ( i === 0 ) {
						if (
							_oodspDocspacePublic.isAnonymous ||
							! _oodspDocspacePublic.docspaceUser
						) {
							DocspaceIntegrationSdk.logout(
								oodspConfigs[ 0 ].frameId,
								function () {
									_initAllFrames( oodspConfigs, true );
								}
							);
						} else {
							DocspaceIntegrationSdk.loginByPasswordHash(
								oodspConfigs[ 0 ].frameId,
								_oodspDocspacePublic.docspaceUser.user_name,
								function () {
									return _oodspDocspacePublic.docspaceUser
										.password_hash;
								},
								function () {
									_initAllFrames( oodspConfigs, false );
								},
								function () {
									DocspaceIntegrationSdk.logout(
										oodspConfigs[ 0 ].frameId,
										function () {
											_initAllFrames(
												oodspConfigs,
												true
											);
										}
									);
								}
							);
						}
					} else {
						DocSpace.SDK.initSystem( {
							frameId: oodspConfigs[ i ].frameId,
							src: DocSpace.SDK.src,
							width: '100%',
							height: '100%',
							waiting: true,
						} );
					}
				}
			} )
			.catch( function ( error ) {
				// eslint-disable-next-line no-console
				console.error( error );

				for ( const config of oodspConfigs ) {
					oodsp.templates.docspaceUnavailable( config.frameId );
				}
			} );
	} );

	const _initAllFrames = ( oodspConfigs, requiredRequestToken ) => {
		for ( const config of oodspConfigs ) {
			if (
				requiredRequestToken &&
				( ! config.hasOwnProperty( 'requestToken' ) ||
					config.requestToken.length <= 0 )
			) {
				if ( DocSpace.SDK.frames[ config.frameId ] !== null ) {
					DocSpace.SDK.frames[ config.frameId ].destroyFrame();
				}

				oodsp.templates.docspaceUnauthorized( config.frameId );

				continue;
			}

			config.src = DocSpace.SDK.src;

			DocSpace.SDK.frames[ config.frameId ].initFrame( config );
		}
	};

	const _initAllPublicFrames = ( oodspConfigs ) => {
		for ( const config of oodspConfigs ) {
			config.src = DocSpace.SDK.src;

			DocSpace.SDK.initFrame( config );
		}
	};
} )( jQuery );
