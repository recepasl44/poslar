<?php
/**
 * Iyzico ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Iyzico sınıfı.
 */
class GPOS_Iyzico extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'iyzico';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'iyzico';

	/**
	 * Ödeme geçidi ayar sınıfı
	 *
	 * @var string $settings_class
	 */
	public $settings_class = 'GPOS_Iyzico_Settings';

	/**
	 * Ödeme geçidi
	 *
	 * @var string $gateway_class
	 */
	public $gateway_class = 'GPOS_Iyzico_Gateway';

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://merchant.iyzipay.com/login?lang=tr';

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document = 'https://yardim.gurmehub.com/docs/pos-entegrator/odeme-yontemleri/iyzico-sanal-pos-ayarlar/';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/iyzico.svg';

	/**
	 * Desteklenilen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'EUR', 'USD', 'GBP', 'IRR', 'NOK', 'RUB', 'CHF' );

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'refund', 'save_card', 'installment_api' );

	/**
	 * Kırılım bazlı mı ?
	 *
	 * @var boolean $line_based
	 */
	public $line_based = true;

	/**
	 * Bağlantı kontrolü yapılabiliyor mu ?
	 *
	 * @var boolean $check_connection_is_available
	 */
	public $check_connection_is_available = true;

	/**
	 * Ödeme geçidi açıklama alanı
	 */
	public function get_description() {
		return __( 'As of 31/01/2024, it can be used with the PRO plugin, it has been removed from the free plugin.', 'gurmepos' );
	}

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
				'label' => __( 'Security Key', 'gurmepos' ),
				'model' => 'api_secret',
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
				'bin'          => '5526 0800 0000 0006',
				'expiry_year'  => '2030',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '283126',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4603 4500 0000 0000',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '283126',
			),
			array(
				'brand'        => 'amex',
				'type'         => 'credit',
				'bin'          => '3744 2700 0000 003',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '283126',
			),
		);
	}
}
