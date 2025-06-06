<?php
/**
 * Ozan ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Ozan sınıfı.
 */
class GPOS_Ozan extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'ozan';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Ozan';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/ozan.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'refund' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://pos.ozan.com/v2/user/login';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'EUR', 'USD' );

	/**
	 * Bağlantı kontrolü yapılabiliyor mu ?
	 *
	 * @var boolean $check_connection_is_available
	 */
	public $check_connection_is_available = true;

	/**
	 * Ödeme için gerekli alanların tanımı
	 *
	 * @return array
	 */
	public function get_payment_fields() {
		return array(
			array(
				'type'  => 'text',
				'label' => __( 'API Key', 'gurmepos' ),
				'model' => 'api_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'API Secret', 'gurmepos' ),
				'model' => 'api_secret',
			),
		);
	}

	/**
	 * Test ödemesi için kredi kartı
	 *
	 * @return array
	 */
	public function get_test_credit_cards() {
		return array(
			array(
				'brand'        => 'mastercard',
				'bin'          => '5218 4879 6245 9752',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'visa',
				'bin'          => '4446 7631 2581 3623',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
		);
	}
}
