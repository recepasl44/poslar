<?php
/**
 * Papara ayarları için oluşturulmuş ödeme geçidi ayar sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * Papara için gerekli ayar sınıfı.
 */
class GPOS_Papara_Settings extends GPOS_Gateway_Settings {

	/**
	 * API anahtarı.
	 *
	 * @var string $api_key
	 */
	public $api_key;

	/**
	 * Secure Key.
	 *
	 * @var string $secure_key
	 */
	public $secure_key;

	/**
	 * API anahtarı.
	 *
	 * @var string $test_api_key
	 */
	public $test_api_key;

	/**
	 * Secure Key.
	 *
	 * @var string $test_secure_key
	 */
	public $test_secure_key;
}
