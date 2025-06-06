<?php
/**
 * Taksit tek tablo gösterimi.
 *
 * @package GurmeHub
 */

?>
<div id="gpos-installment-container">
	<div id="gpos-installment-single-main">
	<div class="gpos-family">
			<div class="familiy-logo"></div>
			<div class="rates">
				<?php foreach ( $rates['months'] as $month ) : ?>
				<div class="box">
					<span class="number"><?php echo esc_html( $month ); ?>&nbsp;<?php esc_html_e( 'Installment', 'gurmehub' ); ?> </span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php foreach ( $rates['rates'] as $family => $data ) : ?>	
		<div class="gpos-family">
			<div class="familiy-logo">
				<img alt="<?php echo esc_attr( $family ); ?>" src="<?php echo esc_url( GPOS_ASSETS_DIR_URL . '/images/card-familiy/' . $family . '.svg' ); ?>">
			</div>
			<div class="rates" style="background-color:<?php echo esc_attr( gpos_get_card_family_color()[ $family ] ); ?>">
				<?php foreach ( $rates['months'] as $month ) : ?>
					<div class="box">
					<?php if ( isset( $data[ $month ] ) ) : ?>
						<span class="month"><?php echo esc_html( $data[ $month ]['installment_number'] ); ?> x <?php echo esc_html( $data[ $month ]['amount_per_month'] . $data[ $month ]['currency_symbol'] ); ?></span>
						<span class="gpos-price"><?php esc_html_e( 'Total', 'gurmehub' ); ?>:<?php echo esc_html( $data[ $month ]['amount_total'] . $data[ $month ]['currency_symbol'] ); ?></span>
					<?php else : ?>
						---
					<?php endif; ?>
					</div>
				<?php endforeach; ?>	
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
