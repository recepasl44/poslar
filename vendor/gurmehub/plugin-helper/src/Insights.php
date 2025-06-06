<?php // phpcs:ignore
namespace GurmeHub;

/**
 * Uygulama sınıfı
 */
class Insights extends \GurmeHub\Api {

	/**
	 * Eklenti tanımlayıcı sınıf
	 *
	 * @var \GurmeHub\Plugin $plugin
	 */
	public $plugin;

	/**
	 * Kurucu method.
	 *
	 * @param \GurmeHub\Plugin $plugin  Eklenti tanımlayıcı sınıf.
	 *
	 * @return void
	 */
	public function __construct( \GurmeHub\Plugin $plugin ) {
		$this->plugin = $plugin;
		register_activation_hook( $this->plugin->get_basefile(), array( $this, 'activation' ) );
		register_deactivation_hook( $this->plugin->get_basefile(), array( $this, 'deactivation' ) );
		add_action( $this->plugin->get_plugin_slug() . '_tracker_event', array( $this, 'send_tracking_data' ) );
		add_filter( 'plugin_action_links_' . $this->plugin->get_basename(), array( $this, 'plugin_action_links' ) );
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete' ), 10, 2 );
		add_action( 'wp_ajax_' . $this->plugin->get_plugin_slug() . '_deactivate_reasons', array( $this, 'deactivate_reasons' ), 10, 2 );
	}

	/**
	 * Eklenti aktif edilme kancasına tutunan method.
	 *
	 * @return void
	 */
	public function activation() {
		$hook_name = $this->plugin->get_plugin_slug() . '_tracker_event';
		if ( ! wp_next_scheduled( $hook_name ) ) {
			wp_schedule_event( time(), 'weekly', $hook_name );
		}
		$this->change_active_status();
	}


	/**
	 * Eklenti deaktif edilme kancasına tutunan method.
	 *
	 * @return void
	 */
	public function deactivation() {
		$this->change_active_status( 0 );
	}

	/**
	 * Eklenti bilgi toplama için gönderilecek verileri düzenler.
	 *
	 * @return void
	 */
	public function send_tracking_data() {
		$this->request(
			$this->get_tracking_data(),
			'trackingData'
		);
	}

	/**
	 * Eklenti aksiyon linkleri
	 *
	 * @param array $links Aksiyon linkleri.
	 *
	 * @return array $links Aksiyon linkleri.
	 */
	public function plugin_action_links( $links ) {
		if ( array_key_exists( 'deactivate', $links ) ) {
			$links['deactivate'] = str_replace( '<a', '<a class="' . $this->plugin->get_plugin_slug() . '-deactivate-reason"', $links['deactivate'] );
		}

		return $links;
	}

