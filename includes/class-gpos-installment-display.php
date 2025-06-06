<?php
/**
 * GPOS_Installment_Display sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 *  Taksit tabı özelliği açıldıysa sınıf çalıştırılır
 */
class GPOS_Installment_Display {

	/**
	 * Js id
	 *
	 * @var string
	 */
	public $id = 'gpos_installment';

	/**
	 * Gösterim ayarları
	 *
	 * @var GPOS_Ins_Display_Settings $settings
	 */
	private $settings;

	/**
	 * Gösterim ayarları
	 *
	 * @var int|float|string $price
	 */
	private $price;

	/**
	 * Para birimi
	 *
	 * @var string $currency
	 */
	private $currency;

	/**
	 * Taksit tabloları kurucu method
	 *
	 * @param int|float|string $price Tutar
	 * @param string           $currency Para birimi
	 */
	public function __construct( $price, $currency ) {
		$this->settings = gpos_ins_display_settings();
		$this->price    = $price;
		$this->currency = $currency;
		add_filter( 'gpos_js_to_module_handlers', array( $this, 'add_js_to_module_handlers' ) );
	}

	/**
	 * Js handler ekleme
	 *
	 * @param array $handlers js idler dizisi
	 */
	public function add_js_to_module_handlers( $handlers ) {
		$handlers[] = $this->id;
		return $handlers;
	}

	/**
	 * Tablo render methodu.
	 */
	public function render_template() {
		$desc     = $this->settings->get_setting_by_key( 'installment_tab_description' );
		$localize = array(
			'price'    => $this->price,
			'currency' => $this->currency,
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( GPOS_AJAX_ACTION ),
		);
		wp_enqueue_script( 'jquery-block' );
		gpos_vue()->set_id( $this->id )->set_localize( $localize, $this->id )->set_vue_page( 'installment' )->require_script()->require_style( 'installment' );
		echo wp_kses_post( ! empty( $desc ) ? sprintf( '<p class="gpos-installment-desc">%s</p>', $desc ) : '' );
		echo wp_kses_post( sprintf( '<div id="gpos-installment-div">%s</div>', $this->render_style() ) );
	}

	/**
	 * Verilen tutara göre taksit tablo ayarlarına göre HTML verisini döndürür
	 *
	 * @return string
	 */
	private function render_style() {
		$rates = $this->prepare_installments();
		$style = $this->settings->get_setting_by_key( 'style' );
		$args  = array(
			'price'    => $this->price,
			'rates'    => $rates,
			'assetUrl' => GPOS_ASSETS_DIR_URL,
		);
		ob_start();
		do_action( 'gpos_before_installment_tab', $args );
		gpos_get_view( "installments/{$style}.php", $args, GPOS_PLUGIN_DIR_PATH );
		do_action( 'gpos_before_installment_tab', $args );
		return ob_get_clean();
	}


	/**
	 * Aktif taksit miktari ve kart ailerini oluşturur
	 *
	 * @return array
	 */
	private function prepare_installments() {
		$installments = gpos_installments(
			GPOS_Transaction_Utils::WOOCOMMERCE,
			gpos_gateway_accounts()->get_default_account(),
			array(
				'amount'   => $this->price,
				'currency' => $this->currency,
			)
		)->prepare_rates( false );
		$months       = array();
		foreach ( $installments as $rate ) {
			$months = array_unique( array_merge( $months, array_keys( $rate ) ) );
		}
		sort( $months );
		return array(
			'rates'  => array_filter( $installments, fn( $ins )=> ! empty( $ins ) ),
			'months' => $months,
		);
	}


	/**
	 * Değişimlere göre fiyata göre taksit html tablsonunu döndürür
	 *
	 * @return array
	 */
	public function get_installment_html() {
		return array(
			'status' => 'success',
			'html'   => $this->render_style(),
		);
	}
}
