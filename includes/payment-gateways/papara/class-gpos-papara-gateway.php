<?php
/**
 * Papara ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOS_Papara_Gateway) barındırır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Papara_Gateway sınıfı.
 */
class GPOS_Papara_Gateway extends GPOS_Payment_Gateway {

	/**
	 * Papara Api Adresi
	 *
	 * @var string  $request_url
	 */
	public $request_url;

	/**
	 * Papara Payment Api Adresi
	 *
	 * @var string  $payment_url
	 */
	public $payment_url;

	/**
	 * Papara Api Ayar Dizisi
	 *
	 * @var array $settings
	 */
	public $settings;

	/**
	 * Papara Doğrulama Verileri
	 *
	 * @var array $request_header
	 */
	public $request_header;

	/**
	 * GPOS_Papara_Gateway kurucu fonksiyon değerindedir gerekli ayarlamaları yapar.
	 *
	 * @param GPOS_Papara_Settings|stdClass $settings Ödeme geçidi ayarlarını içerir.
	 *
	 * @return void
	 */
	public function prepare_settings( $settings ) {
		$is_test_mode      = gpos_is_test_mode();
		$this->request_url = $is_test_mode ? 'https://merchant-api.test.papara.com/v1/vpos' : 'https://merchant-api.papara.com/v1/vpos';

		$this->settings = array(
			'api_key'              => $is_test_mode ? $settings->test_api_key : $settings->api_key,
			'application_password' => $is_test_mode ? $settings->test_secure_key : $settings->secure_key,
		);

		$this->http_request->set_headers(
			array(
				'ApiKey'       => $this->settings['api_key'],
				'Content-Type' => 'application/json',
			)
		);
	}

	/**
	 * Ödeme kuruluşunun bağlantı testi
	 *
	 * @param stdClass $connection_data Ödeme geçidi ayarları.
	 */
	public function check_connection( $connection_data ) {
		$this->prepare_settings( $connection_data );
		$response = $this->http_request->request(
			$this->request_url . '/installment-options',
			'POST',
			wp_json_encode(
				array(
					'OrderId'  => time(),
					'Amount'   => 1,
					'Currency' => 'TRY',
					'CardBin'  => '40591712',
				)
			)
		);

		return array(
			'result'  => isset( $response['succeeded'] ) ? 'success' : 'error',
			'message' => isset( $response['succeeded'] ) ? __( 'Connection Success', 'gurmepos' ) : __( 'Please check your API information.', 'gurmepos' ),
		);
	}

	/**
	 * Papara İşlem Sonucu Doğrulama.
	 *
	 * @param array $response Ödeme geçidinden gelen dönüş verisi.
	 *
	 * @throws Exception Ödeme kontrol hatası.
	 */
	private function check_payment( $response ) {
		if ( is_array( $response ) && isset( $response['succeeded'] ) && true === $response['succeeded'] && isset( $response['data'] ) && '0000' === $response['data']['resultCode'] ) {
			$this->gateway_response->set_success( true )->set_payment_id( $response['data']['orderId'] );
		} else {
			throw new Exception( esc_html( $response['error']['message'] ) );
		}
	}

