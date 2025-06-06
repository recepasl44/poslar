<?php
/**
 * İşlem iade durumu kolonu
 *
 * @var GPOS_Transaction $transaction
 *
 * @package Gurmehub
 */

$process_type = $transaction->get_type();
$taxonomies   = gpos_post_operations()->get_post_taxonomies();
if ( GPOS_Transaction_Utils::PAYMENT === $process_type ) : ?>
<div class="process-type <?php echo esc_attr( $transaction->get_refund_status() ); ?>">
	<span><?php echo esc_html( gpos_get_i18n_texts()['en'][ $transaction->get_refund_status() ] ); ?></span>
</div>
<?php endif; ?>
