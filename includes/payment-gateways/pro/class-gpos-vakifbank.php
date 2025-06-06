<?php
/**
 * Vakıfbank ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Vakifbank sınıfı.
 */
class GPOS_Vakifbank extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'vakifbank';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Vakıfbank';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/vakifbank.svg';

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
	public $merchant_panel = 'https://subesiz.vakifbank.com.tr/ticari/login/sifre';

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document = 'https://yardim.gurmehub.com/docs/pos-entegrator/odeme-yontemleri/vakifbank-sanal-pos-ayarlari/';

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
				'label' => __( 'Merchant Password', 'gurmepos' ),
				'model' => 'merchant_password',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Terminal ID', 'gurmepos' ),
				'model' => 'terminal_id',
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
				'bin'          => '5521 0101 4082 9928',
				'expiry_year'  => '2029',
				'expiry_month' => '12',
				'cvv'          => '691',
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