	/**
	 * Admin footer aksiyonları.
	 */
	public function admin_footer() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			( new \GurmeHub\Frontend( $this->plugin ) )->reason_of_deactivate();
		}
	}

	/**
	 * WordPress eklenti güncellemesinden sonra tetiklenen kancaya atanmış method.
	 *
	 * @param Plugin_Upgrader $upgrader WordPress güncelleme sınıfı.
	 * @param array           $hook_extra Güncellemedeki extra bilgiler
	 */
	public function upgrader_process_complete( $upgrader, $hook_extra ) {

		$is_instance = $upgrader instanceof \Plugin_Upgrader;
		$hook_extra  = is_array( $hook_extra ) ? $hook_extra : array();

		if ( $is_instance && false === $upgrader->bulk && array_key_exists( 'plugin', $hook_extra ) && $hook_extra['plugin'] === $this->plugin->get_basename() ) {
			$this->send_tracking_data();
		}

		if ( $is_instance && true === $upgrader->bulk && array_key_exists( 'plugins', $hook_extra ) && in_array( $this->plugin->get_basename(), $hook_extra['plugins'], true ) ) {
			$this->send_tracking_data();
		}
	}

	/**
	 * Local sunucu kontolü yapar
	 *
	 * @return bool
	 */
	private function is_local_server() {
		$is_local = 'no';
		$host     = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'localhost';

		if ( ! strpos( $host, '.' ) || in_array( strrchr( $host, '.' ), array( '.test', '.testing', '.local', '.localhost', '.localdomain' ), true ) || false !== strpos( $host, 'instawp.xyz' ) ) {
			$is_local = 'yes';
		}

		return $is_local;
	}

	/**
	 * Deaktiflik nedeni gönderme.
	 */
	public function deactivate_reasons() {
		$plugin_name = $this->plugin->get_plugin_slug();

		if ( check_ajax_referer( "{$plugin_name}_deactivate_reasons" ) && isset( $_POST['reasons'] ) ) {
			$this->request(
				array(
					'url'     => str_replace( array( 'https://', 'http://' ), '', esc_url( home_url() ) ),
					'plugin'  => $plugin_name,
					'reasons' => $_POST['reasons'], //phpcs:ignore
				),
				'saveDeactivateReasons'
			);
		}

		wp_send_json( true );
	}

	/**
	 * Eklentinin açılıp kapanması durumunda bilgilendirme yapar.
	 *
	 * @param int $status 1 yada 0 alabilir
	 *
	 * @return array
	 */
	public function change_active_status( $status = 1 ) {

		return $this->request(
			array(
				'url'       => str_replace( array( 'https://', 'http://' ), '', esc_url( home_url() ) ),
				'plugin'    => $this->plugin->get_plugin_slug(),
				'is_active' => $status,
			),
			'activeStatusChanged'
		);
	}

	/**
	 * Eklenti bilgi toplama için gönderilecek verileri düzenler.
	 *
	 * @return array
	 */
	public function get_tracking_data() {
		$all_plugins = $this->get_all_plugins();

		return array(
			'url'              => str_replace( array( 'https://', 'http://' ), '', esc_url( home_url() ) ),
			'site_name'        => $this->get_site_name(),
			'admin_email'      => get_option( 'admin_email' ),
			'server'           => $this->get_server_info(),
			'wp'               => $this->get_wp_info(),
			'users'            => $this->get_user_counts(),
			'active_plugins'   => $all_plugins['active_plugins'],
			'inactive_plugins' => $all_plugins['inactive_plugins'],
			'ip_address'       => $this->get_user_ip_address(),
			'plugin'           => $this->plugin->get_plugin_slug(),
			'plugin_version'   => $this->plugin->get_current_version(),
			'is_local'         => $this->is_local_server(),
		);
	}

	/**
	 * Aktif ve deaktif eklenti listesi.
	 *
	 * @return array
	 */
	private function get_all_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins             = get_plugins();
		$active_plugins_keys = get_option( 'active_plugins', array() );
		$active_plugins      = array();
		$inactive_plugins    = array();

		foreach ( $plugins as $k => $v ) {
			$formatted         = array();
			$formatted['name'] = wp_strip_all_tags( $v['Name'] );

			if ( isset( $v['Version'] ) ) {
				$formatted['version'] = wp_strip_all_tags( $v['Version'] );
			}

			if ( isset( $v['Author'] ) ) {
				$formatted['author'] = wp_strip_all_tags( $v['Author'] );
			}

			if ( isset( $v['Network'] ) ) {
				$formatted['network'] = wp_strip_all_tags( $v['Network'] );
			}

			if ( isset( $v['PluginURI'] ) ) {
				$formatted['plugin_uri'] = wp_strip_all_tags( $v['PluginURI'] );
			}

			if ( in_array( $k, $active_plugins_keys, true ) ) {
				$active_plugins[] = $formatted;
			} else {
				$inactive_plugins[] = $formatted;
			}
		}

		return array(
			'active_plugins'   => $active_plugins,
			'inactive_plugins' => $inactive_plugins,
		);
	}

	/**
	 * Varsa site ismini yok ise urlini döndürür.
	 *
	 * @return string $site_name.
	 */
	private function get_site_name() {
		$site_name = get_bloginfo( 'name' );

		if ( empty( $site_name ) ) {
			$site_name = get_bloginfo( 'description' );
			$site_name = wp_trim_words( $site_name, 3, '' );
		}

		if ( empty( $site_name ) ) {
			$site_name = esc_url( home_url() );
		}

		return $site_name;
	}

	/**
	 * Sunucu bilgilerini döndürür.
	 *
	 * @return array
	 */
	private static function get_server_info() {
		global $wpdb;

		$server_data = array();

		$server_data['software'] = isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '$_SERVER[SERVER_SOFTWARE] Undefined.'; //phpcs:ignore

		if ( function_exists( 'phpversion' ) ) {
			$server_data['php_version'] = phpversion();
		}

		$server_data['mysql_version'] = $wpdb->db_version();

		$server_data['php_max_upload_size']  = size_format( wp_max_upload_size() );
		$server_data['php_default_timezone'] = date_default_timezone_get();
		$server_data['php_soap']             = class_exists( 'SoapClient' ) ? 'Yes' : 'No';
		$server_data['php_fsockopen']        = function_exists( 'fsockopen' ) ? 'Yes' : 'No';
		$server_data['php_curl']             = function_exists( 'curl_init' ) ? 'Yes' : 'No';

		return $server_data;
	}

	/**
	 * WordPress bilgilerini döndürür.
	 *
	 * @return array
	 */
	private function get_wp_info() {
		$wp_data = array();

		$wp_data['memory_limit'] = WP_MEMORY_LIMIT;
		$wp_data['debug_mode']   = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No';
		$wp_data['locale']       = get_locale();
		$wp_data['version']      = get_bloginfo( 'version' );
		$wp_data['multisite']    = is_multisite() ? 'Yes' : 'No';
		$wp_data['theme_slug']   = get_stylesheet();

		$theme = wp_get_theme( $wp_data['theme_slug'] );

		$wp_data['theme_name']    = $theme->get( 'Name' );
		$wp_data['theme_version'] = $theme->get( 'Version' );
		$wp_data['theme_uri']     = $theme->get( 'ThemeURI' );
		$wp_data['theme_author']  = $theme->get( 'Author' );

		return $wp_data;
	}

	/**
	 * Rollere göre toplam kullanıcı adedi.
	 *
	 * @return array
	 */
	public function get_user_counts() {
		$user_count          = array();
		$user_count_data     = count_users();
		$user_count['total'] = $user_count_data['total_users'];

		foreach ( $user_count_data['avail_roles'] as $role => $count ) {
			if ( ! $count ) {
				continue;
			}

			$user_count[ $role ] = $count;
		}

		return $user_count;
	}

	/**
	 * IP adresini döndürür.
	 *
	 * @return string
	 */
	private function get_user_ip_address() {
		$response = wp_remote_get( 'https://icanhazip.com/' );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$ip_address = trim( wp_remote_retrieve_body( $response ) );

		if ( ! filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
			return '';
		}

		return $ip_address;
	}
}
