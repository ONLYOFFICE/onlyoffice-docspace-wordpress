/* global oodsp, _oodspClient */

class HttpError extends Error {
	constructor( statusCode, message = '' ) {
		super( message );
		this.name = 'HttpError';
		this.statusCode = statusCode;
	}
}

( function ( $ ) {
	window.oodsp = window.oodsp || {};
	window.oodsp.client = window.oodsp.client || {};

	oodsp.client.postSystemUser = async ( userName, passwordHash ) => {
		await ajaxRequest( {
			url: _oodspClient.ajaxUrl,
			method: 'POST',
			data: {
				action: 'oodsp_set_system_user',
				_ajax_nonce: _oodspClient.nonce,
				userName,
				passwordHash,
			},
		} );
	};

	oodsp.client.postUser = async ( userName, passwordHash ) => {
		await ajaxRequest( {
			url: _oodspClient.ajaxUrl,
			method: 'POST',
			data: {
				action: 'oodsp_set_user',
				_ajax_nonce: _oodspClient.nonce,
				userName,
				passwordHash,
			},
		} );
	};

	oodsp.client.deleteUser = async () => {
		await ajaxRequest( {
			url: _oodspClient.ajaxUrl,
			method: 'POST',
			data: {
				action: 'oodsp_delete_user',
				_ajax_nonce: _oodspClient.nonce,
			},
		} );
	};

	const ajaxRequest = ( options ) => {
		return new Promise( ( resolve, reject ) => {
			$.ajax( {
				...options,
				success: resolve,
				error: ( xhr, status, error ) => {
					reject(
						new HttpError(
							xhr.status,
							xhr.responseJSON.data.message || error
						)
					);
				},
			} );
		} );
	};
} )( window.jQuery );
