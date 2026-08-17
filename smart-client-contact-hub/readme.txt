=== Smart Client Contact Hub ===
Contributors: hamzadezinr
Tags: contact, floating button, leads, click to call, sms
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Premium floating contact widget with lead capture, built-in math CAPTCHA, lead management, and email notifications. 100% standalone.

== Description ==

Smart Client Contact Hub adds a customizable floating contact button to every page of your site. When clicked, it opens a modern popup listing the ways a visitor can reach you — you decide which, how many, what each one says, and how each one looks.

= Contact channels =

Build the popup from as many buttons as you need on the Channels screen:

* **Lead form** — name, phone, email, service, message, protected by a built-in math CAPTCHA.
* **Phone call** — launches a call with tel:.
* **SMS** — opens the messaging app, optionally with your message pre-filled.
* **WhatsApp** — opens wa.me, optionally with your message pre-filled.
* **Telegram** — a @username or a full t.me link.
* **Facebook Messenger** — a Page username or a full m.me link.
* **Email** — opens the visitor's mail app, optionally with the subject pre-filled.
* **Custom link** — anything else: Viber, Skype, Calendly, a booking page. Pair it with your own uploaded icon.

Every button has its own editable text, an optional line of small print under it, its own icon, and its own icon color, icon tile color, and text color. Any button can be switched off without deleting it, and the order is yours to set. Add the same type more than once — two WhatsApp numbers for two departments works fine.

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

The Appearance screen exposes every visual value in the widget — 117 controls across eleven tabs, with no CSS required:

* **Layout & Behavior** — corner, launcher animation, edge distance, z-index, light/dark/auto color scheme.
* **Brand Colors** — primary, secondary, gradient angle, and whether the launcher uses the gradient or a solid fill.
* **Floating Button** — icon (built-in or your own upload), button and icon size, icon color, hover background and hover icon color, corner rounding, border width and color, and a shadow you control by color, opacity, blur, and offset.
* **Typography** — separate body and heading font stacks, base size, line height, letter spacing.
* **Popup Container** — width, corner radius, panel background, body/muted/divider colors, panel border, content padding, panel shadow, and an overlay with its own color, opacity, and blur.
* **Popup Header** — background, text color, padding, title size and weight, intro size and color, and close-button color, background, hover background, size, and rounding.
* **Channel Buttons** — background, label color, size and weight, border width and color, corner radius, padding, spacing between buttons, hover background and border, plus the icon tile's background, glyph color, tile size, glyph size, and tile radius.
* **Form Fields** — form title size and weight, back-link color, label color/size/weight, spacing between fields, and full control of inputs: background, text, placeholder, border width and color, radius, padding, text size, focus highlight color, and the CAPTCHA question's background and text.
* **Submit Button** — background, text, hover background and hover text, radius, padding, text size and weight, border width and color.
* **Messages & Success** — error and success colors, success check color, circle size, and message size.
* **Dark Mode Palette** — panel background, body text, muted text, dividers, input background, and channel background for dark mode specifically.

Any color left empty falls back to the shipped design, so you only set what you want to change. A Reset button restores every Appearance value at once.

Other customization:

* Form builder: enable/disable fields, labels, placeholders, required flags, field order, success/error messages, optional thank-you redirect.
* Services manager: create, edit, delete, and sort the service dropdown.

= Security =

Nonce verification on every write, capability checks (manage_options), prepared SQL for every query, input sanitization, output escaping, honeypot field, transient-backed IP rate limiting, single-use server-side CAPTCHA tokens.

The spam defenses are layered, and it is worth being clear about what each one does. The honeypot and the IP rate limiter carry most of the weight against automated submissions. The math CAPTCHA stops naive scripted posting and gives a visible signal of intent, but the challenge is readable text in the page — a purpose-built bot can parse and solve it. It is a deliberate trade for keeping the plugin free of third-party CAPTCHA services, not a substitute for one.

= Page caching =

Fully compatible. The submission nonce and the CAPTCHA challenge are issued per visitor over AJAX when the form is opened, never printed into the cached HTML, so pages served from WP Rocket, LiteSpeed, Cloudflare, Varnish or any other full-page cache behave exactly like uncached ones.

= Extensibility =

* `scch_render_widget` filter — hide the widget on specific pages.
* `scch_channels` filter — register extra channels in code; they are appended after the ones configured in the admin.
* `scch_lead_created` action — integrate with CRMs or automation after a lead is stored.

== Installation ==

1. Upload the plugin ZIP via Plugins → Add New → Upload Plugin, then activate it.
2. Go to Contact Hub → Channels and set up the buttons you want: the lead form, a phone number, WhatsApp, and anything else.
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

= 1.0.5 =
* New: Channels screen. The popup is no longer fixed at three buttons — add as many as you want, of eight types: lead form, phone call, SMS, WhatsApp, Telegram, Facebook Messenger, email, and custom link. The same type can be added more than once, so two WhatsApp numbers for two departments is just two rows.
* New: every channel has its own on/off switch. Switching one off hides the button without losing its settings.
* New: every channel has its own editable button text plus an optional line of small print underneath it ("Replies in a few minutes").
* New: per-channel colors — icon color, icon tile color, and text color, set on the channel itself and applying to that button only.
* New: per-channel icon choice, including WhatsApp, Telegram, Messenger and envelope glyphs, or your own uploaded image.
* New: channels are reordered with up/down controls, and non-form channels can open in a new tab.
* New: two Appearance controls for the channel small print — its size and color.
* Change: Contact Settings now holds only the popup title and intro. Phone numbers, SMS text and the three button labels moved to Channels, where they are per-channel.
* Upgrade: your existing setup is migrated automatically on update. The three buttons keep their labels, numbers, pre-filled SMS text, and their on/off state exactly as they were. Nothing needs to be reconfigured.

= 1.0.4 =
* New: the Appearance screen is now a full design system — 117 controls across eleven tabs covering icon colors, text colors, section backgrounds, borders, buttons, typography, spacing, shadows, overlays, hover states, and a dedicated dark-mode palette. Everything the widget draws is editable without writing CSS.
* New: controls are organized into tabs (Layout, Brand, Floating Button, Typography, Popup Container, Popup Header, Channel Buttons, Form Fields, Submit Button, Messages & Success, Dark Mode). Saving from any tab preserves values on all the others.
* New: "Reset Appearance to Defaults" restores every design value at once, leaving all other settings untouched.
* New: gradient angle, per-element hover colors, separate heading and body font stacks, letter spacing, line height, and shadow control by color, opacity, blur, and offset.
* New: the dark-mode palette is editable — panel background, body text, muted text, dividers, input background, and channel background — instead of only the body text color.
* Fix: "Always light" now actually prevents dark mode. The stylesheet already honored a data-forced-light marker, but the widget never emitted it, so a visitor whose device was in dark mode still got the dark palette.
* Change: every color left empty emits no CSS at all and falls back to the shipped design, so a default install now ships noticeably less generated CSS than before despite the far larger option set.
* Internal: all Appearance options are declared once in a single schema that drives the defaults, the sanitizer, the generated CSS, and the admin screen, so the four can no longer drift apart. Every value is range-clamped and type-checked on save.
* Upgrade: all thirty existing Appearance settings keep their names and values. Nothing needs to be reconfigured.

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
