<?php
/**
 * Yapı Kredi ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Yapi_Kredi sınıfı.
 */
class GPOS_Yapi_Kredi extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'yapikredi';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Yapı Kredi';
	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/yapikredi.svg';

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
	public $merchant_panel = 'https://www.yapikredi.com.tr/sinirsiz-bankacilik/internet-subesi/bireysel-internet-subesi/';

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document = 'https://yardim.gurmehub.com/docs/pos-entegrator/odeme-yontemleri/yapi-kredi-sanal-pos-ayarlari/';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'EUR', 'USD', 'GBP', 'JPY', 'RUB' );

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
	 */
	public function get_test_credit_cards() {
		return array(
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4506 3470 2652 3718',
				'expiry_year'  => '2025',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '34020 ',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5400 6170 3040 0291',
				'expiry_year'  => '2025',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '34020 ',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5400 6170 0000 4909',
				'expiry_year'  => '2026',
				'expiry_month' => '09',
				'cvv'          => '000',
				'secure'       => '34020 ',
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
