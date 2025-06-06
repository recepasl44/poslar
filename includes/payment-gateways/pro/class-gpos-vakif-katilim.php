<?php
/**
 * Vakıf Katılım ödeme geçidinin tüm özelliklerini uygulamaya tanıtır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Vakif_Katilim sınıfı.
 */
class GPOS_Vakif_Katilim extends GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği.
	 *
	 * @var string $id
	 */
	public $id = 'vakif-katilim';

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title = 'Vakıf Katılım';
	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo = GPOS_ASSETS_DIR_URL . '/images/logo/vakif-katilim.svg';

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array( 'threed', 'regular' );

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel = 'https://isube.vakifkatilim.com.tr/Login/CorporateInitialLogin';

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
				'label' => __( 'Merchant Number', 'gurmepos' ),
				'model' => 'merchant_id',
			),

			array(
				'type'  => 'text',
				'label' => __( 'Client ID', 'gurmepos' ),
				'model' => 'client_id',
			),

			array(
				'type'  => 'text',
				'label' => __( 'Username', 'gurmepos' ),
				'model' => 'username',
			),

			array(
				'type'  => 'text',
				'label' => __( 'Password', 'gurmepos' ),
				'model' => 'password',
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
