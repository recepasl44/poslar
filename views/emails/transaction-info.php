<?php
/**
 * Günlük işlem bildirimi.
 *
 * @package Gurmehub
 *
 * @var string $success_total Toplam başarılı işlem
 * @var string $failed_total Toplam başarısız işlem
 * @var string $start_date İşlemin başladığı saat
 * @var string $period
 */

?>

<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
	<?php gpos_get_view( 'emails/parts/head.php' ); ?>
	<body style="margin: 0; width: 100%; padding: 0; -webkit-font-smoothing: antialiased; word-break: break-word">
		<div role="article" aria-roledescription="email" aria-label lang="en">
			<table style="width: 100%; background-color: #dbeafe; padding-top: 32px; font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif" cellpadding="0" cellspacing="0" role="none">
			<tbody>
				<tr>
				<td></td>
				<td align="center" width="800">
					<img src="<?php echo esc_url( GPOS_ASSETS_DIR_URL ); ?>/email/logo.png" style="max-width: 100%; vertical-align: middle; line-height: 1; border: 0; width: 320px" alt="">
					<div style="margin-top: 32px; background-color: #fff; padding: 32px; color: #000">
					<?php gpos_get_view( 'emails/parts/hello-title.php', array( 'period' => $period ) ); ?>
					<table style="margin-top: 16px; width: 100%; border-style: none; padding: 0" cellpadding="0" cellspacing="0" role="none">
						<tbody>
						<tr style="width: 100%;">
							<td style="padding-right: 24px">
							<table style="width: 100%;" cellpadding="0" cellspacing="0" role="none">
								<tbody>
								<tr>
									<td align="center" style="width: 50%; border-radius: 4px; padding-top: 12px; padding-bottom: 12px; border: 1px solid #e5e7eb">
									<img src="<?php echo esc_url( GPOS_ASSETS_DIR_URL ); ?>/email/check-circle.png" style="max-width: 100%; vertical-align: middle; line-height: 1; border: 0; width: 24px; padding: 0" alt="">
									<div style="font-weight: 600; color: #16a34a"><?php esc_html_e( 'Success Transactions', 'gurmepos' ); ?></div>
									<div style="font-size: 24px; font-weight: 700;"><?php echo esc_html( $success_total ); ?></div>
									</td>
								</tr>
								</tbody>
							</table>
							</td>
							<td style="padding-left: 24px">
							<table style="width: 100%;" cellpadding="0" cellspacing="0" role="none">
								<tbody>
								<tr>
									<td align="center" style="width: 50%; border-radius: 4px; padding-top: 12px; padding-bottom: 12px; border: 1px solid #e5e7eb;">
									<img src="<?php echo esc_url( GPOS_ASSETS_DIR_URL ); ?>/email/exclamation-triangle.png" style="max-width: 100%; vertical-align: middle; line-height: 1; border: 0; width: 24px; padding: 0;" alt="">
									<div style="font-weight: 600; color: #ca8a04"><?php esc_html_e( 'Failed Transactions', 'gurmepos' ); ?></div>
									<div style="font-size: 24px; font-weight: 700;"><?php echo esc_html( $failed_total ); ?></div>
									</td>
								</tr>
								</tbody>
							</table>
							</td>
						</tr>
						</tbody>
					</table>
					<?php gpos_get_view( 'emails/parts/date-range-info.php', array( 'period' => $period ) ); ?>
					<table style="width: 100%; border-radius: 4px; background-color: #f1f5f9; padding: 8px" cellpadding="0" cellspacing="0" role="none">
						<tbody>
						<tr>
							<th>
							<img src="<?php echo esc_url( GPOS_ASSETS_DIR_URL ); ?>/email/pro-banner.png" style="max-width: 100%; vertical-align: middle; line-height: 1; border: 0; width: 384px" alt="">
							</th>
						</tr>
						<tr>
							<td align="center">
							<div style="font-size: 20px; font-weight: 700">
								<?php esc_html_e( 'Need more information about your transactions?', 'gurmepos' ); ?>
							</div>
							</td>
						</tr>
						<tr>
							<td align="center" style="padding-top: 24px">
							<div style="width: 75%; text-align: center">
								<?php
								echo wp_kses_post(
								// translators: %s PRO Plugin name
									sprintf( __( 'Upgrade to <strong>%1$s</strong>; Unlock advanced emails and more. 25+ Banks and payment institutions, card storage, category-based installment blocking and many more features are in <strong>%2$s</strong>.', 'gurmepos' ), 'POS Entegratör PRO', 'POS Entegratör PRO' )
								);
								?>
							</div>
							</td>
						</tr>
						<tr>
							<td align="center" style="padding-top: 48px; padding-bottom: 48px">
							<a href="https://posentegrator.com/fiyatlandirma/?utm_source=wp_plugin_emails&utm_medium=referral&utm_campaign=daily_mail" style="border-radius: 4px; background-color: #2563eb; padding: 16px 24px; font-weight: 600; color: #fff; text-decoration-line: none">
								<?php esc_html_e( 'Upgrade Now', 'gurmepos' ); ?>
							</a>
							</td>
						</tr>
						</tbody>
					</table>
					</div>
					<?php gpos_get_view( 'emails/parts/disable.php' ); ?>
				</td>
				<td></td>
				</tr>
			</tbody>
			</table>
		</div>
	</body>
</html>
