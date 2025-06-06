<?php
/**
 * WooCommerce Ödemeler Pos Entegratör deaktif ise uyarı gösteren notice.
 *
 * @package GurmeHub
 */

?>
<div class="notice notice-error" style="padding: 10px;">
	<div style="display: flex; gap:20px; align-items:center;">
		<div>
			<img src="<?php echo esc_url( GPOS_ASSETS_DIR_URL . '/images/pos-entegrator-icon.svg' ); ?>" alt="POS Entegratör">
		</div>
		<div>
			<span style="font-weight: 700;"><?php esc_html_e( 'Payment Methods Disabled', 'gurmepos' ); ?></span>
			<p><?php esc_html_e( 'We have detected that the payment method/methods you are using are disabled, please check that your PRO version is enabled. If you are having problems after the update, try updating the PRO version manually.', 'gurmepos' ); ?> </p>
		</div>
	</div>
</div>
