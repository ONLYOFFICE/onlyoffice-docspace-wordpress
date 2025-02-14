/* global jQuery, oodsp */

/**
 * Adds functionality to the window.
 *
 * @param {jQuery} $ jQuery object.
 */
( function ( $ ) {
	'use strict';

	// Initialize disconnect confirmation dialog.
	$( '#oodsp-disconnect-confirm-dialog' ).dialog( {
		autoOpen: false,
		modal: true,
		buttons: [
			{
				text: wp.i18n.__( 'Disconnect', 'onlyoffice-docspace-plugin' ),
				click: () => {
					$( '#oodsp-general-settings-form [name="action"]' ).val(
						'delete'
					);
					$( '#oodsp-general-settings-form' ).submit();

					$( this ).dialog( 'close' );
				},
			},
			{
				text: wp.i18n.__( 'Cancel', 'onlyoffice-docspace-plugin' ),
				click: () => {
					$( this ).dialog( 'close' );
				},
			},
		],
	} );

	// Handle disconnect button click.
	$( '#oodsp-general-settings-disconnect-button' ).on( 'click', ( event ) => {
		event.preventDefault();

		$( '#oodsp-disconnect-confirm-dialog' ).dialog( 'open' );
	} );

	// Handle change button click.
	$( '#oodsp-general-settings-change-button' ).on( 'click', ( event ) => {
		event.preventDefault();

		$( '#oodsp-general-settings-buttons' ).removeAttr( 'hidden' );
		$( '#oodsp-settings-csp-notice' ).removeAttr( 'hidden' );

		$( '#oodsp-general-settings-change-buttons' ).attr( 'hidden', true );

		$( '#oodsp-general-settings-form .form-field :input' ).each(
			function () {
				const clone = $( this )
					.clone()
					.addClass( 'clone' )
					.removeAttr( 'disabled' );
				$( this ).after( clone );
				$( this ).attr( 'hidden', true );
			}
		);
	} );

	// Handle cancel button click.
	$( '#oodsp-general-settings-cancel-button' ).on( 'click', ( event ) => {
		event.preventDefault();

		$( '#oodsp-general-settings-buttons' ).attr( 'hidden', true );
		$( '#oodsp-settings-csp-notice' ).attr( 'hidden', true );

		$( '#oodsp-general-settings-change-buttons' ).removeAttr( 'hidden' );

		$( '#oodsp-general-settings-form .form-field :input.clone' ).remove();
		$( '#oodsp-general-settings-form .form-field :input[hidden]' ).each(
			function () {
				$( this )
					.removeAttr( 'hidden' )
					.parents( '.form-field' )
					.removeClass( 'form-invalid' );
			}
		);
	} );

	// Handle form submission.
	const settingsForm = $( '#oodsp-general-settings-form' );

	settingsForm.on( 'submit', () => {
		if ( ! oodsp.ui.validateForm( 'oodsp-general-settings-form' ) ) {
			return false;
		}
	} );
} )( jQuery );
