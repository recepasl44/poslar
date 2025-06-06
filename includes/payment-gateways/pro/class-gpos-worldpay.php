<?php
/**
 * Garanti Pay ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Garanti sınıfı.
 */
class GPOS_WorldPAY extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id = 'worldpay';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'World Pay';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/worldpay.svg';

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https:// www.yapikredi.com.tr/sinirsiz-bankacilik/mobil-bankacilik/world-pay';

	/**
	 * Desteklenen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array( 'TRY' );

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
	public $payment_form_type = 'worldpay';

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
				'label' => __( '3D Key (ENCKey)', 'gurmepos' ),
				'model' => 'merchant_threed_store_key',
			),
			array(
				'type'  => 'text',
				'label' => __( 'PosNet ID', 'gurmepos' ),
				'model' => 'posnet_id',
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
}
