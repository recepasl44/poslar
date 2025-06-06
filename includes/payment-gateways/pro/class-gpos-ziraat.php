<?php
/**
 * Ziraat ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Ziraat sınıfı.
 */
class GPOS_Ziraat extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'ziraat';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Ziraat';
	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/ziraat.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'refund', 'check_status' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://kurumsal.ziraatbank.com.tr/Transactions/Login/FirstLogin.aspx?customertype=crp';

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document = 'https://yardim.gurmehub.com/docs/pos-entegrator/odeme-yontemleri/ziraat-bankasi-sanal-pos-ayarlari/';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'EUR', 'USD' );

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
					'3d_host'        => '3D Host',
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
		return array();
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
