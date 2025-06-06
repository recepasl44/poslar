<?php
/**
 * GurmePOS çalışma gereksinimleri için hata sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS durum alanında ki hataları listeler, kontrol eder ve ayarlar.
 */
class GPOS_Status_Check {

	/**
	 * Anahtar; wp_options tablosunda ayarın tutulacağı option_name.
	 *
	 * @var string $options_table_key
	 */
	public $options_table_key = 'gpos_total_error_count';

	/**
	 * Callback cagrisinin cevabını tutan options degeri
	 *
	 * @var string $callback_option_key
	 */
	public $callback_option_key = 'gpos_callback_test_status';

	/**
	 * GurmePOS için çalışma gereksinimlerini barındırır.
	 *
	 * @return array
	 */
	public function get_status_error() {

		$time_zone            = (string) get_option( 'gmt_offset' );
		$permalink_type       = (string) get_option( 'permalink_structure' );
		$callback_test_status = get_option(
			$this->callback_option_key,
			array(
				'data'   => 'OK',
				'status' => 200,
			)
		);
		$working_requirements = array(
			array(
				'label'         => __( 'Time Zone (Changed from Settings->General Timezone Field.)', 'gurmepos' ),
				'link'          => 'https://yardim.gurmehub.com/docs/pos-entegrator/neden-hata-aliyorum/akode-validasyon-timestamp-hatalari-hk/',
				'value'         => 'UTC+' . $time_zone,
				'type'          => 'text',
				'default_value' => 'UTC+3',
				'status'        => '3' === $time_zone ? true : false,
			),
			array(
				'label'         => __( 'Permalinks are changed in settings -> permalinks.', 'gurmepos' ),
				'value'         => $permalink_type,
				'type'          => 'text',
				'link'          => 'https://yardim.gurmehub.com/docs/pos-entegrator/kurulum/',
				'default_value' => __( 'Your permalink cannot be empty.', 'gurmepos' ),
				'status'        => $permalink_type ? true : false,
			),
			array(
				'label'         => __( 'Callback Check (OK will prevent the correct operation of external answer notification calls)', 'gurmepos' ),
				'value'         => $callback_test_status['data'],
				'type'          => 'textarea',
				'link'          => 'https://yardim.gurmehub.com/docs/pos-entegrator/geri-donus-kontrolu/',
				'default_value' => 'OK',
				'status'        => 200 === $callback_test_status['status'] && 'OK' === $callback_test_status['data'] ? true : false,
			),
		);

		return $working_requirements;
	}


	/**
	 * GurmePOS için çalışma gereksinimlerinde ki hata sayısını döndürür.
	 *
	 * @return array
	 */
	public function check_total_errors() {
		$total_error_count = 0;
		$errors_list       = $this->get_status_error();
		foreach ( $errors_list as $error ) {
			if ( false === $error['status'] ) {
				++$total_error_count;
			}
		}

		$this->set_total_error_count( $total_error_count );

		return $errors_list;
	}

	/**
	 * GurmePOS için çalışma gereksinimlerinde ki hata sayısını döndürür.
	 *
	 * @return string|int
	 */
	public function get_total_error_count() {
		return get_option( $this->options_table_key, 0 );
	}

	/**
	 * GurmePOS için çalışma gereksinimlerinde ki hata sayısını döndürür.
	 *
	 * @param string|int $count hata sayısı.
	 */
	public function set_total_error_count( $count ) {
		update_option( $this->options_table_key, $count );
	}

	/**
	 * Callback kontrolü yapar.
	 */
	public function check_callback_status() {
		try {
			$response = gpos_tracker()->get_callback_status(
				array(
					'urls' => [ home_url( 'gpos-test-callback' ) ],
				)
			);
			if ( $response['success'] && count( $response['results'] ) > 0 ) {
				update_option( 'gpos_callback_test_status', reset( $response['results'] ) );
			}
		} catch ( Exception $e ) {
			return;
		}
	}
}
