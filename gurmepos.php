<?php //phpcs:ignore
/**
 * Plugin Name: POS Entegratör
 * Plugin URI: https://posentegrator.com
 * Description: 50+ ödeme kuruluşu, 10+ eklenti ile beraber çalışalabilen en gelişmiş ödeme eklentisi. Tüm ödeme alma süreçlerinizi yönetir, kusursuz çalışmasını sağlar. E-Ticaret Ödemeleri, Tek Sayfada Ödeme, Tekrarlı Ödemeler, Taksitli Ödemeler,Bağış Ödemeleri, Özel Tutarlı Ödemeler ve daha fazlası <strong>POS Entegratör</strong> ile çok kolay.
 * Version: 3.7.79
 * Author: GurmeHub
 * Author URI: https://gurmehub.com
 * Text Domain: gurmepos
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 7.6
 * WC tested up to: 9.1
 * Tested up to: 6.8
 *
 * @package GurmeHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * GurmePOS eklenti anasınıfı..
 *
 * @package GurmeHub
 */
final class GurmePOS {



	/**
	 * Eklenti öneki.
	 *
	 * @var string
	 */
	public $prefix = 'gpos';

	/**
	 * Eklenti versiyonu.
	 *
	 * @var string
	 */
	public $version = '3.7.79';

	/**
	 * Veritabanı versiyonu.
	 *
	 * @var string
	 */
	public $db_version = '1.0.2';

	/**
	 * Sınıfın bir örneği.
	 *
	 * @var GurmePOS|null
	 */
	protected static $instance;

	/**
	 * Sınıf örneklerini taşır.
	 *
	 * @var array
	 */
	public $container = [];

	/**
	 * GurmePOS sınıfının bir örneğini türetir.
	 *
	 * @return GurmePOS
	 */
	public static function get() {
		if ( is_null( self::$instance ) || ! ( self::$instance instanceof GurmePOS ) ) {
			self::$instance = new GurmePOS();
			self::$instance->setup();
		}
		return self::$instance;
	}

