<?php
/**
 * Denizbank ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Denizbank sınıfı.
 */
class GPOS_Denizbank extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'denizbank';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Denizbank';
	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/denizbank.svg';

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
	public $merchant_panel = 'https://acikdeniz.denizbank.com/';

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document = 'https://yardim.gurmehub.com/docs/pos-entegrator/odeme-yontemleri/denizbank-sanal-pos-rehberi/';

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
				'label' => __( 'Merchant Code', 'gurmepos' ),
				'model' => 'merchant_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Merchant Password', 'gurmepos' ),
				'model' => 'merchant_password',
			),
			array(
				'type'  => 'text',
				'label' => __( 'User Code', 'gurmepos' ),
				'model' => 'client_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'User Password', 'gurmepos' ),
				'model' => 'client_password',
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
				'bin'          => '4090 7000 9084 0057',
				'expiry_year'  => '2023',
				'expiry_month' => '01',
				'cvv'          => '592',
				'secure'       => '123456',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4090 7001 0117 4272',
				'expiry_year'  => '2023',
				'expiry_month' => '12',
				'cvv'          => '104',
				'secure'       => '123456',
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
