<?php
/**
 * GurmePOS ödeme geçidi işlem sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS işlem sınıfı.
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class GPOS_Transaction extends GPOS_Customer {

	use GPOS_Credit_Card;

	/**
	 * İşlem notları
	 *
	 * @var array $notes
	 */
	protected $notes;

	/**
	 * İşlem kayıtları.
	 *
	 * @var array $logs
	 */
	protected $logs;

	/**
	 * İşlem Hareket kayıtları.
	 *
	 * @var array $action_logs
	 */
	protected $action_logs;

	/**
	 * İşlem satırları.
	 *
	 * @var array $lines
	 */
	protected $lines = array();

	/**
	 * İşlem satırları array biçiminde.
	 *
	 * @var array $lines_array
	 */
	protected $lines_array = array();

	/**
	 * İşlem post tipi.
	 *
	 * @var string $post_type
	 */
	public $post_type = 'gpos_transaction';

	/**
	 * Başlangıç durumu.
	 *
	 * @var string $start_status
	 */
	public $start_status = GPOS_Transaction_Utils::STARTED;

	/**
	 * İşlemin ödeme geçidinde kayıtlı tekil numarası.
	 *
	 * @var string|int $payment_id
	 */
	protected $payment_id;

	/**
	 * İşlemi gerçekleştiren form yada widgetin idsi
	 *
	 * @var string|int $form_id
	 */
	protected $form_id;

	/**
	 * İşlem için ekstra veri alanı
	 *
	 * @var mixed $extra_data
	 */
	protected $extra_data;

	/**
	 * İşlem linki
	 *
	 * @var string $edit_link
	 */
	protected $edit_link;

	/**
	 * Ödeme işleminin geçtiği ödeme geçidinin kimliği.
	 *
	 * @var string $payment_gateway_id
	 */
	protected $payment_gateway_id;

	/**
	 * İşlem tipi.
	 *
	 * @var string $type
	 */
	protected $type;

	/**
	 * İşlem durumu.
	 *
	 * @var string $status
	 */
	protected $status;

	/**
	 * İşlem toplam tutarı
	 *
	 * @var float $total
	 */
	protected $total;

	/**
	 * Ödeme işlemi yapılacak para birimi
	 *
	 * @var string $currency
	 */
	protected $currency = 'TRY';

	/**
	 * İşlemin iptal edilebilme durumu.
	 *
	 * @var bool $cancelable
	 */
	protected $cancelable;

	/**
	 * İşlemin geçtiği ödeme geçidi satır bazlı mı?
	 *
	 * @var bool $line_based
	 */
	protected $line_based;

	/**
	 * İptal ve iade işlemi için hangi ödeme işlemine istinaden türetildiği bilgisi.
	 *
	 * @var bool $payment_transaction_id
	 */
	protected $payment_transaction_id;

	/**
	 * Taksit sayısı
	 *
	 * @var int|string $installment
	 */
	protected $installment = 1;

	/**
	 * Taksit yüzdelik oranı
	 *
	 * @var int|float $installment_rate
	 */
	protected $installment_rate = 1;

	/**
	 * İşlem Test işlemimi değil mi kontrolü için
	 *
	 * @var int $is_test
	 */
	protected $is_test = 0;

	/**
	 * Ödemede kayıtlı kart kullanılacak mı?
	 *
	 * @var bool $use_saved_card
	 */
	protected $use_saved_card = false;

	/**
	 * Ödemede kullanılan kayıtlı kartın kimliği.
	 *
	 * @var bool $saved_card_id
	 */
	protected $saved_card_id = false;

	/**
	 * Ödemede kullanılan kart kayıt edilsin mi?
	 *
	 * @var bool $save_card
	 */
	protected $save_card = false;

	/**
	 * Entegre eklentinin işlem numarası
	 *
	 * @var int $payment_transaction_id
	 */
	protected $plugin_transaction_id;

	/**
	 * İşlem güvenlik türü
	 *
	 * @var string $security_type
	 */
	protected $security_type;

	/**
	 * 3D Model Tipi
	 *
	 * @var string $threed_type
	 */
	protected $threed_type;


	/**
	 * Entegre eklenti ödeme geçidi sınıfı
	 *
	 * @var string $payment_gateway_class
	 */
	protected $payment_gateway_class;

	/**
	 * İşlemi gerçekleştiren hesabın idsi
	 *
	 * @var int $account_id
	 */
	protected $account_id;

	/**
	 * İşlem iade durumu
	 *
	 * @var string $refund_status
	 */
	protected $refund_status;

	/**
	 * İşlem gateten etkilendi mi ?
	 *
	 * @var bool $gate_affected
	 */
	protected $gate_affected;

	/**
	 * Gateten etkilendiği kural
	 *
	 * @var stdClass $gate_affected_rule
	 */
	protected $gate_affected_rule;

	/**
	 * İşlem test ödemesi mi?
	 *
	 * @var boolean $test
	 */
	protected $test;

	/**
	 * İşlemin yapıldığı entegre eklenti
	 *
	 * @var string $plugin
	 */
	protected $plugin;

	/**
	 * Ödemeye çıkmadan önce kart token bilgisini kaydetmek için
	 *
	 * @var string $card_token
	 */
	protected $card_token;

	/**
	 * Kullanıcı idsi
	 *
	 * @var int|string $user_id
	 */
	protected $user_id;

	/**
	 * Önceki ödeme geçidinin idsi
	 *
	 * @var int $id_before_gate
	 */
	protected $id_before_gate;

	/**
	 * Kartın ait oldugu ülke
	 *
	 * @var int $card_country_code
	 */
	protected $card_country_code;

	/**
	 * Ödeme yapılan form tipi.
	 *
	 * @var int $payment_form_type
	 */
	protected $payment_form_type;

	/**
	 * Ödeme yapılan hesabın tipi.
	 *
	 * @var int $payment_form_type
	 */
	protected $payment_method_type;

	/**
	 * Ödemenin çeviriden önceki toplamı
	 *
	 * @var string $before_convert_total
	 */
	protected $before_convert_total;

	/**
	 * Ödemenin çeviriden önceki para birimi
	 *
	 * @var string $before_convert_currency
	 */
	protected $before_convert_currency;

	/**
	 * İadenin entegre eklentiler tarafından yapılıp yapılmadığı
	 *
	 * @var string $refunded_from_payment_plugin
	 */
	protected $refunded_from_payment_plugin;

	/**
	 * Hata mesajı.
	 *
	 * @var string $error_message
	 */
	protected $error_message;

	/**
	 * Post meta verileri.
	 *
	 * @var array $meta_data
	 */
	public $meta_data = array(
		'type',
		'test',
		'status',
		'plugin',
		'plugin_transaction_id',
		'total',
		'security_type',
		'threed_type',
		'currency',
		'customer_id',
		'customer_first_name',
		'customer_last_name',
		'customer_email',
		'customer_phone',
		'customer_address',
		'customer_city',
		'customer_state',
		'customer_country',
		'customer_zipcode',
		'customer_ip_address',
		'installment',
		'installment_rate',
		'masked_card_bin',
		'card_type',
		'card_brand',
		'card_family',
		'card_holder_name',
		'card_country',
		'card_bank_name',
		'card_token',
		'save_card',
		'use_saved_card',
		'date',
		'notes',
		'logs',
		'action_logs',
		'payment_id',
		'form_id',
		'payment_gateway_id',
		'payment_gateway_class',
		'lines',
		'lines_array',
		'account_id',
		'refund_status',
		'cancelable',
		'line_based',
		'payment_transaction_id',
		'edit_link',
		'gate_affected',
		'gate_affected_rule',
		'id_before_gate',
		'human_date_diff',
		'payment_form_type',
		'payment_method_type',
		'before_convert_total',
		'before_convert_currency',
		'gateway_commission_rate',
		'transaction_commission_fee',
		'refunded_from_payment_plugin',
	);


	/**
	 * İşlem numarasını döndürür
	 *
	 * @return int|string
	 */
	public function get_id() {
		$settings = gpos_other_settings()->get_setting_by_key( 'payment_id_settings' );
		if ( $settings && $settings->active ) {
			return $this->get_plugin_transaction_id() . $settings->separator . $this->id;
		}
		return $this->id;
	}

	/**
	 * Yaratıldığında çalışacak method.
	 *
	 * @return void
	 */
	public function created() {
		$this->add_note( __( 'Transaction started.', 'gurmepos' ), 'start' );
		$this->set_refund_status( GPOS_Transaction_Utils::REFUND_STATUS_NOT_REFUNDED );
		$this->set_user_id( get_current_user_id() );
		$this->set_test( gpos_is_test_mode() );
	}

	/**
	 * İşlemi gerçekleştiren kullanıcı kimliğini ayarlar.
	 *
	 * @param string|int $value Kimlik.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_user_id( $value ) {
		wp_update_post(
			array(
				'ID'          => $this->id,
				'post_author' => $value,
			)
		);
		return $this;
	}

	/**
	 * İşlemi gerçekleştiren kullanıcı kimliğini döndürür.
	 *
	 * @return string|int
	 */
	public function get_user_id() {
		$this->user_id = get_post_field( 'post_author', $this->id );
		return $this->user_id;
	}

	/**
	 * İşlemin "test ödemesi mi?" bilgisini ayarlar.
	 *
	 * @param bool $value Test mi?
	 *
	 * @return GPOS_Transaction
	 */
	public function set_test( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlemi gerçekleştiren formun idsini atar
	 *
	 * @param int|string $value Formun idsi
	 *
	 * @return GPOS_Transaction
	 */
	public function set_form_id( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}


	/**
	 * İşlemin 'test ödemesi mi?' bilgisini döndürür .
	 *
	 * @return boolean
	 */
	public function is_test() {
		$this->test = $this->get_prop( __FUNCTION__ );
		return $this->test;
	}

	/**
	 * İşlemi gerçekleştiren formun idsini döndürür
	 *
	 * @return string|int
	 */
	public function get_form_id() {
		return $this->get_prop( __FUNCTION__ );
	}


	/**
	 * İşlem için ektra veriyi atar
	 *
	 * @param string $value Form id.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_extra_data( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlem için ektra veriyi döndürür
	 *
	 * @return string|int
	 */
	public function get_extra_data() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlemler tablosunda arama yapılabilmesi için post_content içerisi doldurulur.
	 *
	 * @return void
	 */
	public function set_searchable() {
		$implode_array = array_filter( $this->to_array(), fn( $value ) => ! ( is_array( $value ) || is_object( $value ) ) );
		wp_update_post(
			array(
				'ID'           => $this->id,
				'post_content' => preg_replace( '/\b(\d{4})(\d{4})(\d{4})(\d{4})\b/', '**************$4', implode( ';', $implode_array ) ),
				'post_title'   => $this->get_customer_full_name(),
			)
		);
	}

	/**
	 * İşlem tipini ayarlar.
	 *
	 * @param string $value Tip
	 *
	 * @return GPOS_Transaction
	 */
	public function set_type( string $value ) {
		$term = get_term_by( 'slug', $value, 'gpos_transaction_process_type' );

		if ( $term ) {
			wp_set_post_terms( $this->id, array( $term->term_id ), 'gpos_transaction_process_type' );
		}

		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlem tipini döndürür
	 *
	 * @param string $contex Varsyılan olarak 'view' dir, haricindeki tüm değerler obje(WP_Term) döndürür.
	 *
	 * @return string|WP_Term
	 */
	public function get_type( $contex = 'view' ) {
		$term_slug = $this->get_prop( __FUNCTION__ );
		$term      = get_term_by( 'slug', $term_slug, 'gpos_transaction_process_type' );
		return 'view' === $contex ? $term_slug : $term;
	}

	/**
	 * İşlem durumunu ayarlar.
	 *
	 * @param string $new_status Durumu
	 * @param bool   $force Zorla güncelleme
	 *
	 * @return GPOS_Transaction
	 *
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function set_status( string $new_status, bool $force = false ) {
		if ( $this->get_status() !== GPOS_Transaction_Utils::COMPLETED || $force ) {
			$all_statuses    = gpos_post_operations()->get_post_statuses();
			$old_status_text = $all_statuses[ $this->get_status() ]['label'];
			$new_status_text = $all_statuses[ $new_status ]['label'];
			// translators: %1$s => Eski durum %2$s => Yeni durum.
			$this->add_note( sprintf( __( 'Status updated %1$s to %2$s', 'gurmepos' ), $old_status_text, $new_status_text ), 'status_update' );

			wp_update_post(
				array(
					'ID'          => $this->id,
					'post_status' => $new_status,
				)
			);
		}

		return $this;
	}

	/**
	 * İşlem durumunu döndürür
	 *
	 * @return string
	 */
	public function get_status() {
		$this->status = get_post_field( 'post_status', $this->id );
		return $this->status;
	}

	/**
	 * İşlemin gerçekleştiği eklenti.
	 * WooCommerce, GiveWP vb.
	 *
	 * @param string $value Eklenti.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_plugin( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlemin gerçekleştiği eklenti döndürür.
	 * WooCommerce, GiveWP vb.
	 *
	 * @return string
	 */
	public function get_plugin() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlemin gerçekleştiği GPOS_Account idsini ayarlar.
	 *
	 * @param string|int $value GPOS_Account id.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_account_id( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlemin gerçekleştiği GPOS_Account idsini döndürür.
	 *
	 * @return string|int
	 */
	public function get_account_id() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlemin eklentideki kayıtlı tekil numarası.
	 * WooCommerce için sipariş numarası, GiveWP için ödeme numarası vb.
	 *
	 * @param string|int $value Numara.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_plugin_transaction_id( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlemin eklentideki kayıtlı tekil numarasını döndürür.
	 *
	 * @return string|int
	 */
	public function get_plugin_transaction_id() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlem toplamını ayarlar
	 *
	 * @param float $value İşlem toplam tutarı.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_total( $value ) {
		$this->set_prop( __FUNCTION__, gpos_number_format( $value ) );
		return $this;
	}

	/**
	 * İşlemin toplamını döndürür
	 *
	 * @return float
	 */
	public function get_total() {
		return $this->get_prop( __FUNCTION__ );
	}

		/**
		 * İşlem çeviriden önceki  toplamını ayarlar
		 *
		 * @param float $value İşlem toplam tutarı.
		 *
		 * @return GPOS_Transaction
		 */
	public function set_before_convert_total( $value ) {
		$this->set_prop( __FUNCTION__, gpos_number_format( $value ) );
		return $this;
	}

	/**
	 * İşlemin çeviriden önceki toplamını döndürür
	 *
	 * @return float
	 */
	public function get_before_convert_total() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlem para birimini ayarlar
	 *
	 * @param string $value İşlem para birimi.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_currency( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlem para birimini döndürür
	 *
	 * @return string
	 */
	public function get_currency() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlem çeviriden önceki para birimini ayarlar
	 *
	 * @param string $value İşlem para birimi.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_before_convert_currency( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlem çeviriden önceki para birimini döndürür
	 *
	 * @return string
	 */
	public function get_before_convert_currency() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlemin güvenlik tipini ayarlar.
	 *
	 * @param string $value Güvenlik tipi.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_security_type( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlemin güvenlik tipini döndürür
	 *
	 * @return string
	 */
	public function get_security_type() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * 3D işlem tipini ayarlar
	 *
	 * @param string $value Model tipi.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_threed_type( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * 3D işlem tipini döndürür
	 *
	 * @return string
	 */
	public function get_threed_type() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödeme geçidi tekil kimlik bilgisini ayarlar.
	 *
	 * @param string $value Ödeme geçidi tekil kimlik bilgisi.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_payment_gateway_id( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödeme geçidi tekil kimlik bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_payment_gateway_id() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödeme geçidi sınıfını ayarlar.
	 *
	 * @param string $value Ödeme geçidi sınıfı.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_payment_gateway_class( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödeme geçidi sınıfını döndürür.
	 *
	 * @return string
	 */
	public function get_payment_gateway_class() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödemenin iptal/iade durumunu ayarlar.
	 *
	 * @param string $value İptal/iade durumunu.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_refund_status( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödemenin iptal/iade durumunu döndürür.
	 *
	 * @return string
	 */
	public function get_refund_status() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Taksit seçeneğini ayarlar
	 *
	 * @param int|string $value Taksit seçeneği.
	 * @return GPOS_Transaction
	 */
	public function set_installment( $value ) {
		$this->set_prop( __FUNCTION__, preg_replace( '/[^0-9]/', '', $value ) );
		return $this;
	}

	/**
	 * Taksit seçeneğini döndürür
	 *
	 * @return int|string
	 */
	public function get_installment() {
		$installment       = $this->get_prop( __FUNCTION__ );
		$this->installment = '' === $installment ? 1 : (int) $installment;
		return $this->installment;
	}

	/**
	 * Taksit seçeneğinin oranını ayarlar
	 *
	 * @param int|float $value Oran.
	 * @return GPOS_Transaction
	 */
	public function set_installment_rate( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Taksit seçeneğinin oranını döndürür
	 *
	 * @return int|float
	 */
	public function get_installment_rate() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödeme geçidinden dönen başarılı işlemin tekil kimlik bilgisini ayarlar.
	 *
	 * @param string $value Ödeme geçidinden dönen tekil numara iade, iptal için kullanılacaktır.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_payment_id( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödeme geçidinden dönen başarılı işlemin tekil kimlik bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_payment_id() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödemede kullanılacak kayıtlı kart kimliğini ayarlar.
	 *
	 * @param string|int $value Kayıtlı kartın kimliği.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_saved_card_id( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödemede kullanılacak kayıtlı kart kimliğini döndürür.
	 *
	 * @return string|int
	 */
	public function get_saved_card_id() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlem iade veya iptalse, hangi ödeme işleminin iptali veya iadesi olduğu verisini tutar.
	 *
	 * @param string $value Ödeme işleminin tekil numarsı.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_payment_transaction_id( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İşlem iade veya iptalse, hangi ödeme işleminin iptali veya iadesi olduğu verisini döndürür.
	 *
	 * @return string
	 */
	public function get_payment_transaction_id() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlem satırları ayarlar.
	 *
	 * @param array $lines İşlem satırı.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_lines( array $lines ) {
		$this->lines = $lines;
		return $this;
	}

	/**
	 * İşlem satırlarına yenisini ekler.
	 *
	 * @param GPOS_Transaction_Line $line İşlem satırı.
	 *
	 * @return GPOS_Transaction
	 */
	public function add_line( GPOS_Transaction_Line $line ) {
		$line->set_transaction_id( $this->id );
		$this->lines[] = $line;
		return $this;
	}

	/**
	 * İşlem satırlarını döndürür
	 *
	 * @param array $types satır türleri
	 * @return GPOS_Transaction_Line[]
	 */
	public function get_lines( $types = array( 'fee', 'product', 'commission' ) ) {
		global $wpdb;
		$this->set_lines(
			array_map(
				fn( $line ) => gpos_transaction_line( $line->ID ),
				$wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'gpos_t_line' AND post_parent = %s", $this->id ) ) // phpcs:ignore 
			)
		);
		$this->lines = array_filter( $this->lines, fn( $line )=> in_array( $line->get_type(), $types, true ) );
		return array_values( $this->lines );
	}


	/**
	 * İşlem satırlarını dizi olarak döndürür.
	 *
	 * @return array
	 */
	public function get_lines_array() {
		$this->lines_array = array_map( fn( $line ) => $line->to_array(), $this->get_lines() );
		return $this->lines_array;
	}

	/**
	 * İşleme not ekler.
	 *
	 * @param string $note İşlem notu.
	 * @param string $type İşlem not tipi.
	 *
	 * @return void
	 */
	public function add_note( $note, $type = 'note' ) {
		if ( is_user_logged_in() ) {
			$user                 = get_user_by( 'id', get_current_user_id() );
			$comment_author       = $user->display_name;
			$comment_author_email = $user->user_email;
		} else {
			$comment_author        = 'POS Entegratör';
			$comment_author_email  = 'posentegrator@';
			$comment_author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www . ', '', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : 'noreply.com';
			$comment_author_email  = sanitize_email( $comment_author_email );
		}

		$commentdata = array(
			'comment_post_ID'      => $this->id,
			'comment_author'       => $comment_author,
			'comment_author_email' => $comment_author_email,
			'comment_author_url'   => '',
			'comment_content'      => $note,
			'comment_agent'        => 'POS Entegratör',
			'comment_type'         => 'transaction_note',
			'comment_parent'       => 0,
			'comment_approved'     => 1,
		);

		$comment_id = wp_insert_comment( $commentdata );

		update_comment_meta( $comment_id, 'note_type', $type );
	}

	/**
	 * İşleme ait notları getirir.
	 *
	 * @return array
	 */
	public function get_notes() {
		global $wpdb;
		$this->notes = array_map(
			function ( $comment ) {
				return array(
					'note' => $comment->comment_content,
					'date' => $comment->comment_date,
					'type' => get_comment_meta( (int) $comment->comment_ID, 'note_type', true ),
				);
			},
			$wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_type = 'transaction_note' ORDER BY comment_ID DESC", (int) $this->id ) ) //phpcs:ignore 
		);
		return $this->notes;
	}

	/**
	 * İşleme istinaden ödeme geçidi loglarını tutar.
	 *
	 * @param string $process İşlem tipi.
	 * @param mixed  $request Ödeme geçidine gönderilen veri.
	 * @param mixed  $response Ödeme geçidinden alınan cevap.
	 */
	public function add_log( $process, $request, $response ) {
		$logger = new GPOS_Transaction_Log();
		$logger->add(
			array(
				'gateway'               => $this->payment_gateway_class,
				'process'               => $process,
				'transaction_id'        => $this->id,
				'plugin'                => $this->get_plugin(),
				'plugin_transaction_id' => $this->get_plugin_transaction_id(),
				'request'               => $request,
				'response'              => $response,
			)
		);
	}

	/**
	 * İşleme istinaden ödeme geçidi loglarını tutar.
	 *
	 * @param string $process İşlem tipi.
	 * @param string $status İşlem Durumu başarılı başarısız vs gibi.
	 * @param mixed  $request İşlem için gönderilen gönderilen veri.
	 * @param mixed  $response İşlem için alınan cevap.
	 */
	public function add_action_log( $process, $status, $request, $response ) {
		$logger = new GPOS_Transaction_Action_Log();
		$logger->add(
			array(
				'status'         => $status,
				'process'        => $process,
				'transaction_id' => $this->id,
				'request'        => $request,
				'response'       => $response,
			)
		);
	}

	/**
	 * İşleme istinaden logları döndürür.
	 *
	 * @return array
	 */
	public function get_logs() {
		$logger     = new GPOS_Transaction_Log();
		$this->logs = $logger->get( $this->id );
		return $this->logs;
	}

	/**
	 * İşleme istinaden hareket logları döndürür.
	 *
	 * @return array
	 */
	public function get_action_logs() {
		$action_logger     = new GPOS_Transaction_Action_Log();
		$this->action_logs = $action_logger->get( $this->id );
		return $this->logs;
	}

	/**
	 * İşlem inceleme html.
	 *
	 * @return string
	 */
	public function get_edit_link_html() {
		// translators: %1$s => Link, %2$s => Text
		$html = sprintf( __( 'Click to view the details of transaction <a href="%1$s" target="_blank">#%2$s</a>.', 'gurmepos' ), $this->get_edit_link(), $this->id, );
		return $html;
	}

	/**
	 * İşlem inceleme linki.
	 *
	 * @return string
	 */
	public function get_edit_link() {
		$this->edit_link = add_query_arg(
			array(
				'page'        => 'gpos-transaction',
				'transaction' => $this->id,
				'_wpnonce'    => wp_create_nonce( GPOS_AJAX_ACTION ),

			),
			admin_url( 'admin.php' ),
		);

		return $this->edit_link;
	}

	/**
	 * Ödemede kayıtlı kart kullanılacak mı?
	 *
	 * @param bool $value Kayıtlı kart kullanılsın mı?
	 *
	 * @return GPOS_Transaction
	 */
	public function set_use_saved_card( bool $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödemede kayıtlı kart kullanılacak mı ?
	 *
	 * @return bool
	 */
	public function need_use_saved_card() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödemede kullanılan kart kayıt edilecek mi?
	 *
	 * @param bool $value Kart kayıt edilsin mi ?
	 *
	 * @return GPOS_Transaction
	 */
	public function set_save_card( bool $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödemede kullanılan kart kayıt edilecek mi ?
	 *
	 * @return true|false
	 */
	public function get_save_card() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödemede kullanılan form tipi
	 *
	 * @param string $value Form tipi
	 *
	 * @return GPOS_Transaction
	 */
	public function set_payment_form_type( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödemede kullanılan form tipi
	 *
	 * @return string
	 */
	public function get_payment_form_type() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödemede kullanılan method tipi
	 *
	 * @param string $value Method tipi
	 *
	 * @return GPOS_Transaction
	 */
	public function set_payment_method_type( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödemede kullanılan method tipi
	 *
	 * @return string
	 */
	public function get_payment_method_type() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İşlem iade edilebilir mi ?
	 *  Edilme durumunda ise true değilse false döndürür
	 *
	 * @return bool
	 */
	public function is_cancelable() {
		$diff             = date_create( $this->get_date() )->diff( date_create() );
		$this->cancelable = 0 === $diff->days;
		return $this->cancelable;
	}

	/**
	 * Tüm ödeme satırlarının durumunu belirtilen duruma getirir.
	 *
	 * @param string $status Satır durumu.
	 *
	 * @return void
	 */
	public function update_lines_status( $status ) {
		foreach ( $this->get_lines() as $line ) {
			$line->set_status( $status );
		}
	}

	/**
	 * İşlemin geçtiği ödeme geçidi satır bazlı mı ?
	 *
	 * @return bool
	 */
	public function is_line_based() {
		$gateway = gpos_payment_gateways()->get_base_gateway_by_gateway_id( $this->get_payment_gateway_id() );
		if ( $gateway instanceof GPOS_Gateway ) {
			$this->line_based = $gateway->line_based;
		}
		return $this->line_based;
	}

	/**
	 * Ödeme işlemi Gateten etkilendi mi ? atamasını yapar.
	 *
	 * @return GPOS_Transaction
	 */
	public function set_gate_affected() {
		$this->set_prop( __FUNCTION__, true );
		return $this;
	}

	/**
	 * Ödeme işlemi Gateten etkilendi mi ? atamasını yapar.
	 *
	 * @return bool|int|string
	 */
	public function get_gate_affected() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödeme işlemi Gateten etkilendiyse kuralı kayıt eder.
	 *
	 * @param stdClass $value Kural
	 */
	public function set_gate_affected_rule( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödeme işlemi Gateten etkilendiyse kuralı döndürür.
	 *
	 * @return stdClass
	 */
	public function get_gate_affected_rule() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Ödeme işlemi Gateten etkilenmeden önceki hesap kimliğini ayarlar.
	 *
	 * @param int|string $value Kural
	 */
	public function set_id_before_gate( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Ödeme işlemi Gateten etkilenmeden önceki hesap kimliği döndürür.
	 *
	 * @return int|string
	 */
	public function get_id_before_gate() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * İade entegre eklenti tarafından mı iade edildi durumu atar
	 *
	 * @param string $value Method tipi
	 *
	 * @return GPOS_Transaction
	 */
	public function set_refunded_from_payment_plugin( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * İade entegre eklenti tarafından mı iade edildi durumu getir
	 *
	 * @return string
	 */
	public function is_refunded_from_payment_plugin() {
		return $this->get_prop( __FUNCTION__ );
	}
	/**
	 * Hata mesajı.
	 *
	 * @param string $value Hata mesajı
	 */
	public function set_error_message( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}
	/**
	 * Hata mesajı.
	 */
	public function get_error_message() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kart kayıt etme
	 *
	 * @return mixed
	 */
	public function save_used_card_for_next_payment() {
		if ( function_exists( 'gpospro_saved_card' ) ) {
			$saved_card = gpospro_saved_card()
			->set_card_name( $this->get_card_name() )
			->set_card_holder_name( $this->get_card_holder_name() )
			->set_card_expiry_month( $this->get_card_expiry_month() )
			->set_card_expiry_year( $this->get_card_expiry_year() )
			->set_card_family( $this->get_card_family() )
			->set_card_brand( $this->get_card_brand() )
			->set_card_bank_name( $this->get_card_bank_name() )
			->set_card_type( $this->get_card_type() )
			->set_card_country( $this->get_card_country() )
			->set_masked_card_bin( $this->get_masked_card_bin(), true )
			->set_user_id( $this->get_user_id() )
			->set_payment_gateway_id( $this->get_payment_gateway_id() )
			->set_account_id( $this->get_account_id() )
			->set_default()
			->set_searchable();

			do_action( 'gpos_saved_card_created', $saved_card );

			return $saved_card;
		}
	}
}