	/**
	 * Kurulum methodu.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function setup() {
		$this->define_constants();

		$this->includes();

		$this->instantiate();
	}

	/**
	 * Eklenti sabitleri tanımlama.
	 *
	 * @return void
	 */
	private function define_constants() {
		define( 'GPOS_PREFIX', $this->prefix );
		define( 'GPOS_AJAX_ACTION', 'gpos_action' );
		define( 'GPOS_VERSION', $this->version );
		define( 'GPOS_DB_VERSION', $this->db_version );
		define( 'GPOS_PRODUCTION', true );
		define( 'GPOS_PLUGIN_BASEFILE', __FILE__ );
		define( 'GPOS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'GPOS_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'GPOS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
		define( 'GPOS_ASSETS_DIR_URL', plugin_dir_url( __FILE__ ) . 'assets' );
	}

	/**
	 * Eklenti dosyaları yükleme.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function includes() {
		$files = array(
			// Vendors
			'vendor/autoload.php',
			// Interfaces
			'includes/interfaces/interface-gpos-plugin-gateway.php',
			// Abstracts
			'includes/abstracts/abstract-gpos-post.php',
			'includes/abstracts/abstract-gpos-model.php',
			'includes/abstracts/abstract-gpos-options.php',
			'includes/abstracts/abstract-gpos-customer.php',
			'includes/abstracts/abstract-gpos-gateway-settings.php',
			'includes/abstracts/abstract-gpos-payment-gateway.php',
			'includes/abstracts/abstract-gpos-gateway.php',
			'includes/abstracts/payten-gateway/abstract-gpos-payten-gateway.php',
			'includes/abstracts/payten-gateway/abstract-gpos-payten-settings.php',

			// Traits
			'includes/traits/trait-gpos-plugin-payment-gateway.php',
			'includes/traits/trait-gpos-credit-card.php',
			// Settings
			'includes/settings/class-gpos-settings.php',
			'includes/settings/class-gpos-other-settings.php',
			'includes/settings/class-gpos-woocommerce-settings.php',
			'includes/settings/class-gpos-form-settings.php',
			'includes/settings/class-gpos-tag-manager-settings.php',
			'includes/settings/class-gpos-notification-settings.php',
			'includes/settings/class-gpos-ins-display-settings.php',
			// Paratika
			'includes/payment-gateways/paratika/class-gpos-paratika-settings.php',
			'includes/payment-gateways/paratika/class-gpos-paratika-gateway.php',
			'includes/payment-gateways/paratika/class-gpos-paratika.php',
			// iyzico
			'includes/payment-gateways/iyzico/class-gpos-iyzico-settings.php',
			'includes/payment-gateways/iyzico/class-gpos-iyzico-gateway.php',
			'includes/payment-gateways/iyzico/class-gpos-iyzico.php',
			// iyzico iFrame
			'includes/payment-gateways/iyzico-iframe/class-gpos-iyzico-iframe-settings.php',
			'includes/payment-gateways/iyzico-iframe/class-gpos-iyzico-iframe-gateway.php',
			'includes/payment-gateways/iyzico-iframe/class-gpos-iyzico-iframe.php',
			// Pay With iyzico.
			'includes/payment-gateways/pay-with-iyzico/class-gpos-pay-with-iyzico-settings.php',
			'includes/payment-gateways/pay-with-iyzico/class-gpos-pay-with-iyzico-gateway.php',
			'includes/payment-gateways/pay-with-iyzico/class-gpos-pay-with-iyzico.php',
			// PayTR iFrame
			'includes/payment-gateways/paytr-iframe/class-gpos-paytr-iframe-settings.php',
			'includes/payment-gateways/paytr-iframe/class-gpos-paytr-iframe-gateway.php',
			'includes/payment-gateways/paytr-iframe/class-gpos-paytr-iframe.php',
			// Papara Checkout
			'includes/payment-gateways/papara-checkout/class-gpos-papara-checkout-settings.php',
			'includes/payment-gateways/papara-checkout/class-gpos-papara-checkout-gateway.php',
			'includes/payment-gateways/papara-checkout/class-gpos-papara-checkout.php',
			// Dummy Payment
			'includes/payment-gateways/dummy-payment/class-gpos-dummy-payment-settings.php',
			'includes/payment-gateways/dummy-payment/class-gpos-dummy-payment-gateway.php',
			'includes/payment-gateways/dummy-payment/class-gpos-dummy-payment.php',
			// Papara
			'includes/payment-gateways/papara/class-gpos-papara-settings.php',
			'includes/payment-gateways/papara/class-gpos-papara-gateway.php',
			'includes/payment-gateways/papara/class-gpos-papara.php',
			// Pro ile gelecekler
			'includes/payment-gateways/pro/class-gpos-albaraka.php',
			'includes/payment-gateways/pro/class-gpos-ingbank.php',
			'includes/payment-gateways/pro/class-gpos-ozan.php',
			'includes/payment-gateways/pro/class-gpos-sekerbank.php',
			'includes/payment-gateways/pro/class-gpos-sipay.php',
			'includes/payment-gateways/pro/class-gpos-param.php',
			'includes/payment-gateways/pro/class-gpos-paytr.php',
			'includes/payment-gateways/pro/class-gpos-esnekpos.php',
			'includes/payment-gateways/pro/class-gpos-craftgate.php',
			'includes/payment-gateways/pro/class-gpos-akbank.php',
			'includes/payment-gateways/pro/class-gpos-akbank-json.php',
			'includes/payment-gateways/pro/class-gpos-denizbank.php',
			'includes/payment-gateways/pro/class-gpos-finansbank.php',
			'includes/payment-gateways/pro/class-gpos-finansbank-payfor.php',
			'includes/payment-gateways/pro/class-gpos-finansbank-payfor-v2.php',
			'includes/payment-gateways/pro/class-gpos-garanti.php',
			'includes/payment-gateways/pro/class-gpos-halkbank.php',
			'includes/payment-gateways/pro/class-gpos-halkbank-mkd.php',
			'includes/payment-gateways/pro/class-gpos-is-bankasi.php',
			'includes/payment-gateways/pro/class-gpos-is-bankasi-girogate.php',
			'includes/payment-gateways/pro/class-gpos-kuveyt-turk.php',
			'includes/payment-gateways/pro/class-gpos-teb.php',
			'includes/payment-gateways/pro/class-gpos-vakifbank.php',
			'includes/payment-gateways/pro/class-gpos-wyld.php',
			'includes/payment-gateways/pro/class-gpos-yapi-kredi.php',
			'includes/payment-gateways/pro/class-gpos-ziraat.php',
			'includes/payment-gateways/pro/class-gpos-paidora.php',
			'includes/payment-gateways/pro/class-gpos-lidio.php',
			'includes/payment-gateways/pro/class-gpos-akode.php',
			'includes/payment-gateways/pro/class-gpos-paybull.php',
			'includes/payment-gateways/pro/class-gpos-united-payment.php',
			'includes/payment-gateways/pro/class-gpos-qnbpay.php',
			'includes/payment-gateways/pro/class-gpos-paynkolay.php',
			'includes/payment-gateways/pro/class-gpos-garanti-pay.php',
			'includes/payment-gateways/pro/class-gpos-weepay.php',
			'includes/payment-gateways/pro/class-gpos-shopier.php',
			'includes/payment-gateways/pro/class-gpos-worldpay.php',
			'includes/payment-gateways/pro/class-gpos-vepara.php',
			'includes/payment-gateways/pro/class-gpos-ziraat-katilim.php',
			'includes/payment-gateways/pro/class-gpos-ziraatpay.php',
			'includes/payment-gateways/pro/class-gpos-mollie.php',
			'includes/payment-gateways/pro/class-gpos-paycell.php',
			'includes/payment-gateways/pro/class-gpos-isyerimpos.php',
			'includes/payment-gateways/pro/class-gpos-rubikpara.php',
			'includes/payment-gateways/pro/class-gpos-erpapay.php',
			'includes/payment-gateways/pro/class-gpos-setcard.php',
			'includes/payment-gateways/pro/class-gpos-vallet.php',
			'includes/payment-gateways/pro/class-gpos-moka.php',
			'includes/payment-gateways/pro/class-gpos-hepsipay.php',
			'includes/payment-gateways/pro/class-gpos-vakif-katilim.php',
			'includes/payment-gateways/pro/class-gpos-tami.php',
			// Functions
			'includes/gpos-class-functions.php',
			'includes/gpos-functions.php',
			// Inc classes
			'includes/class-gpos-admin.php',
			'includes/class-gpos-tracker.php',
			'includes/class-gpos-redirect.php',
			'includes/class-gpos-installer.php',
			'includes/class-gpos-installments.php',
			'includes/class-gpos-http-request.php',
			'includes/class-gpos-gateway-response.php',
			'includes/class-gpos-payment-gateways.php',
			'includes/class-gpos-post-operations.php',
			'includes/class-gpos-gateway-accounts.php',
			'includes/class-gpos-gateway-account.php',
			'includes/class-gpos-transaction-line.php',
			'includes/class-gpos-transaction-utils.php',
			'includes/class-gpos-transactions.php',
			'includes/class-gpos-transaction.php',
			'includes/class-gpos-transaction-log.php',
			'includes/class-gpos-transaction-action-log.php',
			'includes/class-gpos-vue.php',
			'includes/class-gpos-frontend.php',
			'includes/class-gpos-session.php',
			'includes/class-gpos-refund.php',
			'includes/class-gpos-post-tables.php',
			'includes/class-gpos-shortcode.php',
			'includes/class-gpos-forge.php',
			'includes/class-gpos-meta-boxes.php',
			'includes/class-gpos-module-manager.php',
			'includes/class-gpos-dashboard.php',
			'includes/class-gpos-notifications.php',
			'includes/class-gpos-transaction-status-checker.php',
			'includes/class-gpos-export.php',
			'includes/class-gpos-schedule.php',
			'includes/class-gpos-installment-display.php',
			'includes/class-gpos-status-check.php',
			'includes/class-gpos-garbage-collector.php',
			'includes/class-gpos-iyzipos.php',

			// Hooks
			'hooks/class-gpos-woocommerce.php',
			'hooks/class-gpos-gph.php',
			'hooks/class-gpos-self-hooks.php',
			'hooks/class-gpos-ajax.php',
			'hooks/class-gpos-wordpress.php',
		);

		foreach ( $files as $file ) {
			require_once $file;
		}
	}


	/**
	 * Sınıf türetme.
	 */
	public function instantiate() {
		$this->container = array(
			'GPOS_Self_Hooks' => new GPOS_Self_Hooks(),
			'GPOS_WordPress'  => new GPOS_WordPress(),
			'GPOS_Ajax'       => new GPOS_Ajax(),
			'GPOS_Gph'        => new GPOS_Gph(),
		);

		if ( gpos_is_givewp_v3_enabled() ) {
			require_once GPOS_PLUGIN_DIR_PATH . 'includes/plugin-gateways/class-gpos-givewp-v3-payment-gateway.php';
			require_once GPOS_PLUGIN_DIR_PATH . 'hooks/class-gpos-givewp-v3.php';
			$this->container['GPOS_GiveWP_V3'] = new GPOS_GiveWP_V3(); // @phpstan-ignore-line
		}

		register_activation_hook( GPOS_PLUGIN_BASEFILE, array( new GPOS_Installer(), 'install' ) );

		$gurmehub_client = new \GurmeHub\Client( GPOS_PLUGIN_BASEFILE );
		$gurmehub_client->insights();
	}

	/**
	 * Anasınıfı türetir ve eklentinin çalışmasını başlatır.
	 *
	 * @return GurmePOS
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public static function init() {
		return self::get();
	}
}

// Hadi başlayalım.
GurmePOS::get();
