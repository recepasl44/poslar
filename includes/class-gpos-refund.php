<?php
/**
 * GurmePOS için iptal ve iade işlemlerinde kullanılacak sınıf olan GPOS_Refund sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS iade ve iptal sınıfı
 */
class GPOS_Refund {

	/**
	 * İade veya İptal edilecek olan işlem.
	 *
	 * @var GPOS_Transaction $transaction
	 */
	protected $payment_transaction;

	/**
	 * İade veya İptal işlemi.
	 *
	 * @var GPOS_Transaction $refund_transaction
	 */
	protected $refund_transaction;

	/**
	 * İptal veya iadenin yapılacağı ödeme geçidi.
	 *
	 * @var GPOS_Payment_Gateway $gateway
	 */
	protected $gateway;

	/**
	 * İade tutarı.
	 *
	 * @var float|int $total
	 */
	protected $total;

	/**
	 * İptal veya iadenin nedeni.
	 *
	 * @var string $reason
	 */
	protected $reason;


	/**
	 * GPOS_Refund kurucu method.
	 *
	 * @param GPOS_Transaction $payment_transaction İade veya iptal edilecek olan işlem.
	 *
	 * @return void.
	 */
	public function __construct( GPOS_Transaction $payment_transaction ) {
		$this->payment_transaction = $payment_transaction;
	}

	/**
	 * İptal işlemi.
	 */
	public function cancel() {
		$this->prepare_process( GPOS_Transaction_Utils::CANCEL );
		$response = $this->gateway->set_transaction( $this->refund_transaction )->process_cancel();

		if ( $response->is_success() ) {
			$this->refund_transaction->set_status( GPOS_Transaction_Utils::COMPLETED );
			$this->refund_transaction->add_note(
				// translators: %s => Ödeme geçidi benzersiz numarası.
				sprintf( __( 'Cancel process completed successfully. Process Id: %s.', 'gurmepos' ), $response->get_payment_id() ),
				'complete'
			);
			$this->payment_transaction->set_refund_status( GPOS_Transaction_Utils::REFUND_STATUS_CANCELLED );
			$this->payment_transaction->update_lines_status( GPOS_Transaction_Utils::LINE_REFUNDED );
			$this->tracker( GPOS_Transaction_Utils::CANCEL, $response->get_gateway() );
			do_action( "gpos_{$this->payment_transaction->get_plugin()}_transaction_canceled", $this->payment_transaction );
			do_action( 'gpos_transaction_canceled', $this->refund_transaction );
		} else {
			$this->refund_transaction->set_status( GPOS_Transaction_Utils::FAILED );
			$this->refund_transaction->add_note( $response->get_error_message(), 'failed' );
		}

		return $response;
	}

	/**
	 * İşlem bazlı iade işlemi.
	 *
	 * @param int|string $payment_id İade işlemi yapılacak olan ödeme numarası.
	 */
	public function refund( $payment_id ) {
		$this->prepare_process( GPOS_Transaction_Utils::REFUND );
		$this->refund_transaction->set_total( $this->payment_transaction->get_total() );
		$response = $this->gateway->set_transaction( $this->refund_transaction )->process_refund( $payment_id, $this->refund_transaction->get_total() );

		if ( $response->is_success() ) {
			$this->refund_transaction->set_status( GPOS_Transaction_Utils::COMPLETED );
			$this->refund_transaction->add_note(
				// translators: %s => Ödeme geçidi benzersiz numarası.
				sprintf( __( 'Refund process completed successfully. Process Id: %s.', 'gurmepos' ), $response->get_payment_id() ),
				'complete'
			);
			$this->payment_transaction->set_refund_status( GPOS_Transaction_Utils::REFUND_STATUS_REFUNDED );
			$this->payment_transaction->update_lines_status( GPOS_Transaction_Utils::LINE_REFUNDED );
			$this->tracker( GPOS_Transaction_Utils::REFUND, $response->get_gateway() );
			do_action( "gpos_{$this->payment_transaction->get_plugin()}_transaction_refunded", $this->payment_transaction );
			do_action( 'gpos_transaction_refunded', $this->refund_transaction );
		} else {
			$this->refund_transaction->set_status( GPOS_Transaction_Utils::FAILED );
			$this->refund_transaction->add_note( $response->get_error_message(), 'failed' );
		}

		return $response;
	}

