<?php
/**
 * GurmePOS ayarlar reguired badge.
 *
 * @package Gurmehub
 *
 * @var string $total_error_count
 */

if ( $total_error_count ) : ?>
<span style="display: inline-block !important;
					box-sizing: border-box !important;
					margin: 1px 2px -1px 2px !important;
					padding: 0 5px !important;
					min-width: 18px !important;
					height: 18px !important;
					border-radius: 50% !important;
					background-color: #d63638 !important;
					color: #fff !important;
					font-size: 11px !important;
					line-height: 1.6 !important;
					text-align: center !important;
					z-index: 26 !important;"><?php echo esc_html( $total_error_count ); ?> </span>
<?php endif; ?>
