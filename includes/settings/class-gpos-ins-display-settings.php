<?php
/**
 * GurmePOS kart kayıt ayarları sınıfını barındırır.
 *
 * @package GurmeHub
 */

if ( class_exists( 'GPOS_Options' ) ) {
	/**
	 * GurmePOS kart kayıt ayarları sınıfı
	 */
	class GPOS_Ins_Display_Settings extends GPOS_Options {

		/**
		 * Anahtar; wp_options tablosunda ayarın tutulacağı option_name.
		 *
		 * @var string $options_table_key
		 */
		public $options_table_key = 'gpos_ins_display_settings';
	}
}
