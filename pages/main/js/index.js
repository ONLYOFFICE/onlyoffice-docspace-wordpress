/* global oodsp, _oodspMain, DocSpace */

document.addEventListener( 'DOMContentLoaded', function () {
	oodsp.main.loadDocspace( 'oodsp-manager-frame', function () {
		DocSpace.SDK.initManager( {
			frameId: 'oodsp-manager-frame',
			showMenu: true,
			showFilter: true,
			showHeader: true,
			locale: _oodspMain.locale,
			showSignOut: false,
			theme: 'Base',
		} );
	} );
} );
