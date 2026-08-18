/**
 * Smart Client Contact Hub — admin behavior.
 *
 * Color pickers, media library pickers, service/channel/form-field repeaters,
 * row reordering, AJAX test email, log resend, and log delete.
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
		$( document ).on( 'click', 'tr .scch-move-up', function () {
			var row = $( this ).closest( 'tr' );
			row.prev( 'tr' ).before( row );
		} );
		$( document ).on( 'click', 'tr .scch-move-down', function () {
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

		// Channel repeater.
		var channelIndex = $( '#scch-channel-list .scch-channel-row' ).length;

		$( '#scch-add-channel' ).on( 'click', function () {
			var type = $( '#scch-new-channel-type' ).val();
			var tpl = $( '.scch-channel-template[data-type="' + type + '"]' ).html();
			if ( ! tpl ) { return; }

			// Templates are rendered server-side with a placeholder index so
			// the field markup lives in one place. Swap it for a unique one.
			var row = $( tpl.replace( /__INDEX__/g, 'new-' + channelIndex++ ) );
			$( '#scch-channel-list' ).append( row );
			row.find( '.scch-color' ).wpColorPicker();
			row.find( 'input[type="text"]' ).first().trigger( 'focus' );
			row.get( 0 ).scrollIntoView( { behavior: 'smooth', block: 'center' } );
		} );

		$( document ).on( 'click', '.scch-remove-channel', function () {
			if ( window.confirm( scchAdmin.i18n.confirmDeleteChannel ) ) {
				$( this ).closest( '.scch-channel-row' ).remove();
			}
		} );

		// Reorder channel cards (the services repeater uses table rows).
		$( document ).on( 'click', '.scch-channel-row .scch-move-up', function () {
			var row = $( this ).closest( '.scch-channel-row' );
			row.prev( '.scch-channel-row' ).before( row );
		} );
		$( document ).on( 'click', '.scch-channel-row .scch-move-down', function () {
			var row = $( this ).closest( '.scch-channel-row' );
			row.next( '.scch-channel-row' ).after( row );
		} );

		// Form field repeater.
		var fieldIndex = $( '#scch-field-list .scch-fieldrow' ).length;

		/**
		 * Show only the settings that make sense for a card's current type.
		 * The flags ride on the type <option>s so the server stays the single
		 * source of truth for what each type supports.
		 */
		function syncFieldType( row ) {
			var select = row.find( '.scch-field-type' );
			var type = select.length ? select.val() : row.data( 'type' );
			var opt = select.length ? select.find( 'option:selected' ) : null;
			var hasOptions = opt ? '1' === String( opt.data( 'options' ) ) : false;
			var hasPlaceholder = opt ? '1' === String( opt.data( 'placeholder' ) ) : true;

			row.attr( 'data-type', type );

			// A built-in field has no type select; its panels are already
			// rendered correctly and must not be hidden here.
			if ( ! select.length ) { return; }

			row.find( '[data-when="options"]' ).prop( 'hidden', ! hasOptions );
			row.find( '[data-when="placeholder"]' ).prop( 'hidden', ! hasPlaceholder );
			row.find( '[data-when="hidden"]' ).prop( 'hidden', 'hidden' !== type );
		}

		$( '#scch-add-field' ).on( 'click', function () {
			var type = $( '#scch-new-field-type' ).val();
			var tpl = $( '.scch-field-template[data-type="' + type + '"]' ).html();
			if ( ! tpl ) { return; }

			var row = $( tpl.replace( /__INDEX__/g, 'new-' + fieldIndex++ ) );
			$( '#scch-field-list' ).append( row );
			syncFieldType( row );
			row.find( '.scch-field-label' ).trigger( 'focus' );
			row.get( 0 ).scrollIntoView( { behavior: 'smooth', block: 'center' } );
		} );

		$( document ).on( 'click', '.scch-remove-field', function () {
			if ( window.confirm( scchAdmin.i18n.confirmDeleteField ) ) {
				$( this ).closest( '.scch-fieldrow' ).remove();
			}
		} );

		$( document ).on( 'change', '.scch-field-type', function () {
			syncFieldType( $( this ).closest( '.scch-fieldrow' ) );
		} );

		// Keep the card heading in step with the label as it is typed, so a
		// long list of collapsed cards stays readable.
		$( document ).on( 'input', '.scch-field-label', function () {
			var row = $( this ).closest( '.scch-fieldrow' );
			row.find( '.scch-fieldrow__title' ).text( $( this ).val() || scchAdmin.i18n.untitledField );
		} );

		$( document ).on( 'change', '.scch-field-enabled', function () {
			$( this ).closest( '.scch-fieldrow' ).toggleClass( 'is-off', ! this.checked );
		} );

		// Reorder field cards. The stored order is the submitted order, so
		// moving a card is all it takes.
		$( document ).on( 'click', '.scch-fieldrow .scch-move-up', function () {
			var row = $( this ).closest( '.scch-fieldrow' );
			row.prev( '.scch-fieldrow' ).before( row );
		} );
		$( document ).on( 'click', '.scch-fieldrow .scch-move-down', function () {
			var row = $( this ).closest( '.scch-fieldrow' );
			row.next( '.scch-fieldrow' ).after( row );
		} );

		// The custom image field only matters when "your own image" is picked.
		$( document ).on( 'change', '.scch-iconpick__radio', function () {
			var picker = $( this ).closest( '.scch-iconpick' );
			picker.find( '.scch-iconpick__custom' ).prop( 'hidden', 'custom' !== $( this ).val() );
		} );

		// Choosing an image implies the custom option, and previews it.
		$( document ).on( 'change', '.scch-iconpick__custom input[type="url"]', function () {
			var picker = $( this ).closest( '.scch-iconpick' );
			var url = $( this ).val();
			var swatch = picker.find( '.scch-iconpick__opt--custom' );

			picker.find( '.scch-iconpick__radio--custom' ).prop( 'checked', true );

			if ( url ) {
				swatch.html( $( '<img>' ).attr( { src: url, alt: '' } ) );
			}
		} );

		// Dim a row that is switched off so the state reads at a glance.
		$( document ).on( 'change', '.scch-channel-enabled', function () {
			$( this ).closest( '.scch-channel-row' ).toggleClass( 'is-off', ! this.checked );
		} );

		/* ---------- Toasts ---------- */

		var toastHost = null;

		function toast( message, isError ) {
			if ( ! toastHost ) {
				toastHost = $( '<div class="scch-toasts" role="status" aria-live="polite"></div>' ).appendTo( document.body );
			}

			var el = $( '<div class="scch-toast"></div>' )
				.toggleClass( 'scch-toast--error', !! isError )
				.append( $( '<span class="scch-toast__mark" aria-hidden="true"></span>' ).text( isError ? '!' : '✓' ) )
				.append( $( '<span></span>' ).text( message ) )
				.appendTo( toastHost );

			window.setTimeout( function () {
				el.fadeOut( 180, function () { el.remove(); } );
			}, isError ? 6000 : 3200 );
		}

		/**
		 * Call the single CRM endpoint. Every task goes through one nonce and
		 * one capability check on the server.
		 */
		function crm( task, data ) {
			return $.post( scchAdmin.ajaxUrl, $.extend( {
				action: 'scch_crm_action',
				nonce: scchAdmin.nonce,
				task: task
			}, data ) );
		}

		function crmFail( xhr ) {
			var msg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
				? xhr.responseJSON.data.message
				: scchAdmin.i18n.genericError;
			toast( msg, true );
		}

		/* ---------- Lead actions ---------- */

		$( document ).on( 'change', '.scch-stage-select', function () {
			var select = $( this );
			var lead = select.data( 'lead' );
			var stage = select.val();

			select.prop( 'disabled', true );

			crm( 'status', { lead_id: lead, status: stage } )
				.done( function ( res ) {
					toast( ( res.data && res.data.message ) || scchAdmin.i18n.stageChanged );

					// On the board, move the card into its new column.
					var card = select.closest( '.ui-lead-card' );
					var target = $( '.ui-col[data-stage="' + stage + '"] .ui-col__body' );
					if ( card.length && target.length ) {
						card.appendTo( target );
						refreshCounts();
					}
				} )
				.fail( crmFail )
				.always( function () { select.prop( 'disabled', false ); } );
		} );

		$( document ).on( 'change', '.scch-assign-select', function () {
			var select = $( this );
			crm( 'assign', { lead_id: select.data( 'lead' ), user_id: select.val() } )
				.done( function () { toast( scchAdmin.i18n.assigned ); } )
				.fail( crmFail );
		} );

		$( document ).on( 'submit', '.scch-note-form', function ( e ) {
			e.preventDefault();
			var form = $( this );
			var field = form.find( 'textarea' );

			if ( ! $.trim( field.val() ) ) { return; }

			form.find( 'button' ).prop( 'disabled', true );

			crm( 'note', { lead_id: form.data( 'lead' ), note: field.val() } )
				.done( function () {
					toast( scchAdmin.i18n.noteAdded );
					window.location.reload();
				} )
				.fail( function ( xhr ) {
					crmFail( xhr );
					form.find( 'button' ).prop( 'disabled', false );
				} );
		} );

		$( document ).on( 'submit', '.scch-followup-form', function ( e ) {
			e.preventDefault();
			var form = $( this );

			crm( 'followup', {
				lead_id: form.data( 'lead' ),
				title: form.find( '[name="title"]' ).val(),
				due_at: form.find( '[name="due_at"]' ).val(),
				priority: form.find( '[name="priority"]' ).val(),
				notes: form.find( '[name="notes"]' ).val() || ''
			} )
				.done( function () {
					toast( scchAdmin.i18n.followupSet );
					window.location.reload();
				} )
				.fail( crmFail );
		} );

		$( document ).on( 'submit', '.scch-value-form', function ( e ) {
			e.preventDefault();
			var form = $( this );

			crm( 'value', {
				lead_id: form.data( 'lead' ),
				estimated_value: form.find( '[name="estimated_value"]' ).val() || 0,
				actual_revenue: form.find( '[name="actual_revenue"]' ).val() || 0
			} )
				.done( function () { toast( scchAdmin.i18n.saved ); } )
				.fail( crmFail );
		} );

		$( document ).on( 'click', '.scch-rescore', function () {
			var btn = $( this );
			btn.prop( 'disabled', true );

			crm( 'rescore', { lead_id: btn.data( 'lead' ) } )
				.done( function () {
					toast( scchAdmin.i18n.saved );
					window.location.reload();
				} )
				.fail( function ( xhr ) {
					crmFail( xhr );
					btn.prop( 'disabled', false );
				} );
		} );

		$( document ).on( 'click', '.scch-followup-done', function () {
			var btn = $( this );
			var id = btn.data( 'followup' );
			btn.prop( 'disabled', true );

			crm( 'followup_done', { followup_id: id } )
				.done( function () {
					toast( scchAdmin.i18n.followupDone );
					$( '[data-followup-row="' + id + '"], tr[data-followup="' + id + '"]' )
						.fadeOut( 180, function () { $( this ).remove(); } );
				} )
				.fail( function ( xhr ) {
					crmFail( xhr );
					btn.prop( 'disabled', false );
				} );
		} );

		/* ---------- Pipeline drag and drop ---------- */

		// Dragging is an enhancement; each card also carries a stage select,
		// so the board is fully operable from the keyboard without this.
		var dragged = null;

		$( document ).on( 'dragstart', '.ui-lead-card', function ( e ) {
			dragged = this;
			$( this ).addClass( 'is-dragging' );
			if ( e.originalEvent.dataTransfer ) {
				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData( 'text/plain', String( $( this ).data( 'lead' ) ) );
			}
		} );

		$( document ).on( 'dragend', '.ui-lead-card', function () {
			$( this ).removeClass( 'is-dragging' );
			$( '.ui-col' ).removeClass( 'is-over' );
			dragged = null;
		} );

		$( document ).on( 'dragover', '.ui-col', function ( e ) {
			if ( ! dragged ) { return; }
			e.preventDefault();
			$( this ).addClass( 'is-over' );
		} );

		$( document ).on( 'dragleave', '.ui-col', function () {
			$( this ).removeClass( 'is-over' );
		} );

		$( document ).on( 'drop', '.ui-col', function ( e ) {
			if ( ! dragged ) { return; }
			e.preventDefault();

			var col = $( this );
			var card = $( dragged );
			var stage = col.data( 'stage' );

			col.removeClass( 'is-over' );

			if ( card.closest( '.ui-col' ).data( 'stage' ) === stage ) { return; }

			card.appendTo( col.find( '.ui-col__body' ) );
			card.find( '.scch-stage-select' ).val( stage );
			refreshCounts();

			crm( 'status', { lead_id: card.data( 'lead' ), status: stage } )
				.done( function ( res ) { toast( ( res.data && res.data.message ) || scchAdmin.i18n.stageChanged ); } )
				.fail( function ( xhr ) {
					crmFail( xhr );
					window.location.reload();
				} );
		} );

		function refreshCounts() {
			$( '.ui-col' ).each( function () {
				var col = $( this );
				col.find( '.ui-col__count' ).text( col.find( '.ui-lead-card' ).length );
			} );
		}

		/* ---------- Sticky save bar ---------- */

		$( '.scch-dirty-watch' ).each( function () {
			var form = $( this );
			var bar = form.find( '.scch-savebar' );
			var note = bar.find( '.scch-savebar__note' );
			var dirty = false;

			form.on( 'change input', 'input, select, textarea', function () {
				if ( dirty ) { return; }
				dirty = true;
				bar.removeClass( 'is-clean' );
				note.text( scchAdmin.i18n.unsaved );
			} );

			form.on( 'submit', function () { dirty = false; } );

			$( window ).on( 'beforeunload', function () {
				if ( dirty ) { return scchAdmin.i18n.leaveWarning; }
			} );
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
