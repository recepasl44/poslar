<?php
/**
 * İşlem tipi kolonu
 *
 * @var GPOS_Transaction $transaction
 *
 * @package Gurmehub
 */

$process_type = $transaction->get_type();
$taxonomies   = gpos_post_operations()->get_post_taxonomies();

if ( $process_type ) : ?>

<div class="process-type <?php echo esc_attr( $process_type ); ?>">
	<span><?php echo esc_html( $taxonomies['gpos_transaction_process_type']['args']['default_terms'][ $process_type ] ); ?></span>
</div>

	<?php
endif;
