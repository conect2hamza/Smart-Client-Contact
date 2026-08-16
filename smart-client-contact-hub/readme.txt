=== Smart Client Contact Hub ===
Contributors: hamzadezinr
Tags: contact, floating button, leads, click to call, sms
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.2
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
