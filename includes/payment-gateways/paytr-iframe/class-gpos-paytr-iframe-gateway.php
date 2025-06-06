<?php
/**
 * PayTR ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOSPRO_PayTR_Gateway) barındırır.
 *
 * @package Gurmehub
 */

/**
 * GPOSPRO_PayTR_Gateway sınıfı.
 */
class GPOS_PayTR_IFrame_Gateway extends GPOS_Payment_Gateway {

	/**
	 * PayTR Api Adresi
	 *
	 * @var string  $request_url
	 */
	public $request_url = 'https://www.paytr.com/odeme';

	/**
	 * PayTR Api Ayar Dizisi
	 *
	 * @var array $settings
	 */
	public $settings;


	/**
	 * Gurmehub Referans kodu.
	 *
	 * @var string
	 */
	public $gurmehub_ref_number = 'ad05b23df5438f3661bdf3298391ee46a1118d83bdfcfb0bbbc1942b55e3ec11';

	/**
	 * GPOSPRO_PayTR_Settings kurucu fonksiyon değerindedir gerekli ayarlamaları yapar.
	 *
	 * @param GPOS_PayTR_IFrame_Settings|stdClass $settings Ödeme geçidi ayarlarını içerir.
	 *
	 * @return void
	 */
	public function prepare_settings( $settings ) {
		$is_test        = gpos_is_test_mode();
		$this->settings = array(
			'merchant_id'   => $is_test ? $settings->test_merchant_id : $settings->merchant_id,
			'merchant_key'  => $is_test ? $settings->test_merchant_key : $settings->merchant_key,
			'merchant_salt' => $is_test ? $settings->test_merchant_salt : $settings->merchant_salt,
		);
	}

	/**
	 * Ödeme kuruluşunun bağlantı testi
	 *
	 * @param stdClass $connection_data Ödeme geçidi ayarları.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function check_connection( $connection_data ) {
		return false;
	}

	/**
	 * Apilerinde taksit bilgisi gönderen kuruluşlar için otomatik getirir.
	 *
	 * @return array
	 */
	public function get_installments() {
		return array();
	}

	/**
	 * PayTR token.
	 *
	 * @param array $request istek.
	 *
	 * @return string
	 */
	protected function get_payment_token( $request ) {
		return $this->create_hash( "{$this->settings['merchant_id']}{$request['user_ip']}{$request['merchant_oid']}{$request['email']}{$request['payment_amount']}{$request['user_basket']}{$request['no_installment']}{$request['max_installment']}{$request['currency']}{$this->settings['merchant_salt']}" );
	}

	/**
	 * Ödeme işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_payment() {

		$request = $this->prepare_request();
		$request = array_merge(
			$this->prepare_request(),
			array(
				'no_installment'  => $this->get_no_installment(),
				'max_installment' => $this->get_max_installment(),
				'payment_amount'  => $this->transaction->get_total() * 100,
				'user_basket'     => base64_encode( wp_json_encode( $this->get_user_basket() ) ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			),
		);

		$request['paytr_token'] = $this->get_payment_token( $request );

		$response = $this->http_request->request( "{$this->request_url}/api/get-token", 'POST', $request );

		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_START_IFRAME, $request, is_string( $response ) ? array( 'response' => $response ) : $response );

		if ( isset( $response['status'] ) && 'failed' === $response['status'] ) {
			$this->gateway_response->set_error_message( $response['reason'] );
		} elseif ( isset( $response['status'] ) && 'success' === $response['status'] ) {
			ob_start();
			gpos_get_view( 'iframes/paytr-iframe-form.php', array( 'token' => $response['token'] ) );
			$this->gateway_response->set_success( true )->set_html_content( ob_get_clean() );
		}

		return $this->gateway_response;
	}

	/**
	 * 3D Ödeme işlemleri için geri dönüş fonksiyonu.
	 *
	 * @param array $post_data Geri dönüş verileri.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_callback( array $post_data ) {

		if ( isset( $post_data['fail_message'] ) ) {
			$this->gateway_response->set_error_message( $post_data['fail_message'] );
		} else {

			$this->gateway_response->set_success( true );
		}

		return $this->gateway_response;
	}

	/**
	 * PayTR işlem durumunun kontrolü methodu.
	 *
	 * @param string $payment_id Ödeme işlem numarası.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function check_status( $payment_id ) {
		return $this->gateway_response;
	}

	/**
	 * Ödeme iptal işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function process_cancel() {
		$request = array(
			'merchant_id'   => $this->settings['merchant_id'],
			'merchant_oid'  => $this->transaction->get_payment_id(),
			'return_amount' => $this->transaction->get_total(),
			'paytr_token'   => $this->create_hash( $this->settings['merchant_id'] . $this->transaction->get_payment_id() . $this->transaction->get_total() . $this->settings['merchant_salt'] ),
		);

		$response = $this->revert( $request );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_CANCEL, $request, $response );
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
		$request = array(
			'merchant_id'   => $this->settings['merchant_id'],
			'merchant_oid'  => $payment_id,
			'return_amount' => $refund_total,
			'paytr_token'   => $this->create_hash( $this->settings['merchant_id'] . $payment_id . $refund_total . $this->settings['merchant_salt'] ),
		);

		$response = $this->revert( $request );
		$this->log( GPOS_Transaction_Utils::LOG_PROCESS_REFUND, $request, $response );
		return $this->gateway_response;
	}

	/**
	 * İptal iade işlemi ortak methodu.
	 *
	 * @param array $request İşlem verileri.
	 * @return array $response
	 */
	protected function revert( $request ) {
		$response = $this->http_request->request(
			"{$this->request_url}/iade",
			'POST',
			$request
		);

		if ( 'success' === $response['status'] ) {
			$this->gateway_response
			->set_success( true )
			->set_payment_id( $response['merchant_oid'] );

		} else {
			$this->gateway_response
			->set_error_code( $response['err_no'] )
			->set_error_message( $response['err_msg'] );
		}

		return $response;
	}

