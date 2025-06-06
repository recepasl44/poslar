<?php
/**
 * GurmePOS PayTR iFrame.
 *
 * @package Gurmehub
 *
 * @var string $token
 */

?>

<div style="width:100%; padding:20px;">
	<script src="https://www.paytr.com/js/iframeResizer.min.js"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<iframe src="https://www.paytr.com/odeme/guvenli/<?php echo esc_html( $token ); ?>" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
	<script>iFrameResize({},'#paytriframe');</script>
</div>
