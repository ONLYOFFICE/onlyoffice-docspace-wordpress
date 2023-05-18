/**
 * @output includes/js/oodsp-login.js
 */

window.wp = window.wp || {};

( function( $, wp ) {
    wp.oodsp = wp.oodsp || {};

    var __ = wp.i18n.__;

    wp.oodsp.login = function (frameId, url, email, error, callback) {
        const loginTemplate = wp.template( 'oodsp-login' );

        $('#' + frameId).html(loginTemplate({ 
            email: email, 
            url: url,
            domain: new URL(url).host
        }));

        $('#oodsp-login-form').submit( function( event ) {
            window.DocSpaceComponent.onAppReady = false; //ToDo: remove
            event.preventDefault();

            var password = $('#oodsp-password').val();

            if (password.trim() == "" ) {
                $("#login_error").html(messages['empty-password']);
                $("#login_error").show();
            } else {
                callback( password );
            }
        });

        $('.wp-hide-pw').on( 'click', function () {
            if ( 'password' === $('#oodsp-password').attr( 'type' ) ) {
				$('#oodsp-password').attr( 'type', 'text' );
				resetToggle( false );
			} else {
				$('#oodsp-password').attr( 'type', 'password' );
				resetToggle( true );
			}
        });
    };

    function resetToggle( show ) {
		$('.wp-hide-pw')
			.attr({
				'aria-label': show ? __( 'Show password' ) : __( 'Hide password' )
			})
			.find( '.text' )
				.text( show ? __( 'Show' ) : __( 'Hide' ) )
			.end()
			.find( '.dashicons' )
				.removeClass( show ? 'dashicons-hidden' : 'dashicons-visibility' )
				.addClass( show ? 'dashicons-visibility' : 'dashicons-hidden' );
	}

}( window.jQuery, window.wp ));
