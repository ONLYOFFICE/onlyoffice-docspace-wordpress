/* global jQuery */

/**
 * Immediately Invoked Function Expression (IIFE) to encapsulate the code.
 * This pattern helps to avoid polluting the global scope.
 *
 * @param {jQuery} $ - jQuery object.
 */
( function ( $ ) {
	$( '#oodsp-create-docspace-user-confirm-dialog' ).dialog( {
		autoOpen: false,
		modal: true,
		buttons: [
			{
				text: wp.i18n.__( 'Export', 'onlyoffice-docspace-plugin' ),
				click: () => {
					$( '.bulkactions' ).parents( 'form' ).unbind();
					$( '.bulkactions' ).parents( 'form' ).submit();

					$( '#oodsp-create-docspace-user-confirm-dialog' ).dialog(
						'close'
					);
				},
			},
			{
				text: wp.i18n.__( 'Cancel', 'onlyoffice-docspace-plugin' ),
				click: () => {
					$( '#oodsp-create-docspace-user-confirm-dialog' ).dialog(
						'close'
					);
				},
			},
		],
	} );

	$( '#doaction,#doaction2' ).on( 'click', () => {
		const createDocspaceUserHandler = ( event ) => {
			$( '.bulkactions' )
				.parents( 'form' )
				.off( 'submit', createDocspaceUserHandler );
			if ( event.isDefaultPrevented() ) {
				return;
			}

			event.preventDefault();
			$( '#oodsp-create-docspace-user-confirm-dialog' ).dialog( 'open' );
		};

		if (
			$( '#bulk-action-selector-top' ).val() === 'create-docspace-user'
		) {
			$( '.bulkactions' )
				.parents( 'form' )
				.on( 'submit', createDocspaceUserHandler );
		}
	} );
} )( jQuery );
