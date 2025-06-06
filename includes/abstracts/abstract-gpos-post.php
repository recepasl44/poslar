<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * GurmePOS Post sınıfları için temel sınıfın dosyası.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS Post sınıfları için temel sınıf.
 */
abstract class GPOS_Post {
	/**
	 * Post ID'si.
	 *
	 * @var int|string $id
	 */
	public $id;

	/**
	 * Post tipi.
	 *
	 * @var string $post_type
	 */
	public $post_type;

	/**
	 * Post ilk durumu.
	 *
	 * @var string $post_type
	 */
	public $start_status;

	/**
	 * Oluşturulma tarihi
	 *
	 * @var string $date
	 */
	protected $date;

	/**
	 * Tarih farkı.
	 *
	 * @var string $human_date_diff
	 */
	protected $human_date_diff;

	/**
	 * Post meta verileri.
	 *
	 * @var array $meta_data
	 */
	public $meta_data = array();

	/**
	 * Kurucu method.
	 *
	 * @param null|string|int|WP_Post $id İşlem numarası.
	 *
	 * @return void
	 */
	public function __construct( $id = null ) {

		if ( is_int( $id ) || is_string( $id ) ) {
			$this->id = (int) $id;
			$this->load_data();
		} elseif ( $id instanceof WP_Post ) {
			$this->id = $id->ID;
			$this->load_data();
		} else {
			$this->id = wp_insert_post(
				array(
					'post_status'    => $this->start_status,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_type'      => $this->post_type,
				)
			);
			$this->created();
		}
	}

	/**
	 * Post kimliğini döndürür
	 *
	 * @return int|string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Sınıf türediğinde verileri değişkenlere atar.
	 */
	protected function load_data() {

		array_walk(
			$this->meta_data,
			function ( $key ) {
				foreach ( [ 'get', 'need', 'is' ] as $func_prefix ) {
					if ( is_callable( array( $this, "{$func_prefix}_{$key}" ) ) ) {
						call_user_func( array( $this, "{$func_prefix}_{$key}" ) );
						break;
					}
				}
			}
		);
	}

	/**
	 * Sınıfı array olarak döndürür.
	 *
	 * @return array
	 */
	public function to_array() {
		$array = array();
		// @phpstan-ignore-next-line argument.type $this phpstan için dönülebilir bir veri değildir. Fakat (protected) korunan verileri olan sınıfları dizi haline en iyi bu şekilde getirebiliyoruz.
		foreach ( $this as $key => $val ) {
			if ( 'meta_data' === $key ) {
				continue;
			}
			$array[ $key ] = $val;
		}
		return $array;
	}

	/**
	 * Fonksiyon ismi ile veri tabanına özellik yazmayı sağlar.
	 *
	 * @param string $function_name Fonksiyon ismi
	 * @param mixed  $value Yazılacak veri.
	 */
	protected function set_prop( $function_name, $value ) {
		$value       = apply_filters( "{$this->post_type}_{$function_name}", $value, $function_name, $this );
		$prop        = str_replace( [ 'set_' ], '', $function_name );
		$this->$prop = $value;
		update_post_meta( $this->id, $prop, $value );
	}

	/**
	 * Fonksiyon ismi ile veri tabanına özellik okumayı sağlar.
	 *
	 * @param string $function_name Fonksiyon ismi
	 *
	 * @return mixed
	 */
	protected function get_prop( $function_name ) {
		$prop  = str_replace( [ 'get_', 'need_', 'is_' ], '', $function_name );
		$value = get_post_meta( $this->id, $prop, true );
		if ( property_exists( $this, $prop ) ) {
			$this->$prop = apply_filters( "{$this->post_type}_{$function_name}", $value, $function_name, $this );
			return $this->$prop;
		}
		return $value;
	}

	/**
	 * Meta verisini kayıt eder.
	 *
	 * @param string $meta_key Meta veri anahtarı.
	 * @param mixed  $meta_value Meta verisi.
	 */
	public function add_meta( $meta_key, $meta_value ) {
		$this->set_prop( $meta_key, $meta_value );
	}

	/**
	 * Meta verisini döndürür.
	 *
	 * @param string $meta_key Meta veri anahtarı.
	 *
	 * @return mixed
	 */
	public function get_meta( $meta_key ) {
		return $this->get_prop( $meta_key );
	}

	/**
	 * Oluşturulma tarihini döndürür
	 *
	 * @return string
	 */
	public function get_date() {
		$this->date = get_post_field( 'post_date', $this->id );
		return $this->date;
	}

	/**
	 * Oluşturulma tarihini döndürür
	 *
	 * @return string
	 */
	public function get_human_date_diff() {
		$this->human_date_diff = $this->get_date();
		$timestamp             = strtotime( $this->human_date_diff );
		if ( $timestamp > strtotime( '-1 day', time() ) && $timestamp <= time() ) {
			$this->human_date_diff = sprintf(
			/* translators: %s: Saat farkı için kullanılan saat örn. 10 Dakika Önce */
				_x( '%s ago', '%s = human-readable time difference', 'gurmepos' ),
				human_time_diff( $timestamp, time() )
			);
		} else {
			$this->human_date_diff = date_i18n( __( 'j F Y', 'gurmepos' ), $timestamp );
		}
		return $this->human_date_diff;
	}

	/**
	 * Post tipi ilk defa yaratılıyorsa çalışacak fonksiyon.
	 */
	abstract public function created();
}
