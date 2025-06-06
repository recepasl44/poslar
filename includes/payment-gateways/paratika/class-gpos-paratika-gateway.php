<?php
/**
 * Paratika ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOS_Paratika_Gateway) barındırır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Paratika_Gateway sınıfı.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class GPOS_Paratika_Gateway extends GPOS_Payten_Gateway {
	/**
	 * Paratika ödemeleri için gerekli olan API adresi
	 *
	 * @var string $live_api
	 */
	public $live_api = 'https://vpos.paratika.com.tr/paratika/api/v2';

	/**
	 * Paratika test ödemeleri için gerekli olan API adresi
	 *
	 * @var string $test_api
	 */
	public $test_api = 'https://entegrasyon.paratika.com.tr/paratika/api/v2';
}