	/**
	 * Ödeme işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_payment() {
		$basket_amount = 0.0;
		foreach ( $this->transaction->get_lines( array( 'product' ) ) as $line ) {
			$basket_amount += $line->get_total();
		}

		$payment_request = [
			'OrderId'        => $this->transaction->get_id(),
			'Amount'         => number_format( (float) $basket_amount, 2, '.', '' ),
			'FinalAmount'    => number_format( (float) $this->transaction->get_total(), 2, '.', '' ),
			'Currency'       => $this->transaction->get_currency(),
			'Installment'    => $this->transaction->get_installment(),
			'CardNumber'     => $this->transaction->get_card_bin(),
			'ExpireYear'     => '20' . substr( $this->transaction->get_card_expiry_year(), -2 ),
			'ExpireMonth'    => $this->transaction->get_card_expiry_month(),
			'Cvv'            => $this->transaction->get_card_cvv(),
			'CardHolderName' => $this->transaction->get_card_holder_name(),
			'ClientIP'       => gpos_get_user_ip(),
			'CallbackUrl'    => $this->get_callback_url(),
		];
		if ( GPOS_Transaction_Utils::REGULAR === $this->transaction->get_security_type() ) {
			$this->regular_process( $payment_request );
		} else {
			$this->threed_progress( $payment_request );
		}
		return $this->gateway_response;
	}


	/**
	 * Papara 3D Secure işlem.
	 *
	 * @param array $payment_request İstek
	 */
	protected function threed_progress( $payment_request ) {
		$payment_request['ConversationId'] = time();

		$response = $this->http_request->request( $this->request_url . '/3dsecure', 'POST', wp_json_encode( $payment_request ) );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_START_3D, $payment_request, $response );
		if ( array_key_exists( 'succeeded', $response ) && true === $response['succeeded'] ) {
			$this->gateway_response->set_success( true )->set_html_content( $response['data'] );
		} else {
			$this->gateway_response
			->set_error_code( $response['error']['code'] )
			->set_error_message( $response['error']['message'] );
		}
	}

	/**
	 * Papara Regular işlem.
	 *
	 * @param array $payment_request İstek
	 */
	protected function regular_process( $payment_request ) {

		$response = $this->http_request->request( $this->request_url . '/sale', 'POST', wp_json_encode( $payment_request ) );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_START_REGULAR, $payment_request, $response );

		$this->check_payment( $response );
		$this->transaction->add_meta( 'papara_order_id', $response['orderId'] );
		return $this->gateway_response->set_success( true )->set_payment_id( $payment_request['requestHeader']['transactionId'] );
	}

	/**
	 * Hash oluşturma
	 *
	 * @param string $hash_string Hashlanacak değer
	 *
	 * @return string $hash_data Hashlenecek string.
	 */
	private function generate_hash( $hash_string ) {
		return hash(
			'sha512',
			$hash_string,
		);
	}

	/**
	 * 3D Ödeme işlemleri için geri dönüş fonksiyonu.
	 *
	 * @param array $post_data Geri dönüş verileri.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_callback( array $post_data ) {
		$hash_data = $post_data['MerchantId'] . '|' . $post_data['CustomerId'] . '|' . $post_data['OrderId'] . '|' . $post_data['TransactionId'] . '|' . $post_data['ResultCode'] . '|' . $post_data['Random'] . '|' . $this->settings['application_password'];
		if ( $post_data['HashData'] === $this->generate_hash( $hash_data ) && '0000' === $post_data['ResultCode'] ) {
			$this->gateway_response->set_success( true )->set_payment_id( $post_data['OrderId'] );
		} else {
			$this->gateway_response->set_error_message( $post_data['ResultMessage'] )->set_error_code( $post_data['ResultCode'] );
		}

		return $this->gateway_response;
	}


	/**
	 * Ödeme iptal işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_cancel() {
		$request  = array(
			'OrderId'  => $this->transaction->get_payment_id(),
			'ClientIP' => gpos_get_user_ip(),
		);
		$response = $this->http_request->request( $this->request_url . '/cancel', 'POST', wp_json_encode( $request ) );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_CANCEL, $request, $response );
		return $this->check_revert( $response );
	}

	/**
	 * Ödeme iade işlemi fonksiyonu.
	 *
	 * @param int|string $payment_id İade işlemi yapılacak olan ödeme numarası.
	 * @param int|float  $refund_total İade.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_refund( $payment_id, $refund_total ) {
		$refund = [
			'OrderId'      => $payment_id,
			'RefundAmount' => $refund_total,
			'ClientIP'     => gpos_get_user_ip(),
		];

		$response = $this->http_request->request( $this->request_url . '/refund', 'POST', wp_json_encode( $refund ) );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_REFUND, $refund, $response );

		return $this->check_revert( $response );
	}

	/**
	 * Ödeme iptal/iade kontrol işlemi fonksiyonu.
	 *
	 * @param array|string $response ödeme kuruluşundan gelen cevap
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function check_revert( $response ) {

		if ( isset( $response['data']['resultCode'] ) && '0000' === $response['data']['resultCode'] ) {
			$this->gateway_response
			->set_success( true )
			->set_payment_id( $response['data']['transactionId'] );
		} else {
			$this->gateway_response
			->set_error_code( $response['data']['resultCode'] )
			->set_error_message( $response['data']['resultMessage'] );
		}

		return $this->gateway_response;
	}

	/**
	 * İşlem durumunun kontrolü methodu.
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

	/**
	 * Apilerinde taksit bilgisi gönderen kuruluşlar için otomatik getirir.
	 *
	 * @return array|bool Destek var ise taksitler yok ise false.
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function get_installments() {
		$installments = gpos_default_installments_template();
		$query        = array(
			'OrderId'  => time(),
			'Amount'   => 100,
			'Currency' => 'TRY',
		);

		$response = $this->http_request->request(
			$this->request_url . '/all-installment-options',
			'POST',
			wp_json_encode( $query )
		);

		if ( true === $response['succeeded'] ) {
			$api_installment_list = $response['data']['installmentOptions'];
				array_walk(
					$installments,
					function ( &$counts, $family ) use ( $api_installment_list ) {
						$family_filter  = array_filter( $api_installment_list, fn( $api_installment ) =>  gpos_clear_non_alfa( $api_installment['cardBrandName'] ) === $family || ( 'Bankkart' === $api_installment['cardBrandName'] && 'bankkartcombo' === $family ) );
						$api_count_list = empty( $family_filter ) ? false : $family_filter[ array_key_first( $family_filter ) ]['installmentDetails'];

						if ( $api_count_list ) {
							$counts = array_map(
								function ( $count ) use ( $api_count_list ) {
									$count_filter   = array_filter( $api_count_list, fn( $api_count ) => (int) $api_count['installment'] === (int) $count['number'] );
									$filtered_count = empty( $count_filter ) ? false : $count_filter[ array_key_first( $count_filter ) ];
									if ( $filtered_count ) {
										$count['enabled'] = true;
										$count['rate']    = number_format( $filtered_count['interestRate'], 2 );
									}
									return $count;
								},
								$counts
							);
						}
					},
				);
		}
		return array(
			'result'       => true === $response['succeeded'] ? 'success' : 'error',
			'installments' => true === $response['succeeded'] ? $installments : $response['data']['resultMessage'],
		);
	}
}
