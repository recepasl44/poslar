<?php
/**
 * Güvenlik tipi kolonu
 *
 * @var GPOS_Transaction $transaction
 *
 * @package Gurmehub
 */

$security_type = $transaction->get_security_type();
$process_type  = $transaction->get_type();
$form_type     = $transaction->get_payment_form_type();
?>

<div class="security-type <?php echo esc_attr( $process_type ); ?>">
<?php if ( GPOS_Transaction_Utils::PAYMENT === $process_type && GPOS_Transaction_Utils::FORM_TYPE_CARD === $form_type ) : ?>
	<span>
	<?php
	echo esc_html(
		GPOS_Transaction_Utils::THREED === $security_type ?
		__( 'Three-D Secure', 'gurmepos' ) : __( 'Regular', 'gurmepos' )
	);
	?>
	</span>
<?php endif; ?>
</div>
