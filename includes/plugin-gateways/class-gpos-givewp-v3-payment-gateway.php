<?php
/**
 * GiveWP ödeme sınıfı olan GPOS_GiveWP_Payment_Gateway barındırır.
 *
 * @package GurmeHub
 */

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;


/**
 * GiveWP ödeme sınıfları arasına eklenen GPOS_GiveWP_Payment_Gateway ödeme sınıfı.
 *
 * @method GPOS_Gateway_Response create_new_payment_process( $post_data, $plugin_transaction_id, $plugin, $account_id = 0 )
 */
class GPOS_GiveWP_V3_Payment_Gateway extends Give\Framework\PaymentGateways\PaymentGateway implements GPOS_Plugin_Gateway {

	use GPOS_Plugin_Payment_Gateway;

	/**
	 * Benzersiz bağış kimliği
	 *
	 * @var int
	 */
	public $donation_id;

	/**
	 * Form ayarları
	 *
	 * @var GPOS_Form_Settings $form_settings
	 */
	public $form_settings;

	/**
	 * Bağış objesi
	 *
	 * @var Donation $donation
	 */
	public $donation;

	/**
	 * Ödeme geçidi kimliğini döndürür.
	 */
	public static function id(): string {
		return GPOS_PREFIX . '_givewp_v3';
	}

	/**
	 * Ödeme geçidi kimliğini döndürür.
	 */
	public function getId(): string {
		return self::id();
	}

	/**
	 * Ödeme geçidi ismini döndürür.
	 */
	public function getName(): string {
		return 'POS Entegratör';
	}

	/**
	 * Ödeme geçidi ismini döndürür.
	 */
	public function getPaymentMethodLabel(): string {
		return $this->getName();
	}

	/**
	 * Ödeme alma methodu.
	 *
	 * @param Donation $donation Bağış verisi
	 * @param array    $gateway_data Ödeme geçidi verisi
	 *
	 * @return GatewayCommand|RedirectOffsite|void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function createPayment( Donation $donation, $gateway_data ) {
		$this->donation    = $donation;
		$this->donation_id = $donation->id;
		return $this->process_payment( $gateway_data );
	}

	/**
	 * iade methodu
	 *
	 * @param Donation $donation Bağış verisi
	 *
	 * @throws Exception İade hatası
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function refundDonation( Donation $donation ) {
		throw new Exception( esc_html__( 'Use transactions for online refund/cancel operations', 'gurmepos' ) );
	}

	/**
	 * Arayüzde kullanılacak parametreler.
	 *
	 * @param int $form_id Form ID
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function formSettings( int $form_id ): array {
		return array(
			'gpos_nonce' => wp_create_nonce( GPOS_AJAX_ACTION ),
		);
	}

	/**
	 * Arayüz oluşturma methodu
	 *
	 * @param int $form_id Bağış formu numarası
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function enqueueScript( int $form_id ) {
		wp_enqueue_script(
			"{$this->gpos_prefix}-givewp-blocks",
			GPOS_PLUGIN_DIR_URL . 'assets/blocks/givewp/blocks.js',
			array(),
			GPOS_VERSION,
			true
		);
		gpos_frontend( GPOS_Transaction_Utils::GIVEWP_V3 );
	}

	/**
	 * Ödeme alma işlemi.
	 *
	 * @param array $gateway_data Ödeme geçidi verisi
	 *
	 * @return GatewayCommand|RedirectOffsite|void
	 */
	public function process_payment( array $gateway_data ) {

		$response = $this->create_new_payment_process( $_POST['gatewayData'], $this->donation_id, GPOS_Transaction_Utils::GIVEWP_V3 ); //phpcs:ignore 
		$this->transaction->add_meta( 'ok_url', urldecode( $gateway_data['successUrl'] ) );
		$this->transaction->add_meta( 'fail_url', urldecode( $gateway_data['cancelUrl'] ) );
		if ( $response->is_success() ) {

			if ( $this->transaction->get_security_type() === GPOS_Transaction_Utils::REGULAR ) {
				$this->transaction_success_process( $response );
				return $this->success_process( $response, true );
			}

			$redirect_url = $this->get_redirect_url( $response );

			if ( $redirect_url ) {
				return new RedirectOffsite( $redirect_url );
			}

			$redirect_url = $this->get_redirect_url( $response );

			if ( $redirect_url ) {
				return new RedirectOffsite( $redirect_url );
			}
		}

		$this->transaction_error_process( $response );
		$this->error_process( $response, true, true );
	}

