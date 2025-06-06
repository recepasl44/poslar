<?php
/**
 * PayTR ayarları için oluşturulmuş ödeme geçidi ayar sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * PayTR için gerekli ayar sınıfı.
 */
class GPOS_PayTR_IFrame_Settings extends GPOS_Gateway_Settings {

	/**
	 * Merchant İD.
	 *
	 * @var string $api_secret
	 */
	public $merchant_id;

	/**
	 * Merchant Key.
	 *
	 * @var string $api_secret
	 */
	public $merchant_key;

	/**
	 * Merchant SALT.
	 *
	 * @var string $api_secret
	 */
	public $merchant_salt;

	/**
	 * Merchant İD.
	 *
	 * @var string $api_secret
	 */
	public $test_merchant_id;

	/**
	 * Merchant Key.
	 *
	 * @var string $api_secret
	 */
	public $test_merchant_key;

	/**
	 * Merchant SALT.
	 *
	 * @var string $api_secret
	 */
	public $test_merchant_salt;
}
