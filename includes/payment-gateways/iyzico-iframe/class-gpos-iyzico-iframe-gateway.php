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
class GPOS_Iyzico_IFrame_Gateway extends GPOS_Iyzico_Gateway {

	/**
	 * Ödeme işlemi
	 *
	 * @return GPOS_Gateway_Response
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function process_payment() {
		$this->payment_request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
		$this->prepare_request_properties();
		$this->payment_request->setCallbackUrl( $this->get_callback_url() );
		$this->payment_request->setEnabledInstallments( [ 3, 6, 9, 12 ] ); // Todo.
		$response = \Iyzipay\Model\CheckoutFormInitialize::create( $this->payment_request, $this->settings );

		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_START_IFRAME, $this->payment_request, $response );

		if ( 'success' === $response->getStatus() ) {
			ob_start();
			gpos_get_view(
				'iframes/iyzico-iframe-form.php',
				array(
					'type'   => gpos_is_test_mode() ? $this->account_settings->test_type : $this->account_settings->type,
					'script' => $response->getCheckoutFormContent(),
				)
			);
			$this->gateway_response->set_success( true )->set_html_content( ob_get_clean() );
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
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_CALLBACK, [], $post_data );
		if ( array_key_exists( 'token', $post_data ) ) {
			$request = new \Iyzipay\Request\RetrievePayWithIyzicoRequest();
			$request->setLocale( gpos_get_payment_locale() );
			$request->setConversationId( time() );
			$request->setToken( $post_data['token'] );
			$response = \Iyzipay\Model\PayWithIyzico::retrieve( $request, $this->settings );
			$this->log( GPOS_Transaction_Utils::LOG_PROCESS_FINISH, $request, $response );
			$this->response_checker( $response );
		}
		return $this->gateway_response;
	}

	/**
	 * Cevap kontrolü.
	 *
	 * @param mixed $response iyzico cevabı.
	 */
	protected function response_checker( $response ) {
		if ( 'SUCCESS' === $response->getPaymentStatus() && 'success' === $response->getStatus() ) {
			$this->set_payment_success( $response );
		} else {
			// Yetersiz bakiye, Froud vb. gibi kartla ilgili durumlardan dolayı ödeme yapılamazsa bu blok hata mesajını değiştirir.
			$this->set_payment_failed( $response );
		}
	}
}
