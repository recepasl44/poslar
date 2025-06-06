<?php
/**
 * Setcard yemek kartının tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Setcard sınıfı.
 */
class GPOS_Setcard extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği.
	 *
	 * @var string $id
	 */
	public $id = 'setcard';

	/**
	 * Ödeme geçidi başlığı.
	 *
	 * @var string $title
	 */
	public $title = 'Setcard';

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/setcard.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'refund' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://setcard.com.tr/login';

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
	public $check_connection_is_available = false;

	/**
	 * Ödeme geçidi tipi
	 *
	 * @var string $payment_method_type
	 *
	 * 'virtual_pos'|'common_form_payment'|'alternative_payment'|'bank_transfer'|'shopping_credit'
	 */
	public $payment_method_type = 'alternative_payment';

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
				'label' => __( 'Secret Key', 'gurmepos' ),
				'model' => 'secret_key',
			),
		);
	}
}
