<?php
/**
 * WooCommerce Ödeme Geçici için PRO ile tüm istek gönderme ve cevap alma işlemlerini yapan sınıfı (GPOS_WooCommerce_Payment_Gateway) barındırır.
 *
 * @package Gurmehub
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

if ( class_exists( 'GPOS_WooCommerce_Payment_Gateway' ) ) {
	/**
	 * GPOSPRO_WooCommerce_Payment_Gateway sınıfı.
	 */
	class GPOS_WooCommerce_Blocks_Payment_Gateway extends AbstractPaymentMethodType {

		/**
		 * Gateway sınıfı
		 *
		 * @var GPOS_WooCommerce_Payment_Gateway
		 */
		public $gateway;

		/**
		 * Class prefixi
		 *
		 * @var string
		 */
		public $prefix = GPOS_PREFIX;

		/**
		 *  Methodun olusturan fonskyion
		 */
		public function initialize() {
			$this->settings = gpos_woocommerce_settings()->get_settings();
			$this->gateway  = new GPOS_WooCommerce_Payment_Gateway();
		}

		/**
		 *  Block'un ismi
		 */
		public function get_name() {
			return $this->prefix;
		}

		/**
		 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
		 *
		 * @return boolean
		 */
		public function is_active() {
			return $this->gateway->is_available();
		}

		/**
		 * Block tarafında kullanılacak verileri olusturur
		 *
		 * @return array
		 */
		public function get_payment_method_data() {
			return [
				'title'       => __( 'POS Entegratör Woo Blocks Desteği için PRO sürüme Yükseltin ', 'gurmepos' ),
				'description' => $this->gateway->description,
				'button_text' => $this->gateway->order_button_text,
				'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
			];
		}


		/**
		 * Block tarafında admin scriptleri dondürür
		 *
		 * @return array
		 */
		public function get_payment_method_script_handles_for_admin() {
			$this->register_block_scripts();
			return [ "{$this->prefix}-woocommerce-blocks" ];
		}

		/**
		 * * Blocks için gerekli olan scriptleri kayıt eder
		 */
		protected function register_block_scripts() {
			/**
			 * Asset gereklilikleri
			 *
			 * @var array $asset_depts
			 */
			$asset_depts = require GPOS_PLUGIN_DIR_PATH . 'assets/blocks/woocommerce/blocks.asset.php';

			wp_register_script(
				"{$this->prefix}-woocommerce-blocks",
				GPOS_PLUGIN_DIR_URL . 'assets/blocks/woocommerce/blocks.js',
				$asset_depts['dependencies'],
				$asset_depts['version'],
				true
			);
		}

		/**
		 * * Block tarafında ön yüz için scriptleri dondürür
		 *
		 * @return array
		 */
		public function get_payment_method_script_handles() {
			$this->register_block_scripts();
			return [ "{$this->prefix}-woocommerce-blocks", $this->prefix ];
		}
	}
}
