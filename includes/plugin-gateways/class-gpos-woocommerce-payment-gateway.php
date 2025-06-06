<?php
/**
 * WooCommerce ödeme sınıfı olan GPOS_WooCommerce_Payment_Gateway barındırır.
 *
 * @package GurmeHub
 */

/**
 * WooCommerce ödeme sınıfları arasına eklenen GPOS_WooCommerce_Payment_Gateway ödeme sınıfı.
 *
 * @method GPOS_Gateway_Response create_new_payment_process( $post_data, $plugin_transaction_id, $plugin, $account_id = 0 )
 */
class GPOS_WooCommerce_Payment_Gateway extends WC_Payment_Gateway_CC implements GPOS_Plugin_Gateway {

	use GPOS_Plugin_Payment_Gateway;

	/**
	 * GurmePOS WooCommerce ayarları
	 *
	 * @var GPOS_WooCommerce_Settings $woocommerce_settings
	 */
	public $woocommerce_settings;

	/**
	 * WooCommerce siparişi.
	 *
	 * @var WC_Order|null $order
	 */
	public $order;

	/**
	 * GPOS_WooCommerce_Payment_Gateway kurucu sınıfı.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->id                   = $this->gpos_prefix;
		$this->woocommerce_settings = gpos_woocommerce_settings();
		$this->method_title         = __( 'POS Entegratör', 'gurmepos' );
		// translators: %s = POS Entegratör
		$this->method_description = sprintf( __( '%s - Multiple payment solutions', 'gurmepos' ), 'POS Entegratör' );

		$this->title             = apply_filters( 'gpos_woocommerce_gateway_title_text', $this->woocommerce_settings->get_setting_by_key( 'title' ) );
		$this->description       = apply_filters( 'gpos_woocommerce_gateway_description_text', $this->woocommerce_settings->get_setting_by_key( 'description' ) );
		$this->order_button_text = apply_filters( 'gpos_woocommerce_gateway_button_text', $this->woocommerce_settings->get_setting_by_key( 'button_text' ) );
		$this->icon              = $this->woocommerce_settings->get_setting_by_key( 'icon' );
		$this->has_fields        = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
	}

	/**
	 * WooCommerce sipariş sayfasından ödeme tetiklenir.
	 *
	 * @param int $order_id Sipariş numarası.
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {
		try {
			$this->order = wc_get_order( $order_id );
			$response    = $this->create_new_payment_process( gpos_clean( $this->get_post_data() ), $order_id, GPOS_Transaction_Utils::WOOCOMMERCE );

			if ( $response->is_success() ) {

				if ( $this->transaction->get_security_type() === GPOS_Transaction_Utils::REGULAR ) {
					$this->transaction_success_process( $response );
					return $this->success_process( $response, true );
				}

				$redirect_url = $this->get_redirect_url( $response );

				if ( $redirect_url ) {
					$iframe = $this->form_settings->get_setting_by_key( 'use_iframe' );
					return array(
						'result'                          => 'success',
						$iframe ? 'messages' : 'redirect' => $iframe ? gpos_iframe_content( $redirect_url ) : $redirect_url,
					);
				}
			}

			$this->transaction_error_process( $response );
			$this->error_process( $response, true );

		} catch ( Exception $e ) {
			$this->exception_handler( $e, true );
		}
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
		$this->order  = wc_get_order( $this->transaction->get_plugin_transaction_id() );
		$received_url = $this->order->get_checkout_order_received_url();
		$this->set_fee();

		if ( $response->get_payment_id() && $this->order->needs_payment() ) {
			$this->order->payment_complete( $response->get_payment_id() );
			$this->order->add_order_note( gpos_transaction_note( $response ) );
		}

		if ( $on_checkout ) {
			return array(
				'result'   => 'success',
				'redirect' => $received_url,
			);
		}

		if ( $this->form_settings->get_setting_by_key( 'use_iframe' ) ) {
			$this->iframe_redirect( $received_url );
		}

		wp_safe_redirect( $received_url );
		exit;
	}

	/**
	 * Ödeme işleminin hatayla karşılaşması sonucunda yapılacak işlemlerin hepsini barındırır.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
	 * @param bool                  $on_checkout Ödeme sayfasında mı ?
	 *
	 * @return array|void
	 * @throws Exception Ödemede hata
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function error_process( GPOS_Gateway_Response $response, $on_checkout ) {
		if ( ! $this->order instanceof WC_Order ) {
			$this->order = wc_get_order( $this->transaction->get_plugin_transaction_id() );
		}

		if ( ! $this->order->get_transaction_id() ) {
			$this->order->add_order_note( gpos_transaction_note( $response ) );
		}

		if ( false === $on_checkout ) {
			$checkout_url = add_query_arg(
				array(
					"{$this->gpos_prefix}-error" => bin2hex( $response->get_error_message() ),
				),
				wc_get_checkout_url()
			);

			if ( $this->form_settings->get_setting_by_key( 'use_iframe' ) ) {
				$this->iframe_redirect( $checkout_url );
			}

			wp_safe_redirect( $checkout_url );
			exit;
		}

		throw new Exception( esc_html( $response->get_error_message() ) );
	}

	/**
	 * Ödeme işleminin bildirim tarafından gelen cevaba istinaden yapılacak aksiyonları organzie eder.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
	 *
	 * @return void
	 */
	public function notify_process( GPOS_Gateway_Response $response ) {
		$this->order = wc_get_order( $this->transaction->get_plugin_transaction_id() );

		// order kontrolü.
		if ( $this->order ) {
			if ( $response->is_success() && $response->get_payment_id() && $this->order->needs_payment() ) {
				$this->set_fee();
				$this->order->payment_complete( $response->get_payment_id() );
			}

			if ( ! $this->order->get_transaction_id() && $this->order->needs_payment() ) {
				$this->order->update_status( 'failed' );
			}

			$this->order->add_order_note( gpos_transaction_note( $response ) );
		}
	}

