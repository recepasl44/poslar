<?php
/**
 * Iyzico ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Iyzico sınıfı.
 */
class GPOS_Iyzico_IFrame extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'iyzico-iframe';

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
	public $settings_class = 'GPOS_Iyzico_IFrame_Settings';

	/**
	 * Ödeme geçidi
	 *
	 * @var string $gateway_class
	 */
	public $gateway_class = 'GPOS_Iyzico_IFrame_Gateway';

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://merchant.iyzipay.com/login?lang=tr';

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
	public $supports = array( 'threed', 'regular', 'refund' );

	/**
	 * Pro gereksinimi
	 *
	 * @var boolean $is_need_pro
	 */
	public $is_need_pro = false;

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
	 * Ödeme geçidi tipi
	 *
	 * @var string $payment_method_type
	 *
	 * 'virtual_pos'|'common_form_payment'|'alternative_payment'|'bank_transfer'|'shopping_credit'|'iframe_payment
	 */
	public $payment_method_type = 'iframe_payment';

	/**
	 * Ödeme geçidi form tipi
	 *
	 * @var string $payment_form_type
	 *
	 * 'card_form'|'empty_form'
	 */
	public $payment_form_type = 'empty_form';

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
			array(
				'type'    => 'select',
				'label'   => __( 'View Type', 'gurmepos' ),
				'model'   => 'type',
				'options' => array(
					'popup'      => __( 'Popup', 'gurmepos' ),
					'responsive' => __( 'Responsive', 'gurmepos' ),
				),
			),
		);
	}
}
