<?php
/**
 * GurmePOS ödeme geçidi işlem sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS işlem sınıfı.
 */
class GPOS_Transactions {

	/**
	 * İşlemlerin kayıt edildiği post tipi.
	 *
	 * @var string $post_type
	 */
	private $post_type = 'gpos_transaction';


	/**
	 * Tüm işlemleri döndürür.
	 *
	 * @param array $args Sorgu argümanları.
	 *
	 * @return GPOS_Transaction[]
	 */
	public function get_transactions( $args = array() ) {
		$wp_query = new WP_Query();
		$defaults = array(
			'post_type'   => $this->post_type,
			'numberposts' => 50,
		);
		return array_map( 'gpos_transaction', $wp_query->query( wp_parse_args( $args, $defaults ) ) );
	}

	/**
	 * EKlenti işlem numarasına ait gpos işlemlerini döndürür
	 *
	 * @param int|string $plugin_transaction_id İşlem ID'si.
	 * @param string     $type İşlem tipi. payment|refund
	 *
	 *  @return GPOS_Transaction[]
	 */
	public function get_by_plugin_transaction_id( $plugin_transaction_id, $type = 'payment' ) {
		return $this->get_transactions(
			array(
				'meta_query' => array( //phpcs:ignore
					array(
						'key'     => 'plugin_transaction_id',
						'value'   => $plugin_transaction_id,
						'compare' => '=',
					),
					array(
						'key'     => 'type',
						'value'   => $type,
						'compare' => '=',
					),
				),
			)
		);
	}
}
