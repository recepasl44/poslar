<?php
/**
 * GurmePOS ile ödeme geçitlerinden alınacak cevapları organize eder.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS cevap sınıfı
 */
class GPOS_Gateway_Response {

	/**
	 * İşlemin gerçekleştiği ödeme geçidi.
	 *
	 * @var string $gateway
	 */
	public $gateway;

	/**
	 * İşlemin başarılı olup olmadığını belirtir.
	 *
	 * @var bool $success
	 */
	public $success = false;

	/**
	 * Yönlendirme yapılmaması durumunda gösterilecek olan HTML içeriğini belirtir.
	 *
	 * @var string $html_content
	 */
	public $html_content;

	/**
	 * İşlem hata mesajı.
	 *
	 * @var string $error_message
	 */
	public $error_message;

	/**
	 * İşlem hata kodu.
	 *
	 * @var string|int $error_code
	 */
	public $error_code = '';

	/**
	 * Ödeme kuruluşu tarafındaki benzersiz numara.
	 *
	 * @var mixed $payment_id
	 */
	public $payment_id;

	/**
	 * Ortak ödeme formu linki
	 *
	 * @var string $common_form_url
	 */
	public $common_form_url;

	/**
	 * Alternatif ödeme linki
	 *
	 * @var string $alternative_payment_url
	 */
	public $alternative_payment_url;

	/**
	 * Ödemenin tamamlanması için onay gerekiyor mu?
	 *
	 * @var boolean $is_pending_payment
	 */
	public $is_pending_payment = false;

	/**
	 * GPOS_Gateway_Response kurucu fonksiyonu.
	 *
	 * @param string $gateway Ödeme geçidi.
	 *
	 * @return void
	 */
	public function __construct( string $gateway ) {
		$this->set_gateway( $gateway );
	}

	/**
	 * İşlemin başarılı olup olmadığını belirten özelliğin değerini ayarlar.
	 *
	 * @param bool $success İşlemin başarılı olup olmadığını belirten değer.
	 */
	public function set_success( $success ) {
		$this->success = $success;
		return $this;
	}

	/**
	 * İşlemin başarılı olup olmadığını belirten özelliğin değerini döndürür.
	 *
	 * @return bool İşlemin başarılı olup olmadığını belirten değer.
	 */
	public function is_success() {
		return $this->success;
	}

	/**
	 * İşlemin geçtiği ödeme geçidini ayarlar.
	 *
	 * @param string $gateway Ödeme geçidi.
	 */
	public function set_gateway( $gateway ) {
		$this->gateway = $gateway;
		return $this;
	}

	/**
	 * İşlemin geçtiği ödeme geçidini getirir.
	 *
	 * @return string Ödeme geçidi.
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * Yönlendirme yapılmaması durumunda gösterilecek olan HTML içeriğini belirten özelliğin değerini ayarlar.
	 *
	 * @param string $html_content Yönlendirme yapılmaması durumunda gösterilecek olan HTML içeriği.
	 */
	public function set_html_content( $html_content ) {
		$this->html_content = $html_content;
		return $this;
	}

	/**
	 * Yönlendirme yapılmaması durumunda gösterilecek olan HTML içeriğini belirten özelliğin değerini döndürür.
	 *
	 * @return string Yönlendirme yapılmaması durumunda gösterilecek olan HTML içeriği.
	 */
	public function get_html_content() {
		return $this->html_content;
	}

	/**
	 * İşlemin hata mesajını ayarlar.
	 *
	 * @param string $error_message Hata mesajı.
	 */
	public function set_error_message( $error_message ) {
		$this->error_message = $error_message;
		return $this;
	}

	/**
	 * İşlemin hata mesajını döndürür.
	 *
	 * @return string Hata mesajı.
	 */
	public function get_error_message() {
		return $this->error_message ? $this->error_message : __( 'Unknown error please contact admin', 'gurmepos' );
	}

	/**
	 * İşlemin hata kodunu ayarlar.
	 *
	 * @param string $error_code Hata mesajı.
	 */
	public function set_error_code( $error_code ) {
		$this->error_code = $error_code;
		return $this;
	}

	/**
	 * İşlemin hata kodunu döndürür.
	 *
	 * @return string Hata mesajı.
	 */
	public function get_error_code() {
		return $this->error_code ? $this->error_code : '';
	}

	/**
	 * İşlemin ödeme kuruluşu tarafındaki benzersiz numarasını ayarlar.
	 *
	 * @param string $payment_id Ödeme kuruluşu tarafındaki benzersiz numara
	 */
	public function set_payment_id( $payment_id ) {
		$this->payment_id = $payment_id;
		return $this;
	}

	/**
	 * İşlemin ödeme kuruluşu tarafındaki benzersiz numarasını döndürür.
	 *
	 * @return string Ödeme kuruluşu tarafındaki benzersiz numara.
	 */
	public function get_payment_id() {
		return $this->payment_id;
	}

	/**
	 * Ortak ödeme formu linki ayarlar.
	 *
	 * @param string $common_form_url Link
	 */
	public function set_common_form_url( $common_form_url ) {
		$this->common_form_url = $common_form_url;
		return $this;
	}

	/**
	 * Ortak ödeme formu linki döndürür.
	 *
	 * @return string $common_form_url Link
	 */
	public function get_common_form_url() {
		return $this->common_form_url;
	}

	/**
	 * Alternatif ödeme sayfası linki ayarlar.
	 *
	 * @param string $alternative_payment_url Link
	 */
	public function set_alternative_payment_url( $alternative_payment_url ) {
		$this->alternative_payment_url = $alternative_payment_url;
		return $this;
	}

	/**
	 * Alternatif ödeme sayfası linki döndürür.
	 *
	 * @return string $alternative_payment_url Link
	 */
	public function get_alternative_payment_url() {
		return $this->alternative_payment_url;
	}

	/**
	 * Ödeme için webhook, history gibi bir onay gerekiyor mu ?
	 *
	 * @param boolean $is_pending_payment Onay gerekiyor mu?
	 */
	public function set_pending_payment( $is_pending_payment ) {
		$this->is_pending_payment = $is_pending_payment;
		return $this;
	}

	/**
	 * Ödeme için webhook, history gibi bir onay gerekiyor mu ?
	 *
	 * @return boolean $is_pending_payment Onay gerekiyor mu?
	 */
	public function is_pending_payment() {
		return $this->is_pending_payment;
	}
}
