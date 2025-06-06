<?php
/**
 * GurmePOS WooCommerce ayar sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS WooCommerce ayar sınıfı
 */
class GPOS_WooCommerce_Settings extends GPOS_Options {

	/**
	 * Anahtar; wp_options tablosunda ayarın tutulacağı option_name.
	 *
	 * @var string $options_table_key
	 */
	public $options_table_key = 'gpos_woocommerce_settings';
}
