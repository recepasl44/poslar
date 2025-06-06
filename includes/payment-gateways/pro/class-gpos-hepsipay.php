<?php
/**
 * Hepsipay ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Hepsipay sınıfı.
 */
class GPOS_Hepsipay extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'hepsipay';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'HepsiPay';

	/**
	 * Ödeme geçidi tipi
	 *
	 * @var string $payment_method_type
	 *
	 * 'virtual_pos'|'common_form_payment'|'alternative_payment'|'bank_transfer'|'shopping_credit'
	 */
	public $payment_method_type = 'common_form_payment';

	/**
	 * Ödeme geçidi form tipi
	 *
	 * @var string $payment_form_type
	 *
	 * 'card_form'|'empty_form'
	 */
	public $payment_form_type = 'empty_form';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/hepsipay.svg';

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://www.hepsipay.com/tr';

	/**
	 * Desteklenilen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY', 'USD' );

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'refund' );

	/**
	 * Ödeme için gerekli alanların tanımı
	 *
	 * @return array
	 */
	public function get_payment_fields() {
		return array(
			array(
				'type'  => 'text',
				'label' => __( 'Merchant No', 'gurmepos' ),
				'model' => 'merchant_no',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Terminal No', 'gurmepos' ),
				'model' => 'terminal_no',
			),
			array(
				'type'  => 'text',
				'label' => __( 'Store Key', 'gurmepos' ),
				'model' => 'store_key',
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
				'bin'          => '5571 1355 7113 5575',
				'expiry_year'  => '2026',
				'expiry_month' => '12',
				'cvv'          => '000',
				'secure'       => '',
			),
			array(
				'brand'        => 'mastercard',
				'type'         => 'credit',
				'bin'          => '4824 8924 5372 5018',
				'expiry_year'  => '2025',
				'expiry_month' => '01',
				'cvv'          => '567',
				'secure'       => '147852',
			),
		);
	}
}
