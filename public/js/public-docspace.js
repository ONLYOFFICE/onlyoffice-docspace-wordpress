/**
 * JS for OODSP_Public_DocSpace.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			var frames       = document.getElementsByClassName( "onlyoffice-docspace-block" );
			var oodspConfigs = [];

			for ( var frame of frames ) {
				oodspConfigs.push( JSON.parse( frame.dataset.config ) );
			}

			DocSpaceComponent.initScript().then(
				function () {
					for ( var config of oodspConfigs ) {
						if (DocSpaceComponent.isPublic) {
							config.frameId,
							"100%",
							"100%",
							config.locale = DocSpaceComponent.locale;
							DocSpace.SDK.initFrame( config );
						} else {
							DocSpaceComponent.initLoginDocSpace(
								config.frameId,
								null,
								function () {
									"100%",
									"100%",
									config.locale = DocSpaceComponent.locale;
									DocSpace.SDK.initFrame( config );
								},
								function() {
									DocSpaceComponent.renderError(frameId);
								}
							);
						}
					}
				}
			).catch(
				function () {
					for ( var config of oodspConfigs ) {
						DocSpaceComponent.renderError( config.frameId );
					}
				}
			);
		}
	);
})();
