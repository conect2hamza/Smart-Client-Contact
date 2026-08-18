=== Smart Client Contact Hub ===
Contributors: hamzadezinr
Tags: contact, floating button, leads, click to call, sms
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lead capture, CRM and conversion hub for WordPress. Capture. Qualify. Follow up. Convert. 100% standalone.

== Description ==

Smart Client Contact Hub adds a customizable floating contact button to every page of your site. When clicked, it opens a modern popup listing the ways a visitor can reach you — you decide which, how many, what each one says, and how each one looks.

= Contact channels =

Build the popup from as many buttons as you need on the Channels screen:

* **Lead form** — built from whatever fields you want, protected by a built-in math CAPTCHA.
* **Phone call** — launches a call with tel:.
* **SMS** — opens the messaging app, optionally with your message pre-filled.
* **WhatsApp** — opens wa.me, optionally with your message pre-filled.
* **Telegram** — a @username or a full t.me link.
* **Facebook Messenger** — a Page username or a full m.me link.
* **Email** — opens the visitor's mail app, optionally with the subject pre-filled.
* **Custom link** — anything else: Viber, Skype, Calendly, a booking page. Pair it with your own uploaded icon.

Every button has its own editable text, an optional line of small print under it, its own icon chosen from a 43-icon library, and its own icon color, icon tile color, and text color — each with a matching hover color. Leave a hover color empty and that part simply stays as it is when hovered. Any button can be switched off without deleting it, and the order is yours to set. Add the same type more than once — two WhatsApp numbers for two departments works fine.

= Form builder =

The form is yours to build. Add, edit, delete and reorder fields on the Form Builder screen.

* **Thirteen field types** — text, paragraph, email address, phone number, website address, number, date, time, dropdown, radio buttons, checkboxes, consent checkbox, and hidden value.
* **Your own choices** for dropdowns, radios and checkboxes: one per line, or `value|Label` to store a short value but show a longer label.
* Per field: label, placeholder, help text, required, hide label, and full or half width so two short fields sit side by side.
* Any field can be switched off without deleting it, and the order is set with the arrows.
* Five fields are built in — name, phone, email, service and message. They have their own database columns, so they can be relabelled, reordered, made optional and switched off, but not deleted or retyped.
* Nothing is compulsory, not even the email address. A lead without one simply gets no confirmation email and no Reply-To on your notification; the notification itself, the lead record, scoring, the pipeline and exports all work as normal.
* Custom answers show on the lead page, get their own CSV export columns, and are available in email templates.

Every submission is validated client- and server-side, stored in a custom database table, and triggers a branded notification email to the administrator plus a confirmation email to the customer.

= Completely standalone =

* No Elementor, WooCommerce, Contact Form 7, Gravity Forms, or WPForms required.
* No Google reCAPTCHA, no Cloudflare, no external CAPTCHA service — the math CAPTCHA is built in, verified server-side, single-use, and regenerates after every wrong attempt.
* No icon-font CDNs — icons are inline SVG, with custom SVG/PNG upload supported.
* Emails go through wp_mail(), so any SMTP setup you already have (Gmail, Brevo, Mailgun, Amazon SES, SendGrid, Outlook, Postmark) works automatically.

= CRM =

Leads do not just pile up in an inbox — you work them.

* **Dashboard** — leads, new, hot, qualified, won, conversion rate, pipeline value and revenue, each against the previous period, plus a lead trend chart, source and channel breakdowns, your hottest leads, follow-ups due, and a recent-activity feed.
* **Pipeline** — a Kanban board across New, Contacted, Qualified, Proposal Sent, Negotiation, Won, Lost and Spam. Drag a card to move a lead, or use the stage menu on the card if you prefer the keyboard. Stages are customizable.
* **Lead workspace** — quick call / WhatsApp / email / SMS actions, stage and assignee menus, a pipeline progress rail, an activity timeline, timestamped notes, follow-up scheduling, and optional deal value.
* **Follow-ups** — a dedicated screen grouping every open task into Overdue, Due today and Upcoming, with one-click completion.
* **Lead scoring** — a configurable rule engine scoring every lead 0–100 and banding it Cold, Warm or Hot. Ten rules ship enabled with sensible weights and every weight is editable. No AI and no external service.
* **Source tracking** — referrer, landing page, UTM source/medium/campaign/term/content and device are recorded on each lead, and the source is resolved to a readable name.
* **Reports** — leads, won and conversion rate broken down by source, channel, service, campaign and device, plus a stage funnel.
* Search and filter by score band, source, service, stage, assignee and date. Bulk actions and CSV export are unchanged.

