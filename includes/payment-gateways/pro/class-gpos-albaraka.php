<?php
/**
 * Albaraka ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Albaraka sınıfı.
 */
class GPOS_Albaraka extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'albaraka';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Albaraka';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/albaraka.svg';

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
	public $merchant_panel = 'https://esube.albaraka.com.tr/LoginIB.aspx?sID=-1&type=corporate';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'EUR', 'USD' );

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
				'label' => __( 'Terminal ID', 'gurmepos' ),
				'model' => 'terminal_id',
			),
			array(
				'type'  => 'text',
				'label' => __( '3D Key (ENCKey)', 'gurmepos' ),
				'model' => 'merchant_threed_store_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'PosNet ID', 'gurmepos' ),
				'model' => 'posnet_id',
			),
		);
	}

	/**
	 * Test ödemesi için kredi kartı
	 *
	 * @return array
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function get_test_credit_cards() {
		return array(
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506344181092555',
				'expiry_year'  => '2028',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506349043174632',
				'expiry_year'  => '2029',
				'expiry_month' => '02',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506349089054813',
				'expiry_year'  => '2028',
				'expiry_month' => '07',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506349025539513',
				'expiry_year'  => '2029',
				'expiry_month' => '03',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506349068067059',
				'expiry_year'  => '2028',
				'expiry_month' => '08',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506344230780754',
				'expiry_year'  => '2028',
				'expiry_month' => '10',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506344210776947',
				'expiry_year'  => '2029',
				'expiry_month' => '01',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506349076473604',
				'expiry_year'  => '2029',
				'expiry_month' => '02',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506344109967938',
				'expiry_year'  => '2029',
				'expiry_month' => '01',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506344222971809',
				'expiry_year'  => '2029',
				'expiry_month' => '05',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506347007312677',
				'expiry_year'  => '2029',
				'expiry_month' => '01',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506344225609109',
				'expiry_year'  => '2029',
				'expiry_month' => '01',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '5400619340701616',
				'expiry_year'  => '2028',
				'expiry_month' => '07',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5400611063484835',
				'expiry_year'  => '2028',
				'expiry_month' => '05',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5400611072814659',
				'expiry_year'  => '2029',
				'expiry_month' => '08',
				'cvv'          => '000',
				'secure'       => '34020',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5400611056942989',
				'expiry_year'  => '2028',
				'expiry_month' => '10',
				'cvv'          => '000',
				'secure'       => '34020',
			),
		);
	}

	/**
	 * Ödeme geçidi kayıt sonrası uyarı mesajı
	 */
	public function get_save_message() {
		return array(
			'type'    => 'warning',
			'message' => __( 'Your account information has been saved. In order for POS Integrator to work properly, you need to define / define your IP address from the payment institution / bank panel or by contacting the payment institution / bank. You can check "POS Integrator -> Settings -> Status" to learn your IP address.', 'gurmepos' ),
		);
	}
}
