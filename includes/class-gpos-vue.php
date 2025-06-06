<?php // phpcs:ignoreFile
/**
 * GurmePOS için Vue kullanımını sağlayan sınıf olan GPOS_Vue sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * Eklenti için Vue kullanımını sağlayan sınıf
 */
class GPOS_Vue {

	/**
	 * Vue instance id
	 *
	 * @var string $id
	 */
	public $id = GPOS_PREFIX;

	/**
	 * Eklenti versiyonu
	 *
	 * @var string $version
	 */
	protected $version = GPOS_VERSION;

	/**
	 * Ana Vue dizini
	 *
	 * @var string $vue_path
	 */
	protected $vue_path = 'src';

	/**
	 * Dahil edilecek Vue sayfasını temsil eder
	 *
	 * @var string $vue_page
	 */
	protected $vue_page = '';

	/**
	 * Windowdaki veriye ulaşılaak anahtar.
	 *
	 * @var string $vue_page
	 */
	protected $localize_key = 'gpos';

	/**
	 * Vue içerisinde kullanılacak window değişkenlerini taşır.
	 *
	 * @var array $localize_variables
	 */
	protected $localize_variables = array();

	/**
	 * Eklenti dosyalarının bulunduğu dizinin klasör yolu.
	 *
	 * @var string $plugin_dir_path
	 */
	protected $plugin_dir_path = GPOS_PLUGIN_DIR_PATH; // @phpstan-ignore-line

	/**
	 * Eklenti asset dosyalarının bulunduğu dizinin klasör linki.
	 *
	 * @var string $asset_dir_url
	 */
	protected $asset_dir_url = GPOS_ASSETS_DIR_URL;

	/**
	 * Vue oturum id.
	 *
	 * @param string $id Instance id.
	 *
	 * @return GPOS_Vue $this
	 */
	public function set_id( $id ) {
		$this->id = $id;
		return $this;
	}

	/**
	 * Dahil edilmesi istenen Vue sayfasını ayarlar.
	 *
	 * @param string $page dashboard, woocommerce-settings vb.
	 *
	 * @return GPOS_Vue $this
	 */
	public function set_vue_page( string $page ) {
		$this->vue_page = $page;
		return $this;
	}

	/**
	 * Ana Vue dizinini ayarlar.
	 *
	 * @param string $path src, vue vb.
	 *
	 * @return GPOS_Vue $this
	 */
	public function set_vue_path( string $path ) {
		$this->vue_path = $path;
		return $this;
	}

	/**
	 * Dahil edilecek javascript dosyasında kullanılmak istenen
	 * window değişkenlerini ayarlar.
	 *
	 * @param mixed  $variables window.$localize_key.$variables Şeklinde kullanılır.
	 * @param string $localize_key window.$localize_key.$variables Şeklinde kullanılır.
	 *
	 * @return GPOS_Vue $this
	 */
	public function set_localize( $variables, $localize_key = 'gpos' ) {
		$this->localize_key       = $localize_key;
		$this->localize_variables = $variables;
		return $this;
	}

	/**
	 * Vue aplikasyonu için idsi app olan divi oluşturur.
	 *
	 * @return GPOS_Vue $this
	 */
	public function create_app_div() {
		gpos_get_view( 'vue-app-div.php', array( 'at_checkout' => $this->at_checkout() ) );
		return $this;
	}

	/**
	 * Vue projesinin gösterimi için gereki js dosyalarını dahil eder.
	 *
	 * @return GPOS_Vue $this
	 */
	public function require_script_with_tag() {
		?>
			<script type="module" src="<?php echo esc_url( "{$this->asset_dir_url}/vue/js/{$this->vue_page}-{$this->replaced_version()}.js" ); //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>"></script> 
		<?php

		if ( ! empty( $this->localize_variables ) ) {
			?>
			<script>
				var <?php echo esc_html( $this->localize_key ); ?> = <?php echo wp_json_encode( $this->localize_variables ); ?>
			</script>
			<?php
		}

		return $this;
	}

	/**
	 * Vue projesinin gösterimi için gereki js dosyalarını dahil eder.
	 *
	 * @return GPOS_Vue $this
	 */
	public function require_script() {
		wp_enqueue_script(
			$this->id,
			"{$this->asset_dir_url}/vue/js/{$this->vue_page}-{$this->replaced_version()}.js",
			array( 'jquery' ),
			$this->version,
			false
		);

		if ( ! empty( $this->localize_variables ) ) {
			$object =  wp_json_encode( $this->localize_variables );
			wp_localize_script(
				$this->id,
				$this->localize_key . '_object_name',
				array( 'l10n_print_after' => "var {$this->localize_key} = {$object};" )
			);
		}

		return $this;
	}

	/**
	 * Vue projesinin gösterimi için gereki js dosyalarını register eder.
	 *
	 * @return GPOS_Vue $this
	 */
	public function register_script() {
		wp_register_script(
			$this->id,
			"{$this->asset_dir_url}/vue/js/{$this->vue_page}-{$this->replaced_version()}.js",
			array( 'jquery' ),
			$this->version,
			false
		);

		return $this;
	}

	/**
	 * Vue projesinin gösterimi için gereki css dosyalarını dahil eder.
	 *
	 * @param string|bool $css_file Çağrılacak css dosyası
	 *
	 * @return GPOS_Vue $this
	 */
	public function require_style_with_tag( $css_file = '' ) {
		$css_file = $this->get_css_file( $css_file );
		?>
			<link rel="stylesheet" href="<?php echo esc_url( "{$this->asset_dir_url}/vue/css/{$css_file}-{$this->replaced_version()}.css" ); //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>" media="all">
		<?php
		return $this;
	}

	/**
	 * Vue projesinin gösterimi için gereki css dosyalarını dahil eder.
	 *
	 * @param string|bool $css_file Çağrılacak css dosyası
	 *
	 * @return GPOS_Vue $this
	 */
	public function require_style( $css_file = '' ) {
		$css_file = $this->get_css_file( $css_file );
		wp_enqueue_style(
			$this->vue_page,
			"{$this->asset_dir_url}/vue/css/{$css_file}-{$this->replaced_version()}.css",
			array(),
			$this->version,
		);
		return $this;
	}

	/**
	 * Vue projesinin gösterimi için gereki css dosyalarını dahil eder.
	 *
	 * @param string|bool $css_file Çağrılacak css dosyası
	 *
	 * @return GPOS_Vue $this
	 */
	public function register_style( $css_file = '' ) {
		$css_file = $this->get_css_file( $css_file );
		wp_register_style(
			$this->vue_page,
			"{$this->asset_dir_url}/vue/css/{$css_file}-{$this->replaced_version()}.css",
			array(),
			$this->version,
		);
		return $this;
	}

	/**
	 * Dahil edilecek css dosyasını tespit eder.
	 *
	 * @param string|bool $css_file Çağrılacak css dosyası
	 *
	 * @return string $css_file
	 */
	public function get_css_file( $css_file ) {
		if ( empty( $css_file ) ) {
			$css_file = $this->at_checkout() ? 'checkout-app' : 'admin-app';
		}
		return $css_file;
	}

	/**
	 * Cache uygulamalarını engellemek için kullanılır.
	 */
	public function replaced_version() {
		return str_replace( '.', '-', $this->version );
	}

	/**
	 * Ödeme ekranı mı ?
	 *
	 * @return bool
	 */
	private function at_checkout() {
		return '' === $this->vue_page || 'checkout' === $this->vue_page || 'wc-add-payment-method-page' === $this->vue_page;
	}
}
