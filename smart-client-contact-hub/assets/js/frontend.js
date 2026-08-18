/**
 * Smart Client Contact Hub — frontend widget.
 * Vanilla JS. No dependencies.
 */
( function () {
	'use strict';

	var config = window.scchConfig || {};

	// Fallbacks so validation still speaks if the localized strings never
	// arrive — a stripped script, a stale cached page.
	config.i18n = Object.assign( {
		sending: 'Sending…',
		netError: 'Network error. Please try again.',
		expired: 'Your session expired. Please try sending again.',
		required: 'This field is required.',
		nameLength: 'Name must be between 3 and 80 characters.',
		messageLength: 'Message must be 1000 characters or fewer.',
		invalidEmail: 'Please enter a valid email address.',
		invalidUrl: 'Please enter a valid web address.',
		invalidNumber: 'Please enter a number.',
		numberOnly: 'Answer must be a number.'
	}, config.i18n || {} );
	var root, launcher, panel, overlay, form, feedback, submitBtn;
	var lastFocused = null;

	// Timestamp of the last successful challenge fetch. The nonce and the
	// CAPTCHA token both come from that call, so re-fetching on a stale
	// value is what keeps the form working on cached pages.
	var challengeAt = 0;
	var CHALLENGE_TTL = 10 * 60 * 1000;

	/* ---------- Attribution ---------- */

	/**
	 * Where this visitor came from, read at submit time from the page they
	 * are on. Nothing is tracked across pages and nothing is sent anywhere
	 * except with the lead itself.
	 *
	 * The UTM values and referrer are remembered in sessionStorage on first
	 * load so they survive the visitor browsing to another page before
	 * submitting — still first-party, still cleared when the tab closes.
	 */
	var UTM_KEYS = [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' ];
	var STORE_KEY = 'scch_attr';

	function readStore() {
		try {
			return JSON.parse( window.sessionStorage.getItem( STORE_KEY ) ) || null;
		} catch ( e ) { return null; }
	}

	function captureAttribution() {
		var stored = readStore();
		if ( stored ) { return stored; }

		var params = {};
		try {
			var search = new URLSearchParams( window.location.search );
			UTM_KEYS.forEach( function ( key ) {
				var value = search.get( key );
				if ( value ) { params[ key ] = value.slice( 0, 100 ); }
			} );
		} catch ( e ) { /* No URLSearchParams: skip UTM capture. */ }

		var attr = {
			landing_page: window.location.href.slice( 0, 255 ),
			referrer: ( document.referrer || '' ).slice( 0, 255 ),
			device: deviceType(),
			returning: 0
		};

		UTM_KEYS.forEach( function ( key ) { attr[ key ] = params[ key ] || ''; } );

		try {
			// A marker from an earlier visit means this is a return visitor.
			attr.returning = window.localStorage.getItem( STORE_KEY + '_seen' ) ? 1 : 0;
			window.localStorage.setItem( STORE_KEY + '_seen', '1' );
			window.sessionStorage.setItem( STORE_KEY, JSON.stringify( attr ) );
		} catch ( e ) { /* Storage blocked: attribution still works for this page. */ }

		return attr;
	}

	function deviceType() {
		var w = window.innerWidth || document.documentElement.clientWidth || 0;
		if ( w > 0 && w < 768 ) { return 'mobile'; }
		if ( w >= 768 && w < 1024 ) { return 'tablet'; }
		return 'desktop';
	}

	function qs( sel, ctx ) { return ( ctx || document ).querySelector( sel ); }
	function qsa( sel, ctx ) { return Array.prototype.slice.call( ( ctx || document ).querySelectorAll( sel ) ); }

	function init() {
		root = qs( '#scch-root' );
		if ( ! root ) { return; }

		launcher = qs( '#scch-launcher' );
		panel    = qs( '#scch-panel' );
		overlay  = qs( '#scch-overlay' );
		form     = qs( '#scch-form' );
		feedback = form ? qs( '.scch-form-feedback', form ) : null;
		submitBtn = form ? qs( '.scch-submit', form ) : null;

		launcher.addEventListener( 'click', toggle );
		overlay.addEventListener( 'click', close );

		qsa( '[data-scch-close]', panel ).forEach( function ( el ) {
			el.addEventListener( 'click', close );
		} );

		qsa( '[data-scch-goto]', panel ).forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				showView( el.getAttribute( 'data-scch-goto' ) );
			} );
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! panel.hidden ) { close(); }
			if ( 'Tab' === e.key && ! panel.hidden ) { trapFocus( e ); }
		} );

		if ( form ) {
			form.addEventListener( 'submit', submit );
			var refresh = qs( '.scch-captcha-refresh', form );
			if ( refresh ) { refresh.addEventListener( 'click', fetchChallenge ); }
		}

		initExternalTriggers();
		captureAttribution();
	}

	/* ---------- External Trigger System ---------- */

	function isOpen() {
		return !! ( panel && ! panel.hidden );
	}

	function safeOpen()   { if ( panel && panel.hidden ) { open( false ); } }
	function safeClose()  { if ( panel && ! panel.hidden ) { close(); } }
	function safeToggle() { if ( panel ) { toggle(); } }

	function initExternalTriggers() {
		var triggers  = window.scchTriggers || {};
		var selectors = [];

		// Keep only selectors this browser can parse; invalid ones are dropped.
		if ( triggers.enabled && Array.isArray( triggers.selectors ) ) {
			triggers.selectors.forEach( function ( sel ) {
				try {
					document.querySelector( sel );
					if ( selectors.indexOf( sel ) === -1 ) { selectors.push( sel ); }
				} catch ( e ) { /* invalid selector: ignore */ }
			} );
		}

		// Single delegated listener: covers data-scch-open, admin-defined
		// selectors, and elements injected after page load (AJAX, builders).
		if ( triggers.enabled ) {
			document.addEventListener( 'click', function ( e ) {
				var el = e.target && e.target.closest ? e.target.closest( '[data-scch-open]' ) : null;

				if ( ! el ) {
					for ( var i = 0; i < selectors.length; i++ ) {
						try {
							el = e.target.closest( selectors[ i ] );
						} catch ( err ) { el = null; }
						if ( el ) { break; }
					}
				}

				if ( ! el || root.contains( el ) ) { return; }
				e.preventDefault();
				safeOpen();
			} );
		}

		// Custom browser events — always available.
		window.addEventListener( 'scch:open', safeOpen );
		window.addEventListener( 'scch:close', safeClose );
		window.addEventListener( 'scch:toggle', safeToggle );

		if ( triggers.autoOpen ) { safeOpen(); }
	}

	function toggle() { panel.hidden ? open() : close(); }

	/**
	 * @param {boolean} focusFirst When false (external triggers), focus moves
	 * to the dialog container instead of highlighting the first option.
	 */
	function open( focusFirst ) {
		lastFocused = document.activeElement;
		panel.hidden = false;
		overlay.hidden = false;
		launcher.setAttribute( 'aria-expanded', 'true' );
		qs( '.scch-icon-open', launcher ).hidden = true;
		qs( '.scch-icon-close', launcher ).hidden = false;
		showView( 'channels' );
		if ( false === focusFirst ) {
			panel.focus();
			return;
		}
		var first = qs( '.scch-channel', panel ) || qs( '.scch-close', panel );
		if ( first ) { first.focus(); }
	}

	function close() {
		panel.hidden = true;
		overlay.hidden = true;
		launcher.setAttribute( 'aria-expanded', 'false' );
		qs( '.scch-icon-open', launcher ).hidden = false;
		qs( '.scch-icon-close', launcher ).hidden = true;
		if ( lastFocused && lastFocused.focus ) { lastFocused.focus(); }
	}

	function showView( name ) {
		qsa( '.scch-view', panel ).forEach( function ( view ) {
			view.hidden = view.getAttribute( 'data-scch-view' ) !== name;
		} );
		if ( 'form' === name ) {
			ensureChallenge();
			var formView = qs( '[data-scch-view="form"]', panel );
			var firstInput = formView ? qs( 'input, select, textarea', formView ) : null;
			if ( firstInput ) { firstInput.focus(); }
		}
	}

	function trapFocus( e ) {
		var focusables = qsa(
			'button, [href], input:not([tabindex="-1"]), select, textarea',
			panel
		).filter( function ( el ) { return null === el.closest( '[hidden]' ) || el.closest( '[hidden]' ) === el; } )
		 .filter( function ( el ) { return ! el.hidden && null !== el.offsetParent; } );

		if ( ! focusables.length ) { return; }

		var first = focusables[ 0 ];
		var last  = focusables[ focusables.length - 1 ];

		if ( e.shiftKey && document.activeElement === first ) {
			e.preventDefault();
			last.focus();
		} else if ( ! e.shiftKey && document.activeElement === last ) {
			e.preventDefault();
			first.focus();
		}
	}

	/* ---------- Validation ---------- */

	function clearErrors() {
		qsa( '.scch-field', form ).forEach( function ( field ) {
			field.classList.remove( 'scch-invalid' );
			var err = qs( '.scch-field-error', field );
			if ( err ) { err.hidden = true; err.textContent = ''; }
		} );
		if ( feedback ) { feedback.hidden = true; feedback.textContent = ''; }
	}

	function setFieldError( key, message ) {
		var field = qs( '.scch-field[data-field="' + key + '"]', form );
		if ( ! field ) { return; }
		field.classList.add( 'scch-invalid' );
		var err = qs( '.scch-field-error', field );
		if ( err ) { err.textContent = message; err.hidden = false; }
	}

	function validateClient() {
		var ok = true;

		qsa( '.scch-field', form ).forEach( function ( field ) {
			var key = field.getAttribute( 'data-field' );

			// Radio and checkbox sets have no single input to read; they pass
			// when at least one box in the group is ticked.
			var group = qsa( 'input[data-scch-required="1"]', field );
			if ( group.length ) {
				var picked = group.some( function ( box ) { return box.checked; } );
				if ( ! picked ) {
					setFieldError( key, config.i18n.required );
					ok = false;
				}
				return;
			}

			var input = qs( 'input, select, textarea', field );
			if ( ! input ) { return; }

			var required = input.hasAttribute( 'required' );

			if ( 'checkbox' === input.type ) {
				if ( required && ! input.checked ) {
					setFieldError( key, config.i18n.required );
					ok = false;
				}
				return;
			}

			var value = input.value.trim();

			if ( required && '' === value ) {
				setFieldError( key, input.validationMessage || config.i18n.required );
				ok = false;
				return;
			}
			if ( '' === value ) { return; }

			if ( 'name' === key && ( value.length < 3 || value.length > 80 ) ) {
				setFieldError( key, config.i18n.nameLength );
				ok = false;
			}
			if ( 'message' === key && value.length > 1000 ) {
				setFieldError( key, config.i18n.messageLength );
				ok = false;
			}
			if ( 'captcha' === key && ! /^[0-9]+$/.test( value ) ) {
				setFieldError( key, config.i18n.numberOnly );
				ok = false;
			}

			// Everything else is checked by the input's own type, so a custom
			// email or website field validates the same way a core one does.
			if ( 'email' === input.type && ! /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test( value ) ) {
				setFieldError( key, config.i18n.invalidEmail );
				ok = false;
			}
			if ( 'url' === input.type && ! /^(https?:\/\/)?[^\s.]+\.[^\s]{2,}$/.test( value ) ) {
				setFieldError( key, config.i18n.invalidUrl );
				ok = false;
			}
			if ( 'number' === input.type && isNaN( Number( value ) ) ) {
				setFieldError( key, config.i18n.invalidNumber );
				ok = false;
			}
		} );

		return ok;
	}

	/* ---------- Challenge (nonce + CAPTCHA) ---------- */

	/**
	 * Fetch a challenge unless a recent one is still good. Called when the
	 * form view opens, so a visitor on a cached page always submits with a
	 * nonce and token minted for them rather than baked into the HTML.
	 */
	function ensureChallenge() {
		if ( challengeAt && ( Date.now() - challengeAt ) < CHALLENGE_TTL ) { return; }
		fetchChallenge();
	}

	function fetchChallenge() {
		var body = new FormData();
		body.append( 'action', 'scch_refresh_captcha' );

		return fetch( config.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( res ) {
				if ( res && true === res.success ) {
					challengeAt = Date.now();
					applyChallenge( res.data );
				}
			} )
			.catch( function () { /* Keep the current question on network failure. */ } );
	}

	function applyChallenge( data ) {
		if ( ! data ) { return; }

		if ( data.nonce ) { config.nonce = data.nonce; }

		var q = qs( '#scch-captcha-question' );
		var t = qs( '#scch-captcha-token' );
		var a = qs( '#scch-captcha-answer' );
		if ( q && t && data.token ) {
			q.textContent = data.question;
			t.value = data.token;
			if ( a ) { a.value = ''; }
		}
	}

	/* ---------- Submit ---------- */

	function submit( e ) {
		e.preventDefault();
		clearErrors();

		if ( ! validateClient() ) { return; }

		var body = new FormData( form );
		body.append( 'action', 'scch_submit_lead' );
		body.append( 'nonce', config.nonce );

		var attr = captureAttribution();
		Object.keys( attr ).forEach( function ( key ) {
			body.append( key, attr[ key ] );
		} );

		submitBtn.disabled = true;
		var original = submitBtn.textContent;
		submitBtn.textContent = ( config.i18n && config.i18n.sending ) || 'Sending…';

		fetch( config.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( res ) {
				if ( res && true === res.success ) {
					showSuccess( res.data );
					return;
				}

				// A rejected nonce is answered with a bare -1, which parses as
				// valid JSON but carries no data. Without this branch the form
				// would fail completely silently. Pull a fresh challenge so the
				// visitor's next attempt succeeds.
				if ( ! res || 'object' !== typeof res || ! res.data ) {
					showFeedback( ( config.i18n && config.i18n.expired ) || 'Your session expired. Please try again.' );
					challengeAt = 0;
					fetchChallenge();
					return;
				}

				showErrors( res.data );
			} )
			.catch( function () {
				showFeedback( ( config.i18n && config.i18n.netError ) || 'Network error. Please try again.' );
			} )
			.finally( function () {
				submitBtn.disabled = false;
				submitBtn.textContent = original;
			} );
	}

	function showFeedback( message ) {
		if ( ! feedback ) { return; }
		feedback.textContent = message;
		feedback.hidden = false;
	}

	function showErrors( data ) {
		if ( data.errors ) {
			Object.keys( data.errors ).forEach( function ( key ) {
				setFieldError( key, data.errors[ key ] );
			} );
		}
		if ( data.message ) { showFeedback( data.message ); }
		// Server always issues a fresh challenge after any validation failure,
		// so the token in the form is current again — no need to re-fetch.
		if ( data.captcha ) {
			applyChallenge( data.captcha );
			challengeAt = Date.now();
		}

		var firstInvalid = qs( '.scch-invalid input, .scch-invalid select, .scch-invalid textarea', form );
		if ( firstInvalid ) { firstInvalid.focus(); }
	}

	function showSuccess( data ) {
		var view = qs( '[data-scch-view="success"]', panel );
		qs( '.scch-success-message', view ).textContent = data.message || '';
		showView( 'success' );
		form.reset();

		// The token was consumed by this submission. Force a new challenge if
		// the visitor opens the form again.
		challengeAt = 0;

		if ( data.redirect ) {
			window.setTimeout( function () { window.location.assign( data.redirect ); }, 1600 );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	// Public API. Methods are safe no-ops if the widget isn't on the page.
	window.SCCH = {
		open:   function () { safeOpen(); },
		close:  function () { safeClose(); },
		toggle: function () { safeToggle(); },
		isOpen: function () { return isOpen(); }
	};
}() );
