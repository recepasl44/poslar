<?php
/**
 * GPOS_IyziPOS sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GPOS_IyziPOS sınıfı.
 */
class GPOS_IyziPOS {

	/**
	 * Tablo
	 *
	 * @var string $table_name Tablo adı.
	 */
	protected $table_name;

	/**
	 * Kurucu method.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'iyziposplus_user_key';
	}

	/**
	 * iyzipos deprected plugin tespiti.
	 */
	public function check() {

		$active           = class_exists( 'iyziPosPlus' );
		$saved_card_count = 0;

		if ( $active ) {
			global $wpdb;
			$saved_card_count = $wpdb->get_var( "SELECT count(id) FROM {$this->table_name}" ); // phpcs:ignore
		}

		return [
			'active'           => $active,
			'saved_card_count' => $saved_card_count,
		];
	}

	/**
	 * Liste
	 */
	public function card_list() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table_name}" ); // phpcs:ignore
	}

	/**
	 * Ödeme geçidi güncelleme.
	 */
	public function update_gateway() {

		global $wpdb;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_orders'" ) === "{$wpdb->prefix}wc_orders" ) {
			$wpdb->query( "UPDATE {$wpdb->prefix}wc_orders SET payment_method = 'gpos' WHERE payment_method = 'iyziposplus" ); // phpcs:ignore
			$wpdb->query( "DELETE FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = '_iyzico_request'" ); // phpcs:ignore
		}

		$wpdb->query( "UPDATE {$wpdb->postmeta} SET meta_value = 'gpos' WHERE meta_key = '_payment_method' AND meta_value = 'iyziposplus'" ); // phpcs:ignore

		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_iyzico_request'" ); // phpcs:ignore
	}

	/**
	 * Kartı kaydet
	 *
	 * @param stdClass $data Kart bilgileri.
	 */
	public function save_card( $data ) {

		if ( function_exists( 'gpospro_saved_card' ) ) {
			$saved_card = gpospro_saved_card()
			->set_card_expiry_month( '00' )
			->set_card_expiry_year( '00' )
			->set_card_holder_name( $data->card->customer_name )
			->set_card_family( 'VISA' === $data->card->card_family ? 'visa' : 'mastercard' )
			->set_masked_card_bin( '**** **** **** ' . $data->card->last_number, true )
			->set_user_id( $data->card->customer_id )
			->set_payment_gateway_id( 'iyzico' )
			->set_account_id( $data->account_id )
			->set_searchable();

			if ( '1' === $data->card->status ) {
				$saved_card->set_default();
			}

			$saved_card->add_meta( 'cardUserKey', $data->card->card_key );
			$saved_card->add_meta( 'cardToken', $data->card->card_token );

			do_action( 'gpos_saved_card_created', $saved_card );

			return $saved_card;
		}
	}
}
