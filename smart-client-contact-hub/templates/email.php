<?php
/**
 * HTML email wrapper. Table-based with inline styles for broad client support.
 *
 * @var array $context {heading, body, footer, logo_url, brand_color, signature, button}
 *
 * @package SCCH
 */

defined( 'ABSPATH' ) || exit;

$scch_brand  = sanitize_hex_color( $context['brand_color'] ) ?: '#2563eb';
$scch_body   = wpautop( esc_html( $context['body'] ) );
$scch_button = $context['button'];
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 12px;">
		<tr>
			<td align="center">
				<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;">
					<tr>
						<td style="background:<?php echo esc_attr( $scch_brand ); ?>;padding:28px 32px;text-align:center;">
							<?php if ( ! empty( $context['logo_url'] ) ) : ?>
								<img src="<?php echo esc_url( $context['logo_url'] ); ?>" alt="" style="max-height:48px;margin-bottom:12px;">
							<?php endif; ?>
							<h1 style="margin:0;color:#ffffff;font-size:22px;line-height:1.3;"><?php echo esc_html( $context['heading'] ); ?></h1>
						</td>
					</tr>
					<tr>
						<td style="padding:32px;color:#1f2937;font-size:15px;line-height:1.65;">
							<?php echo wp_kses_post( $scch_body ); ?>

							<?php if ( $scch_button && ! empty( $scch_button['url'] ) ) : ?>
								<p style="text-align:center;margin:28px 0 8px;">
									<a href="<?php echo esc_url( $scch_button['url'] ); ?>"
										style="display:inline-block;background:<?php echo esc_attr( $scch_brand ); ?>;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:600;">
										<?php echo esc_html( $scch_button['label'] ); ?>
									</a>
								</p>
							<?php endif; ?>

							<?php if ( ! empty( $context['signature'] ) ) : ?>
								<p style="margin:28px 0 0;color:#374151;">— <?php echo esc_html( $context['signature'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td style="padding:20px 32px;background:#f9fafb;border-top:1px solid #e5e7eb;color:#6b7280;font-size:12px;text-align:center;">
							<?php echo esc_html( $context['footer'] ); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
