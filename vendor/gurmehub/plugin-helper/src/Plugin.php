<?php // phpcs:ignore
namespace GurmeHub;

/**
 * Eklenti sınıfı
 *
 * Eklentinin kimliğinin tanımlandığı sınıf
 */
class Plugin extends \GurmeHub\Api {

	/**
	 * Eklenti lisans anahtarı.
	 *
	 * @var string $license
	 */
	protected $license;

	/**
	 * Eklenti temel dosyası.
	 *
	 * @var string $basefile
	 */
	protected $basefile;

	/**
	 * Eklenti alan adı.
	 *
	 * @var string $textdomain
	 */
	protected $textdomain;

	/**
	 * Kurucu method.
	 *
	 * @param string $basefile Eklenti klasör/dosyaismi (gurmepos/gurmepos.php)
	 * @param string $textdomain Eklenti alan adı
	 *
	 * @return void
	 */
	public function __construct( $basefile, $textdomain ) {
		$this->basefile   = $basefile;
		$this->textdomain = $textdomain;
	}

	/**
	 * Eklenti temel dosyasını var/www/wp-content/plugins/klasör/dosyapsimi.php olarak döndürür.
	 *
	 * @return string
	 */
	public function get_basefile() {
		return $this->basefile;
	}

	/**
	 * Eklenti temel dosyasını klasör/dosyaismi.php olarak döndürür.
	 *
	 * @return string
	 */
	public function get_basename() {
		return plugin_basename( $this->basefile );
	}

	/**
	 * Eklenti slug.
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		$name_arr = explode( '/', (string) $this->get_basename() );
		if ( 2 >= count( $name_arr ) && isset( $name_arr[1] ) ) {
			return str_replace( [ '.php' ], [ '' ], $name_arr[1] );
		}
		return $this->get_basename();
	}

	/**
	 * Eklenti adı.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_plugin_data( $this->basefile )['Name'];
	}


	/**
	 * Eklenti kurulu versiyonu.
	 *
	 * @return string
	 */
	public function get_current_version() {
		return get_plugin_data( $this->basefile )['Version'];
	}


	/**
	 * Eklenti son versiyon bilgilerini döndürür.
	 *
	 * @return false|object $latest_info Eklenti güncel bilgileri.
	 */
	public function get_latest_info() {

		$latest_info_data = $this->request(
			array(
				'plugin' => $this->get_plugin_slug(),
				'url'    => str_replace( array( 'https://', 'http://' ), '', esc_url( home_url() ) ),
			),
			'checkUpdateV2'
		);

		if ( ! is_object( $latest_info_data ) || ! property_exists( $latest_info_data, 'plugin' ) || true === is_wp_error( $latest_info_data ) ) {
			return false;
		}

		$latest_info              = $latest_info_data->plugin;
		$latest_info->banners_rtl = (array) $latest_info->banners_rtl;
		$latest_info->banners     = (array) $latest_info->banners;
		$latest_info->icons       = (array) $latest_info->icons;
		$latest_info->sections    = (array) $latest_info->sections;

		return $latest_info;
	}
}
