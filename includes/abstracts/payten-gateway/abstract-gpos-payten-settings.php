<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Payten ayarları için oluşturulmuş ödeme geçidi ayar sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * Payten için gerekli ayar sınıfı.
 */
abstract class GPOS_Payten_Settings extends GPOS_Gateway_Settings {

	/**
	 * Sistemde tanımlı olan üye iş yeri kodu bilgisidir.
	 *
	 * @var string $merchant
	 */
	public $merchant;

	/**
	 * API Kullanıcısı.
	 *
	 * @var string $merchant_user
	 */
	public $merchant_user;

	/**
	 * API Kullanıcı şifresi.
	 *
	 * @var string $merchant_password
	 */
	public $merchant_password;

	/**
	 * Sistemde tanımlı olan test üye iş yeri kodu bilgisidir.
	 *
	 * @var string $test_merchant
	 */
	public $test_merchant;

	/**
	 * Test API Kullanıcısı.
	 *
	 * @var string $test_merchant_user
	 */
	public $test_merchant_user;

	/**
	 * Test API Kullanıcı şifresi.
	 *
	 * @var string $test_merchant_password
	 */
	public $test_merchant_password;
}
