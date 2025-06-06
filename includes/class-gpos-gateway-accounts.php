<?php
/**
 * GurmePOS ödeme geçidi ekleme listeleme silme gibi işlemleri yapan sınıfı barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS Ödeme geçitleri için CRUD işlemlerini yapar.
 */
class GPOS_Gateway_Accounts {

	/**
	 * Hesapların kayıt edildiği post tipi.
	 *
	 * @var string $post_type
	 */
	private $post_type = 'gpos_account';


	/**
	 * Tüm kayıtlı hesapları döndürür.
	 *
	 * @param string $payment_method_type Ödeme hesabı tipi
	 *
	 * @return array GPOS_Accountlardan oluşan bir array döndürür.
	 */
	public function get_accounts( $payment_method_type = '' ) {
		$accounts = get_posts(
			array(
				'post_type'   => $this->post_type,
				'numberposts' => 100,
				'post_status' => array( 'publish', 'draft' ),
			)
		);

		$accounts = array_filter(
			array_map( array( $this, 'get_account' ), $accounts ),
			fn( $account ) => false !== $account
		);

		if ( '' !== $payment_method_type ) {
			$accounts = array_filter(
				$accounts,
				fn( $account ) => gpos_payment_gateways()->get_base_gateway_by_gateway_id( $account->gateway_id )->payment_method_type === $payment_method_type
			);
		}
		return array_values( $accounts );
	}

	/**
	 * Tüm kayıtlı hesapları döndürür.
	 *
	 * @param array $args argümanlar.
	 * @return array GPOS_Accountlardan oluşan bir array döndürür..
	 */
	public function get_account_list( $args = array() ) {
		$defaults = array(
			'post_type'   => $this->post_type,
			'numberposts' => 100,
			'post_status' => array( 'publish', 'draft' ),
		);

		$accounts = get_posts(
			wp_parse_args( $args, $defaults )
		);

		return array_map( array( $this, 'get_account' ), $accounts );
	}


	/**
	 * Yeni hesap ekleme.
	 *
	 * @param string $gateway_id Ödeme kuruluşunun anahtarı
	 *
	 * @return GPOS_Gateway_Account|WP_Error — Ekleme işlemi başarılı ise hesap, başarısız ise hata döndürür.
	 */
	public function add_account( string $gateway_id ) {
		$base_gateway = gpos_payment_gateways()->get_base_gateway_by_gateway_id( $gateway_id );

		$get_same_account = count(
			$this->get_account_list(
				array(
					'meta_query' => array( //phpcs:ignore
						array(
							'key'     => 'gpos_gateway_id',  // Meta alanının anahtarı
							'value'   => $gateway_id,   // Meta alanının değeri
							'compare' => '=',
						),
					),
				)
			)
		) + 1;
		$account          = array(
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_type'      => $this->post_type,
			'post_title'     => $get_same_account > 1 ? sprintf( '%s #%s', $base_gateway->title, $get_same_account ) : $base_gateway->title,
		);

		$account_id = wp_insert_post( $account, true );

		if ( is_wp_error( $account_id ) ) {
			return $account_id;
		}

		update_post_meta( $account_id, 'gpos_gateway_id', $gateway_id );
		$gpos_account = $this->get_account( $account_id );

		if ( GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_VIRTUAL_POS === $base_gateway->payment_method_type && ! $this->get_default_account() ) {
			$gpos_account->set_default();
		}
		return $gpos_account;
	}

	/**
	 * Id ile eşleşen hesabı siler.
	 *
	 * @param int $id Hesap idsi.
	 *
	 * @return WP_Post|false|null Silme işlemi başarılı ise silinen post verisi, başarısız ise olumsuz değer.
	 */
	public function delete_account( int $id ) {
		return wp_delete_post( $id, true );
	}

	/**
	 * Idsi verilen hesabı sınıf şekilde türeterek döndürür.
	 *
	 * @param int|WP_Post $account_id Hesap idsi yada post.
	 *
	 * @return GPOS_Gateway_Account|false
	 */
	public function get_account( $account_id ) {
		$account = new GPOS_Gateway_Account( $account_id );
		return $account->id && is_object( $account->gateway_class ) && is_object( $account->gateway_settings ) ? $account : false;
	}

	/**
	 * Varsayılan hesap getirme
	 *
	 * @return GPOS_Gateway_Account|false
	 */
	public function get_default_account() {
		return $this->get_account( (int) get_option( 'gpos_default_account', 0 ) );
	}
}
