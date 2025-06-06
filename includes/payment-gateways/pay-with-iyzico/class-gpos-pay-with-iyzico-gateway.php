<?php
/**
 * Iyzico PRO ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOSPRO_Iyzico_Gateway) barındırır.
 *
 * @package Gurmehub
 */

/**
 * GPOS_Pay_With_Iyzico_Gateway sınıfı.
 */
class GPOS_Pay_With_Iyzico_Gateway extends GPOS_Iyzico_Gateway {

	/**
	 * Ödeme işlemi
	 *
	 * @return GPOS_Gateway_Response
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function process_payment() {
		$this->payment_request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
		$this->prepare_request_properties();
		$this->payment_request->setCallbackUrl( $this->get_callback_url() );
		$this->payment_request->setEnabledInstallments( [ 3, 6, 9, 12 ] ); // Todo.
		$response = \Iyzipay\Model\PayWithIyzicoInitialize::create( $this->payment_request, $this->settings );

		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_START_ALTERNATIVE, $this->payment_request, $response );

		if ( 'success' === $response->getStatus() ) {
			$this->gateway_response->set_success( true )->set_alternative_payment_url( $response->getPayWithIyzicoPageUrl() );
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
}
