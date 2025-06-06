<?php
/**
 * GurmePOS 3D Popup.
 *
 * @package Gurmehub
 *
 * @var string $script
 * @var string $type
 */

?>

<div style="width:100%; padding:20px;">
	<div id="iyzipay-checkout-form" class="<?php echo esc_attr( $type ); ?>"></div>
	<?php echo $script; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> 
</div>
