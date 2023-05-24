/**
 * JS for OODSP_Public_DocSpace.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function() {
			var frames       = document.getElementsByClassName( "onlyoffice-docpace-block" );
			var oodspConfigs = [];

			for ( var frame of frames ) {
				oodspConfigs.push( JSON.parse( frame.dataset.config ) );
			}

			DocSpaceComponent.initScript().then(
				function() {
					DocSpaceComponent.initPublicDocSpace(
						oodspConfigs[0].frameId,
						function() {
							for ( var config of oodspConfigs ) {
								DocSpace.SDK.initFrame( config );
							}
						},
						function() {
							for ( var config of oodspConfigs ) {
								DocSpaceComponent.renderError( config.frameId, { message: "Portal unavailable! Please contact the administrator!" } );
							}
						}
					);
				}
			).catch(
				function() {
					for ( var config of oodspConfigs ) {
						DocSpaceComponent.renderError( config.frameId, { message: "Portal unavailable! Please contact the administrator!" } );
					}
				}
			);
		}
	);
})();
