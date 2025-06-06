<?php
/**
 * İşlem taksit kolonu
 *
 * @var GPOS_Transaction $transaction
 *
 * @package Gurmehub
 */

?>

<div class="transaction-installment">
	<span><?php echo esc_html( $transaction->get_installment() ); ?></span>
</div>
