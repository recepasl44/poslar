<?php
/**
 * GPOS_WooCommerce sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * Bu sınıf eklenti aktif olur olmaz çalışmaya başlar ve
 * kurucu fonksiyonu içerisindeki WooCommerce kancalarına tutunur.
 */
class GPOS_WooCommerce {


	/**
	 * Eklenti Prefix
	 *
	 * @var string $prefix
	 */
	protected $prefix = GPOS_PREFIX;

	/**
	 * WooCommerce ayarları
	 *
	 * @var GPOS_WooCommerce_Settings $settings
	 */
	private $settings;

	/**
	 * GPOS_WooCommerce kurucu fonksiyonu
	 *
	 * @return void
	 */
	public function __construct() {
		$this->settings = gpos_woocommerce_settings();
		// WooCommerce işlemlere başlamadan önce
		add_action( 'before_woocommerce_init', array( $this, 'before_woocommerce_init' ) );
		// Ödeme geçitleri arasına GPOS_WooCommerce_Payment_Gateway i ekler.
		add_filter( 'woocommerce_payment_gateways', array( $this, 'payment_gateways' ) );
		// Sipariş için ödeme tamamlandığında geçeceği durumu ayarlar.
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'complete_order_status' ) );
		// Ödeme formundan önceki üst kontent. Hataları görüntülemek için kullanıldı.
		add_action( 'woocommerce_before_checkout_form', array( $this, 'before_checkout_form' ) );
		// Sipariş ürünlerinin gizlenmiş bilgileri
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hidden_order_itemmeta' ) );
		// Sipariş tablosundaki eylemlere, işleme gidiş linki ekleme.
		add_filter( 'woocommerce_admin_order_actions_start', array( $this, 'admin_order_actions' ) );
		// İptal işlemlerinde woocommerce sipariş durumununun tetiklenmesi
		add_action( 'gpos_woocommerce_transaction_canceled', array( $this, 'cancel_order' ) );
		// İade işlemlerinde woocommerce sipariş durumununun tetiklenmesi
		add_action( 'gpos_woocommerce_transaction_refunded', array( $this, 'cancel_order' ) );
		// REST Apiye işlem bilgisi eklemek
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'rest_api_object' ), 10, 2 );
		// GurmePOS için woocommerce blocks özelliği kayıt eder
		add_action( 'woocommerce_blocks_loaded', array( $this, 'woocommerce_block_support' ) );
		// Taksit Tablosu
		add_filter( 'woocommerce_product_tabs', array( $this, 'product_tabs' ) );
	}

	/**
	 *  İşlem iptal edildiğinde ilgili siparişin durumunu iptal edildi olarak değiştirir
	 *
	 *  @param GPOS_Transaction $transaction İptal işlem nesnesi
	 */
	public function cancel_order( $transaction ) {
		$order = wc_get_order( $transaction->get_plugin_transaction_id() );
		$order->update_status( apply_filters( 'gpos_wc_cancelled_status', 'refunded' ), __( 'Order canceled from transaction page.', 'gurmepos' ) );
	}

	/**
	 *  İşlem iade edildiğinde ilgili siparişin durumunu iade edildi olarak değiştirir
	 *
	 *  @param GPOS_Transaction $transaction İptal işlem nesnesi
	 */
	public function refund_order( $transaction ) {
		$order = wc_get_order( $transaction->get_plugin_transaction_id() );
		$order->update_status( apply_filters( 'gpos_wc_refunded_status', 'refunded' ), __( 'Order refunded from transaction page.', 'gurmepos' ) );
	}
	/**
	 * WooCommerce before init kancası.
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function before_woocommerce_init() {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', GPOS_PLUGIN_BASEFILE, true );

			if ( defined( 'GPOSPRO_PLUGIN_BASEFILE' ) ) {
				FeaturesUtil::declare_compatibility( 'custom_order_tables', GPOSPRO_PLUGIN_BASEFILE, true );
			}
			if ( defined( 'GPOSFORM_PLUGIN_BASEFILE' ) ) {
				FeaturesUtil::declare_compatibility( 'custom_order_tables', GPOSFORM_PLUGIN_BASEFILE, true );
			}
		}
	}

	/**
	 * Bu fonksiyon dizi halinde gelen aktif woocommerce ödeme geçitleri
	 * arasına POS Entegratör'ü ekler ve geçitleri geri döndürür.
	 *
	 * @param array $gateways Ödeme geçitleri.
	 *
	 * @return array $gateways
	 */
	public function payment_gateways( $gateways ) {
		$gateways[ $this->prefix ] = 'GPOS_WooCommerce_Payment_Gateway'; // WC_Payment_Gateway_CC devralınarak yaratılan ödeme sınıfı.
		return $gateways;
	}

	/**
	 * Ödeme işlemleri bittiğinde sipariş geçmesi gereken durumu ayarlardan okuyarak döndürür.
	 * Siparişin bu duruma geçmesi için WC_Order::payment_complete metodunun çalışması gerekir.
	 *
	 * @return string
	 */
	public function complete_order_status() {
		return gpos_woocommerce_settings()->get_setting_by_key( 'success_status' );
	}

	/**
	 * 3D Ödeme işlemi sırasında kullanıcıya gösterilmesi gereken hatalar
	 * $_GET isteğine eklenir ve istekten yakalanan uyarıları ekrana yansıtır.
	 *
	 * @return void
	 */
	public function before_checkout_form() {
		if ( isset( $_GET[ "{$this->prefix}-error" ] ) ) {                                          //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wc_add_notice( hex2bin( gpos_clean( $_GET[ "{$this->prefix}-error" ] ) ), 'error' );    //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	/**
	 * Sipariş sonrası ekranında müşteriden, WooCommerce sipariş detay sayfasında
	 * yöneticiden sipariş ürünlerinin  meta bilgilerini gizler.
	 *
	 * @param  array $hidden_metas Gizli metalar.
	 * @return array $hidden_metas
	 */
	public function hidden_order_itemmeta( $hidden_metas ) {
		$hidden_metas = array_merge(
			$hidden_metas,
			array( '_gpos_transaction_id' )
		);
		return $hidden_metas;
	}

	/**
	 * Sipariş aksiyonları.
	 *
	 * @param WC_Order $order WC siparişi.
	 */
	public function admin_order_actions( $order ) {
		$url = add_query_arg(
			array(
				'post_type' => 'gpos_transaction',
				's'         => $order->get_id(),
			),
			admin_url( 'edit.php' ),
		);
		gpos_get_view( 'wc-admin-order-actions.php', array( 'url' => $url ) );
	}


	/**
	 * Rest manipülasyonu
	 *
	 * @param WP_REST_Response $response rest cevabı.
	 * @param WC_Order         $order WC siparişi.
	 */
	public function rest_api_object( $response, $order ) {
		$order_data   = $response->get_data();
		$transactions = array_map(
			fn ( $transaction ) => $transaction->to_array(),
			gpos_transactions()->get_transactions(
				array(
					'post_status' => GPOS_Transaction_Utils::COMPLETED,
					'meta_key'    => 'plugin_transaction_id',           // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'  => $order->get_id(),                  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			)
		);
		$transaction  = empty( $transactions ) ? array() : $transactions[0];
		unset( $transaction['lines_array'] );
		unset( $transaction['lines'] );
		unset( $transaction['notes'] );
		unset( $transaction['logs'] );
		$order_data['gpos_success_transaction'] = $transaction;
		$response->set_data( $order_data );
		return $response;
	}

	/**
	 * WooCommerce blocks için GurmePOS u kayıt eder
	 *
	 * @return void
	 */
	public function woocommerce_block_support() {
		require_once GPOS_PLUGIN_DIR_PATH . '/includes/plugin-gateways/class-gpos-woocommerce-blocks-payment-gateway.php';

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			// @phpstan-ignore-next-line
			function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				// @phpstan-ignore-next-line
				$payment_method_registry->register( new GPOS_WooCommerce_Blocks_Payment_Gateway() );
			}
		);
	}

	/**
	 * Ürün tabları arasına taksit tabını ekler.
	 *
	 * @param array $tabs Ürün tabları.
	 *
	 * @return array $tabs Ürün tabları.
	 */
	public function product_tabs( $tabs ) {
		global $product;

		if ( ! $product || ! $this->settings->get_setting_by_key( 'installment_tab_active' ) ) {
			return $tabs;
		}

		$diff_installment = array_intersect(
			$product->get_category_ids(),
			array_map(
				fn ( $ins_cat ) => (int) $ins_cat,
				array_keys(
					array_filter( (array) $this->settings->get_setting_by_key( 'installment_rules' ), fn ( $ins ) => (int) 1 === $ins )
				)
			)
		);

		if ( ! empty( $diff_installment ) ) {
			return $tabs;
		}

		$tabs[ "{$this->prefix}-installment" ] = array(
			'title'    => $this->settings->get_setting_by_key( 'installment_tab_title' ),
			'priority' => $this->settings->get_setting_by_key( 'installment_tab_priority' ),
			'callback' => function () {
				global $product;
				if ( $product ) {
					echo do_shortcode( sprintf( '[gpos_installment_table price="%s"]', wc_get_price_including_tax( $product ) ) );
				}
			},
		);

		return $tabs;
	}
}
