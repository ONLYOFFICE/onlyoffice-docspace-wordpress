/* global jQuery, oodsp */

( function ( $ ) {
	window.oodsp = window.oodsp || {};
	window.oodsp.ui = window.oodsp.ui || {};

	oodsp.ui.showLoader = function () {
		$( '#oodsp-loader' ).show();
	};

	oodsp.ui.hideLoader = function () {
		$( '#oodsp-loader' ).hide();
	};

	oodsp.ui.addNotice = ( targetId, message, type ) => {
		const $notice = $( '<div></div>' )
			.attr( 'role', 'alert' )
			.attr( 'tabindex', '-1' )
			.addClass( 'is-dismissible notice notice-' + type )
			.append( $( '<p></p>' ).html( message ) )
			.append(
				$( '<button></button>' )
					.attr( 'type', 'button' )
					.addClass( 'notice-dismiss' )
					.append(
						$( '<span></span>' )
							.addClass( 'screen-reader-text' )
							.text( wp.i18n.__( 'Dismiss this notice.' ) )
					)
			);

		$( '#' + targetId ).append( $notice );

		return $notice;
	};

	oodsp.ui.clearNotices = () => {
		$( '.notice', $( '#wpbody-content' ) ).remove();
	};

	oodsp.ui.validateForm = ( targetId ) => {
		const controls = $( '#' + targetId + ' .form-required input' );

		let result = true;

		for ( const control of controls ) {
			const value = $( control ).val() || '';
			if ( '' === value.trim() ) {
				$( control )
					.parents( '.form-field' )
					.addClass( 'form-invalid' );
				result = false;
			} else {
				$( control )
					.parents( '.form-field' )
					.removeClass( 'form-invalid' );
			}
		}

		return result;
	};

	$( '#wpbody-content' ).on( 'click', '.notice-dismiss', function ( e ) {
		e.preventDefault();
		const $el = $( this ).parent();
		$el.removeAttr( 'role' );
		$el.fadeTo( 100, 0, function () {
			$el.slideUp( 100, function () {
				$el.remove();
				$( '#wpbody-content' ).trigger( 'focus' );
			} );
		} );
	} );

	$( '.oodsp-tooltip' ).tooltip( {
		// eslint-disable-next-line object-shorthand
		content: function () {
			return $( this ).attr( 'title' );
		},
		tooltipClass: 'oodsp-tooltip-text',
		position: {
			at: 'right-20',
		},
		open: ( event, ui ) => {
			ui.tooltip.hover(
				function () {
					$( this ).stop( true ).fadeTo( 200, 1 );
				},
				function () {
					$( this ).fadeOut( 200, function () {
						$( this ).remove();
					} );
				}
			);
		},
	} );
} )( jQuery );
