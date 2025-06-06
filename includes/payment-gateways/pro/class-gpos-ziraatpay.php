<?php
/**
 * Ziraat ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Ziraat sınıfı
 */
class GPOS_ZiraatPay extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği.
	 *
	 * @var string $id
	 */
	public $id = 'ziraatpay';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'ZiraatPay';
	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/ziraatpay.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'refund', 'check_connection' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://vpos.ziraatpay.com.tr/ziraatpay/admin/login';

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
				'label' => __( 'Institution/Company ID', 'gurmepos' ),
				'model' => 'merchant',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Username/E-mail', 'gurmepos' ),
				'model' => 'merchant_user',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Password', 'gurmepos' ),
				'model' => 'merchant_password',
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
				'bin'          => '4508 0345 0803 4509',
				'expiry_year'  => '2030',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5400 2454 0024 5409',
				'expiry_year'  => '2030',
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
}
