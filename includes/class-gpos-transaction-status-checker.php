<?php
/**
 * GurmePOS için zamanlanmış görevler ile işlem durumunu kontrol eden sınıfı barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * İşlem durumlarını kontrol zamanlayıcı ile kontrol eden sınıf.
 */
class GPOS_Transaction_Status_Checker {

	/**
	 * İşlem
	 *
	 * @var GPOS_Transaction $transaction İşlem
	 */
	public $transaction;

	/**
	 * İptal veya iadenin yapılacağı ödeme geçidi.
	 *
	 * @var GPOS_Payment_Gateway|false $gateway
	 */
	protected $gateway;

	/**
	 * Hesap ataması yapılmamış ödeme geçidi.
	 *
	 * @var GPOS_Gateway $gateway
	 */
	public $base_gateway;

	/**
	 * Sınıfı kontrole hazırlar
	 *
	 * @param int|string $transaction_id İşlem numarası.
	 */
	public function prepare( $transaction_id ) {
		$this->transaction  = gpos_transaction( $transaction_id );
		$this->gateway      = gpos_payment_gateways()->get_gateway_by_account_id( $this->transaction->get_account_id(), $this->transaction );
		$this->base_gateway = gpos_payment_gateways()->get_base_gateway_by_gateway_id( $this->transaction->get_payment_gateway_id() );
	}

	/**
	 * İşlem durumu kontrol zamanlayıcı ile kontrol eden method.
	 *
	 * @param int|string $transaction_id İşlem numarası.
	 */
	public function add_schedule_check( $transaction_id ) {
		wp_schedule_single_event( strtotime( '+5 minutes' ), 'gpos_transaction_check_status', [ $transaction_id ] );
	}

	/**
	 * İşlem durumu kontrol zamanlayıcı ile kontrol eden method.
	 *
	 * @param int|string $transaction_id İşlem numarası.
	 */
	public function scheduled_check( $transaction_id ) {
		$this->prepare( $transaction_id );
		$this->check();
	}

	/**
	 * İşlem durumu kontrol zamanlayıcı ile kontrol eden method.
	 *
	 * @param int|string $transaction_id İşlem numarası.
	 *
	 * @return mixed
	 */
	public function status_check( $transaction_id ) {
		$this->prepare( $transaction_id );
		$this->check();
	}

	/**
	 * İşlem durum kontorlü
	 *
	 * @return mixed|void
	 */
	private function check() {
		if ( $this->base_gateway && in_array( 'check_status', $this->base_gateway->supports, true ) && $this->gateway ) {
			$response       = $this->gateway->check_status( $this->transaction->get_id() );
			$plugin_gateway = gpos_get_plugin_gateway_by_transaction( $this->transaction );
			if ( $response->is_success() ) {
				$plugin_gateway->transaction_success_process( $response );
			} else {
				$plugin_gateway->transaction_error_process( $response );
			}
			$plugin_gateway->notify_process( $response );
		}
	}
}
