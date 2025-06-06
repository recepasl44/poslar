<?php
/**
 * Paybull ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Paybull sınıfı.
 */
class GPOS_PayBull extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'paybull';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'PayBull';

	/**
	 * Pro gereksinimi
	 *
	 * @var boolean $is_need_pro
	 */
	public $is_need_pro = true;

	/**
	 * Bağlantı kontrolü yapılabiliyor mu ?
	 *
	 * @var boolean $check_connection_is_available
	 */
	public $check_connection_is_available = true;

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/paybull.svg';

	/**
	 * Desteklenilen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'EUR', 'USD' );

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'installment_api', 'refund', 'check_status' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://app.paybull.com/merchant/login';

	/**
	 * Ödeme için gerekli alanların tanımı
	 *
	 * @return array
	 */
	public function get_payment_fields(): array {
		return array(
			array(
				'type'  => 'text',
				'label' => __( 'Merchant Key', 'gurmepos' ),
				'model' => 'merchant_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'App Key', 'gurmepos' ),
				'model' => 'app_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'App Secret', 'gurmepos' ),
				'model' => 'app_secret',
			),
		);
	}

	/**
	 * Test ödemesi için kredi kartı
	 *
	 * @return array
	 */
	public function get_test_credit_cards(): array {
		return array(
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5406 6754 0667 5403',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => 'a',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4446 7631 2581 3623',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => 'a',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5218 4879 6245 9752',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => 'a',
			),
		);
	}

	/**
	 * Ödeme geçidinin taksit hesaplama yöntemi ile çalışan fonksiyon.
	 *
	 * @param float $rate Taksit oranı
	 * @param float $amount Taksitlendirilecek tutar.
	 *
	 * @return float
	 */
	public function installment_rate_calculate( float $rate, float $amount ) {
		$amount = ( ( $amount * 100 ) / ( 100 - $rate ) );
		return $amount;
	}
}
