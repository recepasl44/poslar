<?php
/**
 * İşlem durum kolonu
 *
 * @var GPOS_Transaction $transaction
 *
 * @package Gurmehub
 */

$status       = $transaction->get_status();
$all_statuses = gpos_post_operations()->get_post_statuses();
?>

<div class="status-type <?php echo esc_attr( $status ); ?>">
	<span><?php echo esc_html( array_key_exists( $status, $all_statuses ) ? $all_statuses[ $status ]['label'] : $status ); ?></span>
</div>
