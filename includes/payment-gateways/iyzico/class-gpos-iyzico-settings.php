<?php
/**
 * Iyzico ayarları için oluşturulmuş ödeme geçidi ayar sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * Iyzico için gerekli ayar sınıfı.
 */
class GPOS_Iyzico_Settings extends GPOS_Gateway_Settings {

	/**
	 * API anahtarı.
	 *
	 * @var string $api_key
	 */
	public $api_key;

	/**
	 * API şifresi.
	 *
	 * @var string $api_secret
	 */
	public $api_secret;

	/**
	 * Test API anahtarı.
	 *
	 * @var string $test_api_key
	 */
	public $test_api_key;

	/**
	 * Test API şifresi.
	 *
	 * @var string $test_api_secret
	 */
	public $test_api_secret;
}
