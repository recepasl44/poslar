<?php
/**
 * Craftgate ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Craftgate sınıfı.
 */
class GPOS_Craftgate extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'craftgate';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Craftgate';
	/**
	 * Kırılım bazlı mı ?
	 *
	 * @var boolean $line_based
	 */
	public $line_based = true;

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/craftgate.svg';

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://panel.craftgate.io/login';

	/**
	 * Desteklenilen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'EUR', 'USD', 'GBP', 'CNY', 'ARS', 'BRL', 'AED', 'IQD' );

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'installment_api' );

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
				'label' => __( 'Api Key', 'gurmepos' ),
				'model' => 'api_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Api Password', 'gurmepos' ),
				'model' => 'api_secret',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Member 3DSecure Callback Key" in the Administration -> Merchant Settings interface on the panel."', 'gurmepos' ),
				'model' => 'threed_callback_key',
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
				'bin'          => '5209 2200 0000 0002',
				'expiry_year'  => '2030',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4256 6900 0000 0001',
				'expiry_year'  => '2030',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'amex',
				'type'         => 'credit',
				'bin'          => '378282246310005',
				'expiry_year'  => '2030',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
		);
	}
}
