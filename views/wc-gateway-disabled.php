<?php
/**
 * WooCommerce Ödemeler Pos Entegratör deaktif ise uyarı gösteren notice.
 *
 * @package GurmeHub
 */

?>
<div class="notice notice-info" style="padding: 10px;">
	<div style="display: flex; gap:20px; align-items:center;">
		<div>
			<img src="<?php echo esc_url( GPOS_ASSETS_DIR_URL . '/images/pos-entegrator-icon.svg' ); ?>" alt="POS Entegratör">
		</div>
		<div>
			<span style="font-weight: 700;"><?php esc_html_e( 'POS Entegratör is not active', 'gurmepos' ); ?></span>
			<p><?php esc_html_e( 'In order to use the POS Entegratör, you must first activate it from WooCommerce > Settings > Payments.', 'gurmepos' ); ?> </p>
			<a href="/wp-admin/admin.php?page=wc-settings&tab=checkout"><?php esc_html_e( 'Activate', 'gurmepos' ); ?></a>
		</div>
	</div>
</div>
