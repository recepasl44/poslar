<?php
/**
 * Akbank ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Akbank sınıfı.
 */
class GPOS_Akbank_Json extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'akbank-json';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Akbank (Yeni JSON API)';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/akbank-json.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'regular', 'threed', 'refund' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://sanalpos.akbank.com/';

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document = 'https://yardim.gurmehub.com/docs/pos-entegrator/odeme-yontemleri/akbank-sanal-pos-ayarlar/';

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
				'label' => __( 'Merchant Safe ID', 'gurmepos' ),
				'model' => 'merchant_safe_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Terminal Safe ID', 'gurmepos' ),
				'model' => 'terminal_safe_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Secret Key', 'gurmepos' ),
				'model' => 'secret_key',
			),
			array(
				'type'    => 'select',
				'options' => array(
					'3D'     => '3D',
					'3D_PAY' => '3D Pay',
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
				'bin'          => '4355 0930 0031 5232',
				'expiry_year'  => '2035',
				'expiry_month' => '11',
				'cvv'          => '665',
				'secure'       => '123456',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5218  0760  0740  2834',
				'expiry_year'  => '2040',
				'expiry_month' => '11',
				'cvv'          => '820',
				'secure'       => '123456',
			),
		);
	}
}
