<?php
/**
 * Sipay ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Sipay sınıfı.
 */
class GPOS_Sipay extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'sipay';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Sipay';

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
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/sipay.svg';

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
	 * Ödeme için gerekli alanların tanımı
	 *
	 * @return array
	 */
	public function get_payment_fields() {
		return array(
			array(
				'type'  => 'text',
				'label' => __( 'Merchant Id', 'gurmepos' ),
				'model' => 'merchant_id',
			),
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
	public function get_test_credit_cards() {
		return array(
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4048 0950 1085 7528',
				'expiry_year'  => '2028',
				'expiry_month' => '05',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506 3470 2652 3718',
				'expiry_year'  => '2025',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5400 6170 0000 4909',
				'expiry_year'  => '2026',
				'expiry_month' => '09',
				'cvv'          => '000',
				'secure'       => '34020',
			),
		);
	}
}
