<?php
/**
 * Inline SVG icon library.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Every icon the plugin can draw, in one place.
 *
 * Icons are inline SVG on a 24x24 viewBox using fill="currentColor", so they
 * inherit whatever color the surrounding element sets and no icon font or
 * CDN is ever loaded. The launcher button and each contact channel pick from
 * this same library, and either can upload an image instead.
 */
class Icons {

	/**
	 * Groups the picker renders under, in order.
	 *
	 * @return array<string,string>
	 */
	public static function groups(): array {
		return array(
			'contact' => __( 'Contact', 'smart-client-contact-hub' ),
			'general' => __( 'General', 'smart-client-contact-hub' ),
			'brand'   => __( 'Apps & social', 'smart-client-contact-hub' ),
		);
	}

	/**
	 * The library: id => { label, group, path }.
	 *
	 * 'path' is the contents of a single <path d="…"> on a 24x24 viewBox.
	 *
	 * @return array<string,array{label:string,group:string,path:string}>
	 */
	public static function library(): array {
		return array(

			/* ---------------- Contact ---------------- */
			'chat-bubble' => array(
				'label' => __( 'Message bubble', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M12 3C6.48 3 2 6.94 2 11.8c0 2.5 1.2 4.75 3.14 6.35-.14 1.14-.62 2.6-1.72 3.6 1.9-.06 3.62-.8 4.9-1.7 1.14.36 2.38.55 3.68.55 5.52 0 10-3.94 10-8.8S17.52 3 12 3z',
			),
			'chat-dots'   => array(
				'label' => __( 'Bubble with dots', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M12 3C6.48 3 2 6.94 2 11.8c0 2.5 1.2 4.75 3.14 6.35-.14 1.14-.62 2.6-1.72 3.6 1.9-.06 3.62-.8 4.9-1.7 1.14.36 2.38.55 3.68.55 5.52 0 10-3.94 10-8.8S17.52 3 12 3zm-4 10a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z',
			),
			'phone'       => array(
				'label' => __( 'Phone', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.85 21 3 13.15 3 3.5a1 1 0 0 1 1-1H7.5a1 1 0 0 1 1 1c0 1.24.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z',
			),
			'sms'         => array(
				'label' => __( 'Text message', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM8 11H6V9h2v2zm5 0h-2V9h2v2zm5 0h-2V9h2v2z',
			),
			'mail'        => array(
				'label' => __( 'Envelope', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4.24-8 4.76-8-4.76V6l8 4.76L20 6v2.24z',
			),
			'mail-open'   => array(
				'label' => __( 'Open envelope', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M12 2 2 9v11a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9L12 2zm0 2.44 7.5 5.25L12 14 4.5 9.69 12 4.44zM4 20v-8.1l8 4.6 8-4.6V20H4z',
			),
			'send'        => array(
				'label' => __( 'Paper plane', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M2 21l21-9L2 3v7l15 2-15 2v7z',
			),
			'headset'     => array(
				'label' => __( 'Headset', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M12 2a9 9 0 0 0-9 9v7a3 3 0 0 0 3 3h2v-8H5v-2a7 7 0 1 1 14 0v2h-3v8h2a3 3 0 0 0 3-3v-7a9 9 0 0 0-9-9z',
			),
			'support'     => array(
				'label' => __( 'Life ring', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 2c1.5 0 2.9.42 4.09 1.15l-2.5 2.5a4.9 4.9 0 0 0-3.18 0l-2.5-2.5A7.94 7.94 0 0 1 12 4zM4 12c0-1.5.42-2.9 1.15-4.09l2.5 2.5a4.9 4.9 0 0 0 0 3.18l-2.5 2.5A7.94 7.94 0 0 1 4 12zm8 8a7.94 7.94 0 0 1-4.09-1.15l2.5-2.5a4.9 4.9 0 0 0 3.18 0l2.5 2.5A7.94 7.94 0 0 1 12 20zm0-5a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm6.85-.91-2.5-2.5a4.9 4.9 0 0 0 0-3.18l2.5-2.5A7.94 7.94 0 0 1 20 12c0 1.5-.42 2.9-1.15 4.09z',
			),
			'location'    => array(
				'label' => __( 'Map pin', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z',
			),
			'globe'       => array(
				'label' => __( 'Globe', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.93 6h-2.95a15.6 15.6 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.93 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14a7.9 7.9 0 0 1 0-4h3.38a16.5 16.5 0 0 0 0 4H4.26zm.81 2h2.95c.32 1.25.79 2.45 1.38 3.56A7.99 7.99 0 0 1 5.07 16zm2.95-8H5.07A7.99 7.99 0 0 1 9.4 4.44 15.6 15.6 0 0 0 8.02 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82A13.9 13.9 0 0 1 12 19.96zM14.34 14H9.66a14.7 14.7 0 0 1 0-4h4.68a14.7 14.7 0 0 1 0 4zm.26 5.56c.59-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56zM16.36 14a16.5 16.5 0 0 0 0-4h3.38a7.9 7.9 0 0 1 0 4h-3.38z',
			),
			'link'        => array(
				'label' => __( 'Link', 'smart-client-contact-hub' ),
				'group' => 'contact',
				'path'  => 'M8.5 17.5a3 3 0 0 1 0-4.24l2.12-2.12-1.41-1.41-2.12 2.12a5 5 0 0 0 7.07 7.07l2.12-2.12-1.41-1.41-2.13 2.11a3 3 0 0 1-4.24 0zm7.07-11.31-2.12 2.12 1.41 1.41 2.12-2.12a3 3 0 0 1 4.25 4.24l-2.12 2.12 1.41 1.41 2.12-2.12a5 5 0 0 0-7.07-7.06zM8.46 14.12l1.42 1.42 5.66-5.66-1.42-1.42-5.66 5.66z',
			),

			/* ---------------- General ---------------- */
			'rocket'      => array(
				'label' => __( 'Rocket', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 1.5c3.1 2.6 4.9 6.5 4.9 10.6l-.5 2.9H7.6l-.5-2.9c0-4.1 1.8-8 4.9-10.6zM6.7 15.1 3.6 19.9l4-1 .5-3.1zm10.6 0-1.4.7.5 3.1 4 1zM10.1 19.2h3.8L12 22.5zM12 7.1a1.9 1.9 0 1 0 0 3.8 1.9 1.9 0 0 0 0-3.8z',
				'rule'  => 'evenodd',
			),
			'bolt'        => array(
				'label' => __( 'Lightning', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M13 2 4 14h6l-1 8 9-12h-6l1-8z',
			),
			'star'        => array(
				'label' => __( 'Star', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z',
			),
			'heart'       => array(
				'label' => __( 'Heart', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z',
			),
			'sparkles'    => array(
				'label' => __( 'Sparkles', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 2l1.8 5.2L19 9l-5.2 1.8L12 16l-1.8-5.2L5 9l5.2-1.8L12 2zm6 12l.9 2.6 2.6.9-2.6.9-.9 2.6-.9-2.6-2.6-.9 2.6-.9.9-2.6zM5 14l.7 2 2 .7-2 .7L5 19.4l-.7-2-2-.7 2-.7L5 14z',
			),
			'calendar'    => array(
				'label' => __( 'Calendar', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5z',
			),
			'clock'       => array(
				'label' => __( 'Clock', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z',
			),
			'user'        => array(
				'label' => __( 'Person', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-3.34 0-10 1.67-10 5v3h20v-3c0-3.33-6.66-5-10-5z',
			),
			'users'       => array(
				'label' => __( 'Team', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M16 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-8 0a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-2.67 0-8 1.34-8 4v3h10v-3c0-1.06.42-2.02 1.1-2.78A13.9 13.9 0 0 0 8 13zm8 0c-.29 0-.62.02-.97.05A5.02 5.02 0 0 1 17 17v3h7v-3c0-2.66-5.33-4-8-4z',
			),
			'briefcase'   => array(
				'label' => __( 'Briefcase', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M20 6h-4V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zM10 4h4v2h-4V4z',
			),
			'cart'        => array(
				'label' => __( 'Shopping cart', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7.16 14h9.45c.75 0 1.41-.41 1.75-1.03l3.24-5.88A1 1 0 0 0 20.72 6H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44A2 2 0 0 0 7 19h12v-2H7.42a.25.25 0 0 1-.22-.37L7.16 14z',
			),
			'gift'        => array(
				'label' => __( 'Gift', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M20 7h-2.18A3 3 0 0 0 15 2.5c-1.2 0-2.27.7-3 1.75-.73-1.05-1.8-1.75-3-1.75A3 3 0 0 0 6.18 7H4a2 2 0 0 0-2 2v2h9V9h2v2h9V9a2 2 0 0 0-2-2zM9 5a1 1 0 0 1 0 2H8a1 1 0 0 1 0-2h1zm6 0a1 1 0 0 1 0 2h-1a1 1 0 0 1 0-2h1zM3 13v7a2 2 0 0 0 2 2h6v-9H3zm10 0v9h6a2 2 0 0 0 2-2v-7h-8z',
			),
			'question'    => array(
				'label' => __( 'Question mark', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 17h-2v-2h2v2zm2.07-7.75-.9.92c-.72.73-1.17 1.33-1.17 2.83h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26A1.96 1.96 0 0 0 12 8a2 2 0 0 0-2 2H8a4 4 0 1 1 8 0c0 .88-.36 1.68-.93 2.25z',
			),
			'info'        => array(
				'label' => __( 'Information', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z',
			),
			'check'       => array(
				'label' => __( 'Check mark', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z',
			),
			'bell'        => array(
				'label' => __( 'Bell', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6v-5a6 6 0 0 0-5-5.91V4a1 1 0 1 0-2 0v1.09A6 6 0 0 0 6 11v5l-2 2v1h16v-1l-2-2z',
			),
			'video'       => array(
				'label' => __( 'Video call', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z',
			),
			'wrench'      => array(
				'label' => __( 'Wrench', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M22.7 19l-9.1-9.1a7 7 0 0 0-9.2-9L9 5.5 5.5 9 .9 4.4a7 7 0 0 0 9 9.2l9.1 9.1a1 1 0 0 0 1.4 0l2.3-2.3a1 1 0 0 0 0-1.4z',
			),
			'document'    => array(
				'label' => __( 'Document', 'smart-client-contact-hub' ),
				'group' => 'general',
				'path'  => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm4 18H6V4h7v5h5v11z',
			),

			/* ---------------- Brands ---------------- */
			'whatsapp'    => array(
				'label' => 'WhatsApp',
				'group' => 'brand',
				'path'  => 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12.05 21.785h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413',
			),
			'telegram'    => array(
				'label' => 'Telegram',
				'group' => 'brand',
				'path'  => 'M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0m4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635',
			),
			'messenger'   => array(
				'label' => 'Messenger',
				'group' => 'brand',
				'path'  => 'M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.652V24l4.088-2.242c1.092.301 2.246.464 3.443.464 6.627 0 12-4.974 12-11.111C24 4.974 18.627 0 12 0m1.191 14.963-3.055-3.26-5.963 3.26L10.732 8l3.13 3.259L19.752 8z',
			),
			'facebook'    => array(
				'label' => 'Facebook',
				'group' => 'brand',
				'path'  => 'M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07',
			),
			'instagram'   => array(
				'label' => 'Instagram',
				'group' => 'brand',
				'path'  => 'M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.31-1.46.72-2.13 1.38S.94 3.35.63 4.14C.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.79.72 1.46 1.38 2.13.66.66 1.33 1.07 2.13 1.38.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56.79-.31 1.46-.72 2.13-1.38.66-.66 1.07-1.33 1.38-2.13.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91-.31-.79-.72-1.46-1.38-2.13C20.34 1.35 19.67.94 18.88.63c-.76-.3-1.64-.5-2.91-.56C14.69.01 14.28 0 12 0zm0 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41 1.27-.06 1.65-.07 4.85-.07zm0 3.68a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm7.85-10.4a1.44 1.44 0 1 1-2.88 0 1.44 1.44 0 0 1 2.88 0z',
			),
			'x'           => array(
				'label' => 'X',
				'group' => 'brand',
				'path'  => 'M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.46l8.6-9.83L0 1.15h7.59l5.24 6.93 6.07-6.93zm-1.29 19.5h2.04L6.49 3.24H4.3l13.31 17.41z',
			),
			'linkedin'    => array(
				'label' => 'LinkedIn',
				'group' => 'brand',
				'path'  => 'M20.45 20.45h-3.56v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.63-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zm1.78 13.02H3.55V9h3.57v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0z',
			),
			'youtube'     => array(
				'label' => 'YouTube',
				'group' => 'brand',
				'path'  => 'M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.5A3.02 3.02 0 0 0 .5 6.19C0 8.07 0 12 0 12s0 3.93.5 5.81a3.02 3.02 0 0 0 2.12 2.14c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3.02 3.02 0 0 0 2.12-2.14C24 15.93 24 12 24 12s0-3.93-.5-5.81zM9.55 15.57V8.43L15.82 12l-6.27 3.57z',
			),
			'tiktok'      => array(
				'label' => 'TikTok',
				'group' => 'brand',
				'path'  => 'M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z',
			),
			'viber'       => array(
				'label' => 'Viber',
				'group' => 'brand',
				'path'  => 'M11.4 0C9.47.03 5.32.34 3 2.47 1.28 4.19.68 6.7.61 9.82.55 12.93.47 18.78 6.1 20.37h.01l-.01 2.42s-.03.98.61 1.18c.79.24 1.24-.5 1.99-1.3.41-.44.97-1.09 1.4-1.58 3.85.32 6.8-.42 7.14-.53.78-.25 5.18-.81 5.9-6.66.74-6.03-.36-9.85-2.34-11.57l-.01-.01c-.6-.55-3-2.3-8.37-2.32 0 0-.4-.02-1.02 0zm.06 1.7c.53-.01.86 0 .86 0 4.54.01 6.72 1.38 7.22 1.84 1.68 1.43 2.53 4.87 1.9 9.91-.6 4.88-4.17 5.19-4.83 5.4-.28.09-2.88.73-6.15.52 0 0-2.44 2.94-3.2 3.7-.12.12-.26.17-.35.14-.13-.03-.16-.18-.16-.4l.02-4.01c-4.76-1.32-4.49-6.3-4.44-8.9.06-2.6.55-4.73 2-6.16 1.96-1.79 5.49-2.03 7.13-2.04z',
				'extra' => '<g transform="translate(6.15,3.9) scale(0.4)"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.85 21 3 13.15 3 3.5a1 1 0 0 1 1-1H7.5a1 1 0 0 1 1 1c0 1.24.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></g>',
			),
			'skype'       => array(
				'label' => 'Skype',
				'group' => 'brand',
				'path'  => 'M12.06 0a12.1 12.1 0 0 0-2.28.22A6.94 6.94 0 0 0 6.35 0 6.35 6.35 0 0 0 0 6.35c0 1.24.36 2.4.98 3.38a12.1 12.1 0 0 0-.22 2.28A11.95 11.95 0 0 0 12.71 24c.79 0 1.55-.08 2.29-.22a6.9 6.9 0 0 0 3.43.9A6.35 6.35 0 0 0 24 18.33c0-1.23-.35-2.39-.96-3.36.14-.75.22-1.53.22-2.32A11.95 11.95 0 0 0 12.06 0zm.28 5.36c3.2 0 5.4 1.5 5.4 3.36 0 .87-.62 1.55-1.55 1.55-1.4 0-1.6-1.8-4.1-1.8-1.28 0-2.2.5-2.2 1.34 0 2.3 8.44.83 8.44 5.75 0 2.6-2.24 4.42-5.87 4.42-3.53 0-5.96-1.8-5.96-3.6 0-.9.68-1.5 1.58-1.5 1.62 0 1.8 2.24 4.55 2.24 1.6 0 2.55-.72 2.55-1.6 0-2.5-8.44-1.03-8.44-5.9 0-2.5 2.2-4.26 5.6-4.26z',
			),
			'discord'     => array(
				'label' => 'Discord',
				'group' => 'brand',
				'path'  => 'M20.32 4.37a19.8 19.8 0 0 0-4.89-1.52.07.07 0 0 0-.08.04c-.21.37-.44.86-.61 1.25a18.3 18.3 0 0 0-5.49 0 12.6 12.6 0 0 0-.62-1.25.08.08 0 0 0-.08-.04c-1.71.3-3.35.81-4.89 1.52a.07.07 0 0 0-.03.03C.53 9.05-.32 13.58.1 18.06c0 .02.01.04.03.05a19.9 19.9 0 0 0 6 3.03.08.08 0 0 0 .08-.03c.46-.63.87-1.29 1.23-1.99a.08.08 0 0 0-.04-.11c-.65-.25-1.27-.55-1.87-.89a.08.08 0 0 1-.01-.13l.37-.29a.07.07 0 0 1 .08-.01c3.93 1.79 8.18 1.79 12.06 0a.07.07 0 0 1 .08.01l.37.29a.08.08 0 0 1-.01.13c-.6.35-1.22.64-1.87.89a.08.08 0 0 0-.04.11c.36.7.77 1.36 1.22 1.99a.08.08 0 0 0 .09.03 19.8 19.8 0 0 0 6.01-3.03.08.08 0 0 0 .03-.05c.5-5.18-.84-9.67-3.54-13.66a.06.06 0 0 0-.03-.03zM8.02 15.33c-1.18 0-2.16-1.09-2.16-2.42s.96-2.42 2.16-2.42c1.21 0 2.18 1.1 2.16 2.42 0 1.33-.96 2.42-2.16 2.42zm7.97 0c-1.18 0-2.16-1.09-2.16-2.42s.96-2.42 2.16-2.42c1.21 0 2.18 1.1 2.16 2.42 0 1.33-.95 2.42-2.16 2.42z',
			),
		);
	}

	/**
	 * Icons the close button and CAPTCHA refresh use. Kept out of the picker
	 * because they are structural, not a design choice.
	 *
	 * @return array<string,string>
	 */
	private static function internal(): array {
		return array(
			'close'   => 'M18.3 5.71 12 12.01l-6.3-6.3-1.4 1.42 6.29 6.29-6.3 6.3 1.42 1.41 6.29-6.29 6.3 6.3 1.41-1.42-6.29-6.3 6.3-6.29z',
			'refresh' => 'M17.65 6.35A8 8 0 1 0 19.73 14h-2.08A6 6 0 1 1 12 6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z',
		);
	}

	/**
	 * Choices for a select or picker: id => label.
	 *
	 * @return array<string,string>
	 */
	public static function choices(): array {
		$out = array();
		foreach ( self::library() as $id => $icon ) {
			$out[ $id ] = $icon['label'];
		}
		$out['custom'] = __( 'Custom upload', 'smart-client-contact-hub' );
		return $out;
	}

	/**
	 * Library entries grouped for rendering, plus the custom-upload entry.
	 *
	 * @return array<string,array<string,array>>
	 */
	public static function grouped(): array {
		$out = array();
		foreach ( array_keys( self::groups() ) as $group ) {
			$out[ $group ] = array();
		}
		foreach ( self::library() as $id => $icon ) {
			$out[ $icon['group'] ][ $id ] = $icon;
		}
		return $out;
	}

	/**
	 * Whether an id names a pickable icon.
	 *
	 * @param string $id Icon id.
	 */
	public static function exists( string $id ): bool {
		return 'custom' === $id || isset( self::library()[ $id ] );
	}

	/**
	 * Inline SVG for an icon, or an <img> when a custom upload is selected.
	 *
	 * Output is safe to echo: paths come from the library above, and a custom
	 * URL is escaped.
	 *
	 * @param string $id         Icon id.
	 * @param string $custom_url Uploaded image URL, used when $id is 'custom'.
	 * @param string $fallback   Icon used when $id is unknown.
	 */
	public static function svg( string $id, string $custom_url = '', string $fallback = 'chat-bubble' ): string {
		if ( 'custom' === $id ) {
			if ( '' === $custom_url ) {
				$id = $fallback;
			} else {
				return sprintf(
					'<img src="%s" alt="" aria-hidden="true" class="scch-custom-icon" />',
					esc_url( $custom_url )
				);
			}
		}

		$library  = self::library();
		$internal = self::internal();

		if ( isset( $internal[ $id ] ) ) {
			$path = $internal[ $id ];
		} elseif ( isset( $library[ $id ] ) ) {
			$path = $library[ $id ]['path'];
		} else {
			$path = $library[ $fallback ]['path'] ?? $library['chat-bubble']['path'];
		}

		// Icons may carry extra markup (a second shape, a highlight) after
		// the main path. It is authored here, never user input.
		$extra = isset( $library[ $id ]['extra'] ) ? $library[ $id ]['extra'] : '';

		// evenodd lets an icon punch a hole in itself (the rocket porthole)
		// so the cutout works in any color, rather than painting over it.
		$rule = isset( $library[ $id ]['rule'] ) ? ' fill-rule="' . $library[ $id ]['rule'] . '"' : '';

		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path' . $rule . ' d="' . $path . '"/>' . $extra . '</svg>';
	}
}
