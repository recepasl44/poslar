<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Ödeme geçitleri abstract sınıfını barındırır.
 *
 * @package GurmeHub
 */

use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * GPOS_Payment_Gateway sınıfı.
 *
 * @method GPOS_Gateway_Response delete_saved_card( GPOSPRO_Saved_Card $saved_card )
 * @method GPOS_Gateway_Response save_card( GPOSPRO_Saved_Card $saved_card )
 * @method mixed check_notify( $post_data )
 */
abstract class GPOS_Payment_Gateway {

	/**
	 * Ödeme tipi
	 *
	 * @var string $payment_type
	 */
	protected $payment_type = 'threed';

	/**
	 * Ödeme geçidi geri dönüş urli.
	 *
	 * @var string $callback_url
	 */
	protected $callback_url;

	/**
	 * Ödeme geçidi bildirim adresi urli.
	 *
	 * @var string $notify_url
	 */
	protected $notify_url;

	/**
	 * Http istekleri
	 *
	 * @var GPOS_Http_Request $http_request
	 */
	protected $http_request;

	/**
	 * Http cevapları
	 *
	 * @var GPOS_Gateway_Response $gateway_response
	 */
	public $gateway_response;

	/**
	 * Ödeme işlemi
	 *
	 * @var GPOS_Transaction $transaction
	 */
	public $transaction;

	/**
	 * XML kütüphanesi
	 *
	 * @var XmlEncoder $xml_encoder
	 */
	public $xml_encoder;

	/**
	 * GPOS_Payment_Gateway kurucu fonksiyonu
	 *
	 * @return void
	 */
	public function __construct() {
		$this->http_request     = gpos_http_request();
		$this->gateway_response = new GPOS_Gateway_Response( get_class( $this ) );
		$this->xml_encoder      = new XmlEncoder();
	}

	/**
	 * Ödeme işlemi başlamadan önce hazırlık aşaması
	 */
	public function before_process_payment() {
		do_action( 'gpos_before_process_payment', $this );
		return $this;
	}

	/**
	 * Ödeme işlemi verileri için sınıf
	 *
	 * @param GPOS_Transaction $transaction Ödeme işlemi verileri.
	 */
	public function set_transaction( GPOS_Transaction $transaction ) {
		$transaction->set_searchable();
		$this->transaction = $transaction;
		$this->set_callback_url( apply_filters( 'gpos_callback_url', home_url( "/gpos-callback/{$this->transaction->id}/" ), $this->transaction ) );
		$this->set_notify_url( apply_filters( 'gpos_notify_url', home_url( "/gpos-notify/{$this->transaction->id}/" ), $this->transaction ) );
		return $this;
	}

	/**
	 * Ödeme geçidi geri dönüş urli.
	 *
	 * @param string $callback_url Sipariş kimliği.
	 *
	 * @return $this
	 */
	public function set_callback_url( $callback_url ) {
		$this->callback_url = $callback_url;
		return $this;
	}

	/**
	 * Ödeme geçidi geri dönüş urlini döndürür
	 *
	 * @return string
	 */
	public function get_callback_url() {
		return $this->callback_url;
	}

	/**
	 * Ödeme geçidi geri dönüş urli urli.
	 *
	 * @param string $notify_url Sipariş kimliği.
	 *
	 * @return $this
	 */
	public function set_notify_url( $notify_url ) {
		$this->notify_url = $notify_url;
		return $this;
	}

	/**
	 * Sipariş kimliğini döndürür
	 *
	 * @return string
	 */
	public function get_notify_url() {
		return $this->notify_url;
	}

	/**
	 * Ödeme geçidi ayarlarını setler.
	 *
	 * @param string $process İşlem tipi.
	 * @param mixed  $request Gönderilen istek.
	 * @param mixed  $response Gönderilen isteğe istinaden alınan cevap.
	 *
	 * @return void
	 */
	public function log( $process, $request, $response ) {
		$this->transaction->add_log( $process, $request, $response );
	}

	/**
	 * Ödeme işlemi durum sorgulama.
	 *
	 * @param int|string $payment_id Sorgulanabilir ödeme numarası.
	 *
	 * @return GPOS_Gateway_Response
	 */
	abstract public function check_status( $payment_id );

	/**
	 * Ödeme geçidi ayarlarını setler.
	 *
	 * @param GPOS_Gateway_Settings $settings Ödeme geçidi spesifik ayarları.
	 *
	 * @return void
	 */
	abstract public function prepare_settings( GPOS_Gateway_Settings $settings );

	/**
	 * Ödeme işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	abstract public function process_payment();

	/**
	 * 3D Ödeme işlemleri için geri dönüş fonksiyonu.
	 *
	 * @param array $post_data Geri dönüş verileri.
	 *
	 * @return GPOS_Gateway_Response
	 */
	abstract public function process_callback( array $post_data );

	/**
	 * Ödeme iptal işlemi fonksiyonu.
	 *
	 * @return GPOS_Gateway_Response
	 */
	abstract public function process_cancel();

	/**
	 * Ödeme iade işlemi fonksiyonu.
	 *
	 * @param int|string $payment_id İade işlemi yapılacak olan ödeme numarası.
	 * @param int|float  $total İade tutarı.
	 *
	 * @return GPOS_Gateway_Response
	 */
	abstract public function process_refund( $payment_id, $total );

	/**
	 * Ödeme geçidi bağlantı kontrolü.
	 *
	 * @param stdClass $connection_data Ödeme geçidi ayarları.
	 */
	abstract public function check_connection( $connection_data );

	/**
	 * Apilerinde taksit bilgisi gönderen kuruluşlar için otomatik getirir.
	 *
	 * @return array|bool Destek var ise taksitler yok ise false.
	 */
	abstract public function get_installments();
}
