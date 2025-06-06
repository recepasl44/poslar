<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * GurmePOS ödeme geçidi hesaplarının ayarlarını taşıyacak sınıflar için abstract sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS GPOS_Gateway_Settings abstract sınıfı
 *
 * @property array $test_girogate_methods
 * @property array $girogate_methods
 * @property string $merchant_threed_type
 * @property string $test_merchant_threed_type
 */
abstract class GPOS_Gateway_Settings {

	/**
	 * Ödeme geçidi hesabı idsi.
	 *
	 * @var int $id
	 */
	public $id;

	/**
	 * GPOS_Gateway_Settings kurucu fonksiyonu.
	 *
	 * @param int $id Ödeme geçidi hesabı idsi.
	 *
	 * @return void
	 */
	public function __construct( int $id ) {
		$this->id = $id;
		$this->get_settings();
	}

	/**
	 * Ayarları döndürür
	 *
	 * @return mixed
	 */
	public function get_settings() {
		array_walk(
			$this,
			function ( &$meta_value, $meta_key ) {
				if ( 'id' === $meta_key ) {
					$meta_value = $this->id;
					return;
				}

				$value      = get_post_meta( $this->id, $meta_key, true );
				$meta_value = is_array( $value ) ? $value : (string) $value;
			}
		);
		return $this;
	}

	/**
	 * Ayarları döndürür
	 *
	 * @param array $settings Gönderilen ayarlar dizisi.
	 *
	 * @return void
	 */
	public function save_settings( array $settings ) {
		array_walk(
			$settings,
			function ( $meta_value, $meta_key ) {
				$sanitized_value = is_string( $meta_value ) ? trim( $meta_value ) : $meta_value;
				update_post_meta( $this->id, $meta_key, $sanitized_value );
			}
		);
	}
}
