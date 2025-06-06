<?php
/**
 * Taksit çoklu tablo gösterimi.
 *
 * @package GurmeHub
 */

?>
<div id="gpos-installment-container">
	<div id="gpos-installment-main">
		<?php foreach ( $rates['rates'] as $family => $data ) : ?>
		<div class="gpos-family">
			<div class="familiy-logo">
				<img alt="<?php echo esc_attr( $family ); ?>" src="<?php echo esc_url( GPOS_ASSETS_DIR_URL . '/images/card-familiy/' . $family . '.svg' ); ?>">
			</div>
			<div class="head" style="background-color:<?php echo esc_attr( gpos_get_card_family_color()[ $family ] ); ?>">
				<span class="gpos-number"><?php esc_html_e( 'Installment', 'gurmepos' ); ?></span>
				<span class="gpos-price"><?php esc_html_e( 'Installment Price', 'gurmepos' ); ?></span>
				<span class="gpos-price"><?php esc_html_e( 'Total', 'gurmepos' ); ?></span>
			</div>
			<div class="rates">
				<?php foreach ( $data as $rate ) : ?>
				<div>
					<span class="gpos-number"><?php echo esc_html( $rate['installment_number'] ); ?></span>
					<span class="gpos-price"><?php echo esc_html( $rate['amount_per_month'] . ' ' . $rate['currency_symbol'] ); ?></span>
					<span class="gpos-price"><?php echo esc_html( $rate['amount_total'] . ' ' . $rate['currency_symbol'] ); ?></span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
