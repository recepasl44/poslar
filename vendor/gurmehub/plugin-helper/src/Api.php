<?php // phpcs:ignore
namespace GurmeHub;

/**
 * Uygulama sınıfı
 */
class Api {

	/**
	 * GurmeHub Tracker URL.
	 *
	 * @var string $url;
	 */
	protected $url = 'https://us-central1-gurmepos.cloudfunctions.net';


	/**
	 * İstek methodu
	 *
	 * @param array  $params İstek parametreleri
	 * @param string $route İstek atılacak uç nokta
	 * @param string $http_type İstek tipi
	 *
	 * @return WP_Error|stdClass|string|array
	 */
	public function request( $params, $route, $http_type = 'POST' ) {
		$response = false;

		$headers = array(
			'Accept' => 'application/json',
		);

		$response = wp_remote_request(
			"{$this->url}/{$route}",
			array(
				'method'      => $http_type,
				'timeout'     => 30,
				'httpversion' => '1.0',
				'body'        => $params,
				'headers'     => $headers,
			)
		);

		if ( is_wp_error( $response ) || ! array_key_exists( 'body', $response ) ) {
			return $response;
		}

		$response = json_decode( $response['body'] );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return $response;
		}

		return $response;
	}
}