	/**
	 * Satır bazlı iade işlemi.
	 *
	 * @param int|string $line_id İade işlemi yapılacak olan satırın benzersiz numarası.
	 * @param int|float  $total İade tutarı.
	 */
	public function line_based_refund( $line_id, $total ) {

		$this->prepare_process( GPOS_Transaction_Utils::REFUND );

		$this->refund_transaction->set_total( $total );
		$line       = gpos_transaction_line( $line_id );
		$payment_id = $line->get_payment_id() ? $line->get_payment_id() : $this->payment_transaction->get_payment_id();
		$response   = $this->gateway->set_transaction( $this->refund_transaction )->process_refund( $payment_id, $total );
		if ( $response->is_success() ) {
			$this->refund_transaction->set_status( GPOS_Transaction_Utils::COMPLETED );
			$this->refund_transaction->add_note(
				// translators: %s => Ödeme geçidi benzersiz numarası.
				sprintf( __( 'Refund process completed successfully. Process Id: %s.', 'gurmepos' ), $response->get_payment_id() ),
				'complete'
			);
			$line->set_refunded_total( $total + (float) $line->get_refunded_total() );
			$line->set_status( $line->get_refundable_total() ? GPOS_Transaction_Utils::LINE_PARTIAL_REFUNDED : GPOS_Transaction_Utils::LINE_REFUNDED );

			$statuses     = array_map( fn( $line ) => $line->get_status(), $this->payment_transaction->get_lines() );
			$all_refunded = 1 === count( array_unique( $statuses, SORT_REGULAR ) ) && GPOS_Transaction_Utils::LINE_REFUNDED === $statuses[ array_key_first( $statuses ) ];
			$this->payment_transaction->set_refund_status( $all_refunded ? GPOS_Transaction_Utils::REFUND_STATUS_REFUNDED : GPOS_Transaction_Utils::REFUND_STATUS_PARTIAL_REFUNDED );
			$this->tracker( GPOS_Transaction_Utils::REFUND, $response->get_gateway() );
			do_action( 'gpos_transaction_line_refunded', $this->refund_transaction, $line );
		} else {
			$this->refund_transaction->set_status( GPOS_Transaction_Utils::FAILED );
			$this->refund_transaction->add_note( $response->get_error_message(), 'failed' );
		}

		return $response;
	}

	/**
	 * İşlemler için veri toplama.
	 *
	 * @param string $type İşlem tipi.
	 * @param string $gateway Ödeme geçidi.
	 *
	 * @return void
	 */
	private function tracker( $type, $gateway ) {
		gpos_tracker()->schedule_event(
			'transaction',
			array(
				'type'            => $type,
				'site'            => home_url(),
				'payment_gateway' => str_replace( [ 'GPOS', 'PRO', '_', 'Gateway' ], '', $gateway ),
				'payment_plugin'  => $this->refund_transaction->get_plugin(),
				'total'           => $this->refund_transaction->get_total(),
				'currency'        => $this->refund_transaction->get_currency(),
				'is_test'         => gpos_is_test_mode(),
			)
		);
	}

	/**
	 * Yeni işlem için GPOS_Transaction oluşturulur ve ödeme işlemindeki verileri yeni işleme devreder.
	 *
	 * @param string $type İşlem tipi.
	 *
	 * @return void
	 */
	private function prepare_process( $type ) {
		$this->refund_transaction = gpos_transaction();
		$this->refund_transaction
		->set_type( $type )
		->set_total( $this->payment_transaction->get_total() )
		->set_plugin_transaction_id( $this->payment_transaction->get_plugin_transaction_id() )
		->set_plugin( $this->payment_transaction->get_plugin() )
		->set_payment_id( $this->payment_transaction->get_payment_id() )
		->set_currency( $this->payment_transaction->get_currency() )
		->set_customer_id( $this->payment_transaction->get_customer_id() )
		->set_customer_first_name( $this->payment_transaction->get_customer_first_name() )
		->set_customer_last_name( $this->payment_transaction->get_customer_last_name() )
		->set_customer_address( $this->payment_transaction->get_customer_address() )
		->set_customer_state( $this->payment_transaction->get_customer_state() )
		->set_customer_city( $this->payment_transaction->get_customer_city() )
		->set_customer_country( $this->payment_transaction->get_customer_country() )
		->set_customer_phone( $this->payment_transaction->get_customer_phone() )
		->set_customer_email( $this->payment_transaction->get_customer_email() )
		->set_customer_ip_address( $this->payment_transaction->get_customer_ip_address() )
		->set_masked_card_bin( $this->payment_transaction->get_masked_card_bin() )
		->set_card_cvv( $this->payment_transaction->get_card_cvv() )
		->set_card_expiry_month( $this->payment_transaction->get_card_expiry_month() )
		->set_card_expiry_year( $this->payment_transaction->get_card_expiry_year() )
		->set_installment( $this->payment_transaction->get_installment() )
		->set_card_holder_name( $this->payment_transaction->get_card_holder_name() )
		->set_card_type( $this->payment_transaction->get_card_type() )
		->set_card_brand( $this->payment_transaction->get_card_brand() )
		->set_card_family( $this->payment_transaction->get_card_family() )
		->set_card_bank_name( $this->payment_transaction->get_card_bank_name() )
		->set_card_country( $this->payment_transaction->get_card_country() )
		->set_payment_transaction_id( $this->payment_transaction->get_id() )
		->set_use_saved_card( $this->payment_transaction->need_use_saved_card() );
		$this->gateway = gpos_payment_gateways()->get_gateway_by_account_id( $this->payment_transaction->get_account_id(), $this->refund_transaction );
	}
}
