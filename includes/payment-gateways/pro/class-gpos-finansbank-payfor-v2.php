<?php
/**
 * Paratika ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Finansbank sınıfı.
 */
class GPOS_Finansbank_Payfor_V2 extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'qnbfinansbank-payfor-v2';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Finansbank (PayFor V2)';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/finansbank.svg';

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
	public $merchant_panel = 'https://internetsubesi.qnbfinansbank.com/Login/LoginPage.aspx';


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
				'label' => __( 'Corporate Code', 'gurmepos' ),
				'model' => 'corporate_code',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Merchant Number', 'gurmepos' ), // translator: Üye İşyeri Numarası
				'model' => 'merchant_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Username', 'gurmepos' ),
				'model' => 'merchant_user',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Password', 'gurmepos' ),
				'model' => 'merchant_password',
			),
			array(
				'type'  => 'text',
				'label' => __( '3D Key', 'gurmepos' ),
				'model' => 'merchant_threed_store_key',
			),
			array(
				'type'    => 'select',
				'options' => array(
					'3DModel' => '3D',
					'3DPay'   => '3D Pay',
				),
				'label'   => __( '3D Type', 'gurmepos' ),
				'model'   => 'merchant_threed_type',
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
		);
	}
}
