<?php
/**
 * Papara ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOS_Papara_Checkout_Gateway) barındırır.
 *
 * @package Gurmehub
 */

	/**
	 * GPOS_Papara_Checkout_Gateway sınıfı.
	 */
class GPOS_Papara_Checkout_Gateway extends GPOS_Payment_Gateway {

	/**
	 * Ödeme sunucusu.
	 *
	 * @var string $request_url;
	 */
	private $request_url;

	/**
	 * Papara tarafından verilen api secret bilgisi.
	 *
	 * @var string $api_secret;
	 */
	private $api_secret; // @phpstan-ignore-line

	/**
	 * Papara tarafından verilen api key bilgisi.
	 *
	 * @var string $api_key;
	 */
	private $api_key;

	/**
	 * Ödeme kuruluşunun bağlantı testi
	 *
	 * @param stdClass $connection_data Ödeme geçidi ayarları.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function check_connection( $connection_data ) {
		$this->prepare_settings( $connection_data );
		$response = $this->http_request->set_headers( $this->prepare_header() )->request( "{$this->request_url}/account", 'GET' );
		return array(
			'result'  => is_array( $response ) ? 'success' : 'error',
			'message' => is_array( $response ) ? __( 'Connection Success', 'gurmepos' ) : __( 'Connection Failed', 'gurmepos' ),
		);
	}

	/**
	 * Apilerinde taksit bilgisi gönderen kuruluşlar için otomatik getirir.
	 *
	 * @return array|bool Destek var ise taksitler yok ise false.
	 */
	public function get_installments() {
		return false;
	}

	/**
	 * Papara tarafına gönderilen orderDescription alanını düzenler.
	 */
	public function get_order_desc() {
		$lines       = array_map( fn( $line ) => $line->get_name(), $this->transaction->get_lines() );
		$description = implode( ', ', $lines );

		if ( strlen( $description ) > 95 ) {
			return substr( $description, 0, 92 ) . '...';
		}

		return $description;
	}

	/**
	 * GPOS_Papara_Checkout_Gateway kurucu fonksiyon değerindedir gerekli ayarlamaları yapar.
	 *
	 * @param GPOS_Papara_Checkout_Settings|stdClass $settings Ödeme geçidi ayarlarını içerir.
	 *
	 * @return void
	 */
	public function prepare_settings( $settings ) {
		$is_test_mode      = gpos_is_test_mode();
		$this->request_url = $is_test_mode ? 'https://merchant-api.test.papara.com' : 'https://merchant-api.papara.com';
		$this->api_key     = $is_test_mode ? $settings->test_api_key : $settings->api_key;
		$this->api_secret  = $is_test_mode ? $settings->test_secure_key : $settings->secure_key;
	}

	/**
	 * Ödeme işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_payment() {
		$request = array(
			'Amount'              => number_format( (float) $this->transaction->get_total(), 2, '.', '' ),
			'referenceId'         => $this->transaction->get_id(),
			'notificationUrl'     => $this->get_notify_url(),
			'failNotificationUrl' => $this->get_callback_url(),
			'redirectUrl'         => $this->get_callback_url(),
			'orderDescription'    => $this->get_order_desc(),
			'currency'            => $this->transaction->get_currency(),
		);

		$response = $this->http_request->set_headers( $this->prepare_header() )->request(
			"{$this->request_url}/payments",
			'POST',
			wp_json_encode( $request )
		);

		if ( ! is_array( $response ) ) {
			return $this->gateway_response->set_error_message( 'Lütfen Papara API bilgilerinizi kontrol ediniz.' );
		}

		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_START_ALTERNATIVE, $request, $response );

		if ( array_key_exists( 'succeeded', $response ) && true === $response['succeeded'] ) {
			return $this->gateway_response->set_alternative_payment_url( $response['data']['paymentUrl'] )->set_success( true );
		}

		return $this->gateway_response->set_error_message( $response['error']['message'] )->set_error_code( $response['error']['code'] );
	}

	/**
	 * 3D Ödeme işlemleri için geri dönüş fonksiyonu.
	 *
	 * @param array $post_data Geri dönüş verileri.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_callback( array $post_data ) {
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_CALLBACK, [], $post_data );
		$this->gateway_response->set_error_message( __( 'Payment transaction not confirmed by Papara', 'gurmepos' ) );
		if ( true === array_key_exists( 'status', $post_data ) ) {
			$payment_id = $post_data['paymentId'];
			$response   = $this->http_request->set_headers( $this->prepare_header() )->request(
				"{$this->request_url}/payments?id={$payment_id}",
				'GET'
			);

			if ( $response['succeeded'] && (int) $response['data']['referenceId'] === (int) $this->transaction->get_id() && 1 === (int) $response['data']['status'] ) {
				$this->gateway_response->set_success( true )->set_payment_id( $payment_id );
			}

			$this->log( GPOS_Transaction_Utils::LOG_PROCESS_FINISH, array( 'id' => $payment_id ), $response );
		}

		return $this->gateway_response;
	}

		/**
		 * Papara veri kontrolü.
		 *
		 * @param array $post_data Papara tarafından gönderilen veriler.
		 */
	public function check_notify( array $post_data ) {
		try {
			$plugin_gateway = gpos_get_plugin_gateway_by_transaction( $this->transaction );
			$this->log( GPOS_Transaction_Utils::LOG_PROCESS_NOTIFY, [], $post_data );
			$this->process_callback( $post_data );
			if ( $this->gateway_response->is_success() ) {
				$plugin_gateway->transaction_success_process( $this->gateway_response );
			} else {
				$plugin_gateway->transaction_error_process( $this->gateway_response );
			}
			$plugin_gateway->notify_process( $this->gateway_response );
		} catch ( Exception $e ) {
			/**
			 * Empty Block
			 */
		}
	}

	/**
	 * Ödeme iptal işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_cancel() {
		return $this->process_refund( $this->transaction->get_payment_id(), $this->transaction->get_total() );
	}

	/**
	 * Ödeme iade işlemi fonksiyonu.
	 *
	 * @param int|string $payment_id İade işlemi yapılacak olan ödeme numarası.
	 * @param int|float  $refund_total İade.
	 *
	 * @return GPOS_Gateway_Response
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function process_refund( $payment_id, $refund_total ) {
		$request  = array(
			'paymentId'    => $payment_id,
			'refundAmount' => $refund_total,
		);
		$response = $this->http_request->set_headers( $this->prepare_header() )->request( "{$this->request_url}/payments/refund", 'POST', wp_json_encode( $request ) );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_REFUND, $request, $response );

		if ( $response['succeeded'] ) {
			$this->gateway_response->set_success( true );
		} else {
			$this->gateway_response->set_error_message( $response['error']['message'] );
		}
		return $this->gateway_response;
	}

	/**
	 * Papara istekleri için header bilgisini ayarlar.
	 *
	 * @return array
	 */
	private function prepare_header() {
		return array(
			'Content-Type' => 'application/json',
			'ApiKey'       => $this->api_key,
		);
	}

	/**
	 * İşlem durumunun kontrolü moethodu.
	 *
	 * @param string $payment_id Ödeme işlem numarası.
	 *
	 * @return GPOS_Gateway_Response
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function check_status( $payment_id ) {
		return $this->gateway_response;
	}
}
