<?php
/**
 * İşlem tutar kolonu
 *
 * @var GPOS_Transaction $transaction
 *
 * @package Gurmehub
 */

?>

<div class="transaction-amount">
	<span>
	<?php echo esc_html( number_format( gpos_number_format( $transaction->get_total() ), 2, '.', ',' ) ); ?> </span> 
	<span><?php echo esc_html( $transaction->get_currency() ); ?></span>
</div>
