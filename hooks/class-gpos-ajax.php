<?php
/**
 * Frontend aksiyonları için backend uç noktalarını oluşturan ve
 * ilgili fonksiyonlara yönlendiren GPOS_Ajax sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GPOS_Ajax
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class GPOS_Ajax {

	/**
	 * Ajax çağrılarında kullanılacak ön ek
	 * Örn : gpos_get_settings
	 *
	 * @var string $prefix
	 */
	private $prefix = 'gpos';

	/**
	 * GPOS_Ajax bu dizi içerisindeki uç noktalara cevap verir
	 * ve fonksiyonlara yönlendirir.
	 *
	 * 'get_settings' => array( $this, 'get_settings'),
	 * 'is_active'    => 'is_active',
	 * ...
	 *
	 * @var array $endpoints
	 */
	private $endpoints;

	/**
	 * GPOS_Ajax kurucu method.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'ajax_actions' ) );
	}

	/**
	 * GPOS_Ajax ajax_actions
	 *
	 * @return void
	 */
	public function ajax_actions() {
		$this->endpoints = apply_filters(
			/**
			 * Ajax uç noktalarına ekle/çıkar yapmak için kullanılır.
			 *
			 * @param array Varsayılan uç noktalar.
			 */
			"{$this->prefix}_ajax_endpoints",
			array(
				'update_test_mode'              => array( $this, 'update_test_mode' ),
				'update_active_status'          => array( $this, 'update_active_status' ),
				'update_installment_status'     => array( $this, 'update_installment_status' ),
				'update_installments'           => array( $this, 'update_installments' ),
				'get_installments_from_api'     => array( $this, 'get_installments_from_api' ),
				'update_default_status'         => array( $this, 'update_default_status' ),
				'add_gateway_account'           => array( $this, 'add_gateway_account' ),
				'get_gateway_accounts'          => array( $this, 'get_gateway_accounts' ),
				'update_form_settings'          => array( $this, 'update_form_settings' ),
				'update_woocommerce_settings'   => array( $this, 'update_woocommerce_settings' ),
				'update_account_settings'       => array( $this, 'update_account_settings' ),
				'update_other_settings'         => array( $this, 'update_other_settings' ),
				'remove_gateway_account'        => array( $this, 'remove_gateway_account' ),
				'check_connection'              => array( $this, 'check_connection' ),
				'hide_notice'                   => array( $this, 'hide_notice' ),
				'bin_retrieve'                  => array( $this, 'bin_retrieve' ),
				'process_cancel'                => array( $this, 'process_cancel' ),
				'process_refund'                => array( $this, 'process_refund' ),
				'process_line_based_refund'     => array( $this, 'process_line_based_refund' ),
				'update_tag_manager_settings'   => array( $this, 'update_tag_manager_settings' ),
				'update_notification_settings'  => array( $this, 'update_notification_settings' ),
				'wc_get_cart_total'             => array( $this, 'wc_get_cart_total' ),
				'reinstall_tables'              => array( $this, 'reinstall_tables' ),
				'recheck_status'                => array( $this, 'recheck_status' ),
				'update_ins_display_settings'   => array( $this, 'update_ins_display_settings' ),
				'get_installment_html'          => array( $this, 'get_installment_html' ),
				'calculate_group_product_price' => array( $this, 'calculate_group_product_price' ),
				'get_iyzipos_saved_cards'       => array( $this, 'get_iyzipos_saved_cards' ),
				'iyzipos_save_card'             => array( $this, 'iyzipos_save_card' ),
				'iyzipos_update_gateway'        => array( $this, 'iyzipos_update_gateway' ),
			)
		);

		if ( false === empty( $this->endpoints ) ) {
			foreach ( array_keys( $this->endpoints ) as $endpoint ) {
				add_action( "wp_ajax_{$this->prefix}_{$endpoint}", array( $this, 'middleware' ) );
				add_action( "wp_ajax_nopriv_{$this->prefix}_{$endpoint}", array( $this, 'middleware' ) );
			}
		}
	}

	/**
	 * Ajax çağrılarının güvenlik kontrolünü yapar ve
	 * ilgili aksiyona yönlendirir.
	 *
	 * @return void
	 */
	public function middleware() {
		// Ajax nonce kontrolü yap.
		if ( check_ajax_referer( GPOS_AJAX_ACTION ) && isset( $_REQUEST['action'] ) ) {
			$_REQUEST    = gpos_clean( $_REQUEST ); // Güvenlik için isteğin içerisindeki script, html gibi tagları temizler.
			$next_action = str_replace( "{$this->prefix}_", '', sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) );

			try {
				/**
				 * Uç noktaya istinaden çalıştırılacak fonksiyonu tanımlar,
				 * filter aracılığı ile farklı sınıfların farklı fonksiyonları ile de aksiyon alınabilir.
				 *
				 * @param string|array $callback Çalıştırılacak fonksiyon.
				 * @param mixed $next_action Uç nokta.
				 */
				$action = apply_filters( "{$this->prefix}_ajax_action", $this->endpoints[ $next_action ], $next_action );
				// $action tanımlanan fonksiyonu çağır.
				$response = call_user_func( $action, json_decode( file_get_contents( 'php://input' ) ) );

				// WP_Error kontrolü yapar.
				if ( is_wp_error( $response ) ) {
					wp_send_json( array( 'error_message' => $response->get_error_message() ), 500 );
				}

				wp_send_json( $response );
			} catch ( Exception $e ) {
				wp_send_json( array( 'error_message' => $e->getMessage() ), 500 );
			}
		}
	}

	/**
	 * Geri dönüş fonksiyonu; update_test_mode.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_test_mode( $request ) {
		return gpos_settings()->set_test_mode( (bool) $request->test_mode );
	}

	/**
	 * Geri dönüş fonksiyonu; wc_get_cart_total.
	 *
	 * @return array
	 */
	public function wc_get_cart_total() {

		return array(
			'amount'   => WC()->cart->get_total( 'float' ),
			'currency' => get_woocommerce_currency(),
		);
	}

	/**
	 * Geri dönüş fonksiyonu; update_active_status.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_active_status( $request ) {
		return gpos_gateway_account( $request->id )->update_active_status( $request->status );
	}

	/**
	 * Geri dönüş fonksiyonu; update_installment_status.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_installment_status( $request ) {
		return gpos_gateway_account( $request->id )->update_installment_status( $request->status );
	}

	/**
	 * Geri dönüş fonksiyonu; get_installments_from_api.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function get_installments_from_api( $request ) {
		return gpos_gateway_account( $request->id )->gateway_class->get_installments();
	}

	/**
	 * Geri dönüş fonksiyonu; update_default_status.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return void
	 */
	public function update_default_status( $request ) {
		gpos_gateway_account( $request->id )->set_default();
	}

	/**
	 * Geri dönüş fonksiyonu; update_default_status.
	 *
	 * @return array
	 */
	public function get_gateway_accounts() {
		return gpos_gateway_accounts()->get_accounts( GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_VIRTUAL_POS );
	}

	/**
	 * Geri dönüş fonksiyonu; add_gateway_account.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return GPOS_Gateway_Account
	 */
	public function add_gateway_account( $request ) {
		return add_query_arg(
			array(
				'page'     => 'gpos-add-payment-method',
				'gateway'  => $request->gateway,
				'_wpnonce' => wp_create_nonce( GPOS_AJAX_ACTION ),
			),
			admin_url( '/admin.php' )
		);
	}

	/**
	 * Geri dönüş fonksiyonu; update_woocommerce_settings.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_woocommerce_settings( $request ) {
		return gpos_woocommerce_settings()->set_settings( (array) $request->settings );
	}

	/**
	 * Geri dönüş fonksiyonu; update_form_settings.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_form_settings( $request ) {
		return gpos_form_settings()->set_settings( (array) $request->settings );
	}

	/**
	 * Geri dönüş fonksiyonu; update_account_settings.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return void|GPOS_Gateway_Account
	 */
	public function update_account_settings( $request ) {
		if ( 0 === $request->id ) {
			$new_account = gpos_gateway_accounts()->add_account( $request->gateway_id );
			$request->id = $new_account->id;
		}
		gpos_gateway_account( $request->id )->gateway_settings->save_settings( (array) $request->settings );
		return $request;
	}

	/**
	 * Geri dönüş fonksiyonu; update_other_settings.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_other_settings( $request ) {
		return gpos_other_settings()->set_settings( (array) $request->settings );
	}

	/**
	 * Geri dönüş fonksiyonu; update_installments.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_installments( $request ) {
		return gpos_gateway_account( $request->id )->update_installments( $request->installments );
	}

	/**
	 * Geri dönüş fonksiyonu; update_tag_manager_settings.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_tag_manager_settings( $request ) {
		return gpos_tag_manager_settings()->set_settings( (array) $request->settings );
	}

	/**
	 * Geri dönüş fonksiyonu; update_notification_settings.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_notification_settings( $request ) {
		return gpos_notification_settings()->set_settings( gpos_object_to_array( $request->settings ) );
	}

	/**
	 * Geri dönüş fonksiyonu; remove_gateway_account.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function remove_gateway_account( $request ) {
		return gpos_gateway_accounts()->delete_account( $request->id );
	}

	/**
	 * Geri dönüş fonksiyonu; check_connection.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function check_connection( $request ) {
		$base_gateway = gpos_payment_gateways()->get_base_gateway_by_gateway_id( $request->gateway_id )->gateway_class;
		return ( new $base_gateway() )->check_connection( $request->settings );
	}

	/**
	 * Geri dönüş fonksiyonu; hide_notice.
	 *
	 * @return mixed
	 */
	public function hide_notice() {
		return update_user_meta( get_current_user_id(), 'gpos_hide_rating_message', true );
	}

	/**
	 * Geri dönüş fonksiyonu; bin_retrieve.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function bin_retrieve( $request ) {
		return gpos_tracker()->bin_retrieve( $request->bin );
	}

	/**
	 * Geri dönüş fonksiyonu; process_cancel.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function process_cancel( $request ) {
		return gpos_refund( gpos_transaction( $request->transaction_id ) )->cancel();
	}

	/**
	 * Geri dönüş fonksiyonu; process_refund.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function process_refund( $request ) {
		return gpos_refund( gpos_transaction( $request->transaction_id ) )->refund( $request->payment_id );
	}

	/**
	 * Geri dönüş fonksiyonu; process_line_based_refund.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function process_line_based_refund( $request ) {
		return gpos_refund( gpos_transaction( $request->transaction_id ) )->line_based_refund( $request->line_id, $request->total );
	}

	/**
	 * Geri dönüş fonksiyonu; reinstall_tables.
	 */
	public function reinstall_tables() {
		gpos_installer()->reinstall_db();
	}

	/**
	 * Geri dönüş fonksiyonu; recheck_status.
	 */
	public function recheck_status() {
		gpos_status_check()->check_callback_status();
	}

	/**
	 * Geri dönüş fonksiyonu; update_ins_display_settings.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function update_ins_display_settings( $request ) {
		return gpos_ins_display_settings()->set_settings( (array) $request->settings );
	}

	/**
	 * Geri dönüş fonksiyonu; get_installment_html.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function get_installment_html( $request ) {
		return gpos_installment_display( $request->price, $request->currency )->get_installment_html();
	}

	/**
	 * Geri dönüş fonksiyonu; calculate_group_product_price.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function calculate_group_product_price( $request ) {
		return gpos_calculate_group_product_price( (array) $request );
	}

	/**
	 * Geri dönüş fonksiyonu; get_iyzipos_saved_cards.
	 *
	 * @return mixed
	 */
	public function get_iyzipos_saved_cards() {
		return gpos_iyzipos()->card_list();
	}

	/**
	 * Geri dönüş fonksiyonu; iyzipos_save_card.
	 *
	 * @param stdClass $request İstek parametreleri.
	 *
	 * @return mixed
	 */
	public function iyzipos_save_card( $request ) {
		return gpos_iyzipos()->save_card( $request );
	}

	/**
	 * Geri dönüş fonksiyonu; iyzipos_update_gateway.
	 *
	 * @return mixed
	 */
	public function iyzipos_update_gateway() {
		return gpos_iyzipos()->update_gateway();
	}
}
