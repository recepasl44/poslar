<?php
/**
 * GurmePOS kısa kodlar sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS kısa kodlar sınıfı
 */
class GPOS_Shortcode {

	/**
	 * Eklenti Prefix
	 *
	 * @var string $prefix
	 */
	protected $prefix = GPOS_PREFIX;

	/**
	 * Kullanıcıların kayıtlı kartlarının listelendiği kısa kod
	 */
	public function user_saved_cards() {
		if ( gpos_is_pro_active() && get_current_user_id() ) {
			ob_start();
			gpos_vue()
			->set_vue_page( 'user-saved-cards' )
			->set_localize( $this->get_localize_data( 'user-saved-cards' ) )
			->require_style()
			->require_script()
			->create_app_div();
			return ob_get_clean();
		}
	}

	/**
	 * Kullanıcıların kayıtlı kartlarının listelendiği kısa kod
	 *
	 * @param array $args Kısa kod argümanları
	 */
	public function user_transactions( $args = array() ) {
		if ( gpos_is_form_active() && get_current_user_id() ) {
			ob_start();
			gpos_vue()
			->set_vue_page( 'user-transactions' )
			->set_localize( $this->get_localize_data( 'user-transactions', $args ) )
			->require_style()
			->require_script()
			->create_app_div();
			return ob_get_clean();
		} else {
			?>
				<h4><?php esc_html_e( 'Please login to view your transactions.', 'gurmepos' ); ?> </h4>
			<?php
		}
	}

	/**
	 * Taksit tablosunu bağımsız şekilde gösterim
	 *
	 * @param array $args Kısa kod argümanları
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function installment_table( $args ) {
		if ( ! gpos_gateway_accounts()->get_default_account()->is_installments_active ) {
			return esc_html__( 'Installment information could not be found. You can define your installment information from payment institutions or disable this feature.', 'gurmepos' );
		}

		if ( gpos_is_woocommerce_enabled() && is_cart() ) {
			$price    = WC()->cart->get_total( 'float' );
			$currency = get_woocommerce_currency_symbol();
		} elseif ( gpos_is_woocommerce_enabled() && is_product() ) {

			global $product;
			$currency = get_woocommerce_currency_symbol();
			$price    = 0.00;

			if ( $product ) {
				if ( 'grouped' === $product->get_type() ) {
					$children_product = $product->get_children();
					foreach ( $children_product as $child_id ) {
						$child_product = wc_get_product( $child_id );
						$price        += (float) $child_product->get_price();
					}
				} else {
					$price = $product->get_price();
				}

				$price = wc_get_price_to_display( $product, array( 'price' => $price ) );
			}
		} else {
			$price    = is_array( $args ) && isset( $args['price'] ) ? $args['price'] : 0;
			$currency = is_array( $args ) && isset( $args['currency'] ) ? $args['currency'] : 'TRY';
		}

		ob_start();
		gpos_installment_display( $price, $currency )->render_template();
		return ob_get_clean();
	}


	/**
	 * Vue render edildiğinde kullanacağı verileri düzenler.
	 *
	 * @param string $vue_page Kısa kod için çalıştırılacak Vue sayfası
	 * @param array  $args Kısa kod argümanları
	 * @return array
	 */
	private function get_localize_data( $vue_page, $args = array() ) {

		return apply_filters(
			"gpos_vue_{$vue_page}_localize_data",
			array(
				'prefix'        => GPOS_PREFIX,
				'version'       => GPOS_VERSION,
				'asset_dir_url' => GPOS_ASSETS_DIR_URL,
				'home_url'      => home_url(),
				'nonce'         => wp_create_nonce( GPOS_AJAX_ACTION ),
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'user_id'       => get_current_user_id(),
				'strings'       => gpos_get_i18n_texts(),
				'is_pro_active' => gpos_is_pro_active(),
				'is_test_mode'  => gpos_is_test_mode(),
				'alert_texts'   => gpos_get_alert_texts(),
			),
			$args
		);
	}
}
