<?php
/**
 * GurmePOS ile ödeme geçitlerine atılacak istekleri organize eder.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS istek sınıfı
 */
class GPOS_Http_Request {

	/**
	 * Http istek başlığı
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * Http versiyonu
	 *
	 * @var string
	 */
	private $http_version = '1.0';

	/**
	 * Http isteği başlığını ayarlar.
	 *
	 * @param array $headers Http isteği başlığı.
	 * @return $this
	 */
	public function set_headers( array $headers ) {
		$this->headers = $headers;
		return $this;
	}

	/**
	 * Http isteği başlığını döndürür.
	 *
	 * @return array
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Http isteği başlığını ayarlar.
	 *
	 * @param string $http_version Http isteği başlığı.
	 * @return $this
	 */
	public function set_http_version( string $http_version ) {
		$this->http_version = $http_version;
		return $this;
	}

	/**
	 * Http isteği başlığını döndürür.
	 *
	 * @return string
	 */
	public function get_http_version() {
		return $this->http_version;
	}

	/**
	 * İstek methodu
	 *
	 * @param string $url İstek yapılacak adres.
	 * @param string $method İstek tipi 'POST', 'GET', 'HEAD' ...
	 * @param mixed  $body İstekte gönderilecek parametreler.
	 *
	 * @throws Exception İstekte hata durumunda fırlatılır.
	 * @return array|string $response
	 */
	public function request( $url, $method = 'POST', $body = array() ) {
		$args = array(
			'method'      => $method,
			'timeout'     => 60,
			'httpversion' => $this->get_http_version(),
			'headers'     => $this->get_headers(),
			'body'        => $body,
		);

		$args['user-agent'] = gpos_is_test_mode() ? 'PostmanRuntime/7.36.1' : home_url( '/' );

		$http_response = wp_remote_request( $url, $args );

		if ( is_wp_error( $http_response ) ) {
			throw new Exception( esc_html( $http_response->get_error_message() ) );
		}

		if ( ! array_key_exists( 'body', $http_response ) ) {
			throw new Exception( 'İstekte hata cevap yorumlanamıyor . ' );
		}

		$response = json_decode( $http_response['body'], true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {

			return $http_response['body'];
		}

		return $response;
	}
}
