<?php
/**
 * GurmePOS için 3D öncesi bilgileri tutmak için kullanılacak sınıf olan GPOS_Session sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS oturum sınıfı
 */
class GPOS_Session extends GPOS_Model {

	/**
	 * Verilerinin tutulduğu tablo.
	 *
	 * @var string
	 */
	public $table_name = 'gpos_session';

	/**
	 * Kayıt edilmiş oturum verisini döndürür.
	 *
	 * @param string $session_key Kayıt edilecek anahtar kelime.
	 *
	 * @return mixed
	 */
	public function get_session_meta( string $session_key ) {
		$session_value = $this->connection->get_var(
			$this->connection->prepare( "SELECT `session_value` FROM {$this->get_table_name()} WHERE `transaction_id` = %s AND `session_key` = %s", $this->transaction_id, $session_key )
		);

		$decoded_value = json_decode( $session_value, true );

		if ( json_last_error() === JSON_ERROR_NONE ) {
			return $decoded_value;
		}

		return $session_value;
	}

	/**
	 * Oturum verisi kayıt etme.
	 *
	 * @param string $session_key Kayıt edilecek anahtar kelime.
	 * @param mixed  $session_value Kayıt edilecek anahtar veri.
	 *
	 * @return int|false — Eklenen|güncellenen metanın idsi, yada hata durumunda false.
	 */
	public function add_session_meta( string $session_key, $session_value ) {
		wp_schedule_single_event( strtotime( '+3 days', time() ), 'gpos_delete_session_meta', array( $this->transaction_id, $session_key ) );
		$id                 = false;
		$session_key_exists = $this->get_session_meta( $session_key );
		$session_value      = is_array( $session_value ) ? wp_json_encode( $session_value ) : $session_value;

		if ( $session_key_exists ) {
			$id = $this->connection->update(
				$this->get_table_name(),
				array(
					'session_value' => $session_value,
				),
				array(
					'transaction_id' => $this->transaction_id,
					'session_key'    => $session_key,
				)
			);
		} else {
			$id = $this->connection->insert(
				$this->get_table_name(),
				array(
					'transaction_id' => $this->transaction_id,
					'session_key'    => $session_key,
					'session_value'  => $session_value,
				)
			);
		}

		return $id;
	}

	/**
	 * Oturum verisi silme.
	 *
	 * @param string $transaction_id İşlem numarası.
	 * @param string $session_key Silinecek edilecek anahtar kelime.
	 */
	public function delete_session_meta( $transaction_id, string $session_key ) {
		$this->connection->delete(
			$this->get_table_name(),
			array(
				'transaction_id' => $transaction_id,
				'session_key'    => $session_key,
			)
		);
	}
}
