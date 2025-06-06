<?php
/**
 * Tami ödeme geçidini tanımlar.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Garanti sınıfı.
 */
class GPOS_Tami extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'tami';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Tami';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/tami.svg';

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
	public $merchant_panel = 'https://portal.tami.com.tr/';

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document = 'https://yardim.gurmehub.com/docs/pos-entegrator/';

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
				'label' => __( 'Terminal ID', 'gurmepos' ),
				'model' => 'terminal_id',
			),
			array(
				'type'  => 'text',
				'label' => __( 'API Secret Key', 'gurmepos' ),
				'model' => 'merchant_api_secret_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Sabit KID Değeri', 'gurmepos' ),
				'model' => 'merchant_kid_value',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Sabit K Değeri', 'gurmepos' ),
				'model' => 'merchant_k_value',
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
				'bin'          => '5549 6050 0782 4017',
				'expiry_year'  => '2025',
				'expiry_month' => '12',
				'cvv'          => '460',
				'secure'       => '',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'credit',
				'bin'          => '4446 7631 2581 3623',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'debit',
				'bin'          => '5170 4049 4256 1157',
				'expiry_year'  => '2025',
				'expiry_month' => '10',
				'cvv'          => '329',
				'secure'       => '',
			),
			array(
				'brand'        => 'visa',
				'type'         => 'debit',
				'bin'          => '4938 4101 8080 1789',
				'expiry_year'  => '2029',
				'expiry_month' => '12',
				'cvv'          => '767',
				'secure'       => '',
			),
		);
	}
}
