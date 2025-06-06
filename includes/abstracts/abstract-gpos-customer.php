<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Müşteri yardımcısını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GPOS_Customer sınıfı.
 */
abstract class GPOS_Customer extends GPOS_Post {

	/**
	 * Sipariş müşteri kimliği
	 *
	 * @var int $order_id
	 */
	protected $customer_id;

	/**
	 * Müşterinin adı
	 *
	 * @var string $customer_first_name
	 */
	protected $customer_first_name;

	/**
	 * Müşterinin soyadı
	 *
	 * @var string $customer_last_name
	 */
	protected $customer_last_name;

	/**
	 * Müşterinin e-posta adresi
	 *
	 * @var string $customer_email
	 */
	protected $customer_email;

	/**
	 * Müşterinin telefon numarası
	 *
	 * @var string $customer_phone
	 */
	protected $customer_phone;

	/**
	 * Müşterinin adresi
	 *
	 * @var string $customer_address
	 */
	protected $customer_address;

	/**
	 * Müşterinin şehir bilgisi
	 *
	 * @var string $customer_city
	 */
	protected $customer_city;

	/**
	 * Müşterinin eyalet/bölge bilgisi
	 *
	 * @var string $customer_state
	 */
	protected $customer_state;

	/**
	 * Müşterinin ülke bilgisi
	 *
	 * @var string $customer_country
	 */
	protected $customer_country;

	/**
	 * Müşterinin posta kodu
	 *
	 * @var string $customer_zipcode
	 */
	protected $customer_zipcode;

	/**
	 * Müşterinin ip adresi
	 *
	 * @var string $customer_ip_address
	 */
	protected $customer_ip_address;

	/**
	 * Siparişin müşteri kimliğini ayarlar
	 *
	 * @param int $value Müşteri kimliği.
	 *
	 * @return $this
	 */
	public function set_customer_id( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Sipariş müşteri kimliğini döndürür
	 *
	 * @return int
	 */
	public function get_customer_id() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşteri adını ayarlar
	 *
	 * @param string $value  Müşteri adı.
	 *
	 * @return $this
	 */
	public function set_customer_first_name( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}


	/**
	 * Müşteri adını döndürür
	 *
	 * @return string
	 */
	public function get_customer_first_name() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşteri soyadını ayarlar
	 *
	 * @param string $value Müşteri soyadı.
	 *
	 * @return $this
	 */
	public function set_customer_last_name( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri soyadını döndürür
	 *
	 * @return string
	 */
	public function get_customer_last_name() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşterinin tam ismini döndürür
	 *
	 * @return string
	 */
	public function get_customer_full_name() {
		return "{$this->get_customer_first_name()} {$this->get_customer_last_name()}";
	}

	/**
	 * Müşteri adresini ayarlar
	 *
	 * @param string $value Müşteri adresi.
	 *
	 * @return $this
	 */
	public function set_customer_address( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri adresini döndürür
	 *
	 * @return string
	 */
	public function get_customer_address() {
		return $this->get_prop( __FUNCTION__ );
	}


	/**
	 * Müşteri telefonunu ayarlar
	 *
	 * @param string $value Müşteri telefon.
	 *
	 * @return $this
	 */
	public function set_customer_phone( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri telefonunu döndürür
	 *
	 * @return string
	 */
	public function get_customer_phone() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşteri mailini ayarlar
	 *
	 * @param string $value Müşteri mail.
	 *
	 * @return $this
	 */
	public function set_customer_email( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri mailini döndürür
	 *
	 * @return string
	 */
	public function get_customer_email() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşteri şehrini ayarlar
	 *
	 * @param string $value Müşteri şehri.
	 *
	 * @return $this
	 */
	public function set_customer_city( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri şehrini döndürür
	 *
	 * @return string
	 */
	public function get_customer_city() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşteri eyalet/bölge bilgisini ayarlar
	 *
	 * @param string $value Müşteri şehri.
	 *
	 * @return $this
	 */
	public function set_customer_state( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri eyalet/bölge bilgisini döndürür
	 *
	 * @return string
	 */
	public function get_customer_state() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşteri ülkesini ayarlar
	 *
	 * @param string $value Müşteri posta kodu.
	 * @return $this
	 */
	public function set_customer_country( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri ülkesini döndürür
	 *
	 * @return string
	 */
	public function get_customer_country() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşteri posta kodunu ayarlar
	 *
	 * @param string $value Müşteri posta kodu.
	 * @return $this
	 */
	public function set_customer_zipcode( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri posta kodunu döndürür
	 *
	 * @return string
	 */
	public function get_customer_zipcode() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Müşteri ip adresini ayarlar
	 *
	 * @param string $value Müşteri ip adresi.
	 * @return $this
	 */
	public function set_customer_ip_address( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Müşteri ip adresini döndürür
	 *
	 * @return string
	 */
	public function get_customer_ip_address() {
		$ip_address = $this->get_prop( __FUNCTION__ );
		return filter_var( $ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ? $ip_address : gpos_get_user_ip();
	}
}
