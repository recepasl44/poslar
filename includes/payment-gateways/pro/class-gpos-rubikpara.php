<?php
/**
 * Rubikpara ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Rubikpara sınıfı.
 */
class GPOS_Rubikpara extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'rubikpara';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Rubikpara';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/rubikpara.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'refund', 'cancel' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://rubikpara.com/';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY' );

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
				'label' => __( 'Public Key', 'gurmepos' ),
				'model' => 'public_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Private Key', 'gurmepos' ),
				'model' => 'private_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Merchant Number', 'gurmepos' ),
				'model' => 'merchant_number',
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
				'bin'          => '4119 7901 6654 4284',
				'expiry_year'  => '2024',
				'expiry_month' => '04',
				'cvv'          => '961',
				'secure'       => '123456',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5200 1900 0633 8608',
				'expiry_year'  => '2030',
				'expiry_month' => '01',
				'cvv'          => '410',
				'secure'       => '123456',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4256 6919 4486 7646',
				'expiry_year'  => '2030',
				'expiry_month' => '12',
				'cvv'          => '001',
				'secure'       => '123456',
			),
		);
	}
}
