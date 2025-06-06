<?php
/**
 * GurmePOS meta boxları olşturan sınıf olan GPOS_Meta_Boxes sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS meta box sınıfı
 */
class GPOS_Meta_Boxes {

	/**
	 * Eklenti Prefix
	 *
	 * @var string $prefix
	 */
	protected $prefix = GPOS_PREFIX;

	/**
	 * Meta boxları kayıt etme.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		if ( gpos_is_woocommerce_enabled() && class_exists( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class ) && function_exists( 'wc_get_container' ) ) {
			$hpos_enabled = wc_get_container()->get( Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled();
			add_meta_box( "{$this->prefix}_shop_order_meta_box", 'POS Entegratör', array( $this, 'shop_order_meta_box' ), $hpos_enabled ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order', 'side', 'default' );
		}
	}

	/**
	 * GurmePOS shop order meta boxu render eder.
	 *
	 * @param Automattic\WooCommerce\Admin\Overrides\Order|WC_Order|WP_Post $post Post
	 */
	public function shop_order_meta_box( $post ) {

		if ( $post instanceof Automattic\WooCommerce\Admin\Overrides\Order || $post instanceof WC_Order ) {
			$post_id = $post->get_id();
		} else {
			$post_id = $post->ID;
		}

		$localize = array(
			'assets_url'   => GPOS_ASSETS_DIR_URL,
			'strings'      => gpos_get_i18n_texts(),
			'transactions' => array_map(
				fn( $transaction ) => $transaction->to_array(),
				gpos_transactions()->get_transactions(
					array(
						'meta_key'   => 'plugin_transaction_id',    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => $post_id,                  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					)
				)
			),
		);

		gpos_vue()
		->set_vue_page( 'wc-shop-order-meta-box' )
		->set_localize( $localize )
		->require_script()
		->require_style()
		->create_app_div();
	}
}
