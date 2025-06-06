<?php
/**
 * Dummy Payment ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOS_Iyzico_Gateway) barındırır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Dummy_Payment_Gateway sınıfı.
 */
class GPOS_Dummy_Payment_Gateway extends GPOS_Payment_Gateway {


	/**
	 * Ödeme geçidi ayarlarını taşır.
	 *
	 * @var \Iyzipay\Options $settings;
	 */
	public $settings;

	/**
	 * Ödeme kuruluşunun bağlantı testi
	 *
	 * @param stdClass $connection_data Ödeme geçidi ayarları.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function check_connection( $connection_data ) {
		return true;
	}

	/**
	 * Apilerinde taksit bilgisi gönderen kuruluşlar için otomatik getirir.
	 */
	public function get_installments() {
		return false;
	}

	/**
	 * GPOS_Iyzico_Gateway kurucu fonksiyon değerindedir gerekli ayarlamaları yapar.
	 *
	 * @param GPOS_Iyzico_Settings|stdClass $settings Ödeme geçidi ayarlarını içerir.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function prepare_settings( $settings ) {
	}

	/**
	 * Test ödemesi için kredi kartı
	 *
	 * @return array
	 */
	private function get_test_credit_cards() {
		return array(
			array(
				'bin'          => '5526080000000006',
				'expiry_year'  => '30',
				'expiry_month' => '12',
				'cvv'          => '000',
			),
			array(
				'bin'          => '4603450000000000',
				'expiry_year'  => '26',
				'expiry_month' => '12',
				'cvv'          => '000',
			),
		);
	}
	/**
	 * Ödeme işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_payment() {
		$status = false;
		foreach ( $this->get_test_credit_cards() as $card ) {
			if ( $card['bin'] === $this->transaction->get_card_bin() && $card['expiry_month'] === $this->transaction->get_card_expiry_month() && $card['cvv'] === $this->transaction->get_card_cvv() ) {
				$status = true;
				break;
			}
		}

		$this->transaction->set_security_type( GPOS_Transaction_Utils::REGULAR );

		if ( true === $status ) {
			$this->gateway_response->set_success( true )->set_payment_id( time() );
		} else {
			$this->gateway_response->set_error_message( __( 'Card information is incorrect', 'gurmepos' ) )->set_error_code( 404 );
		}
		return $this->gateway_response;
	}

	/**
	 * 3D Ödeme işlemleri için geri dönüş fonksiyonu.
	 *
	 * @param array $post_data Geri dönüş verileri.
	 *
	 * @return GPOS_Gateway_Response
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function process_callback( array $post_data ) {
		return $this->gateway_response;
	}

	/**
	 * Ödeme iptal işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_cancel() {
		return $this->gateway_response;
	}

	/**
	 * Ödeme iade işlemi fonksiyonu.
	 *
	 * @param int|string $payment_id İade işlemi yapılacak olan ödeme numarası.
	 * @param int|float  $refund_total İade.
	 *
	 * @return GPOS_Gateway_Response
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function process_refund( $payment_id, $refund_total ) {
		return $this->gateway_response;
	}
	/**
	 * İşlem durumunun kontrolü methodu.
	 *
	 * @param string $payment_id Ödeme işlem numarası.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function check_status( $payment_id ) {
		return $this->gateway_response;
	}
}
