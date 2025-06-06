<?php
/**
 * İşlem Ödeme Geçidi kolonu.
 *
 * @var GPOS_Transaction $transaction
 *
 * @package GurmeHub
 */

$payment_gateway = $transaction->get_payment_gateway_id();
?>

<?php if ( $payment_gateway ) : ?>
	<img src="<?php echo esc_url( GPOS_ASSETS_DIR_URL . "/images/logo/{$payment_gateway}.svg" ); ?>" alt="<?php echo esc_attr( $payment_gateway ); ?>" width="80" class="<?php echo esc_attr( $payment_gateway ); ?>">
<?php endif; ?>
