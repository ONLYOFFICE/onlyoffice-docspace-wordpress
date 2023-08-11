/**
 * JS for OODSP_Public_DocSpace.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function() {
			var frames       = document.getElementsByClassName( "onlyoffice-docspace-block" );
			var oodspConfigs = [];

			for ( var frame of frames ) {
				oodspConfigs.push( JSON.parse( frame.dataset.config ) );
			}

			DocSpaceComponent.initScript().then(
				function() {
					for ( var config of oodspConfigs ) {
						DocSpaceComponent.initPublicDocSpace(
							config.frameId,
							config.width || null,
							config.height || null,
							function() {
								config.locale = DocSpaceComponent.locale;
								DocSpace.SDK.initFrame( config );
							},
							function() {
								DocSpaceComponent.renderError( config.frameId );
							}
						);
					}
				}
			).catch(
				function() {
					for ( var config of oodspConfigs ) {
						DocSpaceComponent.renderError( config.frameId );
					}
				}
			);
		}
	);
})();
