<?php
/**
 * Iyzico ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOS_Iyzico_Gateway) barındırır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Iyzico_Gateway sınıfı.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class GPOS_Iyzico_Gateway extends GPOS_Payment_Gateway {


	/**
	 * Ödeme geçidi ayarlarını taşır.
	 *
	 * @var \Iyzipay\Options $settings
	 */
	public $settings;

	/**
	 * Ödeme geçidi ayarlarını taşır.
	 *
	 * @var GPOS_Iyzico_IFrame_Settings $account_settings
	 */
	public $account_settings;

	/**
	 * Ödeme isteği
	 *
	 * @var \Iyzipay\Request\CreatePaymentRequest|\Iyzipay\Request\CreateCheckoutFormInitializeRequest|\Iyzipay\Request\CreatePayWithIyzicoInitializeRequest $payment_request
	 */
	public $payment_request;

	/**
	 * Sepet toplamı
	 *
	 * @var float
	 */
	public $basket_items_total = 0;

	/**
	 * Ödeme kuruluşunun bağlantı testi
	 *
	 * @param stdClass $connection_data Ödeme geçidi ayarları.
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function check_connection( $connection_data ) {
		$this->prepare_settings( $connection_data );
		$request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
		$request->setConversationId( microtime( false ) );
		$request->setLocale( gpos_get_payment_locale() );
		$request->setBinNumber( '589004' );
		$request->setPrice( '100' );
		$response = \Iyzipay\Model\InstallmentInfo::retrieve( $request, $this->settings );
		return array(
			'result'  => $response->getStatus() === 'success' ? 'success' : 'error',
			'message' => $response->getStatus() === 'success' ? __( 'Connection Success', 'gurmepos' ) : $response->getErrorMessage(),
		);
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
		$request      = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
		$request->setConversationId( time() );
		$request->setLocale( gpos_get_payment_locale() );
		$request->setPrice( '100' );
		$response = \Iyzipay\Model\InstallmentInfo::retrieve( $request, $this->settings );

		if ( $response->getStatus() === 'success' ) {
			$api_installment_list = $response->getInstallmentDetails();
			if ( is_array( $api_installment_list ) ) {
				array_walk(
					$installments,
					function ( &$counts, $family ) use ( $api_installment_list ) {
						$family_filter  = array_filter( $api_installment_list, fn( $api_installment ) =>  gpos_clear_non_alfa( $api_installment->getCardFamilyName() ) === $family );
						$api_count_list = empty( $family_filter ) ? false : $family_filter[ array_key_first( $family_filter ) ]->getInstallmentPrices();
						if ( $api_count_list ) {
							$counts = array_map(
								function ( $count ) use ( $api_count_list ) {
									$count_filter   = array_filter( $api_count_list, fn( $api_count ) => (int) $api_count->getInstallmentNumber() === (int) $count['number'] );
									$filtered_count = empty( $count_filter ) ? false : $count_filter[ array_key_first( $count_filter ) ];
									if ( $filtered_count ) {
										$count['enabled'] = true;
										$count['rate']    = number_format( $filtered_count->getTotalPrice() - 100, 2 );
									}
									return $count;
								},
								$counts
							);
						}
					},
				);
			}
			$installments['advantage']   = $installments['cardfinans'];
			$installments['denizbankcc'] = $installments['bonus'];
		}
		return array(
			'result'       => 'success' === $response->getStatus() ? 'success' : 'error',
			'installments' => 'success' === $response->getStatus() ? $installments : $response->getErrorMessage(),
		);
	}

	/**
	 * GPOS_Iyzico_Gateway kurucu fonksiyon değerindedir gerekli ayarlamaları yapar.
	 *
	 * @param GPOS_Iyzico_IFrame_Settings|stdClass $settings Ödeme geçidi ayarlarını içerir.
	 *
	 * @return void
	 */
	public function prepare_settings( $settings ) {
		$is_test_mode           = gpos_is_test_mode();
		$this->account_settings = $settings;
		$this->settings         = new \Iyzipay\Options();
		$this->settings->setApiKey( $is_test_mode ? $settings->test_api_key : $settings->api_key );
		$this->settings->setSecretKey( $is_test_mode ? $settings->test_api_secret : $settings->api_secret );
		$this->settings->setBaseUrl( $is_test_mode ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com' );
	}

	/**
	 * Ödeme işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function process_payment() {
		$this->payment_request = new \Iyzipay\Request\CreatePaymentRequest();
		$this->prepare_request_properties();
		$this->payment_request->setPaymentCard( $this->prepare_payment_card() );
		$this->payment_request->setInstallment( $this->transaction->get_installment() );
		$security_type = $this->transaction->get_security_type();

		if ( 'threed' === $security_type ) {
			$process = GPOS_Transaction_Utils::LOG_PROCESS_START_3D;
			$this->payment_request->setCallbackUrl( $this->get_callback_url() );
			$response = \Iyzipay\Model\ThreedsInitialize::create( $this->payment_request, $this->settings );
		} else {
			$process  = GPOS_Transaction_Utils::LOG_PROCESS_START_REGULAR;
			$response = \Iyzipay\Model\Payment::create( $this->payment_request, $this->settings );
		}

		$this->log( $process, $this->payment_request, $response );

		$response_status = $response->getStatus();

		if ( 'success' === $response_status && 'threed' === $security_type ) {
			$this->gateway_response->set_success( true )->set_html_content( $response->getHtmlContent() );
		} elseif ( 'success' === $response_status && 'regular' === $security_type ) {
			$this->set_payment_success( $response );
		} else {
			$this->set_payment_failed( $response );
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
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function process_callback( array $post_data ) {
		if ( array_key_exists( 'status', $post_data ) && 'success' === $post_data['status'] ) {
			$request = new \Iyzipay\Request\CreateThreedsPaymentRequest();
			$request->setLocale( gpos_get_payment_locale() );
			$request->setConversationId( $post_data['conversationId'] );
			$request->setPaymentId( $post_data['paymentId'] );
			// 3D Sayfasından başarıyla gelen kullanıcı için kartından ödeme çekme bu çağrı ile gerçekleşir.
			$response = \Iyzipay\Model\ThreedsPayment::create( $request, $this->settings );

			$this->log( GPOS_Transaction_Utils::LOG_PROCESS_FINISH, $request, $response );

			$this->response_checker( $response );
		}

		return $this->gateway_response;
	}

	/**
	 * Ödemenin başarılı olması durumunda yapılacak işlem.
	 *
	 * @param \Iyzipay\Model\ThreedsInitialize|\Iyzipay\Model\Payment $response iyzico cevap sınıfı.
	 */
	public function set_payment_success( $response ) {
		$this->gateway_response
		->set_success( true )
		->set_payment_id( $response->getPaymentId() );

		$this->transaction->set_plugin_transaction_id( $response->getBasketId() );

		foreach ( $response->getPaymentItems() as $item ) {
			gpos_transaction_line( $item->getItemId() )->set_payment_id( $item->getPaymentTransactionId() );
		}
	}

	/**
	 * Ödemenin başarısız olması durumunda yapılacak işlem.
	 *
	 * @param \Iyzipay\Model\ThreedsInitialize|\Iyzipay\Model\Payment $response iyzico cevap sınıfı.
	 */
	public function set_payment_failed( $response ) {
		$this->gateway_response
		->set_error_code( $response->getErrorCode() )
		->set_error_message( $response->getErrorMessage() );
	}


	/**
	 * Ödeme iptal işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function process_cancel() {
		$request = new \Iyzipay\Request\CreateCancelRequest();
		$request->setLocale( gpos_get_payment_locale() );
		$request->setConversationId( $this->transaction->id );
		$request->setPaymentId( $this->transaction->get_payment_id() );
		$request->setIp( gpos_get_user_ip() );
		$response = \Iyzipay\Model\Cancel::create( $request, $this->settings );
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
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function process_refund( $payment_id, $refund_total ) {
		$request = new \Iyzipay\Request\CreateRefundRequest();
		$request->setLocale( gpos_get_payment_locale() );
		$request->setConversationId( $this->transaction->id );
		$request->setPaymentTransactionId( $payment_id );
		$request->setPrice( $refund_total );
		$request->setIp( gpos_get_user_ip() );
		$response = \Iyzipay\Model\Refund::create( $request, $this->settings );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_REFUND, $request, $response );
		$this->check_refund_cancel_response( $response );
		return $this->gateway_response;
	}

	/**
	 * Ödeme iptal ve iade işlemi cevabını kontroleder.
	 *
	 * @param \Iyzipay\Model\Cancel|\Iyzipay\Model\Refund $response iyzico cevap sınıfı.
	 *
	 * @return void
	 */
	protected function check_refund_cancel_response( $response ) {
		if ( 'success' === $response->getStatus() ) {
			$this->gateway_response
			->set_success( true )
			->set_payment_id( $response->getPaymentId() );
		} else {
			$this->gateway_response
			->set_error_code( $response->getErrorCode() )
			->set_error_message( $response->getErrorMessage() );
		}
	}

	/**
	 * Iyzico için alıcı bilgisi
	 *
	 * @return \Iyzipay\Model\Buyer
	 */
	protected function prepare_buyer() {
		$buyer = new \Iyzipay\Model\Buyer();
		$buyer->setId( $this->transaction->get_customer_id() );
		$buyer->setName( $this->transaction->get_customer_first_name() );
		$buyer->setSurname( $this->transaction->get_customer_last_name() );
		$buyer->setGsmNumber( $this->transaction->get_customer_phone() );
		$buyer->setEmail( $this->transaction->get_customer_email() );
		$buyer->setIdentityNumber( '11111111111' );
		$buyer->setIp( $this->transaction->get_customer_ip_address() );
		$buyer->setCity( $this->transaction->get_customer_state() );
		$buyer->setCountry( $this->transaction->get_customer_country() );
		$buyer->setZipCode( $this->transaction->get_customer_zipcode() );
		$buyer->setRegistrationAddress( $this->transaction->get_customer_address() );

		return $buyer;
	}

	/**
	 * Iyzico için adres bilgisi
	 *
	 * @return \Iyzipay\Model\Address
	 */
	protected function prepare_address() {
		$address = new \Iyzipay\Model\Address();
		$address->setContactName( $this->transaction->get_customer_full_name() );
		$address->setCity( $this->transaction->get_customer_city() );
		$address->setCountry( $this->transaction->get_customer_country() );
		$address->setAddress( $this->transaction->get_customer_address() );
		$address->setZipCode( $this->transaction->get_customer_zipcode() );
		return $address;
	}

	/**
	 * Iyzico için kart bilgisi
	 *
	 * @return \Iyzipay\Model\PaymentCard
	 */
	public function prepare_payment_card() {
		$payment_card = new \Iyzipay\Model\PaymentCard();
		$payment_card->setCardHolderName( $this->transaction->get_card_holder_name() );
		$payment_card->setCardNumber( $this->transaction->get_card_bin() );
		$payment_card->setExpireMonth( $this->transaction->get_card_expiry_month() );
		$payment_card->setExpireYear( $this->transaction->get_card_expiry_year() );
		$payment_card->setCvc( $this->transaction->get_card_cvv() );

		if ( $this->transaction->get_save_card() ) {
			$payment_card->setRegisterCard( 1 );
		}

		return $payment_card;
	}

	/**
	 * Iyzico için ürün bilgisi
	 *
	 * @return array
	 */
	protected function prepare_basket_items() {
		$basket_items = array();
		foreach ( $this->transaction->get_lines( array( 'product' ) ) as $line ) {
			$basket_item = new \Iyzipay\Model\BasketItem();
			$basket_item->setId( $line->get_id() );
			$basket_item->setName( $line->get_name() );
			$basket_item->setItemType( \Iyzipay\Model\BasketItemType::PHYSICAL );
			$basket_item->setCategory1( $line->get_category() );
			$basket_item->setPrice( number_format( $line->get_total(), 2, '.', '' ) );

			if ( $basket_item->getId() && (float) $basket_item->getPrice() > 0 ) {
				$this->basket_items_total += (float) $basket_item->getPrice();
				array_push( $basket_items, $basket_item );
			}
		}

		return $basket_items;
	}

	/**
	 * İstek ortak özelliklerinin atamalarını yapar
	 */
	protected function prepare_request_properties() {
		$this->payment_request->setPaymentSource( 'Gurmesoft' );
		$this->payment_request->setPaymentGroup( \Iyzipay\Model\PaymentGroup::PRODUCT );
		$this->payment_request->setCurrency( $this->transaction->get_currency() );
		$this->payment_request->setLocale( gpos_get_payment_locale() );
		$this->payment_request->setConversationId( $this->transaction->get_id() );
		$this->payment_request->setBasketId( $this->transaction->get_plugin_transaction_id() );
		$this->payment_request->setBuyer( $this->prepare_buyer() );
		$this->payment_request->setBillingAddress( $this->prepare_address() );
		$this->payment_request->setShippingAddress( $this->prepare_address() );
		$this->payment_request->setBuyer( $this->prepare_buyer() );
		$this->payment_request->setBasketItems( $this->prepare_basket_items() );
		$this->payment_request->setPrice( number_format( $this->basket_items_total, 2, '.', '' ) );
		$this->payment_request->setPaidPrice( number_format( $this->transaction->get_total(), 2, '.', '' ) );
	}

	/**
	 * Cevap kontrolü
	 *
	 * @param mixed $response iyzico cevabı.
	 */
	protected function response_checker( $response ) {
		if ( 'success' === $response->getStatus() ) {
			$this->set_payment_success( $response );
		} else {
			// Yetersiz bakiye, Froud vb. gibi kartla ilgili durumlardan dolayı ödeme yapılamazsa bu blok hata mesajını değiştirir.
			$this->set_payment_failed( $response );
		}
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
	 * Ödeme geçidi ayarlarını setler.
	 *
	 * @param string $process İşlem tipi.
	 * @param mixed  $request Gönderilen istek.
	 * @param mixed  $response Gönderilen isteğe istinaden alınan cevap.
	 *
	 * @return void
	 */
	public function log( $process, $request = array(), $response = array() ) {
		$request  = ! is_array( $request ) && is_object( $request ) && method_exists( $request, 'getJsonObject' ) ? $request->getJsonObject() : $request;
		$response = ! is_array( $response ) && is_object( $response ) && method_exists( $response, 'getRawResult' ) ? $response->getRawResult() : $response;

		if ( $this->transaction instanceof GPOS_Transaction ) {
			$this->transaction->add_log( $process, $request, $response );
		}
	}
}
