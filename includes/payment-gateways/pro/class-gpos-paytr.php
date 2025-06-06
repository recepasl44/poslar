<?php
/**
 * Paratika ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_PayTR sınıfı.
 */
class GPOS_PayTR extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'paytr';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'PayTR Direct API';

	/**
	 * Bağlantı kontrolü yapılabiliyor mu ?
	 *
	 * @var boolean $check_connection_is_available
	 */
	public $check_connection_is_available = true;

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/paytr.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'refund', 'save_card', 'installment_api' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://www.paytr.com/magaza/kullanici-girisi';

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document = 'https://yardim.gurmehub.com/docs/pos-entegrator/odeme-yontemleri/paytr-sanal-pos-ayarlari/';

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
				'label' => __( 'Merchant Password', 'gurmepos' ),
				'model' => 'merchant_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Merchant Salt', 'gurmepos' ),
				'model' => 'merchant_salt',
			),
			array(
				'type'    => 'select',
				'label'   => __( 'Sync Mode', 'gurmepos' ),
				'model'   => 'sync_mode',
				'options' => array(
					'0' => __( 'Off', 'gurmepos' ),
					'1' => __( 'On', 'gurmepos' ),
				),
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
				'bin'          => '5406 6754 0667 5403',
				'expiry_year'  => '2024',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4355 0843 5508 4358',
				'expiry_year'  => '2024',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '9792 0303 9444 0796',
				'expiry_year'  => '2024',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
		);
	}

	/**
	 * Ödeme geçidinin taksit hesaplama yöntemi ile çalışan fonksiyon.
	 *
	 * @param float $rate Taksit oranı
	 * @param float $amount Taksitlendirilecek tutar.
	 *
	 * @return float
	 */
	public function installment_rate_calculate( float $rate, float $amount ) {
		$amount = ( ( $amount * 100 ) / ( 100 - $rate ) );
		return $amount;
	}

	/**
	 * Ödeme geçidi kayıt sonrası uyarı mesajı
	 */
	public function get_save_message() {
		return array(
			'type'    => 'warning',
			'message' => __( 'Your account information has been saved. In order for the POS Integrator to work properly, you need to define/define your notification address from the payment PayTR panel or by contacting PayTR. You can check the “POS Integrator -> Payment Methods -> PayTR” section to learn your notification address.', 'gurmepos' ),
		);
	}
}