	/**
	 * Ödeme işleminin başarıya ulaşması sonucunda yapılacak işlemlerin hepsini barındırır.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
	 * @param bool                  $on_checkout Ödeme sayfasında mı ?
	 *
	 * @return array|void
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public function success_process( GPOS_Gateway_Response $response, $on_checkout ) {
		if ( ! $this->donation_id ) {
			$this->donation_id = $this->transaction->get_plugin_transaction_id();
		}

		give_insert_payment_note( $this->donation->id, gpos_transaction_note( $response ) );

		if ( $on_checkout ) {
			return new PaymentComplete( $response->get_payment_id() );
		}

		give_update_payment_status( $this->donation_id, 'publish' );
		wp_safe_redirect( $this->transaction->get_meta( 'ok_url' ) );
		exit;
	}

	/**
	 * Ödeme işleminin hatayla karşılaşması sonucunda yapılacak işlemlerin hepsini barındırır.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
	 * @param bool                  $on_checkout Ödeme sayfasında mı ?
	 *
	 * @return array|void
	 *
	 * @throws PaymentGatewayException Ödeme işleminde hata
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function error_process( GPOS_Gateway_Response $response, $on_checkout ) {

		if ( ! $this->donation instanceof Donation ) {
			$this->donation = give()->donations->getById( $this->transaction->get_plugin_transaction_id() );
		}

		give_set_error( $this->donation->id, $response->get_error_message() );
		give_insert_payment_note( $this->donation->id, $response->get_error_message() );

		if ( $on_checkout ) {
			throw new PaymentGatewayException( esc_html( $response->get_error_message() ) );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					"{$this->gpos_prefix}-error" => bin2hex( $response->get_error_message() ),
				),
				$this->transaction->get_meta( 'fail_url' )
			)
		);
		exit;
	}

	/**
	 * Ödeme işleminin bildirim tarafından gelen cevaba istinaden yapılacak aksiyonları organzie eder.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
	 *
	 * @return void
	 */
	public function notify_process( GPOS_Gateway_Response $response ) {
		$this->donation = give()->donations->getById( $this->transaction->get_plugin_transaction_id() );

		if ( $response->is_success() && $response->get_payment_id() ) {
			give_update_payment_status( $this->donation->id, 'publish' );
		} else {
			give_update_payment_status( $this->donation->id, 'failed' );
		}

		give_insert_payment_note( $this->donation->id, gpos_transaction_note( $response ) );
	}

	/**
	 * GiveWP bağış bilgilerini ödeme geçidine tanımlar.
	 *
	 * @return void
	 */
	public function set_properties() {
		$donator = $this->donation->billingAddress;
		$this->transaction
		->set_total( $this->donation->amount->getAmount() / 100 )
		->set_currency( $this->donation->amount->getCurrency()->getCode() )
		->set_customer_ip_address( give_get_ip() )
		->set_customer_id( $this->donation->donorId )
		->set_customer_first_name( $this->donation->firstName )
		->set_customer_last_name( $this->donation->lastName )
		->set_customer_address( $donator->address1 )
		->set_customer_state( $donator->state )
		->set_customer_city( $donator->city )
		->set_customer_country( $donator->country )
		->set_customer_zipcode( $donator->zip )
		->set_customer_phone( $this->donation->phone )
		->set_customer_email( $this->donation->email );

		if ( false === $this->form_settings->get_setting_by_key( 'holder_name_field' ) ) {
			$this->transaction->set_card_holder_name( $this->transaction->get_customer_first_name() . ' ' . $this->transaction->get_customer_last_name() );
		}

		$transaction_line = gpos_transaction_line();

		$transaction_line
		->set_plugin_line_id( wp_rand( 1, 10000 ) )
		->set_name( __( 'Donation', 'gurmepos' ) )
		->set_quantity( 1 )
		->set_total( $this->donation->amount->getAmount() / 100 );

		$this->transaction->add_line( $transaction_line );
	}
}
