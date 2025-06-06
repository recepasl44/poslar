<?php
/**
 * AKÖde ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Akode sınıfı.
 */
class GPOS_Akode extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'akode';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Tosla (Aköde)';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/tosla.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'refund', 'installment_api' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://tosla.com/isim-icin';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY' );

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
				'label' => __( 'Client ID', 'gurmepos' ),
				'model' => 'client_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'API User', 'gurmepos' ),
				'model' => 'api_user',
			),
			array(
				'type'  => 'text',
				'label' => __( 'API Password', 'gurmepos' ),
				'model' => 'api_password',
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
				'bin'          => '4159 5600 4741 7732',
				'expiry_year'  => '2024',
				'expiry_month' => '08',
				'cvv'          => '123',
				'secure'       => '',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '5571 1355 7113 5575',
				'expiry_year'  => '2024',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4119 7901 5520 3496',
				'expiry_year'  => '2024',
				'expiry_month' => '04',
				'cvv'          => '579',
				'secure'       => '',
			),
		);
	}
}
