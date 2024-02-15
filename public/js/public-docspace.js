/**
 * JS for OODSP_Public_DocSpace.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

(function ($) {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			var frames       = document.getElementsByClassName( "onlyoffice-docspace-block" );
			var oodspConfigs = [];

			for ( var frame of frames ) {
				oodspConfigs.push( JSON.parse( frame.dataset.config ) );
			}

			const oodspErrorTemplate = wp.template( 'oodsp-error' );

			DocspaceIntegrationSdk.initScript( "oodsp-api-js", _oodsp.docspaceUrl ).then(
				function () {
					for ( var config of oodspConfigs ) {
						config.width  = "100%";
						config.height = "100%";
						config.locale = _oodsp.locale;

						if (_oodsp.isAnonymous) {
							if ( ! config.hasOwnProperty( 'requestToken' ) || config.requestToken.length <= 0 ) {
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

							DocspaceIntegrationSdk.logout(
								config.frameId,
								function () {
									DocSpace.SDK.initFrame( config );
								}
							);
							continue;
						}

						DocspaceIntegrationSdk.loginByPasswordHash(
							config.frameId,
							_oodsp.currentUser,
							function () {
								return wp.oodsp.getPasswordHash()
							},
							function () {
								DocSpace.SDK.initFrame( config );
							},
							function () {
								if ( ! config.hasOwnProperty( 'requestToken' ) || config.requestToken.length <= 0 ) {
									$( "#" + config.frameId ).html(
										oodspErrorTemplate(
											{
												header: _oodsp.messages.unauthorizedHeader,
												message: _oodsp.messages.unauthorizedMessage
											}
										)
									);
									return;
								}

								DocspaceIntegrationSdk.logout(
									config.frameId,
									function () {
										DocSpace.SDK.initFrame( config );
									}
								);
							}
						);
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

})( jQuery );
