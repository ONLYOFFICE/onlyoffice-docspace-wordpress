/**
 * JS for OODSP_Public_DocSpace.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

(function ($) {
	const oodspErrorTemplate = wp.template( 'oodsp-error' );
	const defaultConfig      = {
		width: "100%",
		height: "100%",
		locale: _oodsp.locale,
	};

	document.addEventListener(
		'DOMContentLoaded',
		function () {
			var frames       = document.getElementsByClassName( "onlyoffice-docspace-block" );
			var oodspConfigs = [];

			for ( var frame of frames ) {
				oodspConfigs.push( JSON.parse( frame.dataset.config ) );
			}

			const countElements = oodspConfigs.length;

			if ( countElements === 0 ) {
				return;
			}

			DocspaceIntegrationSdk.initScript( "oodsp-api-js", _oodsp.docspaceUrl ).then(
				function () {
					for (var i = 0; i < countElements; i++) {
						oodspConfigs[i] = Object.assign( oodspConfigs[i], defaultConfig );

						if ( i == 0 ) {
							if ( _oodsp.isAnonymous ) {
								DocspaceIntegrationSdk.logout(
									oodspConfigs[0].frameId,
									function () {
										_initAllFrames( oodspConfigs, true );
									}
								);
							} else {
								DocspaceIntegrationSdk.loginByPasswordHash(
									oodspConfigs[0].frameId,
									_oodsp.currentUser,
									function () {
										return wp.oodsp.getPasswordHash()
									},
									function () {
										_initAllFrames( oodspConfigs, false );
									},
									function () {
										DocspaceIntegrationSdk.logout(
											oodspConfigs[0].frameId,
											function () {
												_initAllFrames( oodspConfigs, true );
											}
										);
									}
								);
							}
						} else {
							DocSpace.SDK.initSystem(
								{
									frameId: oodspConfigs[i].frameId,
									width: "100%",
									height: "100%",
									waiting: true
								}
							);
						}
					}
				}
			).catch(
				function () {
					for ( var config of oodspConfigs ) {
						$( "#" + config.frameId ).html(
							oodspErrorTemplate(
								{
									message: _oodsp.messages.docspaceUnavailable
								}
							)
						);
					}
				}
			);
		}
	);

	const _initAllFrames = (oodspConfigs, requiredRequestToken) => {
		for ( var config of oodspConfigs ) {
			if ( requiredRequestToken &&
						( ! config.hasOwnProperty( 'requestToken' ) || config.requestToken.length <= 0 ) ) {

				if (DocSpace.SDK.frames[config.frameId] != null) {
					DocSpace.SDK.frames[config.frameId].destroyFrame();
				}

				$( "#" + config.frameId ).html(
					oodspErrorTemplate(
						{
							header: _oodsp.messages.unauthorizedHeader,
							message: _oodsp.messages.unauthorizedMessage
						}
					)
				);

				continue;
			}

			DocSpace.SDK.frames[config.frameId].initFrame( config );
		}
	}

})( jQuery );
