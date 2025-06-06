<?php
/**
 * GurmePOS ayar sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS ayar sınıfı
 */
class GPOS_Settings {
	/**
	 * Test modunun options tablosundaki option_name parametresi.
	 *
	 * @var string
	 */
	private $test_mode_key = 'gpos_test_mode';


	/**
	 * Test modu durumunu döndürür.
	 *
	 * @return bool
	 */
	public function is_test_mode() {
		return (bool) get_option( $this->test_mode_key, false );
	}

	/**
	 * Test modu durumunu günceller.
	 *
	 * @param bool $mode Test modu durumu.
	 *
	 * @return bool
	 */
	public function set_test_mode( bool $mode ) {
		return update_option( $this->test_mode_key, $mode );
	}
}
