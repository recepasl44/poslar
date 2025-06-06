<?php
/**
 * Papara Checkout ayarları için oluşturulmuş ödeme geçidi ayar sınıfını barındırır.
 *
 * @package GurmeHub
 */

	/**
	 * Papara Checkout için gerekli ayar sınıfı.
	 */
class GPOS_Papara_Checkout_Settings extends GPOS_Gateway_Settings {

	/**
	 * API Key
	 *
	 * @var string $api_key
	 */
	public $api_key;

	/**
	 * Secret Key
	 *
	 * @var string $secret_key
	 */
	public $secure_key;

	/**
	 * Test API Key
	 *
	 * @var string $test_api_key
	 */
	public $test_api_key;

	/**
	 * Test Secure Key
	 *
	 * @var string $test_secure_key
	 */
	public $test_secure_key;
}
