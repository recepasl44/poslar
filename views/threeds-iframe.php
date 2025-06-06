<?php
/**
 * GurmePOS 3D Popup.
 *
 * @package Gurmehub
 *
 * @var string $iframe_url
 */

?>
<link rel="stylesheet" id="gpos-iframe-css" href="<?php echo esc_url_raw( GPOS_ASSETS_DIR_URL . '/css/threeds-iframe.css?ver=' . GPOS_VERSION ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>" media="all"> 
<div class="gpos-iframe-overlay"></div>
<div class="gpos-iframe-container">
	<div class="gpos-close-iframe"><span onclick="window.parent.location.reload()">&#10006;</span></div>
	<iframe class="gpos-iframe" src="<?php echo esc_url_raw( $iframe_url ); ?>"></iframe>
</div>
