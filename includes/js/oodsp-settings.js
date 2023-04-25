
( function( $ ) {
    const validateSettings = function () {
        const controls = [
            $('#docspace_url'),
            $('#docspace_login'),
            $('#user_pass')
        ]

        var result = true;

        for (var control of controls) {
            const value = control.val() || '';
            if (value.trim() === '') {
                control.parents('.form-field').addClass('form-invalid');
                result = false;
            } else {
                control.parents('.form-field').removeClass('form-invalid');
            }
        }

        return result;
    };

    const addNotice = function ( message, type ) {
		var $notice = $( '<div></div>' )
			.attr( 'role', 'alert' )
			.attr( 'tabindex', '-1' )
			.addClass( 'is-dismissible notice notice-' + type )
			.append( $( '<p></p>' ).text( message ) )
			.append(
				$( '<button></button>' )
					.attr( 'type', 'button' )
					.addClass( 'notice-dismiss' )
					.append( $( '<span></span>' ).addClass( 'screen-reader-text' ).text( wp.i18n.__( 'Dismiss this notice.' ) ) )
			);

		$('#onlyoffice-docspace-settings-notice').append( $notice );

		return $notice;
	};

	const clearNotices = function() {
		$( '.notice', $('#wpbody-content') ).remove();
	};

    const showLoader = function() {
		$('#onlyoffice-docspace-settings-loader').show();
	};

    const hideLoader = function() {
		$('#onlyoffice-docspace-settings-loader').hide();
	};

    const generatePass = function() {
        var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
        var passwordLength = 24;
        var password = "";

        for (var i = 0; i <= passwordLength; i++) {
            var randomNumber = Math.floor(Math.random() * chars.length);
            password += chars.substring(randomNumber, randomNumber +1);
        }

        return password;
    }

    const settingsForm = $('#onlyoffice-docspace-settings');

    settingsForm.on('submit', function () {
        const hash = $('#hash');
        
        if (!hash.length) {
            clearNotices();
            showLoader();

            if (!validateSettings()) {
                hideLoader();
                return false;
            }

            const pass = $('#user_pass').val().trim();
            DocSpaceComponent.initScript($('#docspace_url').val().trim())
                .then(async function() { // ToDo: onAppReady, onError
                    DocSpace.initFrame();
                    setTimeout(async function() {
                        const hashSettings = await DocSpace.getHashSettings();
                        const hash = await DocSpace.createHash(pass.trim(), hashSettings);
                        settingsForm.append(
                            $( '<input />' )
                                .attr('id', "hash")
                                .attr('name', "docspace_pass")
                                .attr('hidden', "true")
                                .attr('value', hash));
                        settingsForm.submit();
                    }, 1000);
                }).catch(function() {
                    const errorMessage = "Undefined API"; //ToDo: message i18n
                    hideLoader();
                    addNotice(errorMessage, "error");
                });
            
            return false;
        } else {
            return true;
        } 
    } );

    const usersForm = $('#onlyoffice-docspace-settings-users');

    usersForm.on('submit', async function (event) {
        if (event.originalEvent.submitter.id === 'doaction' && !usersForm.attr("hashGenerated")) {
            event.preventDefault();
            showLoader();

            const hashSettings = await DocSpace.getHashSettings();

            const users = $('th.check-column[scope="row"] input');

            for (var user of users) {
                if ($(user).is(':checked')) {
                    const hash = await DocSpace.createHash(generatePass(), hashSettings);

                    $(user).val($(user).val() + "$$" + hash);
                }
            }

            usersForm.attr('hashGenerated', true);
            usersForm.attr('action', 'admin.php?page=onlyoffice-docspace-settings&users=true');
            usersForm.attr('method', 'POST');
            usersForm.submit();
        }
    } );

    $('#wpbody-content').on( 'click', '.notice-dismiss', function( e ) {
		e.preventDefault();
		var $el = $( this ).parent();
		$el.removeAttr( 'role' );
		$el.fadeTo( 100, 0, function () {
			$el.slideUp( 100, function () {
				$el.remove();
				$('#wpbody-content').trigger( 'focus' );
			} );
		} );
	} );

}( jQuery ) );