= Email system =

* Template builder for subject, heading, body, footer, logo, signature, button, and brand color.
* Placeholders: {customer_name}, {customer_email}, {customer_phone}, {service}, {message}, {submission_date}, {website_name}, {business_name}, {business_contact}, {response_time}, {view_lead_link}, plus one per custom field and {all_answers} for the lot.
* Multiple recipients, CC, BCC, Reply-To customer, custom sender.
* Email log with status, error detail, one-click resend, per-entry delete, and a Clear All Logs option. Test email button.

= Customization =

The Appearance screen exposes every visual value in the widget — 119 controls across eleven tabs, with no CSS required:

* **Layout & Behavior** — corner, launcher animation, edge distance, z-index, light/dark/auto color scheme.
* **Brand Colors** — primary, secondary, gradient angle, and whether the launcher uses the gradient or a solid fill.
* **Floating Button** — icon (any of 43 built-ins, or your own upload), button and icon size, icon color, hover background and hover icon color, corner rounding, border width and color, and a shadow you control by color, opacity, blur, and offset.
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

* Form builder: add, edit, delete and reorder fields across thirteen types, with labels, placeholders, help text, required flags, widths, choices, success/error messages, and an optional thank-you redirect.
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
* `scch_lead_updated`, `scch_lead_status_changed`, `scch_lead_assigned`, `scch_lead_scored`, `scch_lead_won` actions.
* `scch_followup_created`, `scch_followup_completed` actions.
* `scch_lead_score` filter — adjust a computed score before it is stored.

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

= 1.2.3 =
This release fixes colors you chose in Appearance being ignored on the front end.

* Fix: **your theme was overriding the widget.** Every widget rule used a single CSS class, which loses to the `button`, `input` and `a` rules themes ship for page content. On those sites a submit button set to, say, black kept rendering in the theme's own color, and the same applied to the form fields and the channel buttons. Every rule the plugin writes — in its stylesheet and in the CSS generated from your Appearance settings — is now keyed on the widget's `#scch-root` id, which outranks a theme's class and element selectors. Nothing about the design changed; it just wins now.
* Fix: a theme's uppercase buttons, letter-spacing, text shadows and underlines leaked into the widget. The few inherited properties the widget never declared itself are now reset inside it.
* Fix: the Typography font family reached the form fields and the submit button but not the launcher, close, channel and Back buttons, which stayed on the browser's default font. All of them follow the setting now.
* Note: a theme rule using `!important` still wins. If a color looks stuck after updating, that is the one case left — and clearing any page cache first is worth a try, since the generated CSS is printed into the page.

= 1.2.2 =
* New: border color on hover for the submit button, completing the set alongside the background, text and border colors it already had for both states.
* Fix: a chosen submit hover color snapped into place instead of easing, because only the brightness filter and the lift were being transitioned. Background, text and border now ease like the rest of the widget.
* Clearer wording on the submit background control: it is the resting color, and leaving it empty is what selects the brand gradient rather than a flat color.

= 1.2.1 =
* New: hover colors for the channel buttons. Text, small text, icon, and icon tile each take their own hover color, both globally in Appearance → Channels and per button on the Channels screen, alongside the background and border hover colors that were already there.
* Leave any hover color empty and that part keeps its resting color on hover, so nothing changes unless you ask it to. A color set on one button overrides the global one for that button only.
* Change: the label, small text and icon tile now transition on hover instead of snapping, matching the background and border.

= 1.2.0 =
The form is no longer a fixed set of five fields. Everything from earlier releases keeps working and no stored data is touched.

