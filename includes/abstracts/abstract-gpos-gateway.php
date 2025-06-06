<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Ödeme geçidi tanımlayıcı sınıfları için abstract sınıfı olan GPOS_Gateway
 *
 * @package GurmeHub
 */

/**
 * Ödeme geçidi tanımlayıcı sınıfları için abstract sınıfı
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class GPOS_Gateway {

	/**
	 * Ödeme geçidi benzersiz kimliği
	 *
	 * @var string $id
	 */
	public $id;

	/**
	 * Base gateway kullanılacağında hesap idsine ihtiyaç varsa atama yapılabilir.
	 *
	 * @var string|int $account_id
	 */
	public $account_id;

	/**
	 * Ödeme geçidi başlığı
	 *
	 * @var string $title
	 */
	public $title;

	/**
	 * Ödeme geçidi açıklaması
	 *
	 * @var string $description
	 */
	public $description;

	/**
	 * Ödeme geçidi kayıt sonrası uyarı mesajı
	 *
	 * @var array $save_message
	 */
	public $save_message;

	/**
	 * Ödeme geçidi ayar sınıfı
	 *
	 * @var string $settings_class
	 */
	public $settings_class;

	/**
	 * Ödeme geçidi
	 *
	 * @var string $gateway_class
	 */
	public $gateway_class;

	/**
	 * Firma müşteri panel bilgisi
	 *
	 * @var string $merchant_panel
	 */
	public $merchant_panel;

	/**
	 * Sanal POS yapılandırmaları için yardım dökümanına yönlendirme
	 *
	 * @var string $help_document
	 */
	public $help_document;

	/**
	 * Logo urli
	 *
	 * @var string $logo
	 */
	public $logo;

	/**
	 * Desteklenilen para birimleri
	 *
	 * @var array $currencies
	 */
	public $currencies = array();

	/**
	 * Desteklenen özellikler
	 *
	 * @var array $supports
	 */
	public $supports = array();

	/**
	 * Pro gereksinimi
	 *
	 * @var boolean $is_need_pro
	 */
	public $is_need_pro = true;

	/**
	 * Bağlantı kontrolü yapılabiliyor mu ?
	 *
	 * @var boolean $check_connection_is_available
	 */
	public $check_connection_is_available = false;

	/**
	 * Kırılım bazlı mı ?
	 *
	 * @var boolean $line_based
	 */
	public $line_based = false;

	/**
	 * Taksitin vade farkı manuel eklenmeli mi ?
	 *
	 * @var boolean $add_fee_for_installment
	 */
	public $add_fee_for_installment = true;

	/**
	 * Ayar alanları
	 *
	 * @var array $fields
	 */
	public $fields;

	/**
	 * Test kartları
	 *
	 * @var array $test_cards
	 */
	public $test_cards;

	/**
	 * Ödeme geçidi tipi
	 *
	 * @var string $payment_method_type
	 *
	 * 'virtual_pos'|'common_form_payment'|'alternative_payment'|'bank_transfer'|'shopping_credit'|'iframe_payment
	 */
	public $payment_method_type = 'virtual_pos';

	/**
	 * Ödeme geçidi form tipi
	 *
	 * @var string $payment_form_type
	 *
	 * 'card_form'|'empty_form'
	 */
	public $payment_form_type = 'card_form';

	/**
	 * Ödeme adımları hakkında bilgi
	 *
	 * @var array $payment_steps_description
	 */
	public $payment_steps_description = array();

	/**
	 * GPOS_Gateway kurucu fonksiyonu
	 *
	 * @return void
	 */
	public function __construct() {
		$this->fields                    = $this->get_payment_fields();
		$this->test_cards                = $this->get_test_credit_cards();
		$this->description               = $this->get_description();
		$this->payment_steps_description = $this->get_payment_steps_description();
		$this->save_message              = $this->get_save_message();
	}

	/**
	 * Ödeme için gerekli alanların tanımı
	 *
	 * @return array
	 */
	abstract public function get_payment_fields();

	/**
	 * Test ödemesi için kredi kartı
	 *
	 * @return array
	 */
	public function get_test_credit_cards() {
		return array();
	}

	/**
	 * Ödeme adımları için açıklama alanı
	 *
	 * @return array
	 */
	public function get_payment_steps_description() {
		return apply_filters(
			"gpos_gateway_{$this->id}_payment_steps",
			array(
				// translators: %s: Ödeme geçidi ismi.
				sprintf( __( 'When you click on the payment button, you will be directed to the %s payment form.', 'gurmepos' ), $this->title ),
				__( 'Make your payment by entering your card information.', 'gurmepos' ),
				__( 'When the payment process is completed, the payment completed page is displayed.', 'gurmepos' ),
			)
		);
	}

	/**
	 * Ödeme geçidinin taksit hesaplama yöntemi ile çalışan fonksiyon.
	 *
	 * @param float $rate Taksit oranı
	 * @param float $amount Taksitlendirilecek tutar.
	 *
	 * @return float
	 */
	public function installment_rate_calculate( float $rate, float $amount ) {
		$amount += ( $amount / 100 ) * (float) $rate;
		return $amount;
	}

	/**
	 * Ödeme geçidi açıklama alanı
	 */
	public function get_description() {
		return '';
	}

	/**
	 * Ödeme geçidi kayıt sonrası uyarı mesajı
	 */
	public function get_save_message() {
		return array(
			'type'    => 'success',
			'message' => __( 'Settings saved', 'gurmepos' ),
		);
	}
}
