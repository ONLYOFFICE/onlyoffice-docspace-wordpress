/**
 * JS for OODSP_Settings.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

(function ( $ ) {
	const validateSettings = function () {
		const controls = [
			$( '#docspace_url' ),
			$( '#docspace_login' ),
			$( '#user_pass' )
		]

		var result = true;

		for ( var control of controls ) {
			const value = control.val() || '';
			if ( '' === value.trim() ) {
				control.parents( '.form-field' ).addClass( 'form-invalid' );
				result = false;
			} else {
				control.parents( '.form-field' ).removeClass( 'form-invalid' );
			}
		}

		return result;
	};

	const addNotice = function ( message, type ) {
		var $notice = $( '<div></div>' )
			.attr( 'role', 'alert' )
			.attr( 'tabindex', '-1' )
			.addClass( 'is-dismissible notice notice-' + type )
			.append( $( '<p></p>' ).html( message ) )
			.append(
				$( '<button></button>' )
					.attr( 'type', 'button' )
					.addClass( 'notice-dismiss' )
					.append( $( '<span></span>' ).addClass( 'screen-reader-text' ).text( wp.i18n.__( 'Dismiss this notice.' ) ) )
			);

		$( '#onlyoffice-docspace-settings-notice' ).append( $notice );

		return $notice;
	};

	const clearNotices = function () {
		$( '.notice', $( '#wpbody-content' ) ).remove();
	};

	const showLoader = function () {
		$( '#onlyoffice-docspace-settings-loader' ).show();
	};

	const hideLoader = function () {
		$( '#onlyoffice-docspace-settings-loader' ).hide();
	};

	const generatePass = function () {
		var chars          = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
		var passwordLength = 24;
		var password       = "";

		for (var i = 0; i <= passwordLength; i++) {
			var randomNumber = Math.floor( Math.random() * chars.length );
			password        += chars.substring( randomNumber, randomNumber + 1 );
		}

		return password;
	}

	const settingsForm = $( '#onlyoffice-docspace-settings' );

	settingsForm.on(
		'submit',
		function () {
			const hash = $( '#hash' );
			$( '#docspace-script-tag' ).remove();
			window.DocSpace = null;

			if ( ! hash.length ) {
				clearNotices();
				showLoader();

				if ( ! validateSettings() ) {
					hideLoader();
					return false;
				}
				const docspaceUrl = $( '#docspace_url' ).val().trim();
				const pass        = $( '#user_pass' ).val().trim();
				DocspaceIntegrationSdk.initScript( 'oodsp-api-js', docspaceUrl )
					.then(
						async function () {
							DocSpace.SDK.initSystem(
								{
									frameId: "oodsp-system-frame",
									events: {
										"onAppReady": async function () {
											const hashSettings = await DocSpace.SDK.frames['oodsp-system-frame'].getHashSettings();
											const hash         = await DocSpace.SDK.frames['oodsp-system-frame'].createHash( pass.trim(), hashSettings );
											settingsForm.append(
												$( '<input />' )
													.attr( 'id', 'hash' )
													.attr( 'name', 'docspace_pass' )
													.attr( 'hidden', 'true' )
													.attr( 'value', hash )
											);

											const hashCurrentUser = await DocSpace.SDK.frames['oodsp-system-frame'].createHash( generatePass(), hashSettings );
											settingsForm.append(
												$( '<input />' )
													.attr( 'id', 'hash-current-user' )
													.attr( 'name', 'hash_current_user' )
													.attr( 'hidden', 'true' )
													.attr( 'value', hashCurrentUser )
											);

											$( '#user_pass' ).val( '' )
											settingsForm.submit();
										},
										"onAppError": function (e) {
											hideLoader();

											if ( e === "The current domain is not set in the Content Security Policy (CSP) settings." ) {
												addNotice(
													wp.i18n.sprintf(
														wp.i18n.__( 'The current domain is not set in the Content Security Policy (CSP) settings. Please add it via %sthe Developer Tools section%s.', 'onlyoffice-docspace-plugin' ),
														'<a href="' + stripTrailingSlash( docspaceUrl ) + '/portal-settings/developer-tools/javascript-sdk" target="_blank">',
														"</a>"
													),
													'error'
												);
											} else {
												addNotice( e, 'error' );
											}
										}
									}
								}
							);
						}
					).catch(
						function () {
							hideLoader();
							addNotice( wp.i18n.__( 'ONLYOFFICE DocSpace cannot be reached.', 'onlyoffice-docspace-plugin' ), 'error' );
						}
					);
				return false;
			} else {
				return true;
			}
		}
	);

	const usersForm = $( '#onlyoffice-docspace-settings-users' );

	usersForm.on(
		'submit',
		async function (event) {
			if ('doaction' === event.originalEvent.submitter.id && ! usersForm.attr( 'hashGenerated' ) ) {
				event.preventDefault();
				showLoader();

				const hashSettings = await DocSpace.SDK.frames['oodsp-system-frame'].getHashSettings();

				const users = $( 'th.check-column[scope="row"] input' );

				for (var user of users) {
					if ( $( user ).is( ':checked' ) ) {
						const hash = await DocSpace.SDK.frames['oodsp-system-frame'].createHash( generatePass(), hashSettings );

						$( user ).val( $( user ).val() + "$$" + hash );
					}
				}

				usersForm.attr( 'hashGenerated', true );
				usersForm.attr( 'action', 'admin.php?page=onlyoffice-docspace-settings&users=true' );
				usersForm.attr( 'method', 'POST' );
				usersForm.submit();
			}
		}
	);

	$( '#wpbody-content' ).on(
		'click',
		'.notice-dismiss',
		function ( e ) {
			e.preventDefault();
			var $el = $( this ).parent();
			$el.removeAttr( 'role' );
			$el.fadeTo(
				100,
				0,
				function () {
					$el.slideUp(
						100,
						function () {
							$el.remove();
							$( '#wpbody-content' ).trigger( 'focus' );
						}
					);
				}
			);
		}
	);

	var searchParams = new URLSearchParams( window.location.search );

	if ( 'true' === searchParams.get( 'users' ) ) {
		DocspaceIntegrationSdk.initScript( 'oodsp-api-js', _oodsp.docspaceUrl )
			.then(
				function (e) {
					DocSpace.SDK.initSystem(
						{
							frameId: 'oodsp-system-frame'
						}
					);
				}
			);
	}

	const stripTrailingSlash = ( str ) => {
		return str.endsWith( '/' )
			? str.slice( 0, -1 )
			: str;
	};
}( jQuery ) );