	/**
	 * WooCommerce siparişini ödeme geçidine tanımlar.
	 *
	 * @return void
	 */
	public function set_properties() {
		$this->transaction
		->set_total( $this->order->get_total() )
		->set_currency( $this->order->get_currency() )
		->set_customer_id( $this->order->get_customer_id() )
		->set_customer_first_name( $this->order->get_billing_first_name() )
		->set_customer_last_name( $this->order->get_billing_last_name() )
		->set_customer_address( "{$this->order->get_billing_address_1()} {$this->order->get_billing_address_2()}" )
		->set_customer_state( WC()->countries->get_states( $this->order->get_billing_country() )[ $this->order->get_billing_state() ] )
		->set_customer_city( $this->order->get_billing_city() )
		->set_customer_country( $this->order->get_billing_country() )
		->set_customer_phone( $this->order->get_billing_phone() )
		->set_customer_email( $this->order->get_billing_email() )
		->set_customer_ip_address( $this->order->get_customer_ip_address() );

		if ( false === $this->form_settings->get_setting_by_key( 'holder_name_field' ) ) {
			$this->transaction->set_card_holder_name( $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name() );
		}

		$order_lines = $this->order->get_items( array( 'line_item', 'shipping', 'fee' ) );

		if ( false === empty( $order_lines ) ) {
			/**
			 * WooCommerce ürün sınıfları.
			 *
			 * @var WC_Order_Item_Product|WC_Order_Item_Shipping|WC_Order_Item_Fee $order_line WooCommerce ürünü.
			 */
			foreach ( $order_lines as $order_line ) {
				$total      = method_exists( $order_line, 'get_total' ) ? (float) $order_line->get_total() : 0;
				$tax        = method_exists( $order_line, 'get_total_tax' ) ? (float) $order_line->get_total_tax() : 0;
				$item_total = $total + $tax;

				if ( $item_total > 0 ) {
					$this->transaction->add_line(
						gpos_transaction_line()
						->set_plugin_line_id( $order_line->get_id() )
						->set_name( $order_line->get_name() )
						->set_quantity( 1 )
						->set_total( $item_total )
					);
				}
			}
		}
	}


	/**
	 * WooCommerce ödeme formu.
	 *
	 * @return void
	 */
	public function payment_fields() {

		if ( $this->description ) {
			echo wp_kses_post( "<p class='gpos-description'>{$this->description}</p>" );
		}

		gpos_vue()->create_app_div();
	}

	/**
	 * Kredi kartı alanları için validasyon methodu.
	 *
	 * @return bool
	 */
	public function validate_fields() {

		$validate = true;
		$this->create_post_data( gpos_clean( $this->get_post_data() ) );
			$alerts = gpos_get_alert_texts();
		foreach ( [
			'card-bin',
			'card-expiry-month',
			'card-expiry-year',
			'card-cvv',
			'card-holder-name',
		] as $field ) {
			if ( isset( $this->post_data[ "{$this->gpos_prefix}-{$field}" ] ) && empty( $this->post_data[ "{$this->gpos_prefix}-{$field}" ] ) ) {
				$validate = false;
				wc_add_notice( $alerts[ str_replace( '-', '_', $field ) ], 'error' );

			}
		}

		return $validate;
	}

	/**
	 * WooCommerce -> Ayarlar -> Ödemeler sekmesi altındaki ayarları yönlendirir.
	 *
	 * @return void
	 */
	public function admin_options() {
		?>
			<h3>
				<?php esc_html_e( 'These payment method settings are made through the admin menu.', 'gurmepos' ); ?> 
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=gpos-payment-methods' ) ); ?>"><?php esc_html_e( 'Click to go to settings.', 'gurmepos' ); ?></a> 
			</h3>
		<?php

		parent::admin_options();
	}

	/**
	 * WooCommerce -> Ayarlar -> Ödemeler sekmesi altındaki ayarları yönlendirir.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Active', 'gurmepos' ),
				'label'   => __( 'Receive Payment with POS Entegrator', 'gurmepos' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
		);
	}


	/**
	 * İşlemde taskit vade farkı varsa Sipariş bilgileri eklemeyi yapar
	 */
	protected function set_fee() {
		$fee = $this->get_installment_fee();
		if ( $fee && ! $this->order->get_meta( "{$this->gpos_prefix}_fee", true ) && $this->woocommerce_settings->get_setting_by_key( 'set_fee' ) ) {
			$fee_data = new WC_Order_Item_Fee();
			$fee_data->set_amount( (string) $fee->get_total() );
			$fee_data->set_total( (string) $fee->get_total() );
			$fee_data->set_name( $fee->get_name() );
			$fee_data->set_tax_status( 'none' );
			$fee_data->save();
			$this->order->add_meta_data( "{$this->gpos_prefix}_fee", true );
			$this->order->add_item( $fee_data );
			$this->order->calculate_totals();
			$this->order->save();
		}
	}
}
