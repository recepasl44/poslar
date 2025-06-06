<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Payten ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOS_Payten_Gateway) barındırır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Payten_Gateway sınıfı.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class GPOS_Payten_Gateway extends GPOS_Payment_Gateway {

	/**
	 * Ödeme geçidi ayarlarını taşır.
	 *
	 * @var array $settings;
	 */
	public $settings;

	/**
	 * Ödeme geçidinin çağrı atacağı adres.
	 *
	 * @var string $request_url;
	 */
	public $request_url;

	/**
	 * Ödeme geçidi canlı api adresi.
	 *
	 * @var string $live_api
	 */
	public $live_api;

	/**
	 * Ödeme geçidi test api adresi.
	 *
	 * @var string $test_api
	 */
	public $test_api;

	/**
	 * Ödeme kuruluşunun bağlantı testi
	 *
	 * @param stdClass $connection_data Ödeme geçidi ayarları.
	 *
	 * @return array
	 */
	public function check_connection( $connection_data ) {
		$this->prepare_settings( $connection_data );
		$request  = array(
			'ACTION' => 'QUERYPAYMENTSYSTEMS',
			'BIN'    => '545616',
		);
		$response = $this->http_request->request( $this->request_url, 'POST', array_merge( $this->settings, $request ) );
		return array(
			'result'  => '00' === $response['responseCode'] ? 'success' : 'error',
			'message' => '00' === $response['responseCode'] ? __( 'Connection Success', 'gurmepos' ) : $this->error_message( $response['errorCode'] ),
		);
	}

	/**
	 * Apilerinde taksit bilgisi gönderen kuruluşlar için otomatik getirir.
	 *
	 * @return array|bool Destek var ise taksitler yok ise false.
	 */
	public function get_installments() {
		$installments = gpos_default_installments_template();
		$request      = array(
			'ACTION' => 'QUERYPAYMENTSYSTEMS',
			'BIN'    => '557113',
		);
		$response     = $this->http_request->request( $this->request_url, 'POST', array_merge( $request, $this->settings ) );
		if ( '00' === $response['responseCode'] ) {
			$api_installment_list = $response['installmentPaymentSystem']['installmentList'];
			array_walk(
				$installments,
				function ( &$counts ) use ( $api_installment_list ) {
					$counts = array_map(
						function ( $count ) use ( $api_installment_list ) {
							$count_filter   = array_filter( $api_installment_list, fn( $api_count ) => (int) $api_count['count'] === (int) $count['number'] );
							$filtered_count = empty( $count_filter ) ? false : $count_filter[ array_key_first( $count_filter ) ];
							if ( $filtered_count ) {
								$count['enabled'] = true;
								$count['rate']    = number_format( $filtered_count['customerCostCommissionRate'], 2 );
							}
							return $count;
						},
						$counts
					);
				}
			);
		}
		return array(
			'result'       => '00' === $response['responseCode'] ? 'success' : 'error',
			'installments' => '00' === $response['responseCode'] ? $installments : $this->error_message( $response['errorCode'] ),
		);
	}

	/**
	 * GPOS_Paratika_Gateway kurucu fonksiyon değerindedir gerekli ayarlamaları yapar.
	 *
	 * @param GPOS_Paratika_Settings|stdClass $settings Ödeme geçidi ayarlarını içerir.
	 *
	 * @return void
	 */
	public function prepare_settings( $settings ) {
		$is_test_mode = gpos_is_test_mode();

		$this->settings = array(
			'MERCHANT'         => $is_test_mode ? $settings->test_merchant : $settings->merchant,
			'MERCHANTUSER'     => $is_test_mode ? $settings->test_merchant_user : $settings->merchant_user,
			'MERCHANTPASSWORD' => $is_test_mode ? $settings->test_merchant_password : $settings->merchant_password,
		);
		$this->http_request->set_headers(
			array( 'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8' )
		);

		$this->request_url = $is_test_mode ? $this->test_api : $this->live_api;
	}

	/**
	 * Ödeme işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_payment() {
		if ( 'threed' === $this->transaction->get_security_type() ) {
			$this->threed_payment();
		} else {
			$this->regular_payment();
		}
		return $this->gateway_response;
	}

	/**
	 * 3D Ödeme işlemi
	 *
	 * @return void
	 */
	public function threed_payment() {
		$response = $this->get_session_token();
		if ( array_key_exists( 'responseCode', $response ) && '00' === $response['responseCode'] ) {
			$card     = $this->prepare_credit_card();
			$url      = "{$this->request_url}/post/sale3d/{$response['sessionToken']}";
			$response = $this->http_request->request( $url, 'POST', $card );
			$this->log( GPOS_Transaction_Utils::LOG_PROCESS_REDIRECT, array( 'url' => $url ), array( 'html_content' => $response ) );
			$this->gateway_response->set_success( true )->set_html_content( $response );
		} else {
			$this->gateway_response
			->set_error_code( $response['errorCode'] )
			->set_error_message( $this->error_message( $response['errorCode'] ) );
		}
	}

	/**
	 * Regular işlemi
	 *
	 * @return void
	 */
	public function regular_payment() {
		$request  = array_merge(
			array( 'ACTION' => 'SALE' ),
			$this->settings,
			$this->prepare_order_data(),
			$this->prepare_credit_card()
		);
		$response = $this->http_request->request( $this->request_url, 'POST', $request );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_START_REGULAR, $request, $response );

		if ( array_key_exists( 'responseCode', $response ) && '00' === $response['responseCode'] ) {
			$this->process_callback( $response );
		} else {
			$this->gateway_response
			->set_error_code( $response['errorCode'] )
			->set_error_message( $this->error_message( $response['errorCode'] ) );
		}
	}

	/**
	 * 3D Ödeme işlemleri için geri dönüş fonksiyonu.
	 *
	 * @param array $post_data Geri dönüş verileri.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_callback( array $post_data ) {
		$this->gateway_response
		->set_error_code( array_key_exists( 'errorCode', $post_data ) ? $post_data['errorCode'] : false )
		->set_error_message( array_key_exists( 'errorCode', $post_data ) ? $this->error_message( $post_data['errorCode'] ) : gpos_get_default_callback_error_message() );

		if ( array_key_exists( 'responseCode', $post_data ) && '00' === $post_data['responseCode'] ) {
			$response = $this->check_payment_status( $post_data['pgTranId'] );

			if ( array_key_exists( 'responseCode', $response ) && '00' === $response['responseCode'] && '0' !== $response['transactionCount'] ) {
				$this->gateway_response = $this->find_success_transaction( $response );
			}
		}
		return $this->gateway_response;
	}

	/**
	 * İşlem durumunun kontrolü methodu.
	 *
	 * @param string $payment_id Ödeme işlem numarası.
	 */
	public function check_payment_status( $payment_id ) {
		$request  = array_merge(
			array(
				'ACTION'   => 'QUERYTRANSACTION',
				'PGTRANID' => $payment_id,
			),
			$this->settings
		);
		$response = $this->http_request->request( $this->request_url, 'POST', $request );

		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_FINISH, $request, $response );

		return $response;
	}
	/**
	 * Paratikadan dönen cevap içerisinden başarılı işlemi bulur.
	 *
	 * @param array $response Paratika cevabı
	 *
	 * -@return GPOS_Gateway_Response
	 */
	private function find_success_transaction( $response ) {
		foreach ( $response['transactionList']  as $transaction ) {
			if ( array_key_exists( 'transactionStatus', $transaction ) && 'AP' === strtoupper( $transaction['transactionStatus'] ) && (string) $this->transaction->get_id() === (string) $transaction['merchantPaymentId'] ) {
				return $this->gateway_response
				->set_success( true )
				->set_payment_id( $transaction['pgTranId'] );
			}
		}

		return $this->gateway_response->set_error_message( __( 'No confirmed transactions were found in the transaction list.', 'gurmepos' ) );
	}


	/**
	 * Ödeme iptal işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_cancel() {
		$request  = array(
			'ACTION'   => 'VOID',
			'PGTRANID' => $this->transaction->get_payment_id(),
		);
		$response = $this->http_request->request( $this->request_url, 'POST', array_merge( $request, $this->settings ) );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_CANCEL, $request, $response );
		$this->check_refund_cancel_response( $response );
		return $this->gateway_response;
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
		$request  = array(
			'ACTION'   => 'REFUND',
			'PGTRANID' => $payment_id,
			'CURRENCY' => $this->transaction->get_currency(),
			'AMOUNT'   => $refund_total,
		);
		$response = $this->http_request->request( $this->request_url, 'POST', array_merge( $request, $this->settings ) );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_REFUND, $request, $response );
		$this->check_refund_cancel_response( $response );
		return $this->gateway_response;
	}

	/**
	 * Ödeme iptal ve iade işlemi cevabını kontroleder.
	 *
	 * @param array $response Paratika cevabı.
	 *
	 * @return void
	 */
	private function check_refund_cancel_response( $response ) {
		if ( array_key_exists( 'responseCode', $response ) && '00' === $response['responseCode'] ) {
			$this->gateway_response->set_success( true )->set_payment_id( $response['pgTranId'] );
		} else {
			$this->gateway_response
			->set_error_code( $response['errorCode'] )
			->set_error_message( $this->error_message( $response['errorCode'] ) );
		}
	}

	/**
	 * Paratika session token.
	 *
	 * @param string $session_type Varsayılan PAYMENTSESSION (ödeme oturumu).
	 */
	private function get_session_token( $session_type = 'PAYMENTSESSION' ) {
		$request = array(
			'ACTION'      => 'SESSIONTOKEN',
			'SESSIONTYPE' => $session_type,
		);

		$request = array_merge( $request, $this->settings );

		if ( 'PAYMENTSESSION' === $session_type ) {
			$request = array_merge( $request, $this->prepare_order_data() );
		}

		$response = $this->http_request->request( $this->request_url, 'POST', $request );

		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_AUTH, $request, $response );

		return $response;
	}

	/**
	 * Ödeme için gerekli kart bilgilerini ayarlar.
	 *
	 * @return array $card
	 */
	protected function prepare_credit_card() {
		$card   = array();
		$threed = 'threed' === $this->transaction->get_security_type();

		$card[ $threed ? 'installmentCount' : 'INSTALLMENTS' ] = $this->transaction->get_installment();

		if ( $this->transaction->need_use_saved_card() ) {
			$card[ $threed ? 'cardToken' : 'CARDTOKEN' ] = '';
			return $card;
		}

		if ( $threed ) {
			$card['cardOwner']   = $this->transaction->get_card_holder_name();
			$card['expiryMonth'] = $this->transaction->get_card_expiry_month();
			$card['expiryYear']  = $this->transaction->get_card_expiry_year();
			$card['cvv']         = $this->transaction->get_card_cvv();
			$card['pan']         = $this->transaction->get_card_bin();
		} else {
			$card['NAMEONCARD'] = $this->transaction->get_card_holder_name();
			$card['CARDEXPIRY'] = $this->transaction->get_card_expiry_month() . $this->transaction->get_card_expiry_year();
			$card['CARDCVV']    = $this->transaction->get_card_cvv();
			$card['CARDPAN']    = $this->transaction->get_card_bin();
		}

		if ( $this->transaction->get_save_card() ) {
			$card[ $threed ? 'saveCard' : 'SAVECARD' ] = 'yes';
		}

		return $card;
	}

	/**
	 * Sipariş verisini hazırlar.
	 *
	 * @return array $order_data
	 */
	private function prepare_order_data() {
		$order_data                  = array();
		$order_data['CUSTOMER']      = $this->transaction->get_customer_id();
		$order_data['CUSTOMERNAME']  = $this->transaction->get_customer_full_name();
		$order_data['CUSTOMERPHONE'] = $this->transaction->get_customer_phone();
		$order_data['CUSTOMEREMAIL'] = $this->transaction->get_customer_email();
		$order_data['CUSTOMERIP']    = $this->transaction->get_customer_ip_address();

		foreach ( $this->transaction->get_lines() as $line_item ) {
			$order_data['ORDERITEMS'][] = array(
				'productCode' => $line_item->get_id(),
				'name'        => $line_item->get_name(),
				'quantity'    => $line_item->get_quantity(),
				'description' => $line_item->get_name(),
				'amount'      => number_format( $line_item->get_total(), 2, '.', '' ),
			);
		}

		$order_data['RETURNURL']         = $this->get_callback_url();
		$order_data['AMOUNT']            = $this->transaction->get_total();
		$order_data['ORDERITEMS']        = rawurlencode( wp_json_encode( $order_data['ORDERITEMS'] ) );
		$order_data['MERCHANTPAYMENTID'] = $this->transaction->get_id();
		$order_data['CURRENCY']          = $this->transaction->get_currency();

		return $order_data;
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

	/**
	 * Paratika hata mesajlarını anlamlı şekilde dönmesini sağlar.
	 *
	 * @param string $error_code Paratika hata kodu.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	private function error_message( $error_code ) {
		$errors = array(
			'ERR20001' => __( 'Contact your bank for manual approval', 'gurmepos' ),
			'ERR20002' => __( 'Fake approval, contact your bank', 'gurmepos' ),
			'ERR20003' => __( 'Invalid member merchant or service provider', 'gurmepos' ),
			'ERR20004' => __( 'Confiscate the card', 'gurmepos' ),
			'ERR20005' => __( 'Transaction not approved', 'gurmepos' ),
			'ERR20006' => __( 'Error (Only record update responses found on the Virtual POS or bank side)', 'gurmepos' ),
			'ERR20007' => __( 'Confiscate the card - Special reasons', 'gurmepos' ),
			'ERR20008' => __( 'Fake approval, contact your bank', 'gurmepos' ),
			'ERR20009' => __( 'Installments are not applied to the bank card used in the transaction.', 'gurmepos' ),
			'ERR20011' => __( 'Fake approval (VIP), contact your bank', 'gurmepos' ),
			'ERR20012' => __( 'Invalid transaction on the Virtual POS or bank side', 'gurmepos' ),
			'ERR20013' => __( 'Virtual POS error: Invalid amount information', 'gurmepos' ),
			'ERR20014' => __( 'Invalid account or card number specified', 'gurmepos' ),
			'ERR20015' => __( 'No such bank (issuer) found', 'gurmepos' ),
			'ERR20019' => __( 'Virtual POS error: Try again', 'gurmepos' ),
			'ERR20020' => __( 'Virtual POS error: Invalid/Incorrect amount', 'gurmepos' ),
			'ERR20021' => __( 'Transaction cannot be performed on the Bank/Virtual POS side', 'gurmepos' ),
			'ERR20025' => __( 'Virtual POS error: Record could not be created', 'gurmepos' ),
			'ERR20026' => __( 'No transaction found on the Virtual POS side', 'gurmepos' ),
			'ERR20027' => __( 'Virtual POS error: Bank rejected', 'gurmepos' ),
			'ERR20028' => __( 'Virtual POS error: Original is denied', 'gurmepos' ),
			'ERR20029' => __( 'Virtual POS error: Original not found', 'gurmepos' ),
			'ERR20030' => __( 'Switch-based format error on the Virtual POS side', 'gurmepos' ),
			'ERR20032' => __( 'General routing error on the Virtual POS side', 'gurmepos' ),
			'ERR20033' => __( 'The specified credit card has expired', 'gurmepos' ),
			'ERR20034' => __( 'Suspicion of fraud in the transaction', 'gurmepos' ),
			'ERR20036' => __( 'Virtual POS error: Restricted card', 'gurmepos' ),
			'ERR20037' => __( 'Virtual POS error: Bank (Issuer) is recalling the card', 'gurmepos' ),
			'ERR20038' => __( 'Virtual POS error: Exceeded allowed PIN attempts', 'gurmepos' ),
			'ERR20040' => __( 'Virtual POS error: Refund cannot be processed before the end of the day', 'gurmepos' ),
			'ERR20041' => __( 'Virtual POS error: Lost card, confiscate the card', 'gurmepos' ),
			'ERR20043' => __( 'Virtual POS error: Stolen card, confiscate the card', 'gurmepos' ),
			'ERR20045' => __( 'Refunds are not supported in transactions where points are used. Please contact your bank.', 'gurmepos' ),
			'ERR20051' => __( 'The limit of the specified credit card is not sufficient for this transaction', 'gurmepos' ),
			'ERR20052' => __( 'Virtual POS error: Checking account not found', 'gurmepos' ),
			'ERR20053' => __( 'Virtual POS error: Savings account not found', 'gurmepos' ),
			'ERR20054' => __( 'Card has expired', 'gurmepos' ),
			'ERR20055' => __( 'Virtual POS error: Incorrect/Invalid PIN value', 'gurmepos' ),
			'ERR20056' => __( 'Virtual POS error: Card information not found', 'gurmepos' ),
			'ERR20057' => __( 'The cardholder is not authorized for this transaction', 'gurmepos' ),
			'ERR20058' => __( 'The terminal is not authorized for this transaction', 'gurmepos' ),
			'ERR20059' => __( 'Suspicion of fraud in the transaction', 'gurmepos' ),
			'ERR20061' => __( 'Virtual POS error: Exceeded expected transaction amount limit', 'gurmepos' ),
			'ERR20062' => __( 'The specified credit card is restricted', 'gurmepos' ),
			'ERR20063' => __( 'Security violation on the Virtual POS side', 'gurmepos' ),
			'ERR20065' => __( 'Virtual POS error: Exceeded expected transaction limit', 'gurmepos' ),
			'ERR20075' => __( 'Virtual POS error: Exceeded allowed PIN attempts', 'gurmepos' ),
			'ERR20076' => __( 'Virtual POS key synchronization error', 'gurmepos' ),
			'ERR20077' => __( 'Virtual POS error: Invalid/Inconsistent information sent', 'gurmepos' ),
			'ERR20080' => __( 'Invalid date information', 'gurmepos' ),
			'ERR20081' => __( 'Virtual POS encryption error', 'gurmepos' ),
			'ERR20082' => __( 'Invalid/Incorrect CVV value', 'gurmepos' ),
			'ERR20083' => __( 'PIN value cannot be verified', 'gurmepos' ),
			'ERR20084' => __( 'Invalid/Incorrect CVV value', 'gurmepos' ),
			'ERR20085' => __( 'Rejected on the Virtual POS side (General)', 'gurmepos' ),
			'ERR20086' => __( 'Not verified', 'gurmepos' ),
			'ERR20091' => __( 'The bank/Virtual POS cannot process transactions at the moment', 'gurmepos' ),
			'ERR20092' => __( 'Technical cancellation due to timeout', 'gurmepos' ),
			'ERR20093' => __( 'Your card is closed to e-commerce transactions. Call your bank.', 'gurmepos' ),
			'ERR20096' => __( 'General error on the Virtual POS side', 'gurmepos' ),
			'ERR20098' => __( 'Multiple reversal (Duplicate reversal)', 'gurmepos' ),
			'ERR20099' => __( 'Please try again, contact your bank if the problem persists.', 'gurmepos' ),
			'ERR200YK' => __( 'Card is on the blacklist', 'gurmepos' ),
			'ERR200SF' => __( 'Check the HOSTMSG field in the Virtual POS response for details.', 'gurmepos' ),
			'ERR200GK' => __( 'Virtual POS error: This terminal is not authorized for foreign cards.', 'gurmepos' ),
			'ERR30001' => __( 'This transaction has been rejected by Threat Metrix (TMX). Please check the transaction logs and TMX portal records for details.', 'gurmepos' ),
			'ERR30002' => __( '3D transaction did not complete successfully.', 'gurmepos' ),
			'ERR30004' => __( 'This request has been rejected by fraud rules.', 'gurmepos' ),
			'ERR30005' => __( 'No response received from the bank.', 'gurmepos' ),
			'ERR10010' => __( 'One of the required parameters in the request is missing', 'gurmepos' ),
			'ERR10011' => __( 'The same parameter has been sent more than once', 'gurmepos' ),
			'ERR10012' => __( 'The maximum size for this value has been exceeded.', 'gurmepos' ),
			'ERR10013' => __( 'Invalid data type specified for this value', 'gurmepos' ),
			'ERR10014' => __( 'Invalid security algorithm specified', 'gurmepos' ),
			'ERR10015' => __( 'Invalid member merchant information specified', 'gurmepos' ),
			'ERR10016' => __( 'Invalid amount information specified', 'gurmepos' ),
			'ERR10017' => __( 'Invalid currency specified', 'gurmepos' ),
			'ERR10018' => __( 'Invalid language selection', 'gurmepos' ),
			'ERR10019' => __( 'General error', 'gurmepos' ),
			'ERR10020' => __( 'Invalid user information', 'gurmepos' ),
			'ERR10021' => __( 'Empty parameter specified, check all parameters', 'gurmepos' ),
			'ERR10022' => __( 'The total amount of the ordered products does not match the actual amount', 'gurmepos' ),
			'ERR10023' => __( 'The payment amount does not match the calculated amount', 'gurmepos' ),
			'ERR10024' => __( 'Invalid tax amount specified', 'gurmepos' ),
			'ERR10025' => __( 'The tax amount must be zero for the specified condition', 'gurmepos' ),
			'ERR10026' => __( 'Invalid integration model specified', 'gurmepos' ),
			'ERR10027' => __( 'Invalid card information (TOKEN) specified', 'gurmepos' ),
			'ERR10028' => __( 'The specified payment system (virtual POS) was not found', 'gurmepos' ),
			'ERR10029' => __( 'The specified payment type (campaign) was not found', 'gurmepos' ),
			'ERR10030' => __( 'The specified transaction was not found', 'gurmepos' ),
			'ERR10031' => __( 'This transaction cannot be refunded', 'gurmepos' ),
			'ERR10032' => __( 'Invalid refund amount specified or this transaction has already been refunded', 'gurmepos' ),
			'ERR10033' => __( 'This transaction cannot be canceled', 'gurmepos' ),
			'ERR10034' => __( 'The specified payment was not found', 'gurmepos' ),
			'ERR10035' => __( 'No pre-authorization record found for this transaction', 'gurmepos' ),
			'ERR10036' => __( 'Invalid final authorization (POSTAUTH) amount specified', 'gurmepos' ),
			'ERR10037' => __( 'The specified Cardholder (Customer) is not registered', 'gurmepos' ),
			'ERR10038' => __( 'The relevant payment is awaiting approval', 'gurmepos' ),
			'ERR10039' => __( 'Invalid payment status specified', 'gurmepos' ),
			'ERR10040' => __( 'Invalid sub-action (SUBACTION) specified', 'gurmepos' ),
			'ERR10041' => __( 'The specified card has been added before', 'gurmepos' ),
			'ERR10042' => __( 'The card has been previously deleted', 'gurmepos' ),
			'ERR10043' => __( 'Invalid time range specified', 'gurmepos' ),
			'ERR10044' => __( 'Invalid date format specified', 'gurmepos' ),
			'ERR10045' => __( 'The specified card number is invalid', 'gurmepos' ),
			'ERR10046' => __( 'The specified credit card expiration date is invalid', 'gurmepos' ),
			'ERR10047' => __( 'The user does not have the authority to use API services', 'gurmepos' ),
			'ERR10048' => __( 'A successful transaction already exists with this member merchant order number', 'gurmepos' ),
			'ERR10049' => __( 'Invalid member merchant group number', 'gurmepos' ),
			'ERR10050' => __( 'Invalid HASH value', 'gurmepos' ),
			'ERR10051' => __( 'No payment system (virtual POS) definition. Please check.', 'gurmepos' ),
			'ERR10052' => __( 'Unsupported currency:', 'gurmepos' ),
			'ERR10053' => __( 'The user is not authorized to process transactions on this member merchant', 'gurmepos' ),
			'ERR10054' => __( 'The validity period of the payment is above the maximum limit.', 'gurmepos' ),
			'ERR10055' => __( 'The validity period of the payment is below the minimum limit.', 'gurmepos' ),
			'ERR10056' => __( 'Invalid API request specified', 'gurmepos' ),
			'ERR10057' => __( 'Invalid card BIN information', 'gurmepos' ),
			'ERR10058' => __( 'The card has been activated before', 'gurmepos' ),
			'ERR10059' => __( 'The card has been deactivated before', 'gurmepos' ),
			'ERR10060' => __( 'Invalid IP Address', 'gurmepos' ),
			'ERR10062' => __( 'The specified card has not been activated yet.', 'gurmepos' ),
			'ERR10063' => __( 'This transaction can only be done with LetsBodrum card.', 'gurmepos' ),
			'ERR10064' => __( 'Please use LetsBodrum card or Turkey İş Bankası credit card.', 'gurmepos' ),
			'ERR10065' => __( 'The specified card number has been defined before.', 'gurmepos' ),
			'ERR10066' => __( 'The specified time information is invalid or inconsistent', 'gurmepos' ),
			'ERR10067' => __( 'The specified period value is too high', 'gurmepos' ),
			'ERR10068' => __( 'Invalid repeat pattern parameter', 'gurmepos' ),
			'ERR10069' => __( 'Quartz timer error occurred', 'gurmepos' ),
			'ERR10070' => __( 'The start date must be a future date', 'gurmepos' ),
			'ERR10071' => __( 'Invalid recurring payment status parameter specified', 'gurmepos' ),
			'ERR10072' => __( 'The recurring payment plan is already active', 'gurmepos' ),
			'ERR10073' => __( 'ERR10073', 'gurmepos' ),
			'ERR10074' => __( 'The recurring payment plan has already expired', 'gurmepos' ),
			'ERR10075' => __( 'Invalid member merchant visual (logo) information', 'gurmepos' ),
			'ERR10076' => __( 'Invalid recurring payment status parameter', 'gurmepos' ),
			'ERR10078' => __( 'Transaction is locked', 'gurmepos' ),
			'ERR10079' => __( 'This card is registered in the system.', 'gurmepos' ),
			'ERR10080' => __( 'Please provide the Member Merchant Order number or Payment Session (Token)', 'gurmepos' ),
			'ERR10081' => __( 'Invalid transaction status', 'gurmepos' ),
			'ERR10082' => __( 'The user does not have the authority for this transaction.', 'gurmepos' ),
			'ERR10083' => __( 'Invalid status', 'gurmepos' ),
			'ERR10084' => __( 'The interest or discount rate must be zero', 'gurmepos' ),
			'ERR10085' => __( 'The valid end date cannot be greater than the valid start date', 'gurmepos' ),
			'ERR10086' => __( 'The valid end date must be greater than the current date', 'gurmepos' ),
			'ERR10087' => __( 'There is already a payment type with installment number for this payment system', 'gurmepos' ),
			'ERR10088' => __( 'Installment information must be a value between 1 and 12.', 'gurmepos' ),
			'ERR10089' => __( 'The card for recurring payment cannot be deleted.', 'gurmepos' ),
			'ERR10090' => __( 'Transaction failed', 'gurmepos' ),
			'ERR10091' => __( 'Transaction cannot be processed as the payment system is disabled. Please contact the Member Merchant Super Administrator.', 'gurmepos' ),
			'ERR10092' => __( 'Invalid offset value', 'gurmepos' ),
			'ERR10093' => __( 'Invalid limit value', 'gurmepos' ),
			'ERR10094' => __( 'No registered card found.', 'gurmepos' ),
			'ERR10095' => __( 'The card cannot be deleted due to registered recurring payment plans.', 'gurmepos' ),
			'ERR10096' => __( 'Invalid session information.', 'gurmepos' ),
			'ERR10097' => __( 'Terminated session information.', 'gurmepos' ),
			'ERR10098' => __( 'This session key is not authorized for the intended transaction.', 'gurmepos' ),
			'ERR10099' => __( 'This transaction belongs to another member merchant.', 'gurmepos' ),
			'ERR10100' => __( 'There are multiple successful transactions for this payment. Please use the PGTRANID parameter.', 'gurmepos' ),
			'ERR10101' => __( 'Invalid URL parameter specified.', 'gurmepos' ),
			'ERR10102' => __( 'Invalid BIN value specified.', 'gurmepos' ),
			'ERR10103' => __( 'Transaction request has been rejected due to suspected fraud reported by Inact RT service.', 'gurmepos' ),
			'ERR10104' => __( 'No available commission scheme found.', 'gurmepos' ),
			'ERR10105' => __( 'The current Payment System is not available in the pool', 'gurmepos' ),
			'ERR10106' => __( 'Transaction amount has not been credited to the member merchant account, refund cannot be processed.', 'gurmepos' ),
			'ERR10107' => __( 'This payment has already been made, a new payment session cannot be created with the given Member Merchant Order Number.', 'gurmepos' ),
			'ERR10108' => __( 'Member merchant not approved', 'gurmepos' ),
			'ERR10109' => __( 'The payment pool for the member merchant has not been approved yet.', 'gurmepos' ),
			'ERR10110' => __( 'The payment system used does not support campaign usage.', 'gurmepos' ),
			'ERR10111' => __( 'Point inquiry is not supported by the payment system.', 'gurmepos' ),
			'ERR10112' => __( 'Incorrect point format, please check the point usage format in the API Documentation.', 'gurmepos' ),
			'ERR10113' => __( 'The payment system used does not support point usage.', 'gurmepos' ),
			'ERR10115' => __( 'Unsupported number of installments specified by the member merchant.', 'gurmepos' ),
			'ERR10116' => __( 'This transaction cannot be performed with inactive member merchant information.', 'gurmepos' ),
			'ERR10117' => __( 'This order number has been used in a terminated session, please create a session key with a different order number.', 'gurmepos' ),
			'ERR10118' => __( 'One of the values ​​for the amount, currency, session type, URL return value, or intended transaction does not match with the existing session for the order number in the request.', 'gurmepos' ),
			'ERR10119' => __( 'Exceeding the limit in the integer or fractional part', 'gurmepos' ),
			'ERR10120' => __( 'There is a recurring payment with this plan code', 'gurmepos' ),
			'ERR10121' => __( 'Invalid recurring payment code', 'gurmepos' ),
			'ERR10122' => __( 'The recurring payment in a terminated state cannot be updated.', 'gurmepos' ),
			'ERR10123' => __( 'Invalid transaction type', 'gurmepos' ),
			'ERR10125' => __( 'At least one parameter must be passed for reconciliation inquiry.', 'gurmepos' ),
			'ERR10126' => __( 'Multiple transactions found.', 'gurmepos' ),
			'ERR10127' => __( 'The point parameter for the payment system is incorrect, the sent point parameter is not defined in the payment system to which the transaction will be sent.', 'gurmepos' ),
			'ERR10128' => __( 'Invalid parameter value', 'gurmepos' ),
			'ERR10129' => __( 'Partial point usage is not supported by this payment system', 'gurmepos' ),
			'ERR10130' => __( 'Transaction rejected due to suspected fraud. For detailed information, you can contact the support team. (TMX rejected)', 'gurmepos' ),
			'ERR10131' => __( 'Commission expenses cannot exceed the sellers commission amount.', 'gurmepos' ),
			'ERR10132' => __( 'You do not have the authority to use marketplace parameters in the payment request. Please contact Paratika support line.', 'gurmepos' ),
			'ERR10133' => __( 'The requested transaction cannot be updated.', 'gurmepos' ),
			'ERR10134' => __( 'Payment system type or EFT code not found.', 'gurmepos' ),
			'ERR10135' => __( 'EXTRA parameter cannot be decoded.', 'gurmepos' ),
			'ERR10136' => __( 'Common payment page (HPP) cannot be used for this member merchant.', 'gurmepos' ),
			'ERR10137' => __( 'Query Campaign Not Supported By PaymentSystem', 'gurmepos' ),
			'ERR10138' => __( 'An error occurred while processing the 3D transaction.', 'gurmepos' ),
			'ERR10139' => __( 'Member Merchant Integration Model Error', 'gurmepos' ),
			'ERR10140' => __( 'Transaction type not supported by this payment system.', 'gurmepos' ),
			'ERR10141' => __( 'Unexpected payment system integration error', 'gurmepos' ),
			'ERR10142' => __( 'Invalid redirect address', 'gurmepos' ),
			'ERR10143' => __( 'PAID or CANCELED payment', 'gurmepos' ),
			'ERR10144' => __( 'The member merchant is not authorized to process transactions with foreign bank cards', 'gurmepos' ),
			'ERR10145' => __( 'Recurring payment not found.', 'gurmepos' ),
			'ERR10146' => __( 'Recurring payment card not found.', 'gurmepos' ),
			'ERR10147' => __( 'You do not have the authority to add a card without 3D verification. Please add a card using the HPP integration model or seek help from Paratika support team.', 'gurmepos' ),
			'ERR10148' => __( 'The recurring payment plan has already been added with this card.', 'gurmepos' ),
			'ERR10149' => __( 'Unsupported currency for this transaction', 'gurmepos' ),
			'ERR10150' => __( 'Discount amount cannot be greater than the order amount.', 'gurmepos' ),
			'ERR10151' => __( 'Seller not found', 'gurmepos' ),
			'ERR10152' => __( 'A seller exists with this ID.', 'gurmepos' ),
			'ERR10153' => __( 'Refund transaction has been rejected by Paratika Finance team', 'gurmepos' ),
			'ERR10154' => __( 'Transaction failed due to 3D restriction.', 'gurmepos' ),
			'ERR10155' => __( 'Seller is in deactivated state. This transaction cannot be performed.', 'gurmepos' ),
			'ERR10156' => __( 'Unsupported Currency Conversion', 'gurmepos' ),
			'ERR10157' => __( 'Activation date must be a future date', 'gurmepos' ),
			'ERR10158' => __( 'Invalid default commission rate', 'gurmepos' ),
			'ERR10159' => __( 'Invalid payment system-based commission rate', 'gurmepos' ),
			'ERR10160' => __( 'Missing parameter', 'gurmepos' ),
			'ERR10161' => __( 'Payment system not found in the pool', 'gurmepos' ),
			'ERR10162' => __( 'All installments between 2 and 12 must be provided in the parameter', 'gurmepos' ),
			'ERR10163' => __( 'Save card parameter cannot be used for API integration model', 'gurmepos' ),
			'ERR10164' => __( 'No payment system supporting cardless transaction with this name found in the pool', 'gurmepos' ),
			'ERR10165' => __( 'No commission scheme found', 'gurmepos' ),
			'ERR10166' => __( 'ERR10166', 'gurmepos' ),
			'ERR10167' => __( 'Invalid sellerId - do not use semicolon', 'gurmepos' ),
			'ERR10168' => __( 'This card brand is not supported', 'gurmepos' ),
			'ERR10169' => __( 'Installment is not suitable for this card brand', 'gurmepos' ),
			'ERR10170' => __( 'Entered value is out of valid range. Minimum value must be 1, for maximum value please contact Paratika Operations Team.', 'gurmepos' ),
			'ERR10171' => __( 'The specified MCC was not found.', 'gurmepos' ),
			'ERR10172' => __( 'The specified MCC has already been added', 'gurmepos' ),
			'ERR10173' => __( 'The product commission amounts do not match the commission amount specified in the TOTALSELLERCOMMISSIONAMOUNT parameter.', 'gurmepos' ),
			'ERR10174' => __( 'The Member Merchant Product Type and Commission Obligation could not be correctly updated to use seller sales.', 'gurmepos' ),
			'ERR10175' => __( 'Wrong commission applier type.', 'gurmepos' ),
			'ERR10176' => __( 'Invalid Seller Payment Amount', 'gurmepos' ),
			'ERR10177' => __( 'Invalid Marketplace Integration Model', 'gurmepos' ),
			'ERR10178' => __( 'The total of the seller payment amounts of the payment items does not match the parameter TOTALSELLERPAYMENTAMOUNT.', 'gurmepos' ),
			'ERR10179' => __( 'The commission applier type, CA (commission amount), cannot be sent at the same time as the commission value.', 'gurmepos' ),
			'ERR10180' => __( 'Invalid card type for the type of investor you want to use', 'gurmepos' ),
			'ERR10181' => __( 'Incorrect member merchant information in order items.', 'gurmepos' ),
			'ERR10182' => __( 'Order item not found', 'gurmepos' ),
			'ERR10183' => __( 'Order items are only supported for primary transactions created with session token', 'gurmepos' ),
			'ERR10184' => __( 'You need to specify the quantity or amount of the product', 'gurmepos' ),
			'ERR10185' => __( 'Item quantity or amount exceeded', 'gurmepos' ),
			'ERR10186' => __( 'Amount cannot be greater than the original request amount.', 'gurmepos' ),
			'ERR10187' => __( 'You do not have the authority to use marketplace parameters in the payment request. Please contact Paratika support line.', 'gurmepos' ),
			'ERR10188' => __( 'Payment date cannot be earlier than transaction date. Please check the request.', 'gurmepos' ),
			'ERR10189' => __( 'Invalid or Missing EXTRA parameter value', 'gurmepos' ),
			'ERR10190' => __( 'Manufacturer card template is not supported by this payment system.', 'gurmepos' ),
			'ERR10191' => __( 'VKN and TCKN values ​​cannot be used together. Please use only one.', 'gurmepos' ),
			'ERR10192' => __( 'Please send ACCOUNTOWNERNAME and ACCOUNTOWNERLASTNAME only or COMMERCIALTITLE only.', 'gurmepos' ),
			'ERR10193' => __( 'Seller payment due date must be equal to or higher than the Member Merchant payment due date.', 'gurmepos' ),
			'ERR10194' => __( 'Please make sub-dealer definitions for Kuveyt Türk virtual pos.', 'gurmepos' ),
			'ERR10195' => __( 'No product found in seller transaction', 'gurmepos' ),
			'ERR10196' => __( 'Payment date cannot be earlier than transaction date.', 'gurmepos' ),
			'ERR10197' => __( 'No application found with this email address and name.', 'gurmepos' ),
			'ERR10198' => __( 'There is an application with this name and email', 'gurmepos' ),
			'ERR10199' => __( 'Incorrect phone number', 'gurmepos' ),
			'ERR10200' => __( 'Incorrect web address', 'gurmepos' ),
			'ERR10201' => __( 'Incorrect Common Application Format', 'gurmepos' ),
			'ERR10202' => __( 'Incorrect member merchant application provided', 'gurmepos' ),
			'ERR10203' => __( 'Incorrect Application Finance Data provided', 'gurmepos' ),
			'ERR10204' => __( 'Invalid application document data supplied', 'gurmepos' ),
			'ERR10205' => __( 'Incorrect Application Sales Support Data provided', 'gurmepos' ),
			'ERR10206' => __( 'Invalid Application Risk Management Data supplied', 'gurmepos' ),
			'ERR10207' => __( 'OTP code not found', 'gurmepos' ),
			'ERR10208' => __( 'There is already a BIN Rule with the same name, please define a BIN Rule with a different name.', 'gurmepos' ),
			'ERR10209' => __( 'There is already a BIN Rule with the same configuration, please add a BIN Rule with different configurations.', 'gurmepos' ),
			'ERR10210' => __( 'BIN Rule installment number must consist of numbers', 'gurmepos' ),
			'ERR10211' => __( 'Bin Rule installment number is out of valid range', 'gurmepos' ),
			'ERR10212' => __( 'BIN rule not found with the given bin rule name', 'gurmepos' ),
			'ERR10213' => __( 'Bin Rule minimum limit cannot be greater than the upper limit.', 'gurmepos' ),
			'ERR10214' => __( 'Entered value does not exceed the minimum length', 'gurmepos' ),
			'ERR10215' => __( 'BKM error', 'gurmepos' ),
			'ERR10216' => __( 'Application document is registered, please enter a document with a different name.', 'gurmepos' ),
			'ERR10217' => __( 'Application document name not found.', 'gurmepos' ),
			'ERR10218' => __( 'Maximum Application document number exceeded.', 'gurmepos' ),
			'ERR10219' => __( 'Invalid VKN.', 'gurmepos' ),
			'ERR10220' => __( 'Invalid IBAN TRY.', 'gurmepos' ),
			'ERR10221' => __( 'Payment session terminated by link', 'gurmepos' ),
			'ERR10222' => __( 'A BIN Rule and Payment System Pool record with the same configuration already exists', 'gurmepos' ),
			'ERR10223' => __( 'No BIN Rule and Payment System Pool record found with configuration', 'gurmepos' ),
			'ERR10224' => __( 'ERR10224', 'gurmepos' ),
			'ERR10225' => __( 'BIN cannot be both foreign and its country Turkey at the same time', 'gurmepos' ),
			'ERR10226' => __( 'ERR10226', 'gurmepos' ),
			'ERR10227' => __( 'You are not authorized to use CARDPANTYPE parameter as INSURANCE', 'gurmepos' ),
			'ERR10228' => __( 'Incorrect CARDPAN format. CARDPAN should be in First8/Last4/TCKN-VKN-YKN format', 'gurmepos' ),
			'ERR10229' => __( 'You cannot use NAMEONCARD, CARDEXPIRY, and CARDPANCVV parameters at the same time with CARDPANTYPE parameter', 'gurmepos' ),
			'ERR10230' => __( 'You cannot use CARDTOKEN parameter at the same time with CARDPANTYPE parameter', 'gurmepos' ),
			'ERR10231' => __( 'You cannot use SAVECARD parameter at the same time with CARDPANTYPE parameter', 'gurmepos' ),
			'ERR10232' => __( 'No transaction can be made with a foreign card number', 'gurmepos' ),
			'ERR10233' => __( 'Invalid email', 'gurmepos' ),
			'ERR10234' => __( 'Shopping credit service is not supported by the payment system.', 'gurmepos' ),
			'ERR10235' => __( 'Seller Payment Date Mismatch', 'gurmepos' ),
			'ERR10236' => __( 'Maturity Date Mismatch', 'gurmepos' ),
		);

		return array_key_exists( $error_code, $errors ) ? $errors[ $error_code ] : '';
	}
}
