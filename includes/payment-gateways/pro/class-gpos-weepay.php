<?php
/**
 * Weepay ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Weepay sınıfı.
 */
class GPOS_Weepay extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'weepay';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Weepay';

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
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/weepay.svg';

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
	public $supports = array( 'threed', 'regular' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://pos.weepay.co/Login';

	/**
	 * Ödeme için gerekli alanların tanımı
	 *
	 * @return array
	 */
	public function get_payment_fields() {
		return array(
			array(
				'type'  => 'text',
				'label' => __( 'Seller Id', 'gurmepos' ),
				'model' => 'seller_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Api Key', 'gurmepos' ),
				'model' => 'api_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Secret Key', 'gurmepos' ),
				'model' => 'secret_key',
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
				'type'         => 'credit',
				'bin'          => '5890 0400 0000 0016',
				'expiry_year'  => '2028',
				'expiry_month' => '05',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4766 6200 0000 0001',
				'expiry_year'  => '2025',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5311 5700 0000 0005',
				'expiry_year'  => '2026',
				'expiry_month' => '09',
				'cvv'          => '000',
				'secure'       => '34020',
			),
		);
	}
}
