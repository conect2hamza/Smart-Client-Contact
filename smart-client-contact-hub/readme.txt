=== Smart Client Contact Hub ===
Contributors: hamzadezinr
Tags: contact, floating button, leads, click to call, sms
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Premium floating contact widget with lead capture, built-in math CAPTCHA, lead management, and email notifications. 100% standalone.

== Description ==

Smart Client Contact Hub adds a customizable floating contact button to every page of your site. When clicked, it opens a modern popup with three options:

1. **Get Your Strategy** — a beautiful lead form (name, phone, email, service, message) protected by a built-in math CAPTCHA.
2. **Call Us** — instantly launches a phone call via tel: using your configured number.
3. **Text Us** — launches SMS via sms: using your configured mobile number.

Every submission is validated client- and server-side, stored in a custom database table, and triggers a branded notification email to the administrator plus a confirmation email to the customer.

= Completely standalone =

* No Elementor, WooCommerce, Contact Form 7, Gravity Forms, or WPForms required.
* No Google reCAPTCHA, no Cloudflare, no external CAPTCHA service — the math CAPTCHA is built in, verified server-side, single-use, and regenerates after every wrong attempt.
* No icon-font CDNs — icons are inline SVG, with custom SVG/PNG upload supported.
* Emails go through wp_mail(), so any SMTP setup you already have (Gmail, Brevo, Mailgun, Amazon SES, SendGrid, Outlook, Postmark) works automatically.

= Lead management =

* Native WordPress list table with search, status filter, sortable columns, pagination.
* Bulk delete and bulk status updates (new / contacted / qualified / closed / spam).
* CSV export (UTF-8 BOM, formula-injection safe), optionally filtered by status.

= Email system =

* Template builder for subject, heading, body, footer, logo, signature, button, and brand color.
* Placeholders: {customer_name}, {customer_email}, {customer_phone}, {service}, {message}, {submission_date}, {website_name}, {business_name}, {business_contact}, {response_time}, {view_lead_link}.
* Multiple recipients, CC, BCC, Reply-To customer, custom sender.
* Email log with status, error detail, one-click resend, per-entry delete, and a Clear All Logs option. Test email button.

= Customization =

* Position (bottom right / bottom left), animation (fade, scale, bounce, pulse, none).
* Colors, gradient or solid background, border, shadow, radius, sizes, spacing, typography, popup width, overlay, dark mode (auto/light/dark), z-index.
* Form builder: enable/disable fields, labels, placeholders, required flags, field order, success/error messages, optional thank-you redirect.
* Services manager: create, edit, delete, and sort the service dropdown.

= Security =

Nonce verification on every write, capability checks (manage_options), prepared SQL for every query, input sanitization, output escaping, honeypot field, transient-backed IP rate limiting, single-use server-side CAPTCHA tokens.

The spam defenses are layered, and it is worth being clear about what each one does. The honeypot and the IP rate limiter carry most of the weight against automated submissions. The math CAPTCHA stops naive scripted posting and gives a visible signal of intent, but the challenge is readable text in the page — a purpose-built bot can parse and solve it. It is a deliberate trade for keeping the plugin free of third-party CAPTCHA services, not a substitute for one.

= Page caching =

Fully compatible. The submission nonce and the CAPTCHA challenge are issued per visitor over AJAX when the form is opened, never printed into the cached HTML, so pages served from WP Rocket, LiteSpeed, Cloudflare, Varnish or any other full-page cache behave exactly like uncached ones.

= Extensibility =

* `scch_render_widget` filter — hide the widget on specific pages.
* `scch_channels` filter — register additional channels (WhatsApp, Telegram, …) without touching plugin core.
* `scch_lead_created` action — integrate with CRMs or automation after a lead is stored.

== Installation ==

1. Upload the plugin ZIP via Plugins → Add New → Upload Plugin, then activate it.
2. Go to Contact Hub → Contact Settings and add your phone and SMS numbers.
3. Go to Contact Hub → Services and define your service list.
4. Go to Contact Hub → Notifications, confirm the recipient, and send a test email.
5. The widget appears automatically on every public page.

== Frequently Asked Questions ==

