<?php
/**
 * Isbank ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Isbank sınıfı.
 */
class GPOS_Is_Bankasi_GiroGate extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'isbank-girogate';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Türkiye İş Bankası (GiroGate)';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/isbank-girogate.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'check_status' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://www.isbank.com.tr/TicariInternet/Transactions/Login/FirstLogin.aspx';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array(
		'ENTERCASH (SEK, EUR)',
		'BOLETO (USD, EUR)',
		'PAYU (PLN, CZK, EUR)',
		'TRUSTLY (DKK, NOK, PLN, SEK, EUR)',
		'POLI (NZD, AUD, EUR)',
		'ENETS (SGD, EUR)',
		'SINGPOST (SGD, EUR)',
		'DRAGONPAY (PHP, EUR)',
		'ASTROPAYCARD (USD, EUR)',
		'ZIMPLER (SEK, EUR)',
		'ASTROPAYDIRECT (USD, EUR)',
		'KRUNGTHAIBANK (THB, EUR)',
		'KRUNGSIBANK (THB, EUR)',
		'SIAMCOMMERCIALBANK (THB, EUR)',
		'BANGKOKBANK (THB, EUR)',
	);

	/**
	 * Ödeme geçidi tipi
	 *
	 * @var string $payment_method_type
	 *
	 * 'virtual_pos'|'common_form_payment'|'alternative_payment'|'bank_transfer'|'shopping_credit'
	 */
	public $payment_method_type = 'alternative_payment';

	/**
	 * Ödeme geçidi form tipi
	 *
	 * @var string $payment_form_type
	 *
	 * 'card_form'|'empty_form'
	 */
	public $payment_form_type = 'isbank_girogate_form';

	/**
	 * İşbankası tarafından desteklenen yabancı geçitler.
	 *
	 * @var array
	 */
	public $gates = [
		array(
			'title'     => 'EPS',
			'img'       => 'eps.svg',
			'value'     => 'GIROGATE_EPS',
			'countries' => [ 'AT' ],
		),
		array(
			'title'     => 'giropay',
			'img'       => 'giropay.svg',
			'value'     => 'GIROGATE_GIROPAY',
			'countries' => [ 'DE' ],
		),
		array(
			'title'     => 'iDEAL',
			'img'       => 'ideal.svg',
			'value'     => 'GIROGATE_IDEAL',
			'countries' => [ 'NL' ],
		),
		array(
			'title'     => 'Alipay',
			'img'       => 'alipay.svg',
			'value'     => 'GIROGATE_ALIPAY',
			'countries' => [ 'CN' ],
		),
		array(
			'title'     => 'MyBank',
			'img'       => 'mybank.svg',
			'value'     => 'GIROGATE_MYBANK',
			'countries' => [ 'IT' ],
		),
		array(
			'title'     => 'entercash',
			'img'       => 'entercash.svg',
			'value'     => 'GIROGATE_ENTERCASH',
			'countries' => [ 'SE', 'AT', 'DE', 'FI' ],
		),
		array(
			'title'     => 'Bancontact',
			'img'       => 'bancontact.svg',
			'value'     => 'GIROGATE_BANCONTACT',
			'countries' => [ 'BE' ],
		),
		array(
			'title'     => 'Sofort',
			'img'       => 'sofort.svg',
			'value'     => 'GIROGATE_SOFORT',
			'countries' => [ 'AT', 'BE', 'DE', 'IT', 'NL', 'PL', 'ES', 'CH' ],
		),
		array(
			'title'     => 'MultiBanco',
			'img'       => 'multibanco.svg',
			'value'     => 'GIROGATE_MULTIBANCO',
			'countries' => [ 'PT' ],
		),
		array(
			'title'     => 'SafetyPay',
			'img'       => 'safetypay.svg',
			'value'     => 'GIROGATE_SAFETYPAY',
			'countries' => [
				'AT',
				'BE',
				'ES',
				'IT',
				'NL',
				'CH',
				'BR',
				'PL',
				'MX',
				'EC',
				'PE',
			],
		),
		array(
			'title'     => 'P24',
			'img'       => 'p24.svg',
			'value'     => 'GIROGATE_P24',
			'countries' => [ 'PL' ],
		),
		array(
			'title'     => 'Boleto',
			'img'       => 'boleto.svg',
			'value'     => 'GIROGATE_BOLETO',
			'countries' => [ 'BR' ],
		),
		array(
			'title'     => 'Finnish Online Banking',
			'img'       => 'finnishonlinebanking.svg',
			'value'     => 'GIROGATE_FINNISHONLINEBANKING',
			'countries' => [ 'FI' ],
		),
		array(
			'title'     => 'SEPA',
			'img'       => 'sepa.svg',
			'value'     => 'GIROGATE_SEPA',
			'countries' => [
				'AT',
				'BE',
				'BG',
				'CH',
				'CY',
				'CZ',
				'DE',
				'DK',
				'EE',
				'ES',
				'FI',
				'FR',
				'GB',
				'GR',
				'HR',
				'HU',
				'IE',
				'IS',
				'IT',
				'LI',
				'LT',
				'LU',
				'LV',
				'MC',
				'MT',
				'NL',
				'NO',
				'PL',
				'PT',
				'RO',
				'SE',
				'SI',
				'SK',
				'SM',
			],
		),
		array(
			'title'     => 'WeChat Pay',
			'img'       => 'wechatpay.svg',
			'value'     => 'GIROGATE_WECHATPAY',
			'countries' => [ 'CN' ],
		),
		array(
			'title'     => 'SingPost',
			'img'       => 'singpost.svg',
			'value'     => 'GIROGATE_SINGPOST',
			'countries' => [ 'SG' ],
		),
		array(
			'title'     => 'DragonPay',
			'img'       => 'dragonpay.svg',
			'value'     => 'GIROGATE_DRAGONPAY',
			'countries' => [ 'PH' ],
		),
		array(
			'title'     => 'Enets',
			'img'       => 'enets.svg',
			'value'     => 'GIROGATE_ENETS',
			'countries' => [ 'SG' ],
		),
		array(
			'title'     => 'PayU',
			'img'       => 'payu.svg',
			'value'     => 'GIROGATE_PAYU',
			'countries' => [ 'CZ', 'PL' ],
		),
		array(
			'title'     => 'Poli',
			'img'       => 'poli.svg',
			'value'     => 'GIROGATE_POLI',
			'countries' => [ 'NZ', 'AU' ],
		),
		array(
			'title'     => 'Trustly',
			'img'       => 'trustly.svg',
			'value'     => 'GIROGATE_TRUSTLY',
			'countries' => [ 'DK', 'EE', 'ES', 'FI', 'IT', 'NO', 'PL', 'SE' ],
		),
		array(
			'title'     => 'AstroPayCard',
			'img'       => 'astropaycard.svg',
			'value'     => 'GIROGATE_ASTROPAYCARD',
			'countries' => [ 'AR', 'BR', 'CL', 'CO', 'CR', 'MX', 'PE', 'UY', 'VE' ],
		),
		array(
			'title'     => 'Zimpler',
			'img'       => 'zimpler.svg',
			'value'     => 'GIROGATE_ZIMPLER',
			'countries' => [ 'SE', 'FI' ],
		),
		array(
			'title'     => 'AstroPayDirect',
			'img'       => 'astropaydirect.svg',
			'value'     => 'GIROGATE_ASTROPAYDIRECT',
			'countries' => [ 'AR', 'BR', 'CL', 'CO', 'MX', 'PE', 'UY', 'CN' ],
		),
		array(
			'title'     => 'Krungthai Bank',
			'img'       => 'krungthaibank.svg',
			'value'     => 'GIROGATE_KRUNGTHAIBANK',
			'countries' => [ 'TH' ],
		),
		array(
			'title'     => 'Krungsi Bank',
			'img'       => 'krungsibank.svg',
			'value'     => 'GIROGATE_KRUNGSIBANK',
			'countries' => [ 'TH' ],
		),
		array(
			'title'     => 'Siam Commercial Bank',
			'img'       => 'siamcommercialbank.svg',
			'value'     => 'GIROGATE_SIAMCOMMERCIALBANK',
			'countries' => [ 'TH' ],
		),
		array(
			'title'     => 'Bangkok Bank',
			'img'       => 'bangkokbank.svg',
			'value'     => 'GIROGATE_BANGKOKBANK',
			'countries' => [ 'TH' ],
		),
	];

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
					'3d'             => '3D',
					'3d_pay'         => '3D Pay',
					'3d_pay_hosting' => '3D Pay Hosting',
				),
				'label'   => __( '3D Type', 'gurmepos' ),
				'model'   => 'merchant_threed_type',
			),
			array(
				'type'     => 'select',
				'label'    => __( 'Methods', 'gurmepos' ),
				'model'    => 'girogate_methods',
				'multiple' => true,
				'options'  => $this->gates(),
			),
		);
	}

	/**
	 * Geçitleri option için ayarlar.
	 *
	 * @return array
	 */
	private function gates() {
		$gates = array();
		foreach ( $this->gates as $gate ) {
			$gates[ $gate['value'] ] = $gate['title'];
		}
		return $gates;
	}

	/**
	 * Test ödemesi için kredi kartı
	 *
	 * @return array
	 */
	public function get_test_credit_cards() {
		return array();
	}
}
