/**
 * JS for OODSP_Utils.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

window.wp = window.wp || {};

( function ( $, wp ) {
	wp.oodsp = wp.oodsp || {};

	wp.oodsp.getPasswordHash = function () {
		var xhr      = new XMLHttpRequest();
		var postData = "action=oodsp_credentials";

		xhr.open( "POST", _oodsp.ajaxUrl, false );
		xhr.setRequestHeader( "Content-Type", "application/x-www-form-urlencoded; charset=UTF-8" );
		xhr.send( postData );

		if ( xhr.status === 200 ) {
				return xhr.responseText || null;
		}

		return null;
	}

	wp.oodsp.setPasswordHash = function ( hash = null ) {
		var xhr      = new XMLHttpRequest();
		var postData = "action=oodsp_credentials";

		postData += "&hash=" + hash;

		xhr.open( "POST", _oodsp.ajaxUrl, false );
		xhr.setRequestHeader( "Content-Type", "application/x-www-form-urlencoded; charset=UTF-8" );
		xhr.send( postData );

		if ( xhr.status === 200 ) {
				return xhr.responseText || null;
		}

		return null;
	}

	wp.oodsp.initLoginManager = function (frameId, onSuccessLogin) {
		DocspaceIntegrationSdk.initScript( "oodsp-api-js", _oodsp.docspaceUrl ).then(
			function () {
				DocspaceIntegrationSdk.loginByPasswordHash(
					frameId,
					_oodsp.currentUser,
					function () {
						return wp.oodsp.getPasswordHash()
					},
					onSuccessLogin,
					function () {
						wp.oodsp.initLoginWindow( frameId, false, onSuccessLogin )
					}
				)
			}
		).catch(
			function () {
				const oodspErrorTemplate = wp.template( 'oodsp-error' );

				$( "#" + frameId ).html(
					oodspErrorTemplate(
						{
							message: _oodsp.messages.docspaceUnavailable
						}
					)
				);
			}
		);
	}

	wp.oodsp.initLoginWindow = function ( frameId, error = false, onSuccessLogin ) {
		wp.oodsp.login(
			frameId,
			_oodsp.docspaceUrl,
			_oodsp.currentUser,
			error,
			function (password) {
				DocspaceIntegrationSdk.loginByPassword(
					frameId,
					_oodsp.currentUser,
					password,
					function ( passwordHash ) {
						wp.oodsp.setPasswordHash( passwordHash );
						onSuccessLogin();
					},
					function () {
						wp.oodsp.initLoginWindow( frameId, true, onSuccessLogin );
					}
				);
			}
		);
	}

	wp.oodsp.getAbsoluteUrl = function ( url ) {
		docSpaceUrl = _oodsp.docspaceUrl.endsWith( "/" ) ? _oodsp.docspaceUrl.slice( 0, -1 ) : _oodsp.docspaceUrl;

		if ( url.startsWith( "http://" ) || url.startsWith( "https://" ) ) {
				var origin = new URL( url ).origin;
				url        = url.replace( origin, docSpaceUrl );
		} else {
				url = docSpaceUrl + url;
		}

		return url;
	};
}( window.jQuery, window.wp ));