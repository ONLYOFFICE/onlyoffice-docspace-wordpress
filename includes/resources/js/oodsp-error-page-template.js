/* global oodsp, _oodspTemplates  */

window.wp = window.wp || {};

( function ( $, wp ) {
	window.oodsp = window.oodsp || {};
	window.oodsp.templates = window.oodsp.templates || {};

	const __ = wp.i18n.__;

	oodsp.templates.docspaceUnavailable = ( frameId ) => {
		const errorTemplate = wp.template( 'oodsp-error' );

		$( '#' + frameId ).html(
			errorTemplate( {
				header: __(
					'Not yet available',
					'onlyoffice-docspace-wordpress'
				),
				message: _oodspTemplates.isAdmin
					? wp.i18n.sprintf(
							/* translators: %1$s: opening link tag, %2$s: closing link tag */
							__(
								'Go to the %1$ssettings%2$s to configure ONLYOFFICE DocSpace plugin.',
								'onlyoffice-docspace-plugin'
							),
							'<a href="' +
								_oodspTemplates.settingsPageUrl +
								'">',
							'</a>'
					  )
					: __(
							'Please contact admin to configure the ONLYOFFICE DocSpace plugin.',
							'onlyoffice-docspace-wordpress'
					  ),
				image: _oodspTemplates.resourceUrl + 'unavailable.svg',
			} )
		);
	};

	oodsp.templates.docspaceUnauthorized = ( frameId ) => {
		const errorTemplate = wp.template( 'oodsp-error' );

		let message = __(
			'Please log in to the site!',
			'onlyoffice-docspace-plugin'
		);

		if ( ! _oodspTemplates.isAnonymous ) {
			if ( _oodspTemplates.hasDocSpaceWindow ) {
				message = __(
					'Please proceed to the DocSpace plugin via the left side menu and enter your password to restore access.',
					'onlyoffice-docspace-plugin'
				);
			} else {
				message = __(
					'Please contact the administrator.',
					'onlyoffice-docspace-plugin'
				);
			}
		}

		$( '#' + frameId ).html(
			errorTemplate( {
				header: _oodspTemplates.isAnonymous
					? __(
							'Authorization unsuccessful!',
							'onlyoffice-docspace-plugin'
					  )
					: __( 'Access denied!', 'onlyoffice-docspace-plugin' ),
				message,
				image: _oodspTemplates.resourceUrl + 'unauthorized.svg',
			} )
		);
	};
} )( window.jQuery, window.wp );
