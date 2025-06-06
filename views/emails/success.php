<?php
/**
 * Hatalı işlem bildirimi.
 *
 * @package Gurmehub
 *
 * @var string $error_message Hata mesajı.
 * @var GPOS_Transaction $transaction İşlem sınıfı.
 */

$texts                 = gpos_get_i18n_texts()['en'];
$plugin                = $transaction->get_plugin();
$plugin_transaction_id = $transaction->get_plugin_transaction_id();

$plugin_edit_url = apply_filters(
	'gpos_payment_plugin_edit_page_link',
	add_query_arg(
		array(
			'post'   => $plugin_transaction_id,
			'action' => 'edit',
		),
		admin_url( 'post.php' )
	),
	$plugin,
	$plugin_transaction_id
);

$transaction_edit_url = add_query_arg(
	array(
		's'         => $transaction->id,
		'post_type' => 'gpos_transaction',
	),
	admin_url( 'edit.php' )
);
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
								<table style="border-style: none; padding: 0" cellpadding="0" cellspacing="0" role="none">
									<tbody>
									<tr>
										<td>
										<div style="font-size: 24px; font-weight: 700"><?php esc_html_e( 'Hello, A successful payment transaction was made through your site', 'gurmepos' ); ?> 🎉 </div>
										<div style="margin-top: 8px">
											<?php esc_html_e( 'One of your users has made a successful payment. You can view the details in the table below.', 'gurmepos' ); ?>
										</div>
										</td>
									</tr>
									</tbody>
								</table>
								<div style="position: relative; margin-top: 16px; overflow-x: auto; border-radius: 4px">
									<table class="rtl-text-right" style="width: 100%; text-align: left; font-size: 14px" cellpadding="0" cellspacing="0" role="none">
										<thead style="background-color: #2563eb; font-size: 20px; font-weight: 600; color: #fff">
											<tr>
											<th scope="col" style="padding: 20px 24px"><?php esc_html_e( 'Transaction Details', 'gurmepos' ); ?></th>
											<th scope="col" style="padding: 20px 24px;">
												<span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0">Bağlantılar</span>
											</th>
											</tr>
										</thead>
										<tbody style="background-color: #bfdbfe">
											<tr>
											<th scope="row" style="white-space: nowrap; padding: 12px 24px; font-weight: 500; color: #111827">
												<?php echo esc_html( $texts[ $transaction->get_plugin() ] ); ?>
											</th>
											<td style="padding: 12px 24px;">
												<a href="<?php echo esc_url( $plugin_edit_url ); ?>" target="_blank" style="font-weight: 600; color: #2563eb">#<?php echo esc_html( $transaction->get_plugin_transaction_id() ); ?></a>
											</td>
											</tr>
											<tr>
											<th scope="row" style="white-space: nowrap; padding: 12px 24px; font-weight: 500; color: #111827;">
												POS Entegratör
											</th>
											<td style="padding: 12px 24px;">
												<a href="<?php echo esc_url( $transaction_edit_url ); ?>" target="_blank" style="font-weight: 600; color: #2563eb;">#<?php echo esc_html( $transaction->id ); ?></a>
											</td>
											</tr>
											<tr>
											<th scope="row" style="white-space: nowrap; padding: 12px 24px; font-weight: 500; color: #111827;">
												<?php esc_html_e( 'Total', 'gurmepos' ); ?>
											</th>
											<td style="padding: 12px 24px; font-weight: 700;">
												<?php echo esc_html( $transaction->get_total() . ' ' . $transaction->get_currency() ); ?>
											</td>
											</tr>
											<tr>
											<th scope="row" style="white-space: nowrap; padding: 12px 24px; font-weight: 500; color: #111827;">
												<?php esc_html_e( 'Installment', 'gurmepos' ); ?>
											</th>
											<td style="padding: 12px 24px; font-weight: 700;">
												<?php echo esc_html( $transaction->get_installment() ); ?>
											</td>
											</tr>
											<tr>
											<th scope="row" style="white-space: nowrap; padding: 12px 24px; font-weight: 500; color: #111827;">
												<?php esc_html_e( 'Payment Gateway', 'gurmepos' ); ?>
											</th>
											<td style="padding: 12px 24px; font-weight: 700;">
												<?php echo esc_html( $transaction->get_payment_gateway_id() ); ?>
											</td>
											</tr>
										</tbody>
									</table>
									<div style="font-size: 12px; color: #111827; margin-top: 12px;"><?php esc_html_e( 'Note: Only website admins see this email.', 'gurmepos' ); ?></div>
								</div>
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
