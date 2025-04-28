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
				_initFrames( oodspPublicConfigs );

				for ( let i = 0; i < countElements; i++ ) {
					if ( i === 0 ) {
						if (
							_oodspDocspacePublic.isAnonymous ||
							! _oodspDocspacePublic.docspaceUser
						) {
							_showUnauthorizedTemplates( oodspConfigs );
							return;
						}

						DocspaceIntegrationSdk.loginByPasswordHash(
							oodspConfigs[ 0 ].frameId,
							_oodspDocspacePublic.docspaceUser.user_name,
							function () {
								return _oodspDocspacePublic.docspaceUser
									.password_hash;
							},
							function () {
								_initFrames( oodspConfigs );
							},
							function () {
								_showUnauthorizedTemplates( oodspConfigs );
							}
						);
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

				for ( const config of [
					...oodspConfigs,
					...oodspPublicConfigs,
				] ) {
					oodsp.templates.docspaceUnavailable( config.frameId );
				}
			} );
	} );

	const _initFrames = ( oodspConfigs ) => {
		for ( const config of oodspConfigs ) {
			config.src = DocSpace.SDK.src;

			DocSpace.SDK.initFrame( config );
		}
	};

	const _showUnauthorizedTemplates = ( oodspConfigs ) => {
		for ( const config of oodspConfigs ) {
			if ( DocSpace.SDK.frames[ config.frameId ] ) {
				DocSpace.SDK.frames[ config.frameId ].destroyFrame();
			}

			oodsp.templates.docspaceUnauthorized( config.frameId );
		}
	};
} )( jQuery );
