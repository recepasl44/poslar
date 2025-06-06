<?php
/**
 * QNBpay ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_QNBpay sınıfı.
 */
class GPOS_QNBpay extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'qnbpay';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'QNBpay';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/qnbpay.svg';

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://portal.qnbpay.com.tr/merchant/login';

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
				'label' => __( 'Merchant ID', 'gurmepos' ),
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
				'bin'          => '4022 7805 2066 9303',
				'expiry_year'  => '2050',
				'expiry_month' => '01',
				'cvv'          => '988',
				'secure'       => '',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5456 1601 4022 5418',
				'expiry_year'  => '2050',
				'expiry_month' => '01',
				'cvv'          => '276',
				'secure'       => '',
			),
		);
	}
}
