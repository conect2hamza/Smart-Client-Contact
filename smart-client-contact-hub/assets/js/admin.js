/**
 * Smart Client Contact Hub — admin behavior.
 *
 * Color pickers, media library pickers, service repeater rows,
 * field/service reordering, AJAX test email, log resend, and log delete.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		// Color pickers.
		$( '.scch-color' ).wpColorPicker();

		// Media library pickers (logo / custom icon).
		$( document ).on( 'click', '.scch-media-btn', function ( e ) {
			e.preventDefault();
			var target = $( $( this ).data( 'target' ) );
			var frame = wp.media( {
				title: scchAdmin.i18n.chooseImage,
				button: { text: scchAdmin.i18n.useThisImage },
				multiple: false
			} );
			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				target.val( attachment.url ).trigger( 'change' );
			} );
			frame.open();
		} );

		// Row reordering (form fields + services).
		$( document ).on( 'click', '.scch-move-up', function () {
			var row = $( this ).closest( 'tr' );
			row.prev( 'tr' ).before( row );
		} );
		$( document ).on( 'click', '.scch-move-down', function () {
			var row = $( this ).closest( 'tr' );
			row.next( 'tr' ).after( row );
		} );

		// Services repeater.
		var serviceIndex = $( '#scch-services-table tbody tr' ).length;
		$( '#scch-add-service' ).on( 'click', function () {
			var i = 'new-' + serviceIndex++;
			var row = $(
				'<tr>' +
					'<td class="scch-col-order">' +
						'<button type="button" class="button button-small scch-move-up" aria-label="Up">↑</button> ' +
						'<button type="button" class="button button-small scch-move-down" aria-label="Down">↓</button>' +
					'</td>' +
					'<td>' +
						'<input type="hidden" name="services[' + i + '][id]" value="" />' +
						'<input type="text" class="regular-text" name="services[' + i + '][label]" value="" required />' +
					'</td>' +
					'<td class="scch-col-actions"><button type="button" class="button button-small scch-remove-row">×</button></td>' +
				'</tr>'
			);
			$( '#scch-services-table tbody' ).append( row );
			row.find( 'input[type="text"]' ).trigger( 'focus' );
		} );
		$( document ).on( 'click', '.scch-remove-row', function () {
			if ( window.confirm( scchAdmin.i18n.confirmDel ) ) {
				$( this ).closest( 'tr' ).remove();
			}
		} );

		// Test email.
		$( '#scch-send-test' ).on( 'click', function () {
			var btn = $( this );
			var result = $( '#scch-test-result' );
			result.removeClass( 'scch-ok scch-err' ).text( scchAdmin.i18n.sending );
			btn.prop( 'disabled', true );

			$.post( scchAdmin.ajaxUrl, {
				action: 'scch_test_email',
				nonce: scchAdmin.nonce,
				recipient: $( '#scch-test-recipient' ).val()
			} )
				.done( function () {
					result.addClass( 'scch-ok' ).text( scchAdmin.i18n.sent );
				} )
				.fail( function ( xhr ) {
					var msg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : '';
					result.addClass( 'scch-err' ).text( scchAdmin.i18n.failed + ' ' + msg );
				} )
				.always( function () {
					btn.prop( 'disabled', false );
				} );
		} );

		// Resend a logged email.
		$( document ).on( 'click', '.scch-resend', function () {
			var btn = $( this );
			btn.prop( 'disabled', true ).text( scchAdmin.i18n.sending );

			$.post( scchAdmin.ajaxUrl, {
				action: 'scch_resend_email',
				nonce: scchAdmin.nonce,
				log_id: btn.data( 'log' )
			} )
				.done( function () {
					btn.text( scchAdmin.i18n.resent );
				} )
				.fail( function ( xhr ) {
					var msg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : '';
					btn.prop( 'disabled', false ).text( scchAdmin.i18n.failed + ' ' + msg );
				} );
		} );

		// Delete a single logged email entry.
		$( document ).on( 'click', '.scch-delete-log', function () {
			if ( ! window.confirm( scchAdmin.i18n.confirmDeleteLog ) ) {
				return;
			}

			var btn = $( this );
			var row = btn.closest( 'tr' );
			btn.prop( 'disabled', true ).text( scchAdmin.i18n.deleting );

			$.post( scchAdmin.ajaxUrl, {
				action: 'scch_delete_email_log',
				nonce: scchAdmin.nonce,
				log_id: btn.data( 'log' )
			} )
				.done( function () {
					row.fadeOut( 150, function () {
						row.remove();
					} );
				} )
				.fail( function ( xhr ) {
					var msg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : '';
					btn.prop( 'disabled', false ).text( scchAdmin.i18n.deleteFailed + ' ' + msg );
				} );
		} );
	} );
}( jQuery ) );
