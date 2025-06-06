<?php
/**
 * WordPress mağazasında puanlamaya davet eden yönetici mesajı.
 *
 * @package GurmeHub
 *
 * @var boolean $at_checkout
 */

if ( $at_checkout ) {
	$months = array( '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' );
	$years  = array( '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34' );
	?>
	<div id="app" class="gpos">
		<div class="gpos-checkout-class">
			<input
				name="gpos-threed"
				type="hidden"
				value="on"
			>
			<input
				name="gpos-sample-form"
				type="hidden"
				value="on"
			>
			<div class="w-100 start">
				<input
					name="gpos-card-bin"
					class="w-100"
					placeholder="•••• •••• •••• ••••"
					inputmode="numeric"
					type="tel"
					autocomplete="cc-number"
				>
			</div>
			<div class="w-100 between">
				<div class="w-100 between">
					<select class="w-50" name="gpos-card-expiry-month">
						<option value=""><?php esc_html_e( 'Month', 'gurmepos' ); ?></option>
						<?php foreach ( $months as $month ) : ?>
							<option value="<?php echo esc_attr( $month ); ?>"><?php echo esc_html( $month ); ?></option>
						<?php endforeach; ?>
					</select>
					<select class="w-50" name="gpos-card-expiry-year">
						<option value=""><?php esc_html_e( 'Year', 'gurmepos' ); ?></option>
						<?php foreach ( $years as $year ) : ?>
							<option value="<?php echo esc_attr( $year ); ?>">20<?php echo esc_html( $year ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="w-100 end">
					<input
						name="gpos-card-cvv"
						class="w-75"
						placeholder="CVC"
						inputmode="numeric"
						type="tel"
						autocomplete="cc-csc"
					>
				</div>
			</div>
		</div>
		<style>
			.gpos-checkout-class{
				display: flex;
				flex-direction: column;
				position: relative;
				gap: 12px;
				width: 100%;
			}
			.gpos-checkout-class .between{
				display: flex;
				justify-content: space-between;
			}
			.gpos-checkout-class .end{
				display: flex;
				justify-content: end;
			}
			.gpos-checkout-class .start{
				display: flex;
				justify-content: start;
			}
			.gpos-checkout-class .w-50{
				width: 50% !important;
			}
			.gpos-checkout-class .w-75{
				width: 75% !important;
			}
			.gpos-checkout-class .w-100{
				width: 100% !important;
			}
		</style>
	</div>	
	<?php
	wp_nonce_field( GPOS_AJAX_ACTION, '_gpos_nonce' );
} else {
	?>
	<div id="app" class="gpos"></div>
	<?php
}
