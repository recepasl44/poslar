<?php
/**
 * İşyerimPOS ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_IsyerimPOS sınıfı.
 */
class GPOS_IsyerimPOS extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'isyerimpos';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'İşyerimPOS';

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
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/isyerimpos.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'installment_api', 'refund' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://panel.isyerimpos.com/';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY' );

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
				'label' => __( 'User ID', 'gurmepos' ),
				'model' => 'user_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'API Key', 'gurmepos' ),
				'model' => 'api_key',
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
				'bin'          => '4155 6501 0041 6111',
				'expiry_year'  => '2025',
				'expiry_month' => '01',
				'cvv'          => '123',
				'secure'       => '',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5818 7758 1877 2285',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '001',
				'secure'       => 'a',
			),
		);
	}
}
