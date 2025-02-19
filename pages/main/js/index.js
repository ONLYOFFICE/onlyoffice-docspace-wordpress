/* global oodsp, _oodspMain, DocSpace */

( function () {
	const defaultConfig = {
		frameId: 'oodsp-manager-frame',
		showMenu: true,
		showFilter: true,
		showHeader: true,
		locale: _oodspMain.locale,
		theme: 'Base',
	};

	const showDocspace = ( config ) => {
		DocSpace.SDK.initManager( Object.assign( defaultConfig, config ) );
	};

	oodsp.main.loadDocspace( 'oodsp-manager-frame', showDocspace );
} )();
