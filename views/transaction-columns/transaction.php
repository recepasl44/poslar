<?php
/**
 * İşlem kolonu
 *
 * @var GPOS_Transaction $transaction
 *
 * @package GurmeHub
 */

?>

<a href="<?php echo esc_url( $transaction->get_edit_link() ); ?>" class="transaction">
	#<?php echo esc_html( $transaction->id ); ?> <?php echo esc_html( $transaction->get_customer_full_name() ); ?>
</a>