= Does it need any other plugin? =

No. It is fully standalone and uses only core WordPress APIs.

= Which SMTP services are supported? =

All of them that integrate with wp_mail() — Gmail SMTP, Brevo, Mailgun, Amazon SES, SendGrid, Outlook, Postmark, and others. The plugin never implements its own SMTP layer.

= Where are leads stored? =

In a dedicated custom table (client_leads with your site's table prefix), plus an email log table. On uninstall you choose whether settings and/or leads are deleted or kept.

= Is the CAPTCHA accessible? =

The challenge is plain text (e.g. "2 + 3 = ?") with a proper label, keyboard focusable, and screen-reader friendly. Answers are verified server-side.

== Changelog ==

= 1.0.3 =
* Fix: the form no longer breaks behind a full-page cache. The CAPTCHA challenge and the submission nonce were printed into the page HTML, so every visitor served the same cached page shared one single-use token — the first submission consumed it and everyone after it was told their correct answer was wrong. Both are now issued per visitor over AJAX when the form is opened.
* Performance: generating that challenge on every page render wrote two rows into wp_options for every single page view, cached or not, whether or not the visitor ever opened the widget. Challenges are now created only when a form is actually opened.
* Security: the challenge endpoint is rate limited, closing an unauthenticated path that could be looped to inflate the options table. Challenge and submission limits are counted separately, so opening the form never consumes a visitor's submission allowance.
* Security: the Appearance font-family value is restricted to characters valid in a font stack. It is printed into a stylesheet on every public page, and the previous escaping allowed an administrator to break out of the declaration and inject arbitrary CSS site-wide (relevant on multisite, where site administrators do not have unfiltered_html).
* Fix: phone and email are validated against the storage column widths. Over-length values previously aborted the insert under MySQL strict mode, losing the lead and showing the visitor a generic failure.
* Fix: a rejected nonce no longer fails silently. The bare "-1" response parsed as valid JSON, so the form displayed nothing at all; it now reports an expired session and fetches a fresh challenge.
* Fix: no more PHP warning on every wp-admin page load for users below manage_options. add_submenu_page() returns false without the capability, and the Leads screen hook was read unconditionally.
* Fix: submissions are rejected when the form channel is switched off, instead of only hiding the button.
* Fix: the scch_triggers option and the per-user Leads screen option are removed on uninstall when "Delete plugin settings" is selected.
* Performance: CSV export streams in batches instead of loading every lead into memory, and the email log table gains an index on created_at.
* Compat: translations load on init rather than plugins_loaded, avoiding the WordPress 6.7+ notice for loading a text domain too early.
* Housekeeping: removed the unused scch_flush_needed option and a duplicate bulk-action nonce field, regenerated the translation template, and corrected the readme's description of what the math CAPTCHA does and does not stop.

= 1.0.2 =
* New: delete individual email log entries from the Logs screen (AJAX, capability + nonce checked).
* New: "Clear All Logs" button to remove every email log entry at once (capability + nonce checked, confirmation required).

= 1.0.1 =
* Hardening: logs/.htaccess now blocks direct access on Apache 2.4+ (previously used Apache 2.2-only syntax that modern Apache ignores).
* Hardening: submission rate limiter uses an atomic object-cache counter when a persistent cache backend (Redis/Memcached) is available, closing a race condition that could let a scripted burst slip a few extra submissions past the configured limit.
* Fix: added the bulk-action nonce field on the Leads screen so bulk delete / bulk status-change actions verify correctly.
* Docs: added a note on the CAPTCHA/rate-limiting screen that limits are IP-based and may behave differently behind a proxy or CDN that doesn't forward the real client IP.

= 1.0 =
* Initial release: floating contact widget with lead form, built-in math CAPTCHA, lead management with CSV export, email templates and logs with resend, rate limiting.
* Appearance controls: brand colors, panel text, heading background/text, submit button colors including hover states, dark mode text color.
* Form Builder with per-field Hide Label option.
* Universal External Trigger System: window.SCCH JavaScript API, custom browser events, data-scch-open attribute, admin-defined CSS selectors, [scch_trigger] shortcode, developer hooks.
* Translation-ready.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