	/**
	 * PayTR tokenlar için hash mekanizması.
	 *
	 * @param string $hash_str Hashlenecek string.
	 */
	protected function create_hash( $hash_str ) {
		return base64_encode(  //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			hash_hmac(
				'sha256',
				$hash_str,
				$this->settings['merchant_key'],
				true
			)
		);
	}

	/**
	 * PayTR veri kontrolü.
	 *
	 * @param array $post_data PayTR tarafından gönderilen veriler.
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public function check_notify( array $post_data ) {

		try {
			$this->log( GPOS_Transaction_Utils::LOG_PROCESS_NOTIFY, [], $post_data );
			$plugin_gateway = gpos_get_plugin_gateway_by_transaction( $this->transaction );

			if ( 'success' === $post_data['status'] ) {
				if ( array_key_exists( 'installment_count', $post_data ) && (int) $post_data['installment_count'] > 1 ) {
					$total_amount     = (float) $post_data['total_amount'] / 100;
					$payment_amount   = (float) $post_data['payment_amount'] / 100;
					$installment_rate = ( ( 100 * $total_amount ) / $payment_amount ) / 100;
					$this->transaction->set_installment( $post_data['installment_count'] )->set_installment_rate( $installment_rate );
					$plugin_gateway->set_fee_line( $total_amount );
				}

				$this->gateway_response->set_success( true )->set_payment_id( $post_data['merchant_oid'] );
				$plugin_gateway->transaction_success_process( $this->gateway_response );

			} elseif ( 'failed' === $post_data['status'] ) {
				$this->gateway_response
				->set_error_code( $post_data['failed_reason_code'] )
				->set_error_message( $post_data['failed_reason_msg'] );
				$plugin_gateway->transaction_error_process( $this->gateway_response );
			}

			$plugin_gateway->notify_process( $this->gateway_response );

		} catch ( Exception $e ) {
			/**
			* Empty Block
			*/
		}

		echo 'OK';
		exit;
	}


	/**
	 * PayTR sipariş verilerini ayarlar
	 *
	 * @return array
	 */
	protected function prepare_request() {
		$request = array(
			'ref_id'            => $this->gurmehub_ref_number,
			'merchant_id'       => (int) $this->settings['merchant_id'],
			'user_ip'           => $this->transaction->get_customer_ip_address(),
			'merchant_oid'      => $this->transaction->get_id(),
			'currency'          => $this->get_currency(),
			'user_name'         => $this->transaction->get_customer_full_name(),
			'user_address'      => "{$this->transaction->get_customer_address()} {$this->transaction->get_customer_city()}/{$this->transaction->get_customer_state()}",
			'user_phone'        => $this->transaction->get_customer_phone(),
			'merchant_ok_url'   => $this->get_callback_url(),
			'merchant_fail_url' => $this->get_callback_url(),
			'email'             => $this->transaction->get_customer_email(),
			'client_lang'       => gpos_get_payment_locale(),
			'debug_on'          => (int) gpos_is_test_mode(),
		);

		return $request;
	}


	/**
	 * PayTR sepet bilgilerini ayarlar.
	 */
	protected function get_user_basket() {
		$basket = array();
		foreach ( $this->transaction->get_lines() as $line_item ) {
			$basket[] = array(
				$line_item->get_name(),
				gpos_number_format( $line_item->get_total() ),
				$line_item->get_quantity(),
			);
		}
		return $basket;
	}

	/**
	 * PayTR hash kontrolü.
	 *
	 * @param array $post_data PayTR tarafından gönderilen veriler.
	 */
	public function check_hash( array $post_data ) {
		return $post_data['hash'] === $this->create_hash( $post_data['merchant_oid'] . $this->settings['merchant_salt'] . $post_data['status'] . $post_data['total_amount'] );
	}

	/**
	 * PayTR formatında para birimini döndürür.
	 *
	 * @return string
	 */
	protected function get_currency() {
		return 'TRY' === $this->transaction->get_currency() ? 'TL' : $this->transaction->get_currency();
	}

	/**
	 * PayTR formatında taksit adedini döndürür.
	 *
	 * @return string|int
	 */
	protected function get_no_installment() {
		return 0; // todo
	}

	/**
	 * PayTR formatında taksit adedini döndürür.
	 *
	 * @return string|int
	 */
	protected function get_max_installment() {
		// Sıfır (0) gönderilmesi durumunda yürürlükteki en fazla izin verilen taksit geçerli olur.
		return 0; // todo
	}
}