* New: **Form Builder is a real field manager.** Add, edit, delete and reorder fields. Each field is a card with its own settings instead of a row in a fixed table.
* New: **thirteen field types** — text, paragraph, email address, phone number, website address, number, date, time, dropdown, radio buttons, checkboxes, consent checkbox, and hidden value.
* New: **choices you write yourself.** Dropdowns, radios and checkboxes take one choice per line, optionally as `value|Label` so you can store a short value but show a longer label.
* New: per-field **help text**, **placeholder**, **required**, **hide label**, and **half width** so two short fields can sit side by side.
* New: custom answers appear on the lead page under "Their answers", get their own columns in the CSV export, and can be used in email templates as `{your_field_key}` or all at once as `{all_answers}`.
* Change: the five built-in fields (name, phone, email, service, message) keep their own database columns and can be relabelled, reordered, made optional and switched off — but not deleted or retyped, because reports and exports depend on them.
* Change: no field is forced any more, including the email address. Turn it off or make it optional and the plugin simply skips the confirmation email and the Reply-To header for leads that arrive without one.
* Change: the service dropdown still takes its choices from the Services screen, so that one list stays in step across the site.
* Change: client-side validation now follows each field's type, so a custom email or website field is checked the same way a built-in one is. Radio and checkbox groups are validated as groups.
* Note: a custom field can never shadow a built-in column or a reserved form name — those keys are prefixed automatically. Up to 40 custom fields per form, 2000 characters per answer.
* Upgrade: one column is appended to the leads table for the custom answers. Existing rows, fields and settings are untouched.

= 1.1.0 =
This release turns the lead inbox into a working CRM. Everything from 1.0 keeps working and no stored data is touched.

* New: **Dashboard** — eight KPIs each compared against the previous period, a lead trend chart, source and channel breakdowns, hot leads, follow-ups due, recent activity, and a setup checklist that disappears once you are done.
* New: **Pipeline** — a Kanban board with eight stages. Drag cards between columns, or use the stage menu on each card. Every move is logged.
* New: **Lead workspace** — the lead page is now a work surface: quick contact actions, stage and assignee menus, a pipeline rail, activity timeline, notes, follow-up scheduling, deal value, and the full attribution record.
* New: **Follow-ups** — tasks against leads with due date, priority and assignee, grouped into Overdue, Due today and Upcoming.
* New: **Lead scoring** — ten configurable rules score each lead 0–100 and band it Cold, Warm or Hot. Weights are editable and the screen warns you if your weights make Hot unreachable.
* New: **Source tracking** — referrer, landing page, UTM parameters and device are captured with each submission and resolved to a readable source. Read once, from the page the visitor submitted on; the plugin does not track visitors across your site.
* New: **Reports** — breakdowns by source, channel, service, campaign and device, with a stage funnel. Every figure is counted from stored leads; nothing is estimated.
* New: **Revenue** — optional estimated value and actual revenue per lead, feeding pipeline value and revenue metrics. Never mandatory.
* New: admin navigation is grouped into Dashboard, CRM, Contact Hub, Analytics and Settings instead of one flat list.
* New: a shared admin design system — tokens, cards, badges, tables that become cards on mobile, empty states on every screen, toast notifications for AJAX actions, and a sticky save bar with unsaved-change warnings.
* New: hooks — `scch_lead_updated`, `scch_lead_status_changed`, `scch_lead_assigned`, `scch_lead_scored`, `scch_lead_won`, `scch_followup_created`, `scch_followup_completed`, and a `scch_lead_score` filter.
* Change: the leads list now shows score, stage, source and next follow-up, with filters for score band, source, service and date range.
* Change: deleting a lead now also removes its timeline and follow-ups, instead of leaving them orphaned.
* Upgrade: new columns are appended to the leads table and two new tables are added. Existing rows, statuses and settings are untouched; the pre-1.1 statuses stay valid and legacy "closed" leads count as Won.

= 1.0.6 =
* New: a 43-icon built-in library, up from six. Grouped as Contact (message bubbles, phone, SMS, envelopes, paper plane, headset, life ring, map pin, globe, link), General (rocket, lightning, star, heart, sparkles, calendar, clock, person, team, briefcase, cart, gift, question, info, check, bell, video, wrench, document) and Apps & social (WhatsApp, Telegram, Messenger, Facebook, Instagram, X, LinkedIn, YouTube, TikTok, Viber, Skype, Discord).
* New: icons are chosen from a visual picker instead of a dropdown — a grid of the actual icons, grouped, with the current one highlighted. It is a plain radio group, so it works without JavaScript and is fully keyboard navigable.
* New: the same picker is used for the floating button and for every channel, so both offer the identical set. Uploading your own image is the last option in the grid and previews there once chosen.
* Change: all icons now live in one shared library rather than being duplicated across three files, so the launcher and the channels can no longer offer different sets.
* Fix: the floating button's icon list previously offered an "Envelope" option that had no matching icon and silently fell back to the message bubble.
* Icons remain inline SVG using currentColor — still no icon font, no CDN, and they take their color from your icon color settings.

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
