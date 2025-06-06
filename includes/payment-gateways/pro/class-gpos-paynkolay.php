<?php
/**
 * Pay N Kolay ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_PayNKolay sınıfı.
 */
class GPOS_PayNKolay extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'paynkolay';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Pay N Kolay';

	/**
	 * Pro gereksinimi
	 *
	 * @var boolean $is_need_pro
	 */
	public $is_need_pro = true;

	/**
	 * Bağlantı kontrolü yapılabiliyor mu ?
	 *
	 * @var boolean $check_connection_is_available
	 */
	public $check_connection_is_available = false;

	/**
	 * Taksitin vade farkı manuel eklenmeli mi ?
	 *
	 * @var boolean $add_fee_for_installment
	 */
	public $add_fee_for_installment = false;

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/paynkolay.svg';

	/**
	 * Desteklenilen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'EUR', 'USD' );

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular', 'installment_api', 'refund' );
	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://paynkolay.nkolayislem.com.tr/';

	/**
	 * Ödeme için gerekli alanların tanımı
	 *
	 * @return array
	 */
	public function get_payment_fields(): array {
		return array(
			array(
				'type'  => 'text',
				'label' => __( 'Sx', 'gurmepos' ),
				'model' => 'sx',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Cancel Sx', 'gurmepos' ),
				'model' => 'cancel_sx',
			),
			array(
				'type'  => 'text',
				'label' => __( 'List Sx', 'gurmepos' ),
				'model' => 'list_sx',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Merchant Secret Key', 'gurmepos' ),
				'model' => 'merchant_secret_key',
			),
		);
	}

	/**
	 * Test ödemesi için kredi kartı
	 *
	 * @return array
	 */
	public function get_test_credit_cards(): array {
		return array(
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5401 3412 3456 7891',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '001',
				'secure'       => 'a',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4155 6501 0041 6111',
				'expiry_year'  => '2050',
				'expiry_month' => '01',
				'cvv'          => '715',
				'secure'       => 'a',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5818 7758 1877 2285',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '001',
				'secure'       => 'a',
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
